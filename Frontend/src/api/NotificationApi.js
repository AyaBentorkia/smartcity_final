import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

const apiClient = axios.create({
  baseURL: API_URL,
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

export const getNotifications  = () => apiClient.get('/notifications')
export const markAsRead        = (id) => apiClient.patch(`/notifications/${id}/read`)
export const markAllAsRead     = () => apiClient.patch('/notifications/read-all')
export const getUnreadCount    = () => apiClient.get('/notifications/unread-count')
export const updateFcmToken    = (token) => apiClient.post('/fcm-token', { fcm_token: token })

export default apiClient