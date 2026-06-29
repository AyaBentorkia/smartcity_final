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
apiClient.interceptors.response.use(
  response => response,
  error => {
    console.log('API Error details:', error.response?.data?.errors) // ← champs en erreur
    return Promise.reject(error)
  }
)

// export const getMyEmployees  = () => apiClient.get(`/admin_manager/users`);
export const getMyEmployees  = (page = 1, perPage = 10) => apiClient.get(`/admin_manager/users`, { params: { page, per_page: perPage } });
export const getMyEmployeesByCategory  = (categoryId) => apiClient.get(`/admin_manager/agents/${categoryId}`);
export const createAgent  = (data) => apiClient.post(`/admin_manager/users`,data);
export const getProfile  = () => apiClient.get(`/me`);
export const updateProfile = (data) => apiClient.put(`/me`, data);
// export const getAllUsers = () => apiClient.get('/admin/users');
export const getAllUsers       = (page = 1, perPage = 10)  => apiClient.get('/admin/users', { params: { page, per_page: perPage } });
export const getUserById = (user) => apiClient.get(`/admin/users/${user}`);
export const createUser = (data) => apiClient.post('/admin/users', data);
export const updateUserStatus = (user, status) => apiClient.patch(`/users/${user}/status`, { status });
// export const updateAgentStatus = (user, status) => apiClient.patch(`/admin/users/${user}/status`, { status });

export default apiClient