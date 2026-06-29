import { ref, computed } from 'vue'
import axios from 'axios'
// adapte selon ton setup

export function useStatistics(role) {
  const stats   = ref(null)
  const loading = ref(false)
  const error   = ref(null)

  // Endpoint selon le rôle
  const endpoint = {
    super_admin:     '/admin/statistics',
    admin_municipal: '/admin_manager/statistics',
    agent:           '/agent/statistics',
  }[role]

  const fetchStats = async () => {
    loading.value = true
    error.value   = null
    try {
      const { data } = await axios.get(endpoint)
      stats.value = data.data
    } catch (e) {
      error.value = e?.response?.data?.message ?? 'Erreur lors du chargement des statistiques'
    } finally {
      loading.value = false
    }
  }

  // ── Helpers computed ─────────────────────────────

  /** incidents_by_status → format [{label, value}] pour graphique */
  const statusChartData = computed(() => {
    if (!stats.value?.incidents_by_status) return []
    return Object.entries(stats.value.incidents_by_status).map(([label, value]) => ({
      label, value
    }))
  })

  /** incidents_by_category — déjà [{name, total}] */
  const categoryChartData = computed(() => stats.value?.incidents_by_category ?? [])

  /** incidents_by_zone */
  const zoneChartData = computed(() => stats.value?.incidents_by_zone ?? [])

  /** incidents_by_municipality (SuperAdmin) */
  const municipalityChartData = computed(() => stats.value?.incidents_by_municipality ?? [])

  /** monthly_trend */
  const monthlyTrendData = computed(() => stats.value?.monthly_trend ?? [])

  /** assignments_by_status → format [{label, value}] */
  const assignmentStatusData = computed(() => {
    if (!stats.value?.assignments_by_status) return []
    return Object.entries(stats.value.assignments_by_status).map(([label, value]) => ({
      label, value
    }))
  })

  return {
    stats,
    loading,
    error,
    fetchStats,
    // computed
    statusChartData,
    categoryChartData,
    zoneChartData,
    municipalityChartData,
    monthlyTrendData,
    assignmentStatusData,
  }
}