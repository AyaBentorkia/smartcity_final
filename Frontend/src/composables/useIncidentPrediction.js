// composables/useIncidentPrediction.js
import { ref } from 'vue'
import {
  predictIncident,
  getPredictions,
  getLatestPrediction,
  getPredictionsByCategory,
} from '../api/IncidentPredictionApi'

// ── Clé localStorage ─────────────────────────────────────────────────────────
const STORAGE_KEY = 'incident_predictions_cache' // { [zoneId]: predictionData }

// ── Helpers localStorage ──────────────────────────────────────────────────────
const readCache = () => {
  try {
    return JSON.parse(localStorage.getItem(STORAGE_KEY) ?? '{}')
  } catch {
    return {}
  }
}

const writeCache = (cache) => {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(cache))
  } catch { /* quota exceeded → ignorer */ }
}

const getCachedPrediction = (zoneId) => {
  if (!zoneId) return null
  return readCache()[zoneId] ?? null
}

const setCachedPrediction = (zoneId, data) => {
  if (!zoneId || !data) return
  const cache = readCache()
  cache[zoneId] = data
  writeCache(cache)
}

// ── Composable ────────────────────────────────────────────────────────────────
export function useIncidentPrediction() {
  const prediction      = ref(null)   // dernière prédiction affichée
  const predictions     = ref([])     // historique
  const isPredicting    = ref(false)
  const historyLoading  = ref(false)
  const error           = ref(null)

  /**
   * Charge depuis le localStorage si disponible.
   * N'appelle jamais l'API — appelé à chaque changement de zone sélectionnée.
   * @param {number} zoneId
   */
  const loadFromCache = (zoneId) => {
    error.value = null
    prediction.value = getCachedPrediction(zoneId) // null → empty state
  }

  /**
   * Lance une nouvelle prédiction IA (UNIQUEMENT via le bouton).
   * Requiert category_id et period en plus de zoneId.
   * Met à jour le cache + le ref local.
   * @param {number} zoneId
   * @param {number} categoryId
   * @param {string} period  — YYYY-MM-DD
   */
  const predict = async (zoneId, categoryId, period) => {
    if (!zoneId || !categoryId || !period) return
    isPredicting.value = true
    error.value = null
    try {
      const response = await predictIncident(zoneId, categoryId, period)
      const data = response.data.data
      prediction.value = data
      setCachedPrediction(zoneId, data)
      return data
    } catch (err) {
      error.value = err.response?.data?.message || "Erreur lors de la prédiction IA"
    } finally {
      isPredicting.value = false
    }
  }

  /**
   * Historique complet des prédictions d'une zone (appel API distinct).
   * @param {number} zoneId
   */
  const fetchHistory = async (zoneId) => {
    if (!zoneId) return
    historyLoading.value = true
    error.value = null
    try {
      const response = await getPredictions(zoneId)
      predictions.value = response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || "Erreur lors du chargement de l'historique"
    } finally {
      historyLoading.value = false
    }
  }

  /**
   * Récupère la dernière prédiction depuis l'API et met à jour le cache.
   * @param {number} zoneId
   */
  const fetchLatest = async (zoneId) => {
    if (!zoneId) return
    error.value = null
    try {
      const response = await getLatestPrediction(zoneId)
      const data = response.data.data
      prediction.value = data
      setCachedPrediction(zoneId, data)
    } catch (err) {
      if (err.response?.status !== 404) {
        error.value = err.response?.data?.message || 'Erreur lors du chargement'
      }
    }
  }

  /**
   * Récupère les prédictions d'une zone filtrées par catégorie.
   * @param {number} zoneId
   * @param {string} category
   */
  const fetchByCategory = async (zoneId, category) => {
    if (!zoneId || !category) return
    historyLoading.value = true
    error.value = null
    try {
      const response = await getPredictionsByCategory(zoneId, category)
      predictions.value = response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du filtre par catégorie'
    } finally {
      historyLoading.value = false
    }
  }

  /**
   * Efface le résultat affiché et le cache localStorage pour cette zone.
   * @param {number|null} zoneId  — si null, efface tout le cache
   */
  const resetPrediction = (zoneId = null) => {
    prediction.value = null
    error.value = null
    if (zoneId) {
      const cache = readCache()
      delete cache[zoneId]
      writeCache(cache)
    } else {
      localStorage.removeItem(STORAGE_KEY)
    }
  }

  return {
    prediction,
    predictions,
    isPredicting,
    historyLoading,
    error,
    loadFromCache,
    predict,
    fetchHistory,
    fetchLatest,
    fetchByCategory,
    resetPrediction,
  }
}