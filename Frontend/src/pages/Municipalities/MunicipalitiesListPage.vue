<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import {
  Eye, House, HousePlus, SquarePen, Trash2,
  ChevronLeft, ChevronRight,
} from 'lucide-vue-next'

import { useMunicipalities } from '../../composables/useMunicipalities'

import Card        from '../../components/ui/Card.vue'
import CardHeader  from '../../components/ui/CardHeader.vue'
import CardContent from '../../components/ui/CardContent.vue'
import Button      from '../../components/Button.vue'
import Table       from '../../components/ui/Table/Table.vue'
import TableBody   from '../../components/ui/Table/TableBody.vue'
import TableCell   from '../../components/ui/Table/TableCell.vue'
import TableHead   from '../../components/ui/Table/TableHead.vue'
import TableHeader from '../../components/ui/Table/TableHeader.vue'
import TableRow    from '../../components/ui/Table/TableRow.vue'
import LoadingSpinner          from '../../components/LoadingSpinner.vue'
import ViewMunicipalityModal   from './ViewMunicipalityModal.vue'
import AddMunicipalityModal    from './AddMunicipalityModal.vue'
import EditMunicipalityModal   from './EditMunicipalityModal.vue'
import DeleteMunicipalityModal from './DeleteMunicipalityModal.vue'

const {
  municipalities, municipality, meta,
  listLoading, detailLoading, actionLoading, error,
  fetchMunicipalities, fetchMunicipalityById, removeMunicipality,
} = useMunicipalities()

// ── Pagination ────────────────────────────────────────
const currentPage = ref(1)
const perPage     = ref(10)

const pageNumbers = computed(() => {
  if (!meta.value) return []
  const last  = meta.value.last_page    ?? 1
  const cur   = meta.value.current_page ?? 1
  const delta = 2
  const range = []
  for (let i = Math.max(1, cur - delta); i <= Math.min(last, cur + delta); i++) {
    range.push(i)
  }
  return range
})

const loadPage = (page) => {
  currentPage.value = page
  fetchMunicipalities(page, perPage.value, true)
}

onMounted(() => loadPage(1))

// ── State ─────────────────────────────────────────────
const search               = ref('')
const showViewModal        = ref(false)
const showAddModal         = ref(false)
const showEditModal        = ref(false)
const showDeleteModal      = ref(false)
const municipalityToEdit   = ref(null)
const municipalityToDelete = ref(null)

watch(search, () => { currentPage.value = 1 })

const filteredMunicipalities = computed(() =>
  (municipalities.value || []).filter(m =>
    m.name.toLowerCase().includes(search.value.toLowerCase())
  )
)

const totalMunicipalities = computed(() => meta.value?.total ?? 0)

// ── Handlers — View ───────────────────────────────────
const handleView = async (id) => {
  showViewModal.value = true
  await fetchMunicipalityById(id)
}

// ── Handlers — Add ────────────────────────────────────
const handleAdd   = () => { showAddModal.value = true }
const handleAdded = () => { loadPage(1) }

// ── Handlers — Edit ───────────────────────────────────
const handleEdit = (mun) => {
  municipalityToEdit.value = mun
  showEditModal.value      = true
}
const handleUpdated = () => { loadPage(currentPage.value) }

// ── Handlers — Delete ─────────────────────────────────
const handleDeleteClick = (mun) => {
  municipalityToDelete.value = mun
  showDeleteModal.value      = true
}

const handleDeleteCancel = () => {
  showDeleteModal.value      = false
  municipalityToDelete.value = null
}

const handleDeleteConfirm = async () => {
  if (!municipalityToDelete.value) return
  await removeMunicipality(municipalityToDelete.value.id)
  showDeleteModal.value      = false
  municipalityToDelete.value = null
  const targetPage = municipalities.value.length === 0 && currentPage.value > 1
    ? currentPage.value - 1
    : currentPage.value
  loadPage(targetPage)
}
</script>

