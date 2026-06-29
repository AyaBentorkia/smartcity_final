<script setup>
import { computed, onMounted, ref } from 'vue'
import { storeToRefs } from 'pinia'
import {
  CheckCircle2, Clock, ClipboardList, TrendingUp,
  ArrowUpRight, Activity, BarChart3, MapPin,
  Sun, Cloud, CloudRain, CloudSnow, Zap, Wind, Droplets, Thermometer,
} from 'lucide-vue-next'
import { use } from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'
import { BarChart, PieChart } from 'echarts/charts'
import { GridComponent, TooltipComponent, LegendComponent } from 'echarts/components'
import VChart from 'vue-echarts'
import { useStatisticsStore } from '../../stores/useStatisticsStore'
import { useAssignments }     from '../../composables/useAssignments'

use([CanvasRenderer, BarChart, PieChart, GridComponent, TooltipComponent, LegendComponent])

// ─── Store stats ──────────────────────────────────────────────────────────────
const statsStore = useStatisticsStore()
const {
  loading,
  totalAssignments,       // int
  avgClosureHours,        // float|null — calculé par SQL côté backend
  assignmentsByStatus,    // { pending: N, in_progress: N, done: N }
  monthlyAssignments,     // [{ month: 'YYYY-MM', total }] — 6 derniers mois
} = storeToRefs(statsStore)

// ─── useAssignments reste pour le tableau "Affectations récentes" ─────────────
// Les stats viennent du store, la liste détaillée reste dans le composable
const { assignments, fetchMyAssignments } = useAssignments()

onMounted(async () => {
  // Les deux en parallèle
  await Promise.all([
    statsStore.fetchAgentStats(),
    fetchMyAssignments(),
  ])

  // Météo
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      pos => { fetchWeather(pos.coords.latitude, pos.coords.longitude); reverseGeocode(pos.coords.latitude, pos.coords.longitude) },
      () => { fetchWeather(36.8065, 10.1815); weatherCity.value = 'Tunis' }
    )
  } else { fetchWeather(36.8065, 10.1815); weatherCity.value = 'Tunis' }
})

// ─── KPIs — viennent du store (backend), plus de calcul JS ────────────────────
const avgClosureFmt = computed(() => {
  const h = avgClosureHours.value; if (!h) return '—'
  return h < 24 ? `${h}h` : `${Math.round(h / 24)}j`
})

const completionRate = computed(() => {
  const done  = assignmentsByStatus.value['done']        ?? 0
  const total = totalAssignments.value
  return total > 0 ? ((done / total) * 100).toFixed(1) : 0
})

const kpis = computed(() => [
  { title: 'Total Affectations', value: totalAssignments.value,                           icon: ClipboardList, color: 'text-blue-500',    bg: 'bg-blue-500/10',    sub: 'Depuis le début',       trend: true  },
  { title: 'En cours',           value: assignmentsByStatus.value['in_progress'] ?? 0,    icon: Clock,         color: 'text-amber-500',   bg: 'bg-amber-500/10',   sub: 'À traiter',             trend: false },
  { title: 'Clôturées',          value: assignmentsByStatus.value['done'] ?? 0,            icon: CheckCircle2,  color: 'text-emerald-500', bg: 'bg-emerald-500/10', sub: 'Terminées avec succès', trend: true  },
  { title: 'Taux de complétion', value: `${completionRate.value}%`,                        icon: TrendingUp,    color: 'text-violet-500',  bg: 'bg-violet-500/10',  sub: `${assignmentsByStatus.value['done'] ?? 0} résolues`, trend: true },
  { title: 'Temps moyen',        value: avgClosureFmt.value,                               icon: Activity,      color: 'text-sky-500',     bg: 'bg-sky-500/10',     sub: 'De résolution',         trend: false },
])

// ─── Tableau des 5 dernières affectations ─────────────────────────────────────
const recentAssignments = computed(() =>
  [...(assignments.value || [])]
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    .slice(0, 5)
)

const formatDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'

// ─── Colors ───────────────────────────────────────────────────────────────────
const FALLBACK_COLORS = ['#ef4444','#f97316','#3b82f6','#22c55e','#10b981','#a855f7']
const MONTHS_SHORT    = { '01':'Jan','02':'Fév','03':'Mar','04':'Avr','05':'Mai','06':'Juin','07':'Jul','08':'Aoû','09':'Sep','10':'Oct','11':'Nov','12':'Déc' }

