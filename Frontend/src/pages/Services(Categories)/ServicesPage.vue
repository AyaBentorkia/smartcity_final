<script setup>
import { ref, computed, onMounted } from 'vue'
import { storeToRefs } from 'pinia'

import CardContent  from '../../components/ui/CardContent.vue'
import Card         from '../../components/ui/Card.vue'
import Button       from '../../components/Button.vue'
import CardHeader   from '../../components/ui/CardHeader.vue'
import Table        from '../../components/ui/Table/Table.vue'
import TableBody    from '../../components/ui/Table/TableBody.vue'
import TableCell    from '../../components/ui/Table/TableCell.vue'
import TableHead    from '../../components/ui/Table/TableHead.vue'
import TableHeader  from '../../components/ui/Table/TableHeader.vue'
import TableRow     from '../../components/ui/Table/TableRow.vue'
import LoadingSpinner from '../../components/LoadingSpinner.vue'
import Sidebar      from '../../components/Sidebar/Sidebar.vue'
import DashboardLayout from '../../components/DashboardLayout.vue'

import { Search, HousePlus, SquarePen, Trash, Wrench } from 'lucide-vue-next'

import { useCategoryStore } from '../../stores/CategoryStore'
import AddCategoryModal    from './AddCategoryModal.vue'
import EditCategoryModal   from './EditCategoryModal.vue'
import DeleteCategoryModal from './DeleteCategoryModal.vue'

// ── State ───────────────────────────────────────────────────────
const search = ref('')

const addCategoryModal    = ref(false)
const editCategoryModal   = ref(false)
const deleteCategoryModal = ref(false)

// The category object currently selected for edit or delete
const selectedCategory = ref(null)

const categoryStore = useCategoryStore()
const { categories, listLoading, actionLoading, error } = storeToRefs(categoryStore)
const { fetchCategories, removeCategory, fetchCategoryById } = categoryStore

// ── Init ────────────────────────────────────────────────────────
onMounted(fetchCategories)

// ── Computed ────────────────────────────────────────────────────
const filteredCategories = computed(() =>
  (categories.value || []).filter(c =>
    c.name.toLowerCase().includes(search.value.toLowerCase()),
  ),
)

const totalCategories = computed(() => (categories.value || []).length)

// ── Handlers ────────────────────────────────────────────────────
const formatDate = (d) =>
  d ? new Date(d).toLocaleDateString('fr-TN', { day: '2-digit', month: 'long', year: 'numeric' }) : '—'

/** Open the "Add" modal */
const handleOpenAdd = () => {
  selectedCategory.value = null
  addCategoryModal.value = true
}

/** Called after a category is successfully created */
const handleCreated = () => {
  // Cache is already invalidated inside addNewCategory, list refreshes automatically
  //********** */ fetchCategories(true)
}

/** Open the "Edit" modal for a given category row */
const handleOpenEdit = (cat) => {
  selectedCategory.value   = { ...cat }   // clone so form edits don't mutate the list
  editCategoryModal.value  = true
}

/** Called after a category is successfully updated */
const handleUpdated = () => {
  // updateCategory already patches the local list; a forced refresh is optional
}

/** Open the "Delete" confirmation modal */
const handleOpenDelete = (cat) => {
  selectedCategory.value    = cat
  deleteCategoryModal.value = true
}

/** Called when the user confirms deletion */
const handleConfirmDelete = async () => {
  if (!selectedCategory.value) return
  try {
    await removeCategory(selectedCategory.value.id)
    deleteCategoryModal.value = false
    selectedCategory.value    = null
  } catch {
    // error is exposed via `error` ref from the composable
  }
}

/** Called when the user cancels deletion */
const handleCancelDelete = () => {
  deleteCategoryModal.value = false
  selectedCategory.value    = null
}
</script>

