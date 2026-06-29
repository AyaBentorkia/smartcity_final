import { defineStore } from 'pinia'
import { ref } from 'vue'
import { createCategory, editCategory, getAllCategories, getCategoryById, deleteCategory } from '../api/CategoryApi'

const CACHE_DURATION = 5 * 60 * 1000

export const useCategoryStore = defineStore('categories', () => {
  const categories     = ref([])
  const category       = ref(null)
  const cacheTimestamp = ref(null)
  const listLoading    = ref(false)
  const detailLoading  = ref(false)
  const actionLoading  = ref(false)
  const error          = ref(null)

  let _fetchPromise = null

  const isCacheValid = () =>
    cacheTimestamp.value !== null &&
    categories.value.length > 0 &&
    Date.now() - cacheTimestamp.value < CACHE_DURATION

  const invalidateCache = () => {
    categories.value  = []
    cacheTimestamp.value = null
    _fetchPromise = null
  }

  // ← $reset défini manuellement
  const $reset = () => {
    categories.value     = []
    category.value       = null
    cacheTimestamp.value = null
    listLoading.value    = false
    detailLoading.value  = false
    actionLoading.value  = false
    error.value          = null
    _fetchPromise        = null
  }

  const fetchCategories = (forceRefresh = false) => {
    if (!forceRefresh && isCacheValid()) return Promise.resolve()
    if (_fetchPromise) return _fetchPromise
    listLoading.value = true
    error.value       = null
    _fetchPromise = getAllCategories()
      .then(r => { categories.value = r.data.data; cacheTimestamp.value = Date.now() })
      .catch(err => { error.value = err.response?.data?.message || 'Erreur chargement'; throw err })
      .finally(() => { listLoading.value = false; _fetchPromise = null })
    return _fetchPromise
  }

  const fetchCategoryById = async (categoryId) => {
    const cached = categories.value.find(c => c.id === categoryId)
    if (cached) { category.value = cached; return }
    detailLoading.value = true
    error.value         = null
    try {
      category.value = (await getCategoryById(categoryId)).data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur chargement'; throw err
    } finally { detailLoading.value = false }
  }

  const addNewCategory = async (data) => {
    actionLoading.value = true
    error.value         = null
    try {
      const created = (await createCategory(data)).data.data
      categories.value.push(created)
      cacheTimestamp.value = Date.now()
      return created
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur création'; throw err
    } finally { actionLoading.value = false }
  }

  const updateCategory = async (categoryId, data) => {
    actionLoading.value = true
    error.value         = null
    try {
      const updated = (await editCategory(categoryId, data)).data.data
      const idx = categories.value.findIndex(c => c.id === categoryId)
      if (idx !== -1) categories.value[idx] = updated
      category.value = updated
      return updated
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur modification'; throw err
    } finally { actionLoading.value = false }
  }

  const removeCategory = async (categoryId) => {
    actionLoading.value = true
    error.value         = null
    try {
      await deleteCategory(categoryId)
      categories.value = categories.value.filter(c => c.id !== categoryId)
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur suppression'; throw err
    } finally { actionLoading.value = false }
  }

  return {
    categories, category, listLoading, detailLoading, actionLoading, error,
    fetchCategories, fetchCategoryById, addNewCategory, updateCategory, removeCategory,
    invalidateCache, $reset,
  }
})
// { persist: true }