const tooltip     = { trigger: 'item', backgroundColor: '#ffffff', borderColor: '#e5e7eb', textStyle: { color: '#111827' }, extraCssText: 'box-shadow:0 4px 12px rgba(0,0,0,0.08);border-radius:8px;' }
const axisTooltip = { ...tooltip, trigger: 'axis' }

// ─── Bar — Affectations mensuelles (6 mois) ───────────────────────────────────
// monthlyAssignments = [{ month: '2024-12', total: 8 }, ...]
const barChartOption = computed(() => ({
  tooltip: axisTooltip,
  grid: { left: '2%', right: '2%', bottom: '8%', top: '4%', containLabel: true },
  xAxis: {
    type: 'category',
    data: monthlyAssignments.value.map(d => MONTHS_SHORT[d.month.split('-')[1]] ?? d.month),
    axisLabel: { color: '#9ca3af', fontSize: 11 },
    axisLine: { lineStyle: { color: '#e5e7eb' } },
    axisTick: { show: false },
  },
  yAxis: { type: 'value', axisLabel: { color: '#9ca3af', fontSize: 11 }, splitLine: { lineStyle: { color: '#f3f4f6' } } },
  series: [{
    name: 'Affectations', type: 'bar',
    data: monthlyAssignments.value.map(d => d.total),
    itemStyle: { color: '#10b981', borderRadius: [4,4,0,0] },
    barMaxWidth: 48,
  }],
}))

// ─── Pie — Répartition par statut ─────────────────────────────────────────────
const STATUS_COLORS = { pending: '#f59e0b', in_progress: '#3b82f6', done: '#10b981' }
const STATUS_LABELS = { pending: 'En attente', in_progress: 'En cours', done: 'Clôturée' }

const pieChartOption = computed(() => ({
  tooltip,
  series: [{
    type: 'pie', radius: ['42%', '70%'], center: ['50%', '48%'], label: { show: false },
    data: Object.entries(assignmentsByStatus.value).map(([status, total]) => ({
      name: STATUS_LABELS[status] ?? status,
      value: total,
      itemStyle: { color: STATUS_COLORS[status] ?? '#64748b' },
    })),
    emphasis: { itemStyle: { shadowBlur: 10, shadowOffsetX: 0, shadowColor: 'rgba(0,0,0,0.15)' } },
  }],
}))

const pieLegend = computed(() =>
  Object.entries(assignmentsByStatus.value).map(([status, total]) => ({
    name: STATUS_LABELS[status] ?? status,
    color: STATUS_COLORS[status] ?? '#64748b',
    value: total,
    pct: totalAssignments.value > 0 ? Math.round((total / totalAssignments.value) * 100) : 0,
  }))
)

// ─── Météo ────────────────────────────────────────────────────────────────────
const weather = ref(null); const weatherLoading = ref(true); const weatherError = ref(false); const weatherCity = ref('Ma position')
function getWeatherInfo(code) {
  if (code === 0) return { label: 'Ensoleillé', icon: Sun }; if (code <= 2) return { label: 'Peu nuageux', icon: Sun }
  if (code === 3) return { label: 'Nuageux', icon: Cloud }; if (code <= 49) return { label: 'Brouillard', icon: Cloud }
  if (code <= 69) return { label: 'Pluie', icon: CloudRain }; if (code <= 79) return { label: 'Neige', icon: CloudSnow }
  if (code <= 84) return { label: 'Averses', icon: CloudRain }; if (code <= 94) return { label: 'Orage', icon: Zap }
  return { label: 'Orage fort', icon: Zap }
}
async function fetchWeather(lat, lon) {
  try {
    const url = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,relative_humidity_2m,wind_speed_10m,weathercode,apparent_temperature&hourly=temperature_2m,weathercode&wind_speed_unit=kmh&timezone=auto&forecast_days=1`
    const data = await (await fetch(url)).json(); const c = data.current; const nowHour = new Date().getHours()
    const hours = data.hourly.time.map((t, i) => ({ hour: new Date(t).getHours(), temp: data.hourly.temperature_2m[i], code: data.hourly.weathercode[i] })).filter(h => h.hour >= nowHour).slice(0, 6)
    weather.value = { temp: Math.round(c.temperature_2m), feelsLike: Math.round(c.apparent_temperature), humidity: c.relative_humidity_2m, wind: Math.round(c.wind_speed_10m), code: c.weathercode, info: getWeatherInfo(c.weathercode), hourly: hours }
  } catch { weatherError.value = true } finally { weatherLoading.value = false }
}
async function reverseGeocode(lat, lon) {
  try { const data = await (await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`)).json(); weatherCity.value = data.address?.city || data.address?.town || data.address?.village || 'Ma position' } catch { /* ignore */ }
}
</script>