<template>
  <div class="flex">
    <!-- <Sidebar /> -->
    <main class="flex-1 overflow-y-auto  bg-gray-50">
      <!-- <DashboardLayout /> -->

      <div class="space-y-6 p-6">

        <!-- ── KPI ───────────────────────────────────────────── -->
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-[#0F2356]/10 flex items-center justify-center shrink-0">
              <Wrench class="w-6 h-6 text-[#0F2356]" />
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wide mb-0.5">Total categories</p>
              <p class="text-2xl font-bold text-[#0F2356]">{{ totalCategories }}</p>
            </div>
          </div>
        </div>

        <!-- ── Actions bar ───────────────────────────────────── -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 flex items-center gap-3 shadow-sm">
          <div class="relative flex-1">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
            <input
              v-model="search"
              type="text"
              placeholder="Rechercher une catégorie..."
              class="w-full bg-[#F7F8FB] border border-gray-200 rounded-lg pl-10 pr-4 py-2 text-sm text-[#0F2356] placeholder-gray-400 focus:outline-none focus:border-[#0F2356] focus:ring-2 focus:ring-[#0F2356]/20 transition-colors"
            />
          </div>

          <Button
            size="sm"
            class="flex items-center gap-2 px-4 py-2 bg-[#CC1525] text-white text-sm font-medium rounded hover:bg-[#a81120] transition-colors"
            @click="handleOpenAdd"
          >
            <HousePlus class="w-4 h-4" />
            nouvelle catégorie
          </Button>
        </div>

        <!-- ── Global error ──────────────────────────────────── -->
        <div v-if="error" class="text-sm text-red-600 bg-red-50 px-3 py-2 rounded-lg">{{ error }}</div>

        <!-- ── Table ─────────────────────────────────────────── -->
        <Card>
          <CardHeader :grid="true">
            <h2 class="text-white text-lg mb-1">Catégories </h2>
            <p class="text-white/50 text-sm">Gérer les catégories d'incidents</p>
          </CardHeader>

          <CardContent class="p-0">
            <LoadingSpinner v-if="listLoading && !categories.length" />

            <Table v-else>
              <TableHeader>
                <TableRow>
                  <TableHead>ID</TableHead>
                  <TableHead>Nom</TableHead>
                  <TableHead class="hidden md:table-cell">Couleur</TableHead>
                  <TableHead>Créé le</TableHead>
                  <TableHead>Modifié le</TableHead>
                  <TableHead class="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>

              <TableBody>
                <TableRow v-for="cat in filteredCategories" :key="cat.id">
                  <TableCell class="font-mono text-xs">{{ cat.id }}</TableCell>
                  <TableCell class="font-medium max-w-[200px] truncate">{{ cat.name }}</TableCell>
                  <TableCell class="hidden md:table-cell">
                    <div class="flex items-center gap-2">
                      <span
                        class="w-4 h-4 rounded-full border border-gray-200 shrink-0"
                        :style="{ backgroundColor: cat.color }"
                      />
                      <span :style="{ color: cat.color }" class="text-xs font-mono">{{ cat.color }}</span>
                    </div>
                  </TableCell>
                  <TableCell>{{ formatDate(cat.created_at) }}</TableCell>
                  <TableCell>{{ formatDate(cat.updated_at) }}</TableCell>

                  <TableCell>
                    <div class="flex items-center justify-end gap-1">

                      <!-- Edit -->
                      <Button
                        variant="ghost"
                        size="icon"
                        title="Modifier la catégorie"
                        class="text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50"
                        :disabled="actionLoading"
                        @click="handleOpenEdit(cat)"
                      >
                        <SquarePen class="h-4 w-4" />
                      </Button>

                      <!-- Delete -->
                      <Button
                        variant="ghost"
                        size="icon"
                        title="Supprimer la catégorie"
                        class="text-red-600 hover:text-red-700 hover:bg-red-50"
                        :disabled="actionLoading"
                        @click="handleOpenDelete(cat)"
                      >
                        <Trash class="h-4 w-4" />
                      </Button>

                    </div>
                  </TableCell>
                </TableRow>

                <TableRow v-if="!listLoading && filteredCategories.length === 0">
                  <TableCell colspan="6" class="text-center py-10 text-muted-foreground">
                    Aucune catégorie trouvée
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </CardContent>
        </Card>

      </div>
    </main>
  </div>

  <!-- ── Modals ─────────────────────────────────────────────── -->

  <!-- Add -->
  <AddCategoryModal
    v-model="addCategoryModal"
    @created="handleCreated"
  />

  <!-- Edit -->
  <EditCategoryModal
    v-model="editCategoryModal"
    :category="selectedCategory"
    @updated="handleUpdated"
  />

  <!-- Delete confirmation -->
  <DeleteCategoryModal
    v-model="deleteCategoryModal"
    :category-name="selectedCategory?.name ?? ''"
    :loading="actionLoading"
    @confirm="handleConfirmDelete"
    @cancel="handleCancelDelete"
  />
</template>