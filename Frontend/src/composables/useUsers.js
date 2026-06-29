import { ref } from 'vue'
import {
  createAgent, createUser, getAllUsers, getMyEmployees,
  getMyEmployeesByCategory, getUserById, updateUserStatus,
} from '../api/UserApi'

const CACHE_DURATION = 5 * 60 * 1000

// ── Cache partagé (module-level) ────────────────────────────────────────────
const cache = {
  users:      {},   // { 'page_1_10': { data, meta, timestamp } }
  agents:     {},   // { 'page_1_10': { data, meta, timestamp } }
  byCategory: {},   // { categoryId: { data, timestamp } }
}

// ── Promises en vol ──────────────────────────────────────────────────────────
let _usersPromise  = null
let _agentsPromise = null

// ── État partagé (module-level) ──────────────────────────────────────────────
const users            = ref([])
const user             = ref(null)
const agents           = ref([])
const agentsByCategory = ref([])

const usersMeta = ref({
  current_page: 1, per_page: 10, total: 0, last_page: 1,
  from: null, to: null, has_more_pages: false, has_previous_pages: false,
})

const agentsMeta = ref({
  current_page: 1, per_page: 10, total: 0, last_page: 1,
  from: null, to: null, has_more_pages: false, has_previous_pages: false,
})

const listLoading   = ref(false)
const detailLoading = ref(false)
const actionLoading = ref(false)
const error         = ref(null)

// ── Helpers cache ────────────────────────────────────────────────────────────
function isCacheValid(store, page, perPage) {
  const key   = `page_${page}_${perPage}`
  const entry = store[key]
  return entry && Date.now() - entry.timestamp < CACHE_DURATION
}

function isCategoryValid(categoryId) {
  const entry = cache.byCategory[categoryId]
  return entry && Date.now() - entry.timestamp < CACHE_DURATION
}

function invalidateAgents() {
  Object.keys(cache.agents).forEach(k => delete cache.agents[k])
  Object.keys(cache.byCategory).forEach(k => delete cache.byCategory[k])
  _agentsPromise = null
}

function invalidateUsers() {
  Object.keys(cache.users).forEach(k => delete cache.users[k])
  _usersPromise = null
}

