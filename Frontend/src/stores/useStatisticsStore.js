import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { getGlobalStats, getMunicipalStats, getAgentStats } from '../api/StatisticsApi'

export const useStatisticsStore = defineStore('statistics', () => {

  // ─────────────────────────────────────────────
  //  STATE
  // ─────────────────────────────────────────────

  const globalStats    = ref(null)   // SuperAdmin
  const municipalStats = ref(null)   // Admin Municipal
  const agentStats     = ref(null)   // Agent

  const loading = ref(false)
  const error   = ref(null)

  // ─────────────────────────────────────────────
  //  COMPUTED — SuperAdmin
  // ─────────────────────────────────────────────

  const totalIncidents      = computed(() => globalStats.value?.total_incidents      ?? 0)
  const totalMunicipalities = computed(() => globalStats.value?.total_municipalities ?? 0)
  const totalAgents         = computed(() => globalStats.value?.total_agents         ?? 0)
  const avgResolutionHours  = computed(() => globalStats.value?.avg_resolution_hours ?? null)

  const incidentsByStatus       = computed(() => globalStats.value?.incidents_by_status       ?? {})
  const incidentsByCategory     = computed(() => globalStats.value?.incidents_by_category     ?? [])
  const incidentsByMunicipality = computed(() => globalStats.value?.incidents_by_municipality ?? [])
  const incidentsByZone         = computed(() => globalStats.value?.incidents_by_zone         ?? [])
  const usersByRole             = computed(() => globalStats.value?.users_by_role             ?? {})
  const globalMonthlyTrend      = computed(() => globalStats.value?.monthly_trend             ?? [])

  // ─────────────────────────────────────────────
  //  COMPUTED — Admin Municipal
  // ─────────────────────────────────────────────

  const municipalTotalIncidents = computed(() => municipalStats.value?.total_incidents      ?? 0)
  const municipalTotalZones     = computed(() => municipalStats.value?.total_zones          ?? 0)
  const municipalTotalAgents    = computed(() => municipalStats.value?.total_agents         ?? 0)
  const municipalAvgResolution  = computed(() => municipalStats.value?.avg_resolution_hours ?? null)

  const municipalIncidentsByStatus   = computed(() => municipalStats.value?.incidents_by_status   ?? {})
  const municipalIncidentsByCategory = computed(() => municipalStats.value?.incidents_by_category ?? [])
  const municipalIncidentsByZone     = computed(() => municipalStats.value?.incidents_by_zone     ?? [])
  const municipalAgentsByStatus      = computed(() => municipalStats.value?.agents_by_status      ?? {})
  const municipalAgentsByCategory    = computed(() => municipalStats.value?.agents_by_category    ?? [])
  const municipalAssignmentsByStatus = computed(() => municipalStats.value?.assignments_by_status ?? {})
  const municipalMonthlyTrend        = computed(() => municipalStats.value?.monthly_trend         ?? [])

  // ─────────────────────────────────────────────
  //  COMPUTED — Agent
  // ─────────────────────────────────────────────

  const totalAssignments    = computed(() => agentStats.value?.total_assignments     ?? 0)
  const avgClosureHours     = computed(() => agentStats.value?.avg_closure_hours     ?? null)
  const assignmentsByStatus = computed(() => agentStats.value?.assignments_by_status ?? {})
  const monthlyAssignments  = computed(() => agentStats.value?.monthly_assignments   ?? [])

  // ─────────────────────────────────────────────
  //  ACTIONS
  // ─────────────────────────────────────────────

  async function fetchGlobalStats() {
    loading.value = true
    error.value   = null
    try {
      const { data } = await getGlobalStats()
      globalStats.value = data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur chargement stats globales'
    } finally {
      loading.value = false
    }
  }

  async function fetchMunicipalStats() {
    loading.value = true
    error.value   = null
    try {
      const { data } = await getMunicipalStats()
      municipalStats.value = data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur chargement stats municipales'
    } finally {
      loading.value = false
    }
  }

  async function fetchAgentStats() {
    loading.value = true
    error.value   = null
    try {
      const { data } = await getAgentStats()
      agentStats.value = data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur chargement stats agent'
    } finally {
      loading.value = false
    }
  }

  /**
   * Reset complet du store (logout)
   */
  function $reset() {
    globalStats.value    = null
    municipalStats.value = null
    agentStats.value     = null
    loading.value        = false
    error.value          = null
  }

  // ─────────────────────────────────────────────
  //  EXPOSE
  // ─────────────────────────────────────────────
  return {
    // raw state
    globalStats,
    municipalStats,
    agentStats,

    // UI
    loading,
    error,

    // computed — SuperAdmin
    totalIncidents,
    totalMunicipalities,
    totalAgents,
    avgResolutionHours,
    incidentsByStatus,
    incidentsByCategory,
    incidentsByMunicipality,
    incidentsByZone,
    usersByRole,
    globalMonthlyTrend,

    // computed — Admin Municipal
    municipalTotalIncidents,
    municipalTotalZones,
    municipalTotalAgents,
    municipalAvgResolution,
    municipalIncidentsByStatus,
    municipalIncidentsByCategory,
    municipalIncidentsByZone,
    municipalAgentsByStatus,
    municipalAgentsByCategory,
    municipalAssignmentsByStatus,
    municipalMonthlyTrend,

    // computed — Agent
    totalAssignments,
    avgClosureHours,
    assignmentsByStatus,
    monthlyAssignments,

    // actions
    fetchGlobalStats,
    fetchMunicipalStats,
    fetchAgentStats,
    $reset,
  }
})