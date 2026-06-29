import { ref } from 'vue'
import { getAllGovernorates } from '../api/GovernorateApi'

const cache          = ref([])
const cacheTimestamp = ref(null)
const CACHE_DURATION = 24 * 60 * 60 * 1000 // 24h — données quasi-statiques

export function useGovernorates() {
  const governorates  = ref([])
  const listLoading   = ref(false)
  const error         = ref(null)

  const isCacheValid = () =>
    cacheTimestamp.value &&
    cache.value.length > 0 &&
    Date.now() - cacheTimestamp.value < CACHE_DURATION

  const fetchGovernorates = async (forceRefresh = false) => {
    if (!forceRefresh && isCacheValid()) {
      governorates.value = cache.value
      return
    }
    listLoading.value = true
    error.value       = null
    try {
      const response     = await getAllGovernorates()
      governorates.value = response.data.data
      cache.value        = response.data.data
      cacheTimestamp.value = Date.now()
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur chargement gouvernorats'
    } finally {
      listLoading.value = false
    }
  }

  const invalidateCache = () => {
    cache.value          = []
    cacheTimestamp.value = null
  }

  return { governorates, listLoading, error, fetchGovernorates, invalidateCache }
}