import { defineStore } from 'pinia'
import { ref } from 'vue'
import { editMyMunicipality, getMyMunicipality } from '../api/MunicipalityApi'

// ── Store allégé : uniquement la municipalité courante de l'admin connecté ──
// Les 300 municipalités (liste, search, CRUD) → useMunicipalities() composable

export const useMunicipalityStore = defineStore('municipality', () => {
  const municipality  = ref(null)
  const loading       = ref(false)
  const actionLoading = ref(false)
  const error         = ref(null)

  // ← $reset défini manuellement (obligatoire pour les setup stores)
  const $reset = () => {
    municipality.value  = null
    loading.value       = false
    actionLoading.value = false
    error.value         = null
  }

  const fetchMyMunicipality = async (forceRefresh = false) => {
    if (!forceRefresh && municipality.value) return
    loading.value = true
    error.value   = null
    try {
      municipality.value = (await getMyMunicipality()).data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur chargement'
    } finally {
      loading.value = false
    }
  }

  const updateMyMunicipality = async (data) => {
    actionLoading.value = true
    error.value         = null
    try {
      municipality.value = (await editMyMunicipality(data)).data.data
      return municipality.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur mise à jour'
      throw err
    } finally {
      actionLoading.value = false
    }
  }

  return {
    municipality,
    loading,
    actionLoading,
    error,
    fetchMyMunicipality,
    updateMyMunicipality,
    $reset,
  }
})