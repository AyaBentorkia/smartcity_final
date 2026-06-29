<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import {
  AlertTriangle, CheckCircle, Clock, TrendingUp,
  BarChart3, PieChart as PieChartIcon, Activity, ArrowUpRight,
  Wind, Droplets, Thermometer, CloudRain, Sun, Cloud, CloudSnow, Zap, MapPin,
  Sparkles, RefreshCw, Lightbulb, ChevronDown, Calendar,
} from 'lucide-vue-next'
import { use } from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'
import { PieChart, BarChart, LineChart } from 'echarts/charts'
import { GridComponent, TooltipComponent, LegendComponent } from 'echarts/components'
import VChart from 'vue-echarts'
import IncidentMap from '../../components/Dashboard/Incidentmap.vue'
import { useStatisticsStore } from '../../stores/useStatisticsStore'
import { useZones }                from '../../composables/useZones'
import { useIncidentPrediction }   from '../../composables/useIncidentPrediction'
import { useCategories }           from '../../composables/useCategories' // à adapter selon votre projet

use([CanvasRenderer, PieChart, BarChart, LineChart, GridComponent, TooltipComponent, LegendComponent])

// ─── Store stats ──────────────────────────────────────────────────────────────
const statsStore = useStatisticsStore()
const {
  loading,
  municipalTotalIncidents,
  municipalTotalZones,
  municipalTotalAgents,
  municipalAvgResolution,
  municipalIncidentsByStatus,
  municipalIncidentsByCategory,
  municipalIncidentsByZone,
  municipalAgentsByStatus,
  municipalAssignmentsByStatus,
  municipalMonthlyTrend,
} = storeToRefs(statsStore)

// ─── Zones + Catégories + Prédiction IA ──────────────────────────────────────
const { zones, fetchZones }         = useZones()
const { categories, fetchCategories } = useCategories()

const {
  prediction,
  isPredicting,
  error: predictionError,
  predict,
  loadFromCache,
} = useIncidentPrediction()

const selectedZoneId     = ref(null)
const selectedCategoryId = ref(null)
const selectedPeriod     = ref(getTodayIso())

// Date par défaut = aujourd'hui
function getTodayIso() {
  return new Date().toISOString().slice(0, 10)
}

watch(selectedZoneId, (zoneId) => { if (zoneId) loadFromCache(zoneId) })

const onZoneChange     = (e) => { selectedZoneId.value = Number(e.target.value) || null }
const onCategoryChange = (e) => { selectedCategoryId.value = Number(e.target.value) || null }

const handlePredict = async () => {
  if (!selectedZoneId.value || !selectedCategoryId.value || !selectedPeriod.value) return
  await predict(selectedZoneId.value, selectedCategoryId.value, selectedPeriod.value)
}

// ── Couleurs selon risque (probabilite sur [0,1]) ─────────────────────────────
// Le backend retourne risque : 'faible' | 'modéré' | 'élevé' | 'critique'
const getRiskColors = (risque) => {
  const r = risque?.toLowerCase()
  if (r === 'critique') return { bg: 'bg-red-50',    border: 'border-red-200',    text: 'text-red-700',    bar: 'bg-red-500',    badge: 'bg-red-100 border-red-200 text-red-700' }
  if (r === 'élevé')   return { bg: 'bg-orange-50', border: 'border-orange-200', text: 'text-orange-700', bar: 'bg-orange-500', badge: 'bg-orange-100 border-orange-200 text-orange-700' }
  if (r === 'modéré')  return { bg: 'bg-amber-50',  border: 'border-amber-200',  text: 'text-amber-700',  bar: 'bg-amber-400',  badge: 'bg-amber-100 border-amber-200 text-amber-700' }
  return { bg: 'bg-green-50', border: 'border-green-200', text: 'text-green-700', bar: 'bg-green-500', badge: 'bg-green-100 border-green-200 text-green-700' }
}

const predColors = computed(() => prediction.value ? getRiskColors(prediction.value.risque) : null)

// ── Probabilité en pourcentage (le backend retourne 0-1 ou 0-100 selon l'IA) ─
const probabilitePct = computed(() => {
  const p = prediction.value?.probabilite
  if (p == null) return 0
  // normalise : si < 1 on multiplie par 100
  return p <= 1 ? Math.round(p * 100) : Math.round(p)
})

