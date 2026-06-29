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

// SuperAdmin → GET /admin/statistics
export const getGlobalStats    = () => apiClient.get('/admin/statistics')

// Admin Municipal → GET /admin_manager/statistics
export const getMunicipalStats = () => apiClient.get('/admin_manager/statistics')

// Agent → GET /agent/statistics
export const getAgentStats     = () => apiClient.get('/agent/statistics')

export default apiClient