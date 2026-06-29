import { defineStore } from 'pinia'
import { ref } from 'vue'
import {
  getIncidentsNearBy, getIncidentById, updateIncidentStatus,
  getAllIncidents, getIncidentsByMunicipality, deleteIncident,
} from '../api/IncidentApi'

const CACHE_DURATION = 5 * 60 * 1000

export const useIncidentStore = defineStore('incidents', () => {
  const incidents               = ref([])
  const incident                = ref(null)
  const incidentsByMunicipality = ref([])
  const cacheTimestamp          = ref(null)
  const listLoading             = ref(false)
  const detailLoading           = ref(false)
  const actionLoading           = ref(false)
  const error                   = ref(null)

  // ── Pagination ────────────────────────────────────────
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

  let _nearByPromise       = null
  let _allPromise          = null
  let _municipalityPromise = null
    // Le cache est invalide si on change de page, donc on ne le vérifie
  // qu'en cas de même page et même per_page.

  // const isCacheValid = () =>
  //   cacheTimestamp.value !== null &&
  //   incidents.value.length > 0 &&
  //   Date.now() - cacheTimestamp.value < CACHE_DURATION
 const isCacheValid = (page, perPage) =>
    cacheTimestamp.value !== null &&
    incidents.value.length > 0 &&
    meta.value.current_page === page &&
    meta.value.per_page === perPage &&
    Date.now() - cacheTimestamp.value < CACHE_DURATION

  const invalidateCache = () => {
    incidents.value      = []
    cacheTimestamp.value = null
    _nearByPromise       = null
    _allPromise          = null
  }

  // ← $reset défini manuellement (obligatoire pour les setup stores)
  const $reset = () => {
    incidents.value               = []
    incident.value                = null
    incidentsByMunicipality.value = []
    cacheTimestamp.value          = null
    listLoading.value             = false
    detailLoading.value           = false
    actionLoading.value           = false
    error.value                   = null
    _nearByPromise                = null
    _allPromise                   = null
    _municipalityPromise          = null
   meta.value = {
      current_page: 1, per_page: 10, total: 0, last_page: 1,
      from: null, to: null, has_more_pages: false, has_previous_pages: false,
    }
  }

  // const fetchAllIncidents = (forceRefresh = false) => {
  const fetchAllIncidents = (page = 1, perPage = 10, forceRefresh = false) => {

    // if (!forceRefresh && isCacheValid()) return Promise.resolve()
    if (!forceRefresh && isCacheValid(page, perPage)) return Promise.resolve()
    if (_allPromise) return _allPromise
    listLoading.value = true
    error.value       = null
    // _allPromise = getAllIncidents()
       _allPromise = getAllIncidents(page, perPage)
      .then(r => { 
        incidents.value = r.data.data; 
        meta.value      = r.data.meta;
        cacheTimestamp.value = Date.now() })
      .catch(err => { error.value = err.response?.data?.message || 'Erreur chargement' })
      .finally(() => { listLoading.value = false; _allPromise = null })
    return _allPromise
  }

  // ── fetchIncidentsNearBy ──────────────────────────────
  const fetchIncidentsNearBy = (page = 1, perPage = 10, forceRefresh = false) => {
    if (!forceRefresh && isCacheValid(page, perPage)) return Promise.resolve()
  // const fetchIncidentsNearBy = (forceRefresh = false) => {
  //   if (!forceRefresh && isCacheValid()) return Promise.resolve()
    if (_nearByPromise) return _nearByPromise
    listLoading.value = true
    error.value       = null
    // _nearByPromise = getIncidentsNearBy()
     _nearByPromise = getIncidentsNearBy(page, perPage)
      .then(r => { 
        incidents.value = r.data.data; 
        meta.value      = r.data.meta;
        cacheTimestamp.value = Date.now() })
      .catch(err => { error.value = err.response?.data?.message || 'Erreur chargement' })
      .finally(() => { listLoading.value = false; _nearByPromise = null })
    return _nearByPromise
  }

  const fetchIncidentsByMunicipality = (forceRefresh = false) => {
    if (!forceRefresh && incidentsByMunicipality.value.length > 0) return Promise.resolve()
    if (_municipalityPromise) return _municipalityPromise
    listLoading.value = true
    error.value       = null
    _municipalityPromise = getIncidentsByMunicipality()
      .then(r => { incidentsByMunicipality.value = r.data.data })
      .catch(err => { error.value = err.response?.data?.message || 'Erreur chargement' })
      .finally(() => { listLoading.value = false; _municipalityPromise = null })
    return _municipalityPromise
  }

  const fetchIncidentById = async (id) => {
    const cached = incidents.value.find(i => i.id === id)
    if (cached) { incident.value = cached; return }
    detailLoading.value = true
    error.value         = null
    try {
      incident.value = (await getIncidentById(id)).data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur chargement'
    } finally { detailLoading.value = false }
  }

  const UpdateIncidentStatus = async (id, status) => {
    actionLoading.value = true
    error.value         = null
    try {
      const updated = (await updateIncidentStatus(id, status)).data.data
      const idx = incidents.value.findIndex(i => i.id === id)
      if (idx !== -1) incidents.value.splice(idx, 1, updated)
      incident.value = updated
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur mise à jour'
    } finally { actionLoading.value = false }
  }

  const DeleteIncident = async (id) => {
    actionLoading.value = true
    error.value         = null
    try {
      await deleteIncident(id)
      incidents.value = incidents.value.filter(i => i.id !== id)
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur suppression'
    } finally { actionLoading.value = false }
  }

  return {
    incidents, incident, incidentsByMunicipality,
    meta,
    listLoading, detailLoading, actionLoading, error,
    fetchAllIncidents, fetchIncidentsNearBy, fetchIncidentsByMunicipality,
    fetchIncidentById, UpdateIncidentStatus, DeleteIncident,
    invalidateCache, $reset,
  }
})
//{ persist: true }