// ── Composable ───────────────────────────────────────────────────────────────
export function useUsers() {

  // ── fetchUsers ─────────────────────────────────────────────────────────────
  const fetchUsers = (page = 1, perPage = 10, forceRefresh = false) => {
    const key = `page_${page}_${perPage}`

    if (!forceRefresh && isCacheValid(cache.users, page, perPage)) {
      users.value     = cache.users[key].data
      usersMeta.value = cache.users[key].meta
      return Promise.resolve()
    }

    if (forceRefresh) _usersPromise = null
    if (_usersPromise) return _usersPromise

    listLoading.value = true
    error.value       = null

    _usersPromise = getAllUsers(page, perPage)
      .then(r => {
        users.value      = r.data.data
        usersMeta.value  = r.data.meta
        cache.users[key] = { data: r.data.data, meta: r.data.meta, timestamp: Date.now() }
      })
      .catch(err => {
        error.value = err.response?.data?.message || 'Erreur chargement'
      })
      .finally(() => {
        listLoading.value = false
        _usersPromise     = null
      })

    return _usersPromise
  }

  // ── fetchUserById ───────────────────────────────────────────────────────────
  const fetchUserById = async (id) => {
    const cached = users.value.find(u => u.id === id)
    if (cached) { user.value = cached; return }

    detailLoading.value = true
    error.value         = null
    try {
      user.value = (await getUserById(id)).data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur chargement'
    } finally {
      detailLoading.value = false
    }
  }

  // ── fetchAgents ─────────────────────────────────────────────────────────────
  const fetchAgents = (page = 1, perPage = 10, forceRefresh = false) => {
    const key = `page_${page}_${perPage}`

    if (!forceRefresh && isCacheValid(cache.agents, page, perPage)) {
      agents.value     = cache.agents[key].data
      agentsMeta.value = cache.agents[key].meta
      return Promise.resolve()
    }

    if (forceRefresh) _agentsPromise = null
    if (_agentsPromise) return _agentsPromise

    listLoading.value = true
    error.value       = null

    _agentsPromise = getMyEmployees(page, perPage)
      .then(r => {
        agents.value       = r.data.data
        agentsMeta.value   = r.data.meta
        cache.agents[key]  = { data: r.data.data, meta: r.data.meta, timestamp: Date.now() }
      })
      .catch(err => {
        error.value = err.response?.data?.message || 'Erreur chargement'
      })
      .finally(() => {
        listLoading.value = false
        _agentsPromise    = null
      })

    return _agentsPromise
  }

  // ── fetchAgentsByCategory ───────────────────────────────────────────────────
  const fetchAgentsByCategory = async (categoryId) => {
    if (isCategoryValid(categoryId)) {
      agentsByCategory.value = cache.byCategory[categoryId].data
      return
    }

    listLoading.value      = true
    agentsByCategory.value = []
    error.value            = null
    try {
      const data                   = (await getMyEmployeesByCategory(categoryId)).data.data
      agentsByCategory.value       = data
      cache.byCategory[categoryId] = { data, timestamp: Date.now() }
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur chargement'
    } finally {
      listLoading.value = false
    }
  }

  // ── CreateAgent ─────────────────────────────────────────────────────────────
  const CreateAgent = async (data) => {
    actionLoading.value = true
    error.value         = null
    try {
      const created = (await createAgent(data)).data.data
      agents.value.push(created)
      invalidateAgents()
      return created
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur création'
      throw err
    } finally {
      actionLoading.value = false
    }
  }

  // ── CreateUser ──────────────────────────────────────────────────────────────
  const CreateUser = async (data) => {
    actionLoading.value = true
    error.value         = null
    try {
      const created = (await createUser(data)).data.data
      users.value.push(created)
      invalidateUsers()
      return created
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur création'
      throw err
    } finally {
      actionLoading.value = false
    }
  }

  // ── toggleUserStatus ────────────────────────────────────────────────────────
  const toggleUserStatus = async (userId, currentStatus) => {
    actionLoading.value = true
    error.value         = null
    try {
      const newStatus = currentStatus === 'active' ? 'inactive' : 'active'
      await updateUserStatus(userId, newStatus)

      const agentIdx = agents.value.findIndex(a => a.id === userId)
      if (agentIdx !== -1) {
        agents.value[agentIdx] = { ...agents.value[agentIdx], status: newStatus }
        const agentKey = `page_${agentsMeta.value.current_page}_${agentsMeta.value.per_page}`
        if (cache.agents[agentKey]) cache.agents[agentKey].data = agents.value
      }

      const userIdx = users.value.findIndex(u => u.id === userId)
      if (userIdx !== -1) {
        users.value[userIdx] = { ...users.value[userIdx], status: newStatus }
        const userKey = `page_${usersMeta.value.current_page}_${usersMeta.value.per_page}`
        if (cache.users[userKey]) cache.users[userKey].data = users.value
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur mise à jour'
      throw err
    } finally {
      actionLoading.value = false
    }
  }

  // ── reset ───────────────────────────────────────────────────────────────────
  const reset = () => {
    users.value            = []
    user.value             = null
    agents.value           = []
    agentsByCategory.value = []
    listLoading.value      = false
    detailLoading.value    = false
    actionLoading.value    = false
    error.value            = null
    usersMeta.value = {
      current_page: 1, per_page: 10, total: 0, last_page: 1,
      from: null, to: null, has_more_pages: false, has_previous_pages: false,
    }
    agentsMeta.value = {
      current_page: 1, per_page: 10, total: 0, last_page: 1,
      from: null, to: null, has_more_pages: false, has_previous_pages: false,
    }
  }

  return {
    users, user, agents, agentsByCategory,
    usersMeta, agentsMeta,
    listLoading, detailLoading, actionLoading, error,
    fetchUsers, fetchUserById, fetchAgents, fetchAgentsByCategory,
    CreateAgent, CreateUser, toggleUserStatus,
    invalidateAgents, invalidateUsers, reset,
  }
}