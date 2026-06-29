import { ref } from 'vue'
import {
  assignIncident, clotureAssignment, getAgentAssignmentById,
  getAgentAssignments, getMyAssignments, updateAssignment,
} from '../api/AssignmentApi'
import { useAuthStore } from '../stores/AuthStore'
import { UserRole } from '../constants/UserRole'

const CACHE_DURATION = 5 * 60 * 1000

// ── Cache partagé entre tous les composants (module-level) ──────────────────
// Clé : page_${page}_${perPage}_${status}
const cache = {}

function isCacheValid(page, perPage, status) {
  const key   = `page_${page}_${perPage}_${status}`
  const entry = cache[key]
  return entry && Date.now() - entry.timestamp < CACHE_DURATION
}

function invalidateCache() {
  Object.keys(cache).forEach(k => delete cache[k])
}

// ── Promises en vol ─────────────────────────────────────────────────────────
let _assignmentsPromise = null

// ── État partagé (module-level) ─────────────────────────────────────────────
const assignments = ref([])
const assignment  = ref(null)

// ── Composable ──────────────────────────────────────────────────────────────
export function useAssignments() {

  const meta = ref({
    current_page:       1,
    per_page:           10,
    total:              0,
    last_page:          1,
    from:               null,
    to:                 null,
    has_more_pages:     false,
    has_previous_pages: false,
  })

  const listLoading   = ref(false)
  const detailLoading = ref(false)
  const actionLoading = ref(false)
  const error         = ref(null)

  const authStore    = useAuthStore()
  const { userRole } = authStore

  // ── AssignIncident ────────────────────────────────────────────────────────
  const AssignIncident = async (incidentId, agentId) => {
    actionLoading.value = true
    error.value         = null
    try {
      const response   = await assignIncident(incidentId, agentId)
      assignment.value = response.data.data
      assignments.value.push(response.data.data)
      invalidateCache()
      return assignment
    } catch (err) {
      error.value = err.response?.data?.message || "Erreur lors de l'assignation"
      throw err
    } finally {
      actionLoading.value = false
    }
  }

  // ── fetchMyAssignments ────────────────────────────────────────────────────
  // status : 'all' | 'active' | 'closed'  (envoyé au backend comme filtre)
  const fetchMyAssignments = (page = 1, perPage = 10, status = 'all', forceRefresh = false) => {
    const key = `page_${page}_${perPage}_${status}`

    if (!forceRefresh && isCacheValid(page, perPage, status)) {
      assignments.value = cache[key].data
      meta.value        = cache[key].meta
      return Promise.resolve()
    }

    if (forceRefresh) _assignmentsPromise = null
    if (_assignmentsPromise) return _assignmentsPromise

    listLoading.value = true
    error.value       = null

    const apiFn = userRole === UserRole.ADMIN_MUNICIPAL
      ? () => getMyAssignments(page, perPage, status)
      : () => getAgentAssignments(page, perPage, status)

    _assignmentsPromise = apiFn()
      .then(r => {
        assignments.value = r.data.data
        meta.value        = r.data.meta
        cache[key]        = { data: r.data.data, meta: r.data.meta, timestamp: Date.now() }
      })
      .catch(err => {
        error.value = err.response?.data?.message || 'Erreur lors du chargement'
        throw err
      })
      .finally(() => {
        listLoading.value   = false
        _assignmentsPromise = null
      })

    return _assignmentsPromise
  }

  // ── fetchAssignmentById ───────────────────────────────────────────────────
  const fetchAssignmentById = async (id) => {
    const cached = assignments.value.find(a => a.id === id)
    if (cached) { assignment.value = cached; return assignment }

    detailLoading.value = true
    error.value         = null
    try {
      const response   = await getAgentAssignmentById(id)
      assignment.value = response.data.data
      return assignment
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement'
      throw err
    } finally {
      detailLoading.value = false
    }
  }

  // ── ReassignIncident ──────────────────────────────────────────────────────
  const ReassignIncident = async (assignmentId, agentId) => {
    actionLoading.value = true
    error.value         = null
    try {
      const response   = await updateAssignment(assignmentId, agentId)
      const updated    = response.data.data
      assignment.value = updated
      const index = assignments.value.findIndex(a => a.id === assignmentId)
      if (index !== -1) assignments.value.splice(index, 1, updated)
      invalidateCache()
      return assignment
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la réassignation'
      throw err
    } finally {
      actionLoading.value = false
    }
  }

  // ── ClotureAssignment ─────────────────────────────────────────────────────
  const ClotureAssignment = async (assignmentId) => {
    actionLoading.value = true
    error.value         = null
    try {
      const response   = await clotureAssignment(assignmentId)
      const updated    = response.data.data
      assignment.value = updated
      const index = assignments.value.findIndex(a => a.id === assignmentId)
      if (index !== -1) assignments.value.splice(index, 1, updated)
      invalidateCache()
      return assignment
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la clôture'
      throw err
    } finally {
      actionLoading.value = false
    }
  }

  // ── reset ─────────────────────────────────────────────────────────────────
  const reset = () => {
    assignments.value   = []
    assignment.value    = null
    listLoading.value   = false
    detailLoading.value = false
    actionLoading.value = false
    error.value         = null
    meta.value = {
      current_page: 1, per_page: 10, total: 0, last_page: 1,
      from: null, to: null, has_more_pages: false, has_previous_pages: false,
    }
  }

  return {
    assignment, assignments, meta,
    listLoading, detailLoading, actionLoading, error,
    AssignIncident, fetchMyAssignments, fetchAssignmentById,
    ReassignIncident, ClotureAssignment, invalidateCache, reset,
  }
}