<template>
  <div class="flex">
    <main class="flex-1 overflow-y-auto bg-gray-50">
      <div class="space-y-6 p-6">

        <!-- ── KPI ────────────────────────────────────── -->
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-[#0F2356]/10 flex items-center justify-center shrink-0">
              <House class="w-6 h-6 text-[#0F2356]" />
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wide mb-0.5">Total municipalités</p>
              <p class="text-2xl font-bold text-[#0F2356]">{{ totalMunicipalities }}</p>
            </div>
          </div>
        </div>

        <!-- ── Actions Bar ─────────────────────────────── -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 flex items-center gap-3 shadow-sm">
          <div class="relative flex-1">
            <svg
              class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
              fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
            >
              <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <input
              v-model="search"
              type="text"
              placeholder="Rechercher une municipalité..."
              class="w-full bg-[#F7F8FB] border border-gray-200 rounded-lg pl-10 pr-4 py-2 text-sm text-[#0F2356] placeholder-gray-400 focus:outline-none focus:border-[#0F2356] focus:ring-2 focus:ring-[#0F2356]/20 transition-colors"
            />
          </div>

          <Button
            size="sm"
            class="flex items-center gap-2 px-4 py-2 bg-[#CC1525] text-white text-sm font-medium rounded hover:bg-[#a81220] transition-colors"
            @click="handleAdd"
          >
            <HousePlus class="w-4 h-4" />
            Nouvelle municipalité
          </Button>
        </div>

        <!-- ── Error ──────────────────────────────────── -->
        <div
          v-if="error"
          class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-4 py-3"
        >
          {{ error }}
        </div>

        <!-- ── Table Card ─────────────────────────────── -->
        <Card>
          <CardHeader :grid="true">
            <h2 class="text-white text-lg mb-1">Municipalités</h2>
            <p class="text-white/50 text-sm">Gérer les municipalités</p>
          </CardHeader>

          <CardContent class="p-0">
            <div class="relative min-h-[400px]">
              <div v-if="listLoading" class="absolute inset-0 flex items-center justify-center">
                <LoadingSpinner />
              </div>

              <Table v-show="!listLoading">
                <TableHeader>
                  <TableRow>
                    <TableHead>ID</TableHead>
                    <TableHead>Nom</TableHead>
                    <TableHead class="hidden md:table-cell">Gouvernorat</TableHead>
                    <TableHead>Ville</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead class="text-right">Actions</TableHead>
                  </TableRow>
                </TableHeader>

                <TableBody>
                  <TableRow v-for="mun in filteredMunicipalities" :key="mun.id">
                    <TableCell class="font-mono text-xs text-gray-500">{{ mun.id }}</TableCell>
                    <TableCell class="font-medium max-w-[180px] truncate">{{ mun.name }}</TableCell>
                    <!-- Gouvernorat via relation — plus de champ string direct -->
                    <TableCell class="hidden md:table-cell text-gray-500">
                      {{ mun.city?.governorate?.name ?? '—' }}
                    </TableCell>
                    <TableCell class="text-gray-500">{{ mun.city?.name ?? '—' }}</TableCell>
                    <TableCell class="text-gray-500">{{ mun.email ?? '—' }}</TableCell>

                    <TableCell>
                      <div class="flex items-center justify-end gap-1">
                        <Button variant="ghost" size="icon" title="Voir"
                          :disabled="detailLoading"
                          @click="handleView(mun.id)">
                          <Eye class="h-4 w-4" />
                        </Button>

                        <Button variant="ghost" size="icon" title="Modifier"
                          class="text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50"
                          :disabled="actionLoading"
                          @click="handleEdit(mun)">
                          <SquarePen class="h-4 w-4" />
                        </Button>

                        <Button variant="ghost" size="icon" title="Supprimer"
                          class="text-red-500 hover:text-red-700 hover:bg-red-50"
                          :disabled="actionLoading"
                          @click="handleDeleteClick(mun)">
                          <Trash2 class="h-4 w-4" />
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>

                  <TableRow v-if="!listLoading && filteredMunicipalities.length === 0">
                    <TableCell colspan="6" class="text-center py-10 text-muted-foreground">
                      Aucune municipalité trouvée
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </CardContent>

          <!-- ── Pagination Footer ──────────────────── -->
          <div class="border-t border-gray-200 px-6 py-3 flex items-center justify-between bg-[#F7F8FB]">
            <p class="text-sm text-gray-500">
              <template v-if="(meta?.total ?? 0) > 0">
                {{ meta?.from }}–{{ meta?.to }} sur {{ meta?.total }}
                municipalité{{ (meta?.total ?? 0) !== 1 ? 's' : '' }}
              </template>
              <template v-else>Aucune municipalité</template>
            </p>

            <div class="flex items-center gap-1">
              <Button
                variant="outline" size="sm"
                :disabled="!meta?.has_previous_pages || listLoading"
                @click="loadPage((meta?.current_page ?? 1) - 1)"
              >
                <ChevronLeft class="w-4 h-4" />
              </Button>

              <Button
                v-for="n in pageNumbers" :key="n"
                :variant="n === (meta?.current_page) ? 'default' : 'outline'"
                size="sm"
                :disabled="listLoading"
                @click="loadPage(n)"
              >
                {{ n }}
              </Button>

              <Button
                variant="outline" size="sm"
                :disabled="!meta?.has_more_pages || listLoading"
                @click="loadPage((meta?.current_page ?? 1) + 1)"
              >
                <ChevronRight class="w-4 h-4" />
              </Button>
            </div>

            <select
              v-model="perPage"
              class="text-sm border border-gray-200 rounded-lg px-2 py-1 bg-white text-gray-600 focus:outline-none focus:border-[#0F2356] transition-colors"
              @change="loadPage(1)"
            >
              <option :value="10">10 / page</option>
              <option :value="15">15 / page</option>
              <option :value="25">25 / page</option>
              <option :value="50">50 / page</option>
            </select>
          </div>
        </Card>

      </div>
    </main>
  </div>

  <!-- ── Modals ─────────────────────────────────────── -->
  <ViewMunicipalityModal
    v-model="showViewModal"
    :municipality="municipality"
    :loading="detailLoading"
  />

  <AddMunicipalityModal
    v-model="showAddModal"
    :loading="actionLoading"
    @created="handleAdded"
  />

  <EditMunicipalityModal
    v-model="showEditModal"
    :municipality="municipalityToEdit"
    @updated="handleUpdated"
  />

  <DeleteMunicipalityModal
    v-model="showDeleteModal"
    :municipality-name="municipalityToDelete?.name ?? ''"
    :loading="actionLoading"
    @confirm="handleDeleteConfirm"
    @cancel="handleDeleteCancel"
  />
</template>