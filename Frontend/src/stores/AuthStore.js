import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { login as loginApi, logout as logoutApi, getMe, forgotPassword as forgotPasswordApi, resetPassword as resetPasswordApi } from '../api/AuthApi.js'

export const useAuthStore = defineStore('auth', () => {
  const router  = useRouter()
  const user    = ref(localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')) : null)
  const loading = ref(false)
  const error   = ref(null)

  const isAuthenticated = computed(() => !!user.value)
  const userRole        = computed(() => user.value?.role ?? null)
  const isAdmin         = computed(() => userRole.value === 'super admin')
  const isCitizen       = computed(() => userRole.value === 'citizen')
  const isMunAdmin      = computed(() => userRole.value === 'admin municipal')
  const isAgent         = computed(() => userRole.value === 'agent')

  const login = async (credentials) => {
    loading.value = true
    error.value   = null
    try {
      const response = await loginApi(credentials)
      setUser(response.data.data, response.data.token)

      const { useNotificationStore } = await import('./NotificationStore.js')
      const notifStore = useNotificationStore()
      await notifStore.fetchIfNeeded()
      notifStore.initEcho(response.data.data.id)

      redirectByRole()
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur de connexion'
    } finally {
      loading.value = false
    }
  }

  const setUser = (userData, token = null) => {
    user.value = userData
    localStorage.setItem('user', JSON.stringify(userData))
    if (token) localStorage.setItem('jwt_token', token)
  }

  const logout = async () => {
    try {
      await logoutApi()
    } catch (_) { /* ignore */ }

    try {
      const { useIncidentStore }     = await import('./IncidentStore.js')
      const { useCategoryStore }     = await import('./CategoryStore.js')
      const { useUserStore }         = await import('./UserStore.js')
      const { useProfileStore }      = await import('./ProfileStore.js')
      const { useMunicipalityStore } = await import('./MunicipalityStore.js')
      const { useNotificationStore } = await import('./NotificationStore.js')
      const { useStatisticsStore }   = await import('./useStatisticsStore.js')

      const notifStore = useNotificationStore()
      if (user.value?.id) notifStore.leaveEcho(user.value.id)

      useIncidentStore().$reset()
      useCategoryStore().$reset()
      useUserStore().$reset()
      useProfileStore().$reset()
      useMunicipalityStore().$reset()
      useStatisticsStore().$reset()
      notifStore.$reset()
    } catch (err) {
      console.error('Erreur lors du reset des stores', err)
    } finally {
      localStorage.clear()
      user.value = null
      router.push('/login')
    }
  }

  const fetchUser = async () => {
    try {
      const response = await getMe()
      user.value     = response.data.data
    } catch (err) {
      user.value = null
    }
  }

  const redirectByRole = () => {
    router.push('/dashboard')
  }

  const forgotPassword = async (email) => {
    const response = await forgotPasswordApi(email)
    return response.data
  }

  const resetPassword = async (data) => {
    const response = await resetPasswordApi(data)
    return response.data
  }

  return {
    user, loading, error,
    isAuthenticated, userRole, isAdmin, isCitizen, isMunAdmin, isAgent,
    login, logout, fetchUser, setUser, redirectByRole, forgotPassword, resetPassword,
  }
})