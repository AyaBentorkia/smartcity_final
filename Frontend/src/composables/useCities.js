import { ref } from 'vue'
import { getAllCities, getCitiesByGovernorate } from '../api/CityApi'

const citiesCache    = ref([])
const cacheTimestamp = ref(null)
const CACHE_DURATION = 5 * 60 * 1000

export function useCities() {
  const cities  = ref([])
  const city    = ref(null)

  const listLoading   = ref(false)
  const detailLoading = ref(false)
  const actionLoading = ref(false)
  const error         = ref(null)

  const isCacheValid = () =>
    cacheTimestamp.value &&
    citiesCache.value.length > 0 &&
    Date.now() - cacheTimestamp.value < CACHE_DURATION

  const fetchCities = async (forceRefresh = false) => {
    if (!forceRefresh && isCacheValid()) {
      cities.value = citiesCache.value
      return
    }
    listLoading.value = true
    error.value       = null
    try {
      const response       = await getAllCities()
      cities.value         = response.data.data
      citiesCache.value    = response.data.data
      cacheTimestamp.value = Date.now()
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur chargement villes'
      throw err
    } finally {
      listLoading.value = false
    }
  }

  // governorateId est maintenant un int (FK) et non plus un string
  const fetchCitiesByGovernorate = async (governorateId, forceRefresh = false) => {
    if (!governorateId) return
    if (!forceRefresh && isCacheValid()) {
      cities.value = citiesCache.value
      return
    }
    listLoading.value = true
    error.value       = null
    try {
      const response       = await getCitiesByGovernorate(governorateId)
      cities.value         = response.data.data
      citiesCache.value    = response.data.data
      cacheTimestamp.value = Date.now()
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur chargement villes'
      throw err
    } finally {
      listLoading.value = false
    }
  }

  const invalidateCache = () => {
    citiesCache.value    = []
    cacheTimestamp.value = null
  }

  return {
    cities, city,
    listLoading, detailLoading, actionLoading, error,
    fetchCities, fetchCitiesByGovernorate, invalidateCache,
  }
}