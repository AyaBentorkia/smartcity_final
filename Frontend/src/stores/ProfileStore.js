import { defineStore } from 'pinia'
import { ref } from 'vue'
import { getProfile, updateProfile } from '../api/UserApi'

export const useProfileStore = defineStore('profile', () => {
  const profile       = ref(null)
  const loading       = ref(false)
  const actionLoading = ref(false)
  const error         = ref(null)

  // ← $reset défini manuellement (obligatoire pour les setup stores)
  const $reset = () => {
    profile.value       = null
    loading.value       = false
    actionLoading.value = false
    error.value         = null
  }

  const fetchProfile = async (forceRefresh = false) => {
    if (!forceRefresh && profile.value) return
    loading.value = true
    error.value   = null
    try {
      profile.value = (await getProfile()).data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur chargement profil'
    } finally {
      loading.value = false
    }
  }

  const editProfile = async (data) => {
    actionLoading.value = true
    error.value         = null
    try {
      profile.value = (await updateProfile(data)).data.data
      return profile.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur mise à jour profil'
      throw err
    } finally {
      actionLoading.value = false
    }
  }

  return {
    profile,
    loading,
    actionLoading,
    error,
    fetchProfile,
    editProfile,
    $reset,
  }
})