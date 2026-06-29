import { ref } from 'vue'
import { getAllZones, createNewZone, updateZone as updateZoneApi, deleteZone as deleteZoneApi } from '../api/ZoneApi'

// ─── State partagé au niveau module (singleton) ───────────────────────────────
// Toutes les instances de useZones() partagent le même state
const zones          = ref([])
const cacheTimestamp = ref(null)
const CACHE_DURATION = 5 * 60 * 1000 // 5 minutes

export function useZones() {
  const zone          = ref(null)
  const listLoading   = ref(false)
  const detailLoading = ref(false)
  const actionLoading = ref(false)
  const error         = ref(null)

  const isCacheValid = () =>
    cacheTimestamp.value !== null &&
    zones.value.length > 0 &&
    Date.now() - cacheTimestamp.value < CACHE_DURATION

  const fetchZones = async (forceRefresh = false) => {
    if (!forceRefresh && isCacheValid()) return

    listLoading.value = true
    error.value = null
    try {
      const response       = await getAllZones()
      zones.value          = response.data.data ?? []
      cacheTimestamp.value = Date.now()
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement'
    } finally {
      listLoading.value = false
    }
  }

  const invalidateCache = () => {
    cacheTimestamp.value = null
  }

  const createZone = async (zoneData) => {
    actionLoading.value = true
    error.value = null
    try {
      await createNewZone(zoneData)
      // Recharge depuis l'API pour avoir la zone complète avec ses relations
      await fetchZones(true)
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la création'
      throw err
    } finally {
      actionLoading.value = false
    }
  }

  const updateZone = async (id, zoneData) => {
    actionLoading.value = true
    error.value = null
    try {
      const response = await updateZoneApi(id, zoneData)
      const updated  = response.data.data ?? response.data
      const idx      = zones.value.findIndex(z => z.id === id)
      if (idx !== -1) zones.value[idx] = updated
      invalidateCache()
      return updated
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la modification'
      throw err
    } finally {
      actionLoading.value = false
    }
  }

  const deleteZone = async (id) => {
    actionLoading.value = true
    error.value = null
    try {
      await deleteZoneApi(id)
      zones.value = zones.value.filter(z => z.id !== id)
      invalidateCache()
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la suppression'
      throw err
    } finally {
      actionLoading.value = false
    }
  }

  return {
    zones,
    zone,
    listLoading,
    detailLoading,
    actionLoading,
    error,
    fetchZones,
    invalidateCache,
    createZone,
    updateZone,
    deleteZone,
  }
}