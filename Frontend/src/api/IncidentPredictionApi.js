// api/IncidentPredictionApi.js
import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

const apiClient = axios.create({
  baseURL: API_URL,
  withCredentials: false,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('jwt_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

/**
 * POST /admin_manager/zones/{zone_id}/predict
 * Déclenche une prédiction IA pour une zone
 * @param {number} zoneId
 * @param {number} categoryId
 * @param {string} period  — format YYYY-MM-DD
 */
export const predictIncident = (zoneId, categoryId, period) =>
  apiClient.post(`/admin_manager/zones/${zoneId}/predict`, {
    category_id: categoryId,
    period,
  })

/**
 * GET /admin_manager/zones/{zone_id}/predictions
 * Historique de toutes les prédictions d'une zone
 */
export const getPredictions = (zoneId) =>
  apiClient.get(`/admin_manager/zones/${zoneId}/predictions`)

/**
 * GET /admin_manager/zones/{zone_id}/predictions/latest
 * Dernière prédiction d'une zone
 */
export const getLatestPrediction = (zoneId) =>
  apiClient.get(`/admin_manager/zones/${zoneId}/predictions/latest`)

/**
 * GET /admin_manager/zones/{zone_id}/predictions/category?category=…
 * Prédictions d'une zone filtrées par catégorie
 */
export const getPredictionsByCategory = (zoneId, category) =>
  apiClient.get(`/admin_manager/zones/${zoneId}/predictions/category`, {
    params: { category },
  })

export default apiClient