// ── Date de période formatée ──────────────────────────────────────────────────
const periodFormatted = computed(() => {
  if (!prediction.value?.period) return ''
  return new Date(prediction.value.period).toLocaleDateString('fr-TN', {
    day: 'numeric', month: 'long', year: 'numeric',
  })
})

const analyzedAtFormatted = computed(() => {
  if (!prediction.value?.analyzed_at) return ''
  return new Date(prediction.value.analyzed_at).toLocaleDateString('fr-TN', {
    day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit',
  })
})

// ── Météo issue du champ meteo (JSON retourné par l'IA) ──────────────────────
// Le champ meteo peut contenir : { temperature, humidity, wind_speed, precipitation, description, ... }
// On l'affiche tel quel si présent.
const meteo = computed(() => prediction.value?.meteo ?? null)

// ── Météo locale (Open-Meteo) ─────────────────────────────────────────────────
const weather = ref(null)
const weatherLoading = ref(true)
const weatherError   = ref(false)
const weatherCity    = ref('Ma position')

function getWeatherInfo(code) {
  if (code === 0)   return { label: 'Ensoleillé',  icon: Sun }
  if (code <= 2)    return { label: 'Peu nuageux', icon: Sun }
  if (code === 3)   return { label: 'Nuageux',     icon: Cloud }
  if (code <= 49)   return { label: 'Brouillard',  icon: Cloud }
  if (code <= 69)   return { label: 'Pluie',       icon: CloudRain }
  if (code <= 79)   return { label: 'Neige',       icon: CloudSnow }
  if (code <= 84)   return { label: 'Averses',     icon: CloudRain }
  if (code <= 94)   return { label: 'Orage',       icon: Zap }
  return { label: 'Orage fort', icon: Zap }
}

async function fetchWeather(lat, lon) {
  try {
    const url = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,relative_humidity_2m,wind_speed_10m,weathercode,apparent_temperature&hourly=temperature_2m,weathercode&wind_speed_unit=kmh&timezone=auto&forecast_days=1`
    const data = await (await fetch(url)).json()
    const c = data.current
    const nowHour = new Date().getHours()
    const hours = data.hourly.time
      .map((t, i) => ({ hour: new Date(t).getHours(), temp: data.hourly.temperature_2m[i], code: data.hourly.weathercode[i] }))
      .filter(h => h.hour >= nowHour)
      .slice(0, 6)
    weather.value = {
      temp: Math.round(c.temperature_2m),
      feelsLike: Math.round(c.apparent_temperature),
      humidity: c.relative_humidity_2m,
      wind: Math.round(c.wind_speed_10m),
      code: c.weathercode,
      info: getWeatherInfo(c.weathercode),
      hourly: hours,
    }
  } catch { weatherError.value = true }
  finally { weatherLoading.value = false }
}

async function reverseGeocode(lat, lon) {
  try {
    const data = await (await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`)).json()
    weatherCity.value = data.address?.city || data.address?.town || data.address?.village || 'Ma position'
  } catch { /* ignore */ }
}

// ─── onMounted ────────────────────────────────────────────────────────────────
onMounted(() => {
  statsStore.fetchMunicipalStats()
  fetchZones().then(() => {
    if (zones.value.length > 0) selectedZoneId.value = zones.value[0].id
  })
  fetchCategories().then(() => {
    if (categories.value.length > 0) selectedCategoryId.value = categories.value[0].id
  })
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      pos => {
        fetchWeather(pos.coords.latitude, pos.coords.longitude)
        reverseGeocode(pos.coords.latitude, pos.coords.longitude)
      },
      () => { fetchWeather(36.8065, 10.1815); weatherCity.value = 'Tunis' }
    )
  } else {
    fetchWeather(36.8065, 10.1815)
    weatherCity.value = 'Tunis'
  }
})

