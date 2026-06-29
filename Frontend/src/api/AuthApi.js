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

export const register     = (data)        => apiClient.post('/auth/register', data)
export const login        = (credentials) => apiClient.post('/auth/login', credentials)
export const logout       = ()            => apiClient.post('/logout')
export const getMe        = ()            => apiClient.get('/me')
export const refreshToken = ()            => apiClient.post('/refresh')

// ─── Google OAuth ──────────────────────────────────────────────────────────
// Obtenir l'URL de redirection Google depuis le backend
export const getGoogleRedirectUrl = () => apiClient.get('/auth/google')

// ─── Password Reset ────────────────────────────────────────────────────────
export const forgotPassword = (email)  => apiClient.post('/auth/forgot-password', { email })
export const resetPassword  = (data)   => apiClient.post('/auth/reset-password', data)
 
// ───────────────────────────────────────────────────────────────────────────

export default apiClient