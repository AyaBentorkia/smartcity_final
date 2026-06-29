import { ref } from 'vue'
import {
  getIncidentsNearBy, getIncidentById, updateIncidentStatus,
  getAllIncidents, getIncidentsByMunicipality, deleteIncident,
} from '../api/IncidentApi'

const CACHE_DURATION = 5 * 60 * 1000

// ── Cache partagé entre tous les composants (module-level) ──────────────────
const cache = {
  all:          {},
  nearby:       {},
  municipality: { data: null, timestamp: null },
}

function isCacheValid(store, page, perPage) {
  const key   = `page_${page}_${perPage}`
  const entry = store[key]
  return entry && Date.now() - entry.timestamp < CACHE_DURATION
}

function isMunicipalityCacheValid() {
  return (
    cache.municipality.data !== null &&
    cache.municipality.timestamp !== null &&
    Date.now() - cache.municipality.timestamp < CACHE_DURATION
  )
}

function invalidateCache() {
  Object.keys(cache.all).forEach(k => delete cache.all[k])
  Object.keys(cache.nearby).forEach(k => delete cache.nearby[k])
  cache.municipality.data      = null
  cache.municipality.timestamp = null
}

// ── Promise en vol pour getAllIncidents uniquement ──────────────────────────
// (évite les doubles requêtes simultanées sur le montage initial)
// Pour fetchIncidentsNearBy, on ne bloque PAS les appels explicites (pagination)
let _allPromise          = null
let _municipalityPromise = null

// ── Composable ──────────────────────────────────────────────────────────────
export function useIncidents() {

  const incidents               = ref([])
  const incident                = ref(null)
  const incidentsByMunicipality = ref([])

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

  // ── fetchAllIncidents ────────────────────────────────────────────────────
  const fetchAllIncidents = (page = 1, perPage = 10, forceRefresh = false) => {
    const key = `page_${page}_${perPage}`

    if (!forceRefresh && isCacheValid(cache.all, page, perPage)) {
      incidents.value = cache.all[key].data
      meta.value      = cache.all[key].meta
      return Promise.resolve()
    }

    // Guard uniquement pour les requêtes non-forcées simultanées
    if (!forceRefresh && _allPromise) return _allPromise

    listLoading.value = true
    error.value       = null

    const promise = getAllIncidents(page, perPage)
      .then(r => {
        incidents.value = r.data.data
        meta.value      = r.data.meta
        cache.all[key]  = { data: r.data.data, meta: r.data.meta, timestamp: Date.now() }
      })
      .catch(err => {
        error.value = err.response?.data?.message || 'Erreur chargement'
      })
      .finally(() => {
        listLoading.value = false
        _allPromise       = null
      })

    if (!forceRefresh) _allPromise = promise
    return promise
  }

  // ── fetchIncidentsNearBy ─────────────────────────────────────────────────
  // FIX : pas de guard _nearByPromise → chaque appel explicite (loadPage) passe
  const fetchIncidentsNearBy = (page = 1, perPage = 10, forceRefresh = false) => {
    const key = `page_${page}_${perPage}`

    if (!forceRefresh && isCacheValid(cache.nearby, page, perPage)) {
      incidents.value = cache.nearby[key].data
      meta.value      = cache.nearby[key].meta
      return Promise.resolve()
    }

    listLoading.value = true
    error.value       = null

    return getIncidentsNearBy(page, perPage)
      .then(r => {
        incidents.value    = r.data.data
        meta.value         = r.data.meta
        cache.nearby[key]  = { data: r.data.data, meta: r.data.meta, timestamp: Date.now() }
      })
      .catch(err => {
        error.value = err.response?.data?.message || 'Erreur chargement'
      })
      .finally(() => {
        listLoading.value = false
      })
  }

  // ── fetchIncidentsByMunicipality ─────────────────────────────────────────
  const fetchIncidentsByMunicipality = (forceRefresh = false) => {
    if (!forceRefresh && isMunicipalityCacheValid()) {
      incidentsByMunicipality.value = cache.municipality.data
      return Promise.resolve()
    }

    if (_municipalityPromise) return _municipalityPromise

    listLoading.value = true
    error.value       = null

    _municipalityPromise = getIncidentsByMunicipality()
      .then(r => {
        incidentsByMunicipality.value = r.data.data
        cache.municipality = { data: r.data.data, timestamp: Date.now() }
      })
      .catch(err => {
        error.value = err.response?.data?.message || 'Erreur chargement'
      })
      .finally(() => {
        listLoading.value    = false
        _municipalityPromise = null
      })

    return _municipalityPromise
  }

  // ── fetchIncidentById ────────────────────────────────────────────────────
  const fetchIncidentById = async (id) => {
    const cached = incidents.value.find(i => i.id === id)
    if (cached) { incident.value = cached; return }

    detailLoading.value = true
    error.value         = null
    try {
      incident.value = (await getIncidentById(id)).data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur chargement'
    } finally {
      detailLoading.value = false
    }
  }

  // ── UpdateIncidentStatus ─────────────────────────────────────────────────
  const UpdateIncidentStatus = async (id, status) => {
    actionLoading.value = true
    error.value         = null
    try {
      const updated = (await updateIncidentStatus(id, status)).data.data
      const idx = incidents.value.findIndex(i => i.id === id)
      if (idx !== -1) incidents.value.splice(idx, 1, updated)
      incident.value = updated
      invalidateCache()
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur mise à jour'
    } finally {
      actionLoading.value = false
    }
  }

  // ── DeleteIncident ───────────────────────────────────────────────────────
  const DeleteIncident = async (id) => {
    actionLoading.value = true
    error.value         = null
    try {
      await deleteIncident(id)
      incidents.value = incidents.value.filter(i => i.id !== id)
      invalidateCache()
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur suppression'
    } finally {
      actionLoading.value = false
    }
  }

  // ── reset ────────────────────────────────────────────────────────────────
  const reset = () => {
    incidents.value               = []
    incident.value                = null
    incidentsByMunicipality.value = []
    listLoading.value             = false
    detailLoading.value           = false
    actionLoading.value           = false
    error.value                   = null
    meta.value = {
      current_page: 1, per_page: 10, total: 0, last_page: 1,
      from: null, to: null, has_more_pages: false, has_previous_pages: false,
    }
  }

  return {
    incidents,
    incident,
    incidentsByMunicipality,
    meta,
    listLoading,
    detailLoading,
    actionLoading,
    error,
    fetchAllIncidents,
    fetchIncidentsNearBy,
    fetchIncidentsByMunicipality,
    fetchIncidentById,
    UpdateIncidentStatus,
    DeleteIncident,
    invalidateCache,
    reset,
  }
}