// zones
import axios from 'axios'

const API_URL = 'http://localhost:8000/api'

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

export const getAllZones   = ()                    => apiClient.get('/admin_manager/zones/nearby')
export const createNewZone = (zoneData)            => apiClient.post('/admin_manager/zones', zoneData)
export const updateZone    = (id, zoneData)        => apiClient.patch(`/admin_manager/zones/${id}`, zoneData)
export const deleteZone    = (id)                  => apiClient.delete(`/admin_manager/zones/${id}`)

export default apiClient