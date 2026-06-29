import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { login as loginApi, logout as logoutApi, getMe } from '../api/AuthApi.js'

const user    = ref(null)
const loading = ref(false)
const error   = ref(null)

export function useAuth() {
  const router = useRouter()

  // =============================================
  // GETTERS
  // =============================================

  const isAuthenticated = computed(() => !!user.value)
  const userRole        = computed(() => user.value?.role ?? null)
  const isAdmin         = computed(() => userRole.value === 'super admin')
  const isCitizen       = computed(() => userRole.value === 'citizen')
  const isMunAdmin      = computed(() => userRole.value === 'admin_municipal')
  const isAgent         = computed(() => userRole.value === 'agent')

  // =============================================
  // LOGIN (email + password)
  // =============================================

  const login = async (credentials) => {
    loading.value = true
    error.value   = null

    try {
      const response = await loginApi(credentials)
      const { data: userData, token } = response.data

      // Stocker le token et l'utilisateur
      localStorage.setItem('jwt_token', token)
      user.value = userData

      redirectByRole()
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur de connexion'
      throw err
    } finally {
      loading.value = false
    }
  }

  // =============================================
  // SET USER (utilisé par GoogleCallback après OAuth)
  // =============================================

  const setUser = (userData, token = null) => {
    user.value = userData
    if (token) {
      localStorage.setItem('jwt_token', token)
    }
  }

  // =============================================
  // LOGOUT
  // =============================================

  const logout = async () => {
    try {
      await logoutApi()
    } catch {
      // ignorer les erreurs réseau
    } finally {
      localStorage.removeItem('jwt_token')
      user.value = null
      router.push('/login')
    }
  }

  // =============================================
  // FETCH USER (au refresh de page)
  // =============================================

  const fetchUser = async () => {
    const token = localStorage.getItem('jwt_token')
    if (!token) return

    try {
      const response = await getMe()
      user.value = response.data.data
    } catch {
      user.value = null
      localStorage.removeItem('jwt_token')
    }
  }

  // =============================================
  // REDIRECTION PAR RÔLE
  // =============================================

  const redirectByRole = () => {
    const role = user.value?.role

    const routes = {
      'super admin':     '/dashboard',
      'admin_municipal': '/dashboard',
      'citizen':         '/dashboard',
      'agent':           '/dashboard',
    }

    router.push(routes[role] ?? '/dashboard')
  }

  return {
    // state
    user,
    loading,
    error,
    // getters
    isAuthenticated,
    userRole,
    isAdmin,
    isCitizen,
    isMunAdmin,
    isAgent,
    // actions
    login,
    logout,
    fetchUser,
    setUser,
    redirectByRole,
  }
}