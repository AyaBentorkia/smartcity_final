import { defineStore } from 'pinia'
import { ref } from 'vue'
import {
  createAgent, createUser, getAllUsers, getMyEmployees,
  getMyEmployeesByCategory, getProfile, getUserById, updateProfile, updateUserStatus,
} from '../api/UserApi'

const CACHE_DURATION = 5 * 60 * 1000

export const useUserStore = defineStore('users', () => {
  const users            = ref([])
  const user             = ref(null)
  const agents           = ref([])
  const agentsByCategory = ref([])
  const profile          = ref(null)
  const cacheTimestamp   = ref(null)
  const listLoading      = ref(false)
  const detailLoading    = ref(false)
  const actionLoading    = ref(false)
  const error            = ref(null)

  const isCacheValid = () =>
    cacheTimestamp.value !== null &&
    agents.value.length > 0 &&
    Date.now() - cacheTimestamp.value < CACHE_DURATION

  const invalidateCache = () => {
    agents.value         = []
    cacheTimestamp.value = null
  }

  // ← $reset défini manuellement
  const $reset = () => {
    users.value            = []
    user.value             = null
    agents.value           = []
    agentsByCategory.value = []
    profile.value          = null
    cacheTimestamp.value   = null
    listLoading.value      = false
    detailLoading.value    = false
    actionLoading.value    = false
    error.value            = null
  }

  const fetchUsers = async () => {
    listLoading.value = true
    error.value       = null
    try {
      users.value = (await getAllUsers()).data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur chargement'
    } finally { listLoading.value = false }
  }

  const fetchUserById = async (id) => {
    const cached = users.value.find(u => u.id === id)
    if (cached) { user.value = cached; return }
    detailLoading.value = true
    error.value         = null
    try {
      user.value = (await getUserById(id)).data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur chargement'
    } finally { detailLoading.value = false }
  }

  const fetchAgents = async (forceRefresh = false) => {
    if (!forceRefresh && isCacheValid()) return
    listLoading.value = true
    error.value       = null
    try {
      agents.value         = (await getMyEmployees()).data.data
      cacheTimestamp.value = Date.now()
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur chargement'
    } finally { listLoading.value = false }
  }

  const fetchAgentsByCategory = async (categoryId) => {
    agentsByCategory.value = []
    listLoading.value      = true
    error.value            = null
    try {
      agentsByCategory.value = (await getMyEmployeesByCategory(categoryId)).data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur chargement'
    } finally { listLoading.value = false }
  }

  const CreateAgent = async (data) => {
    actionLoading.value = true
    error.value         = null
    try {
      const created = (await createAgent(data)).data.data
      agents.value.push(created)
      invalidateCache()
      return created
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur création'
    } finally { actionLoading.value = false }
  }

  const CreateUser = async (data) => {
    actionLoading.value = true
    error.value         = null
    try {
      const created = (await createUser(data)).data.data
      users.value.push(created)
      return created
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur création'
    } finally { actionLoading.value = false }
  }

  const toggleUserStatus = async (userId, currentStatus) => {
    actionLoading.value = true
    error.value         = null
    try {
      const newStatus = currentStatus === 'active' ? 'inactive' : 'active'
      await updateUserStatus(userId, newStatus)
      const idx = agents.value.findIndex(a => a.id === userId)
      if (idx !== -1) agents.value[idx] = { ...agents.value[idx], status: newStatus }
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur mise à jour'
    } finally { actionLoading.value = false }
  }

  const fetchProfile = async () => {
    if (profile.value) return
    listLoading.value = true
    error.value       = null
    try {
      profile.value = (await getProfile()).data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur chargement'
    } finally { listLoading.value = false }
  }

  const editProfile = async (data) => {
    actionLoading.value = true
    error.value         = null
    try {
      profile.value = (await updateProfile(data)).data.data
      return profile
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur mise à jour'; throw err
    } finally { actionLoading.value = false }
  }

  return {
    users, user, agents, agentsByCategory, profile,
    listLoading, detailLoading, actionLoading, error,
    fetchUsers, fetchUserById, fetchAgents, fetchAgentsByCategory,
    CreateAgent, CreateUser, toggleUserStatus, fetchProfile, editProfile,
    invalidateCache, $reset,
  }
  })
// }, { persist: true })