<template>
  <div class="p-6 space-y-6 bg-gray-50 min-h-screen">

    <!-- ── Loading ── -->
    <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
      <div v-for="n in 5" :key="n" class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm animate-pulse h-28" />
    </div>

    <template v-else>

      <!-- ── KPI Cards ── -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div v-for="kpi in kpis" :key="kpi.title" class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between mb-3">
            <div :class="`p-2 rounded-xl ${kpi.bg}`"><component :is="kpi.icon" :class="`w-5 h-5 ${kpi.color}`" /></div>
            <ArrowUpRight v-if="kpi.trend" class="w-4 h-4 text-emerald-500" />
          </div>
          <p class="text-3xl font-bold text-gray-900 leading-none mb-1">{{ kpi.value }}</p>
          <p class="text-xs font-medium text-gray-700 leading-tight mb-0.5">{{ kpi.title }}</p>
          <p class="text-xs text-gray-400 leading-tight">{{ kpi.sub }}</p>
        </div>
      </div>

      <!-- ── Row 1 : Monthly bar + Status pie ── -->
      <div class="grid lg:grid-cols-2 gap-6">

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <div class="flex items-center gap-2 mb-4">
            <div class="p-1.5 rounded-lg bg-emerald-50"><BarChart3 class="w-4 h-4 text-emerald-500" /></div>
            <span class="font-semibold text-gray-800 text-sm">Affectations par mois (6 mois)</span>
          </div>
          <VChart :option="barChartOption" style="height:280px" autoresize />
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <div class="flex items-center gap-2 mb-4">
            <div class="p-1.5 rounded-lg bg-blue-50"><Activity class="w-4 h-4 text-blue-500" /></div>
            <span class="font-semibold text-gray-800 text-sm">Répartition par statut</span>
          </div>
          <div v-if="Object.keys(assignmentsByStatus).length === 0" class="flex items-center justify-center h-[220px] text-gray-400 text-sm">Aucune donnée</div>
          <template v-else>
            <VChart :option="pieChartOption" style="height:220px" autoresize />
            <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 mt-3">
              <div v-for="item in pieLegend" :key="item.name" class="flex items-center gap-1.5 min-w-0">
                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{ backgroundColor: item.color }" />
                <span class="text-xs text-gray-500 truncate">{{ item.name }}</span>
                <span class="text-xs font-semibold text-gray-700 ml-auto">{{ item.pct }}%</span>
              </div>
            </div>
          </template>
        </div>

      </div>

      <!-- ── Recent Assignments ── -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 flex items-center justify-between" style="background:linear-gradient(135deg,#0F2356,#162d63);">
          <div>
            <h2 class="text-white text-base font-semibold mb-0.5">Affectations récentes</h2>
            <p class="text-white/50 text-xs">Les 5 dernières affectations</p>
          </div>
          <ClipboardList class="w-5 h-5 text-white/40" />
        </div>
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-100 bg-[#F7F8FB]">
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Incident</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Catégorie</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Date début</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Date fin</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="a in recentAssignments" :key="a.id" class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
              <td class="px-6 py-3 font-medium text-[#0F2356] max-w-[200px] truncate">{{ a.incident?.title || '—' }}</td>
              <td class="px-6 py-3 hidden md:table-cell">
                <div class="flex items-center gap-2">
                  <span v-if="a.incident?.category?.color" class="w-2 h-2 rounded-full shrink-0" :style="{ backgroundColor: a.incident.category.color }" />
                  <span class="text-gray-500 text-xs">{{ a.incident?.category?.name || '—' }}</span>
                </div>
              </td>
              <td class="px-6 py-3">
                <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium', a.end_time === null ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700']">
                  <component :is="a.end_time === null ? Clock : CheckCircle2" class="w-3 h-3" />
                  {{ a.end_time === null ? 'En cours' : 'Clôturé' }}
                </span>
              </td>
              <td class="px-6 py-3 hidden lg:table-cell text-gray-400 text-xs">{{ formatDate(a.start_time) }}</td>
              <td class="px-6 py-3 hidden lg:table-cell text-gray-400 text-xs">{{ formatDate(a.end_time) }}</td>
            </tr>
            <tr v-if="recentAssignments.length === 0">
              <td colspan="5" class="px-6 py-12 text-center text-gray-400 text-sm">
                <ClipboardList class="w-8 h-8 mx-auto mb-2 text-gray-200" />Aucune affectation
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ── Météo ── -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center gap-2 mb-5">
          <div class="p-1.5 rounded-lg bg-sky-50"><Sun class="w-4 h-4 text-sky-500" /></div>
          <span class="font-semibold text-gray-800 text-sm">Météo locale</span>
          <div class="flex items-center gap-1 ml-auto text-xs text-gray-400"><MapPin class="w-3 h-3" /><span>{{ weatherCity }}</span></div>
        </div>
        <div v-if="weatherLoading" class="flex items-center justify-center py-10 text-gray-400 gap-2">
          <div class="w-5 h-5 border-2 border-sky-300 border-t-transparent rounded-full animate-spin" /><span class="text-sm">Chargement météo…</span>
        </div>
        <div v-else-if="weatherError" class="flex items-center justify-center py-10 text-gray-400 text-sm gap-2"><Cloud class="w-5 h-5" /><span>Données météo indisponibles.</span></div>
        <div v-else-if="weather" class="flex flex-col lg:flex-row gap-6">
          <div class="flex items-center gap-6 flex-1">
            <div class="flex flex-col items-center justify-center bg-sky-50 rounded-2xl p-5 min-w-[120px]">
              <component :is="weather.info.icon" class="w-10 h-10 text-sky-500 mb-1" />
              <span class="text-4xl font-bold text-gray-900">{{ weather.temp }}°</span>
              <span class="text-xs text-gray-400 mt-1">{{ weather.info.label }}</span>
            </div>
            <div class="grid grid-cols-2 gap-x-8 gap-y-3">
              <div class="flex items-center gap-2"><div class="p-1.5 rounded-lg bg-orange-50"><Thermometer class="w-4 h-4 text-orange-400" /></div><div><p class="text-xs text-gray-400">Ressenti</p><p class="text-sm font-semibold text-gray-700">{{ weather.feelsLike }}°C</p></div></div>
              <div class="flex items-center gap-2"><div class="p-1.5 rounded-lg bg-blue-50"><Droplets class="w-4 h-4 text-blue-400" /></div><div><p class="text-xs text-gray-400">Humidité</p><p class="text-sm font-semibold text-gray-700">{{ weather.humidity }}%</p></div></div>
              <div class="flex items-center gap-2"><div class="p-1.5 rounded-lg bg-teal-50"><Wind class="w-4 h-4 text-teal-400" /></div><div><p class="text-xs text-gray-400">Vent</p><p class="text-sm font-semibold text-gray-700">{{ weather.wind }} km/h</p></div></div>
            </div>
          </div>
          <div class="hidden lg:block w-px bg-gray-100 self-stretch" />
          <div class="flex items-center gap-3 flex-1 justify-start lg:justify-center overflow-x-auto pb-1">
            <div v-for="h in weather.hourly" :key="h.hour" class="flex flex-col items-center gap-1.5 min-w-[52px] bg-gray-50 rounded-xl px-3 py-2.5">
              <span class="text-xs text-gray-400 font-medium">{{ String(h.hour).padStart(2, '0') }}h</span>
              <component :is="getWeatherInfo(h.code).icon" class="w-4 h-4 text-sky-400" />
              <span class="text-sm font-semibold text-gray-700">{{ Math.round(h.temp) }}°</span>
            </div>
          </div>
        </div>
      </div>

    </template>
  </div>
</template>