// ─── KPIs ─────────────────────────────────────────────────────────────────────
const resolvedCount  = computed(() => municipalIncidentsByStatus.value['resolved'] ?? 0)
const resolutionRate = computed(() =>
  municipalTotalIncidents.value > 0
    ? ((resolvedCount.value / municipalTotalIncidents.value) * 100).toFixed(1)
    : 0
)
const avgResolutionFmt = computed(() => {
  const h = municipalAvgResolution.value
  if (!h) return '—'
  return h < 24 ? `${h}h` : `${Math.round(h / 24)}j`
})

const kpis = computed(() => [
  { title: 'Total Incidents',    value: municipalTotalIncidents.value, icon: AlertTriangle, color: 'text-blue-500',    bg: 'bg-blue-500/10',    sub: 'Dans ma municipalité',         trend: false },
  { title: 'Taux de résolution', value: `${resolutionRate.value}%`,    icon: CheckCircle,   color: 'text-emerald-500', bg: 'bg-emerald-500/10', sub: `${resolvedCount.value} résolus`, trend: true  },
  { title: 'In progress',        value: municipalIncidentsByStatus.value['in_progress'] ?? 0, icon: Clock, color: 'text-amber-500', bg: 'bg-amber-500/10', sub: 'À traiter', trend: false },
  { title: 'Temps moyen',        value: avgResolutionFmt.value,        icon: TrendingUp,    color: 'text-violet-500',  bg: 'bg-violet-500/10',  sub: 'De résolution',                trend: false },
  { title: 'Zones / Agents',     value: `${municipalTotalZones.value} / ${municipalTotalAgents.value}`, icon: Activity, color: 'text-sky-500', bg: 'bg-sky-500/10', sub: 'Zones et agents actifs', trend: false },
])

// ─── Colors & helpers ─────────────────────────────────────────────────────────
const FALLBACK_COLORS = ['#ef4444','#f97316','#3b82f6','#22c55e','#10b981','#a855f7','#14b8a6','#f59e0b']
const MONTHS_SHORT    = { '01':'Jan','02':'Fév','03':'Mar','04':'Avr','05':'Mai','06':'Juin','07':'Jul','08':'Aoû','09':'Sep','10':'Oct','11':'Nov','12':'Déc' }

const tooltip     = { trigger: 'item', backgroundColor: '#ffffff', borderColor: '#e5e7eb', textStyle: { color: '#111827' }, extraCssText: 'box-shadow:0 4px 12px rgba(0,0,0,0.08);border-radius:8px;' }
const axisTooltip = { ...tooltip, trigger: 'axis' }

// ─── Pie — Catégories ─────────────────────────────────────────────────────────
const categoryChartOption = computed(() => ({
  tooltip,
  series: [{
    type: 'pie', radius: ['42%', '70%'], center: ['50%', '48%'], label: { show: false },
    data: municipalIncidentsByCategory.value.map((d, i) => ({
      name: d.name, value: d.total,
      itemStyle: { color: FALLBACK_COLORS[i % FALLBACK_COLORS.length] },
    })),
    emphasis: { itemStyle: { shadowBlur: 10, shadowOffsetX: 0, shadowColor: 'rgba(0,0,0,0.15)' } },
  }],
}))

const categoryLegend = computed(() =>
  municipalIncidentsByCategory.value.map((d, i) => ({
    name: d.name, value: d.total,
    color: FALLBACK_COLORS[i % FALLBACK_COLORS.length],
    pct: municipalTotalIncidents.value > 0 ? Math.round((d.total / municipalTotalIncidents.value) * 100) : 0,
  }))
)

// ─── Bar — Par zone ───────────────────────────────────────────────────────────
const zoneChartOption = computed(() => ({
  tooltip: axisTooltip,
  grid: { left: '2%', right: '2%', bottom: '8%', top: '4%', containLabel: true },
  xAxis: { type: 'category', data: municipalIncidentsByZone.value.map(d => d.name), axisLabel: { color: '#9ca3af', fontSize: 11 }, axisLine: { lineStyle: { color: '#e5e7eb' } }, axisTick: { show: false } },
  yAxis: { type: 'value', axisLabel: { color: '#9ca3af', fontSize: 11 }, splitLine: { lineStyle: { color: '#f3f4f6' } } },
  series: [{ name: 'Incidents', type: 'bar', data: municipalIncidentsByZone.value.map(d => d.total), itemStyle: { color: '#3b82f6', borderRadius: [4,4,0,0] }, barMaxWidth: 48 }],
}))

