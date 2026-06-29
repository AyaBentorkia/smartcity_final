import { ref } from 'vue'
import {
  createCategory,
  editCategory,
  getAllCategories,
  getCategoryById,
  deleteCategory,
} from '../api/CategoryApi'

// Cache lives outside the composable — shared across all instances
const categoriesCache = ref([])
const cacheTimestamp  = ref(null)
const CACHE_DURATION  = 5 * 60 * 1000 // 5 minutes

export function useCategories() {
  const categories = ref([])
  const category   = ref(null)

  const listLoading   = ref(false)
  const detailLoading = ref(false)
  const actionLoading = ref(false)

  const error = ref(null)

  const isCacheValid = () =>
    cacheTimestamp.value &&
    categoriesCache.value.length > 0 &&
    Date.now() - cacheTimestamp.value < CACHE_DURATION

  // ── Fetch all categories ──────────────────────────────────────
  const fetchCategories = async (forceRefresh = false) => {
    if (!forceRefresh && isCacheValid()) {
      categories.value = categoriesCache.value
      return
    }

    listLoading.value = true
    error.value = null
    try {
      const response = await getAllCategories()
      categories.value      = response.data.data
      categoriesCache.value = response.data.data
      cacheTimestamp.value  = Date.now()
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement'
      throw err
    } finally {
      listLoading.value = false
    }
  }

  const invalidateCache = () => {
    categoriesCache.value = []
    cacheTimestamp.value  = null
  }

  // ── Create category ───────────────────────────────────────────
  // BUG FIX: `data` was missing as a parameter; duplicate `categories.push` removed
  const addNewCategory = async (data) => {
    actionLoading.value = true
    error.value = null
    try {
      const response = await createCategory(data)
      const created  = response.data.data
      categories.value.push(created)
      invalidateCache()
      return created
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la création'
      throw err
    } finally {
      actionLoading.value = false
    }
  }

  // ── Fetch category by id ──────────────────────────────────────
  const fetchCategoryById = async (categoryId) => {
    const cached = categories.value.find(c => c.id === categoryId)
    if (cached) {
      category.value = cached
      return category
    }

    detailLoading.value = true
    error.value = null
    try {
      const response = await getCategoryById(categoryId)
      category.value = response.data.data
      return category
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement'
      throw err
    } finally {
      detailLoading.value = false
    }
  }

  // ── Update category ───────────────────────────────────────────
  const updateCategory = async (categoryId, data) => {
    actionLoading.value = true
    error.value = null
    try {
      console.log("updating (debut)")
      
      const response = await editCategory(categoryId, data)
                        console.log("updating (avant updated)")

      const updated  = response.data.data
                  console.log("updating (apres updated)")

      // Update the item in the local list so the table reflects changes immediately
      const idx = categories.value.findIndex(c => c.id === categoryId)
      if (idx !== -1) categories.value[idx] = updated
            console.log("updating (avant category.value)")

      category.value = updated
                  console.log("updating (avant invalidatecache)")

      invalidateCache()
            console.log("updating (fin)")

      return updated
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la modification'
      throw err
    } finally {
      actionLoading.value = false
    }
  }

  // ── Delete category ───────────────────────────────────────────
  const removeCategory = async (categoryId) => {
    actionLoading.value = true
    error.value = null
    try {
      await deleteCategory(categoryId)
      // Remove from local list immediately — no full refetch needed
      categories.value = categories.value.filter(c => c.id !== categoryId)
      invalidateCache()
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la suppression'
      throw err
    } finally {
      actionLoading.value = false
    }
  }

  return {
    categories,
    category,
    listLoading,
    detailLoading,
    actionLoading,
    error,
    fetchCategories,
    invalidateCache,
    addNewCategory,
    fetchCategoryById,
    updateCategory,
    removeCategory,
  }
}