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

export const assignIncident  = (incidentId,agentId) =>   apiClient.post(`/admin_manager/incidents/${incidentId}/assign`, { agent: agentId });
export const updateAssignment  = (assignmentId,agentId) =>   apiClient.put(`/admin_manager/assignments/${assignmentId}`, { agent: agentId });
// export const getAllAssignments  = () => apiClient.get('/admin_manager/assignments');
export const getAllAssignments  = (page = 1, perPage = 10) => apiClient.get('/admin_manager/assignments', { params: { page, per_page: perPage } })
// export const getMyAssignments  = () => apiClient.get('/admin_manager/my-assignments');
export const getMyAssignments  = (page = 1, perPage = 10, status = 'all') => apiClient.get('/admin_manager/my-assignments', { params: { page, per_page: perPage, ...(status !== 'all' && { status }) } })
export const deleteAssignment  = (id) => apiClient.delete(`/admin_manager/assignments/${id}`);
//Agent
// export const getAgentAssignments  = () => apiClient.get('/agent/my-assignments');
export const getAgentAssignments  = (page = 1, perPage = 10, status = 'all') => apiClient.get('/agent/my-assignments', { params: { page, per_page: perPage, ...(status !== 'all' && { status }) } })
export const getAgentAssignmentById  = (assignment_id) => apiClient.get(`/agent/assignments/${assignment_id}`);
export const clotureAssignment  = (assignmentId) =>   apiClient.patch(`/agent/assignments/${assignmentId}`);


export default apiClient