// ─── Line — Tendance mensuelle ─────────────────────────────────────────────────
const lineChartOption = computed(() => ({
  tooltip: axisTooltip,
  grid: { left: '2%', right: '4%', bottom: '8%', top: '4%', containLabel: true },
  xAxis: { type: 'category', data: municipalMonthlyTrend.value.map(d => MONTHS_SHORT[d.month.split('-')[1]] ?? d.month), axisLabel: { color: '#9ca3af', fontSize: 11 }, axisLine: { lineStyle: { color: '#e5e7eb' } }, axisTick: { show: false } },
  yAxis: { type: 'value', axisLabel: { color: '#9ca3af', fontSize: 11 }, splitLine: { lineStyle: { color: '#f3f4f6' } } },
  series: [{ name: 'Incidents', type: 'line', smooth: true, data: municipalMonthlyTrend.value.map(d => d.total), itemStyle: { color: '#10b981' }, lineStyle: { width: 2, color: '#10b981' }, symbol: 'circle', symbolSize: 7, areaStyle: { opacity: 0.07, color: '#10b981' } }],
}))
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

      <!-- ── Row 1 : Category pie + Zone bar ── -->
      <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <div class="flex items-center gap-2 mb-4">
            <div class="p-1.5 rounded-lg bg-blue-50"><PieChartIcon class="w-4 h-4 text-blue-500" /></div>
            <span class="font-semibold text-gray-800 text-sm">Distribution par catégorie</span>
          </div>
          <VChart :option="categoryChartOption" style="height:220px" autoresize />
          <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 mt-3">
            <div v-for="item in categoryLegend" :key="item.name" class="flex items-center gap-1.5 min-w-0">
              <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{ backgroundColor: item.color }" />
              <span class="text-xs text-gray-500 truncate">{{ item.name }}</span>
              <span class="text-xs font-semibold text-gray-700 ml-auto">{{ item.pct }}%</span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <div class="flex items-center gap-2 mb-4">
            <div class="p-1.5 rounded-lg bg-emerald-50"><BarChart3 class="w-4 h-4 text-emerald-500" /></div>
            <span class="font-semibold text-gray-800 text-sm">Distribution par zone</span>
          </div>
          <VChart :option="zoneChartOption" style="height:280px" autoresize />
        </div>
      </div>

      <!-- ── Row 2 : Monthly trend ── -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center gap-2 mb-4">
          <div class="p-1.5 rounded-lg bg-violet-50"><Activity class="w-4 h-4 text-violet-500" /></div>
          <span class="font-semibold text-gray-800 text-sm">Tendance mensuelle (12 mois)</span>
        </div>
        <VChart :option="lineChartOption" style="height:260px" autoresize />
      </div>

      <!-- ── AI Incident Prediction ── -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-md" style="background:linear-gradient(135deg,#7c3aed,#a855f7);">
              <Sparkles class="w-5 h-5 text-white" />
            </div>
            <div>
              <h3 class="text-sm font-semibold text-gray-900">Prédiction d'Incidents IA</h3>
              <p class="text-xs text-gray-400">Probabilité d'incidents par zone, catégorie et période</p>
            </div>
          </div>

          <!-- Contrôles -->
          <div class="flex items-center gap-3 flex-wrap">

            <!-- Zone -->
            <div class="relative">
              <MapPin class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
              <select :value="selectedZoneId" @change="onZoneChange"
                      class="appearance-none bg-white border border-gray-200 rounded-lg pl-9 pr-8 py-2 text-sm focus:outline-none focus:border-gray-400 cursor-pointer"
                      style="color:#0F2356;">
                <option v-if="zones.length === 0" value="">Chargement…</option>
                <option v-for="z in zones" :key="z.id" :value="z.id">{{ z.name }}</option>
              </select>
              <ChevronDown class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" />
            </div>

            <!-- Catégorie -->
            <div class="relative">
              <select :value="selectedCategoryId" @change="onCategoryChange"
                      class="appearance-none bg-white border border-gray-200 rounded-lg px-3 pr-8 py-2 text-sm focus:outline-none focus:border-gray-400 cursor-pointer"
                      style="color:#0F2356;">
                <option v-if="categories.length === 0" value="">Catégories…</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
              <ChevronDown class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" />
            </div>

            <!-- Période (date) -->
            <div class="relative">
              <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
              <input v-model="selectedPeriod" type="date"
                     class="bg-white border border-gray-200 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:border-gray-400 cursor-pointer"
                     style="color:#0F2356;" />
            </div>

            <!-- Bouton -->
            <button
              :disabled="isPredicting || !selectedZoneId || !selectedCategoryId || !selectedPeriod"
              class="flex items-center gap-2 px-4 py-2 text-white rounded-lg text-sm shadow-sm disabled:opacity-40 disabled:cursor-not-allowed"
              style="background:#0F2356;"
              @click="handlePredict">
              <RefreshCw :class="['w-4 h-4', { 'animate-spin': isPredicting }]" />
              {{ isPredicting ? 'Prédiction en cours…' : 'Lancer la Prédiction' }}
            </button>
          </div>
        </div>

        <!-- Erreur -->
        <div v-if="predictionError" class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
          {{ predictionError }}
        </div>

        <!-- Empty state -->
        <div v-if="!prediction && !isPredicting" class="text-center py-12 border-2 border-dashed border-gray-200 rounded-xl">
          <Sparkles class="w-12 h-12 text-gray-200 mx-auto mb-3" />
          <p class="text-sm text-gray-500 font-medium">Aucune prédiction disponible</p>
          <p class="text-xs text-gray-400 mt-1">Sélectionnez une zone, une catégorie, une période puis cliquez sur "Lancer la Prédiction"</p>
        </div>

        <!-- Loading state -->
        <div v-else-if="isPredicting" class="text-center py-12">
          <div class="w-14 h-14 border-4 border-gray-100 rounded-full mx-auto mb-4 animate-spin" style="border-top-color:#0F2356;" />
          <p class="text-sm font-semibold text-gray-700">Prédiction IA en cours…</p>
        </div>

        <!-- ── Résultat ── -->
        <div v-else-if="prediction" class="space-y-5">

          <!-- Header zone + badge risque -->
          <div class="flex items-start justify-between pb-4 border-b border-gray-100 flex-wrap gap-3">
            <div>
              <div class="flex items-center gap-3 mb-1.5 flex-wrap">
                <MapPin class="w-5 h-5 flex-shrink-0" style="color:#CC1525;" />
                <h4 class="text-lg font-semibold text-gray-900">{{ prediction.zone_name }}</h4>
                <span :class="['px-3 py-0.5 text-xs font-bold uppercase tracking-wide rounded border', predColors?.badge]">
                  {{ prediction.risque }}
                </span>
              </div>
              <p class="text-xs text-gray-400 ml-8">
                Catégorie : <span class="font-medium text-gray-600">{{ prediction.category }}</span>
                · Période : <span class="font-medium text-gray-600">{{ periodFormatted }}</span>
                · Semaine {{ prediction.semaine }}
              </p>
              <p v-if="prediction.triggered_by" class="text-xs text-gray-400 ml-8 mt-0.5">
                Déclenché par : <span class="font-medium text-gray-600">{{ prediction.triggered_by }}</span>
                · Analysé le {{ analyzedAtFormatted }}
              </p>
            </div>
          </div>

          <!-- Probabilité (barre sur 100 %) -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Probabilité d'incident</span>
              <span :class="['text-2xl font-bold', predColors?.text]">
                {{ probabilitePct }}<span class="text-base font-medium text-gray-400">%</span>
              </span>
            </div>
            <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
              <div :class="['h-full rounded-full transition-all duration-700', predColors?.bar]"
                   :style="{ width: `${probabilitePct}%` }" />
            </div>
          </div>

          <!-- Stats : météo IA + fériés -->
          <div v-if="meteo || prediction.est_ferie != null" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <template v-if="meteo">
              <div v-for="(val, key) in meteo" :key="key" class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                <p class="text-xs text-gray-400 mb-1 capitalize">{{ key.replace(/_/g, ' ') }}</p>
                <p class="text-sm font-semibold text-gray-700">{{ val }}</p>
              </div>
            </template>
            <div v-if="prediction.est_ferie != null" class="bg-gray-50 rounded-xl p-3 border border-gray-100">
              <p class="text-xs text-gray-400 mb-1 flex items-center gap-1"><Calendar class="w-3.5 h-3.5" /> Jour férié</p>
              <p class="text-xl font-bold text-gray-800">{{ prediction.est_ferie ? 'Oui' : 'Non' }}</p>
            </div>
          </div>

          <!-- Explication IA -->
          <div v-if="prediction.explication" class="p-5 rounded-xl" style="background:linear-gradient(135deg,#0F2356,#162d63);">
            <div class="flex items-center gap-2 mb-3">
              <Lightbulb class="w-5 h-5 flex-shrink-0" style="color:#CC1525;" />
              <h5 class="text-sm font-bold text-white uppercase tracking-wider">Explication IA</h5>
            </div>
            <p class="text-sm text-white/90 leading-relaxed whitespace-pre-line">{{ prediction.explication }}</p>
          </div>

        </div>
      </div>

      <!-- ── Météo locale ── -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center gap-2 mb-5">
          <div class="p-1.5 rounded-lg bg-sky-50"><Sun class="w-4 h-4 text-sky-500" /></div>
          <span class="font-semibold text-gray-800 text-sm">Météo locale</span>
          <div class="flex items-center gap-1 ml-auto text-xs text-gray-400">
            <MapPin class="w-3 h-3" /><span>{{ weatherCity }}</span>
          </div>
        </div>
        <div v-if="weatherLoading" class="flex items-center justify-center py-10 text-gray-400 gap-2">
          <div class="w-5 h-5 border-2 border-sky-300 border-t-transparent rounded-full animate-spin" />
          <span class="text-sm">Chargement météo…</span>
        </div>
        <div v-else-if="weatherError" class="flex items-center justify-center py-10 text-gray-400 text-sm gap-2">
          <Cloud class="w-5 h-5" /><span>Données météo indisponibles.</span>
        </div>
        <div v-else-if="weather" class="flex flex-col lg:flex-row gap-6">
          <div class="flex items-center gap-6 flex-1">
            <div class="flex flex-col items-center justify-center bg-sky-50 rounded-2xl p-5 min-w-[120px]">
              <component :is="weather.info.icon" class="w-10 h-10 text-sky-500 mb-1" />
              <span class="text-4xl font-bold text-gray-900">{{ weather.temp }}°</span>
              <span class="text-xs text-gray-400 mt-1">{{ weather.info.label }}</span>
            </div>
            <div class="grid grid-cols-2 gap-x-8 gap-y-3">
              <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-orange-50"><Thermometer class="w-4 h-4 text-orange-400" /></div>
                <div><p class="text-xs text-gray-400">Ressenti</p><p class="text-sm font-semibold text-gray-700">{{ weather.feelsLike }}°C</p></div>
              </div>
              <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-blue-50"><Droplets class="w-4 h-4 text-blue-400" /></div>
                <div><p class="text-xs text-gray-400">Humidité</p><p class="text-sm font-semibold text-gray-700">{{ weather.humidity }}%</p></div>
              </div>
              <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-teal-50"><Wind class="w-4 h-4 text-teal-400" /></div>
                <div><p class="text-xs text-gray-400">Vent</p><p class="text-sm font-semibold text-gray-700">{{ weather.wind }} km/h</p></div>
              </div>
            </div>
          </div>
          <div class="hidden lg:block w-px bg-gray-100 self-stretch" />
          <div class="flex items-center gap-3 flex-1 justify-start lg:justify-center overflow-x-auto pb-1">
            <div v-for="h in weather.hourly" :key="h.hour"
                 class="flex flex-col items-center gap-1.5 min-w-[52px] bg-gray-50 rounded-xl px-3 py-2.5">
              <span class="text-xs text-gray-400 font-medium">{{ String(h.hour).padStart(2, '0') }}h</span>
              <component :is="getWeatherInfo(h.code).icon" class="w-4 h-4 text-sky-400" />
              <span class="text-sm font-semibold text-gray-700">{{ Math.round(h.temp) }}°</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Map ── -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <IncidentMap />
      </div>

    </template>
  </div>
</template>