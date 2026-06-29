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

export const getMyMunicipality      = ()             => apiClient.get(`/admin_manager/my-municipality/`)
export const getAllMunicipalities    = ()             => apiClient.get(`/admin/municipalities/`)
export const getAllMunicipalitiesPaginated = (page = 1, perPage = 10)  => apiClient.get(`/admin/municipalities/`, { params: { page, per_page: perPage } });
export const createMunicipality     = (data)         => apiClient.post(`/admin/municipalities`, data)
export const editMyMunicipality     = (data)         => apiClient.patch(`/admin_manager/municipalities/my-municipality/`, data)
export const getMunicipalityById    = (municipality) => apiClient.get(`/municipalities/${municipality}/`)
export const updateMunicipality     = (id, data)     => apiClient.put(`/admin/municipalities/${id}`, data)
export const deleteMunicipality     = (id)           => apiClient.delete(`/admin/municipalities/${id}`)

export default apiClient