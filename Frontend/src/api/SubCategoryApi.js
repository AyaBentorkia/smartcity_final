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

// ── Subcategories ─────────────────────────────────────────────────────────────
export const getAllSubcategories         = ()         => apiClient.get('/subcategories')
export const getSubcategoriesByCategory = (categoryId) => apiClient.get(`/categories/${categoryId}/subcategories`)
export const getSubcategoryById         = (id)       => apiClient.get(`/subcategories/${id}`)
export const createSubcategory          = (data)     => apiClient.post('/admin/subcategories', data)
export const editSubcategory            = (id, data) => apiClient.put(`/admin/subcategories/${id}`, data)
export const deleteSubcategory          = (id)       => apiClient.delete(`/admin/subcategories/${id}`)

export default apiClient