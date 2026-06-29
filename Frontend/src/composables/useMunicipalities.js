import { ref } from 'vue'
import {
  createMunicipality, getAllMunicipalitiesPaginated, getMunicipalityById,
  updateMunicipality, deleteMunicipality,
  //  searchMunicipalities
} from '../api/MunicipalityApi'

const CACHE_DURATION = 5 * 60 * 1000

// ── Cache module-level ──────────────────────────────────────────────────────
const _cacheById = {}
const _cacheList = {}
let _paginatedPromise = null

function isByIdCacheValid(id) {
  const entry = _cacheById[id]
  return entry && Date.now() - entry.timestamp < CACHE_DURATION
}

function isListCacheValid(page, perPage) {
  const key   = `page_${page}_${perPage}`
  const entry = _cacheList[key]
  return entry && Date.now() - entry.timestamp < CACHE_DURATION
}

function invalidateCacheById(id) {
  if (id) delete _cacheById[id]
  else Object.keys(_cacheById).forEach(k => delete _cacheById[k])
}

function invalidateListCache() {
  Object.keys(_cacheList).forEach(k => delete _cacheList[k])
  _paginatedPromise = null
}

// ── Parsing de la réponse réelle de l'API ───────────────────────────────────
// Le controller retourne :
//   { message: "...", data: { current_page, data: [...items], total, last_page, ... } }
//
// Donc après axios :
//   r.data         = { message, data: { current_page, data:[...], total, ... } }
//   r.data.data    = l'objet paginator Laravel
//   r.data.data.data = le tableau des items
//
function parsePaginatedResponse(axiosResponseData) {
  const paginator = axiosResponseData.data  // objet paginator Laravel

  const current_page = paginator.current_page ?? 1
  const last_page    = paginator.last_page    ?? 1
  const total        = paginator.total        ?? 0
  const per_page     = paginator.per_page     ?? 10
  const from         = paginator.from         ?? null
  const to           = paginator.to           ?? null

  const meta = {
    current_page,
    last_page,
    total,
    per_page,
    from,
    to,
    has_more_pages:     current_page < last_page,
    has_previous_pages: current_page > 1,
  }

  const items = Array.isArray(paginator.data) ? paginator.data : []

  return { items, meta }
}

// ── Composable ──────────────────────────────────────────────────────────────
export function useMunicipalities() {

  const municipalities = ref([])
  const meta           = ref({
    current_page: 1, per_page: 10, total: 0, last_page: 1,
    from: null, to: null, has_more_pages: false, has_previous_pages: false,
  })

  const searchResults = ref([])
  const municipality  = ref(null)

  const listLoading   = ref(false)
  const searchLoading = ref(false)
  const detailLoading = ref(false)
  const actionLoading = ref(false)
  const error         = ref(null)

  // ── fetchMunicipalities ──────────────────────────────────────────────────
  const fetchMunicipalities = (page = 1, perPage = 10, forceRefresh = false) => {
    const key = `page_${page}_${perPage}`

    if (!forceRefresh && isListCacheValid(page, perPage)) {
      municipalities.value = _cacheList[key].data
      meta.value           = _cacheList[key].meta
      return Promise.resolve()
    }

    if (_paginatedPromise) return _paginatedPromise

    listLoading.value = true
    error.value       = null

    _paginatedPromise = getAllMunicipalitiesPaginated(page, perPage)
      .then(r => {
        const { items, meta: normalizedMeta } = parsePaginatedResponse(r.data)

        municipalities.value = items
        meta.value           = normalizedMeta
        _cacheList[key]      = { data: items, meta: normalizedMeta, timestamp: Date.now() }
      })
      .catch(err => {
        error.value = err.response?.data?.message || 'Erreur chargement'
      })
      .finally(() => {
        listLoading.value = false
        _paginatedPromise = null
      })

    return _paginatedPromise
  }

  // ── search (autocomplete) ─────────────────────────────────────────────────
  const search = async (query, limit = 10) => {
    if (!query || query.trim().length < 2) {
      searchResults.value = []
      return
    }
    searchLoading.value = true
    error.value         = null
    try {
      searchResults.value = (await searchMunicipalities(query, limit)).data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur recherche'
    } finally {
      searchLoading.value = false
    }
  }

  const clearSearch = () => { searchResults.value = [] }

  // ── fetchMunicipalityById ─────────────────────────────────────────────────
  const fetchMunicipalityById = async (id) => {
    if (isByIdCacheValid(id)) {
      municipality.value = _cacheById[id].data
      return
    }
    detailLoading.value = true
    error.value         = null
    try {
      const data         = (await getMunicipalityById(id)).data.data
      municipality.value = data
      _cacheById[id]     = { data, timestamp: Date.now() }
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur chargement'
    } finally {
      detailLoading.value = false
    }
  }

  // ── addNewMunicipality ────────────────────────────────────────────────────
  const addNewMunicipality = async (data) => {
    actionLoading.value = true
    error.value         = null
    try {
      const created = (await createMunicipality(data)).data.data
      municipalities.value.push(created)
      invalidateListCache()
      return created
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur création'
      throw err
    } finally {
      actionLoading.value = false
    }
  }

  // ── editMunicipality ──────────────────────────────────────────────────────
  const editMunicipality = async (id, data) => {
    actionLoading.value = true
    error.value         = null
    try {
      const updated = (await updateMunicipality(id, data)).data.data
      const idx = municipalities.value.findIndex(m => m.id === id)
      if (idx !== -1) municipalities.value.splice(idx, 1, updated)
      if (_cacheById[id]) _cacheById[id] = { data: updated, timestamp: Date.now() }
      invalidateListCache()
      municipality.value = updated
      return updated
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur mise à jour'
      throw err
    } finally {
      actionLoading.value = false
    }
  }

  // ── removeMunicipality ────────────────────────────────────────────────────
  const removeMunicipality = async (id) => {
    actionLoading.value = true
    error.value         = null
    try {
      await deleteMunicipality(id)
      municipalities.value = municipalities.value.filter(m => m.id !== id)
      invalidateCacheById(id)
      invalidateListCache()
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur suppression'
      throw err
    } finally {
      actionLoading.value = false
    }
  }

  // ── reset ─────────────────────────────────────────────────────────────────
  const reset = () => {
    municipalities.value = []
    searchResults.value  = []
    municipality.value   = null
    listLoading.value    = false
    searchLoading.value  = false
    detailLoading.value  = false
    actionLoading.value  = false
    error.value          = null
    meta.value = {
      current_page: 1, per_page: 10, total: 0, last_page: 1,
      from: null, to: null, has_more_pages: false, has_previous_pages: false,
    }
  }

  return {
    municipalities,
    municipality,
    searchResults,
    meta,
    listLoading,
    searchLoading,
    detailLoading,
    actionLoading,
    error,
    fetchMunicipalities,
    search,
    clearSearch,
    fetchMunicipalityById,
    addNewMunicipality,
    editMunicipality,
    removeMunicipality,
    invalidateCacheById,
    reset,
  }
}