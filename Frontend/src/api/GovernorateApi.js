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

export const getAllGovernorates       = ()            => apiClient.get(`/governorates`)
export const getGovernorateById      = (id)          => apiClient.get(`/governorates/${id}`)
export const getGovernoratesByCountry = (countryId)  => apiClient.get(`/countries/${countryId}/governorates`)

export default apiClient