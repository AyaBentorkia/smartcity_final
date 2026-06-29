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

export const getAllCities           = ()                  => apiClient.get(`/cities`)
export const getCitiesByGovernorate = (governorateId)    => apiClient.get(`/governorates/${governorateId}/cities`)
// Route mise à jour : /cities/governorate/{string} → /governorates/{id}/cities

export default apiClient