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

export const createIncident  = () => apiClient.get(`/citizen/incidents`);
export const getIncidentsNearBy        = (page = 1, perPage = 10)  => apiClient.get('/admin_manager/incidents-nearby', { params: { page, per_page: perPage } });
export const getIncidentsByMunicipality  = () => apiClient.get('/admin/incidents-per-municipality');
export const getIncidentsByZone  = (zone) => apiClient.get(`/admin/incidents/${zone}`);
export const getIncidentById  = (id) => apiClient.get(`/admin_manager/incidents/${id}`);
export const deleteIncident  = (id) => apiClient.delete(`/admin/incidents/${id}`);
// export const getAllIncidents  = () => apiClient.get('/incidents');
export const getAllIncidents            = (page = 1, perPage = 10)  => apiClient.get('/incidents', { params: { page, per_page: perPage } })
export const updateIncidentStatus = (id,status) => apiClient.patch(`/admin_manager/incidents/${id}/status`, { status } );


export default apiClient