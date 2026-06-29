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

export const getAllCategories = ()               => apiClient.get('/categories')
export const getCategoryById  = (id)            => apiClient.get(`/categories/${id}`)
export const createCategory   = (data)          => apiClient.post('/admin/categories', data)
export const editCategory     = (id, data)      => apiClient.put(`/admin/categories/${id}`, data)
export const deleteCategory   = (id)            => apiClient.delete(`/admin/categories/${id}`)

export default apiClient