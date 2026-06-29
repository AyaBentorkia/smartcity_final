<script setup>
import { computed, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import {
  AlertTriangle, CheckCircle, Users, Tag, Building2,
  BarChart3, PieChart as PieChartIcon, Activity, ArrowUpRight, TrendingUp,
} from 'lucide-vue-next'
import { use } from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'
import { PieChart, BarChart, LineChart } from 'echarts/charts'
import { GridComponent, TooltipComponent, LegendComponent } from 'echarts/components'
import VChart from 'vue-echarts'
import Incidentmap from '../../components/Dashboard/Incidentmap.vue'
import { useStatisticsStore } from '../../stores/useStatisticsStore'

use([CanvasRenderer, PieChart, BarChart, LineChart, GridComponent, TooltipComponent, LegendComponent])

// ─── Store ────────────────────────────────────────────────────────────────────
const statsStore = useStatisticsStore()
const {
  loading,
  // KPI compteurs
  totalIncidents,
  totalMunicipalities,
  totalAgents,
  avgResolutionHours,
  // Distributions
  incidentsByStatus,
  incidentsByCategory,    // [{ name, total }]
  incidentsByMunicipality,// [{ name, total }]
  incidentsByZone,        // [{ name, total }]
  usersByRole,
  globalMonthlyTrend,     // [{ month: 'YYYY-MM', total }]
} = storeToRefs(statsStore)

onMounted(() => {
  statsStore.fetchGlobalStats()
})

// ─── KPIs ─────────────────────────────────────────────────────────────────────
const resolvedCount    = computed(() => incidentsByStatus.value['resolved']    ?? 0)
const totalCount       = computed(() => totalIncidents.value)
const resolutionRate   = computed(() =>
  totalCount.value > 0 ? ((resolvedCount.value / totalCount.value) * 100).toFixed(1) : 0
)
const avgResolutionFmt = computed(() => {
  const h = avgResolutionHours.value
  if (!h) return '—'
  return h < 24 ? `${h}h` : `${Math.round(h / 24)}j`
})

const kpis = computed(() => [
  { title: 'Total Incidents',       value: totalIncidents.value,       icon: AlertTriangle, color: 'text-blue-500',    bg: 'bg-blue-500/10',    sub: 'All municipalities',        trend: false },
  { title: 'Global Resolution Rate',value: `${resolutionRate.value}%`, icon: CheckCircle,   color: 'text-emerald-500', bg: 'bg-emerald-500/10', sub: `${resolvedCount.value} resolved`, trend: true },
  { title: 'Total Agents',          value: totalAgents.value,          icon: Users,         color: 'text-amber-500',   bg: 'bg-amber-500/10',   sub: 'Active field agents',       trend: false },
  { title: 'Municipalities',        value: totalMunicipalities.value,  icon: Building2,     color: 'text-sky-500',     bg: 'bg-sky-500/10',     sub: 'Active territories',        trend: false },
  { title: 'Avg Resolution Time',   value: avgResolutionFmt.value,     icon: TrendingUp,    color: 'text-violet-500',  bg: 'bg-violet-500/10',  sub: 'Per incident',              trend: false },
])

// ─── Colors ───────────────────────────────────────────────────────────────────
const FALLBACK_COLORS = ['#ef4444','#f97316','#3b82f6','#22c55e','#10b981','#a855f7','#14b8a6','#f59e0b','#ec4899','#64748b']

// ─── Shared tooltip style ─────────────────────────────────────────────────────
const tooltip     = { trigger: 'item', backgroundColor: '#ffffff', borderColor: '#e5e7eb', textStyle: { color: '#111827' }, extraCssText: 'box-shadow:0 4px 12px rgba(0,0,0,0.08);border-radius:8px;' }
const axisTooltip = { ...tooltip, trigger: 'axis' }

// ─── Pie — Distribution par catégorie ─────────────────────────────────────────
// incidentsByCategory = [{ name: 'Voirie', total: 42 }, ...]
const categoryChartOption = computed(() => ({
  tooltip,
  series: [{
    type: 'pie', radius: ['42%', '70%'], center: ['50%', '48%'],
    label: { show: false },
    data: incidentsByCategory.value.map((d, i) => ({
      name: d.name, value: d.total,
      itemStyle: { color: FALLBACK_COLORS[i % FALLBACK_COLORS.length] },
    })),
    emphasis: { itemStyle: { shadowBlur: 10, shadowOffsetX: 0, shadowColor: 'rgba(0,0,0,0.15)' } },
  }],
}))

const categoryLegend = computed(() =>
  incidentsByCategory.value.map((d, i) => ({
    name: d.name, value: d.total,
    color: FALLBACK_COLORS[i % FALLBACK_COLORS.length],
    pct: totalIncidents.value > 0 ? Math.round((d.total / totalIncidents.value) * 100) : 0,
  }))
)

// ─── Bar — Distribution par municipalité (horizontal) ─────────────────────────
// incidentsByMunicipality = [{ name: 'Tunis', total: 120 }, ...]
const municipalityChartOption = computed(() => ({
  tooltip: axisTooltip,
  grid: { left: '2%', right: '4%', bottom: '8%', top: '4%', containLabel: true },
  xAxis: {
    type: 'value',
    axisLabel: { color: '#9ca3af', fontSize: 11 },
    splitLine: { lineStyle: { color: '#f3f4f6' } },
  },
  yAxis: {
    type: 'category',
    data: incidentsByMunicipality.value.map(d => d.name),
    axisLabel: { color: '#9ca3af', fontSize: 11 },
    axisLine: { lineStyle: { color: '#e5e7eb' } },
    axisTick: { show: false },
  },
  series: [{
    name: 'Incidents',
    type: 'bar',
    data: incidentsByMunicipality.value.map(d => d.total),
    itemStyle: { color: '#3b82f6', borderRadius: [0, 4, 4, 0] },
    barMaxWidth: 20,
  }],
}))

// ─── Line — Tendance mensuelle ─────────────────────────────────────────────────
// globalMonthlyTrend = [{ month: '2024-01', total: 12 }, ...]
const MONTHS_SHORT = { '01':'Jan','02':'Fév','03':'Mar','04':'Avr','05':'Mai','06':'Juin','07':'Jul','08':'Aoû','09':'Sep','10':'Oct','11':'Nov','12':'Déc' }

const lineChartOption = computed(() => ({
  tooltip: axisTooltip,
  grid: { left: '2%', right: '4%', bottom: '8%', top: '4%', containLabel: true },
  xAxis: {
    type: 'category',
    data: globalMonthlyTrend.value.map(d => MONTHS_SHORT[d.month.split('-')[1]] ?? d.month),
    axisLabel: { color: '#9ca3af', fontSize: 11 },
    axisLine: { lineStyle: { color: '#e5e7eb' } },
    axisTick: { show: false },
  },
  yAxis: {
    type: 'value',
    axisLabel: { color: '#9ca3af', fontSize: 11 },
    splitLine: { lineStyle: { color: '#f3f4f6' } },
  },
  series: [{
    name: 'Incidents', type: 'line', smooth: true,
    data: globalMonthlyTrend.value.map(d => d.total),
    itemStyle: { color: '#3b82f6' },
    lineStyle: { width: 2, color: '#3b82f6' },
    symbol: 'circle', symbolSize: 7,
    areaStyle: { opacity: 0.08, color: '#3b82f6' },
  }],
}))

// ─── Donut — Répartition par statut ───────────────────────────────────────────
const STATUS_COLORS = { reported: '#f59e0b', in_progress: '#3b82f6', resolved: '#10b981', validated: '#a855f7' }
const STATUS_LABELS = { reported: 'Signalé', in_progress: 'En cours', resolved: 'Résolu', validated: 'Validé' }

const statusChartOption = computed(() => ({
  tooltip,
  series: [{
    type: 'pie', radius: ['42%', '70%'], center: ['50%', '48%'],
    label: { show: false },
    data: Object.entries(incidentsByStatus.value).map(([status, total]) => ({
      name: STATUS_LABELS[status] ?? status,
      value: total,
      itemStyle: { color: STATUS_COLORS[status] ?? '#64748b' },
    })),
    emphasis: { itemStyle: { shadowBlur: 10, shadowOffsetX: 0, shadowColor: 'rgba(0,0,0,0.15)' } },
  }],
}))

const statusLegend = computed(() =>
  Object.entries(incidentsByStatus.value).map(([status, total]) => ({
    name: STATUS_LABELS[status] ?? status,
    color: STATUS_COLORS[status] ?? '#64748b',
    value: total,
    pct: totalIncidents.value > 0 ? Math.round((total / totalIncidents.value) * 100) : 0,
  }))
)
</script>

<template>
  <div class="p-6 space-y-6 bg-gray-50 min-h-screen">

    <!-- ── Loading skeleton ── -->
    <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
      <div v-for="n in 5" :key="n" class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm animate-pulse h-28" />
    </div>

    <template v-else>

      <!-- ── KPI Cards ── -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div
          v-for="kpi in kpis" :key="kpi.title"
          class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow"
        >
          <div class="flex items-start justify-between mb-3">
            <div :class="`p-2 rounded-xl ${kpi.bg}`">
              <component :is="kpi.icon" :class="`w-5 h-5 ${kpi.color}`" />
            </div>
            <ArrowUpRight v-if="kpi.trend" class="w-4 h-4 text-emerald-500" />
          </div>
          <p class="text-3xl font-bold text-gray-900 leading-none mb-1">{{ kpi.value }}</p>
          <p class="text-xs font-semibold text-gray-500 mb-0.5">{{ kpi.title }}</p>
          <p class="text-xs text-gray-400 leading-tight">{{ kpi.sub }}</p>
        </div>
      </div>

      <!-- ── Row 1 : Category pie + Status donut ── -->
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
            <div class="p-1.5 rounded-lg bg-amber-50"><Activity class="w-4 h-4 text-amber-500" /></div>
            <span class="font-semibold text-gray-800 text-sm">Répartition par statut</span>
          </div>
          <VChart :option="statusChartOption" style="height:220px" autoresize />
          <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 mt-3">
            <div v-for="item in statusLegend" :key="item.name" class="flex items-center gap-1.5 min-w-0">
              <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{ backgroundColor: item.color }" />
              <span class="text-xs text-gray-500 truncate">{{ item.name }}</span>
              <span class="text-xs font-semibold text-gray-700 ml-auto">{{ item.pct }}%</span>
            </div>
          </div>
        </div>

      </div>

      <!-- ── Row 2 : Municipality bar + Monthly trend ── -->
      <div class="grid lg:grid-cols-2 gap-6">

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <div class="flex items-center gap-2 mb-4">
            <div class="p-1.5 rounded-lg bg-emerald-50"><BarChart3 class="w-4 h-4 text-emerald-500" /></div>
            <span class="font-semibold text-gray-800 text-sm">Distribution par municipalité</span>
          </div>
          <VChart :option="municipalityChartOption" style="height:280px" autoresize />
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <div class="flex items-center gap-2 mb-4">
            <div class="p-1.5 rounded-lg bg-violet-50"><Activity class="w-4 h-4 text-violet-500" /></div>
            <span class="font-semibold text-gray-800 text-sm">Tendance mensuelle (12 mois)</span>
          </div>
          <VChart :option="lineChartOption" style="height:280px" autoresize />
        </div>

      </div>

      <!-- ── Map ── -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <Incidentmap />
      </div>

    </template>
  </div>
</template>