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

export const getCommentsByIncidentForMunicipalAdmin = (incident_id) =>
  apiClient.get(`/admin_manager/incidents/${incident_id}/comments`)

export const getCommentsByIncidentForAgent = (incident_id) =>
  apiClient.get(`/agent/incidents/${incident_id}/comments`)

export const addComment = (incidentId, content) =>
  apiClient.post(`/agent/incidents/${incidentId}/comments`, { content })

export const updateComment = (commentId, content) =>
  apiClient.put(`/agent/comments/${commentId}`, { content })

export const deleteComment = (commentId) =>
  apiClient.delete(`/agent/comments/${commentId}`)

export default apiClient