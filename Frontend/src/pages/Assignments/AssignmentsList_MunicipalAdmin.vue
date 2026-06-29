<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import {
  RefreshCw, Search, Download,
  CheckCircle2, Clock,
  ChevronLeft, ChevronRight,
} from 'lucide-vue-next'

// ── UI Components ─────────────────────────────────────
import Card          from '../../components/ui/Card.vue'
import CardHeader    from '../../components/ui/CardHeader.vue'
import CardContent   from '../../components/ui/CardContent.vue'
import Badge         from '../../components/ui/Badge.vue'
import Button        from '../../components/Button.vue'
import Table         from '../../components/ui/Table/Table.vue'
import TableHeader   from '../../components/ui/Table/TableHeader.vue'
import TableHead     from '../../components/ui/Table/TableHead.vue'
import TableBody     from '../../components/ui/Table/TableBody.vue'
import TableRow      from '../../components/ui/Table/TableRow.vue'
import TableCell     from '../../components/ui/Table/TableCell.vue'
import LoadingSpinner from '../../components/LoadingSpinner.vue'

// ── Feature Components ────────────────────────────────
import AssignIncidentModal from './AssignIncidentModal.vue'

// ── Composables ───────────────────────────────────────
import { useAssignments } from '../../composables/useAssignments'
import { useUsers       } from '../../composables/useUsers'

const {
  listLoading, actionLoading, assignments, meta,
  fetchMyAssignments, ReassignIncident,
} = useAssignments()

const { agentsByCategory, fetchAgentsByCategory } = useUsers()

// ── Pagination state ──────────────────────────────────
const currentPage = ref(1)
const perPage     = ref(10)

// ── State ─────────────────────────────────────────────
const filter               = ref('active')
const search               = ref('')
const showAssignModal      = ref(false)
const selectedAssignmentId = ref(null)

// ── Filter options ────────────────────────────────────
const filterOptions = [
  { value: 'active', label: 'Actifs'   },
  { value: 'closed', label: 'Clôturés' },
  { value: 'all',    label: 'Tous'     },
]

// ── Pages calculées ───────────────────────────────────
const pageNumbers = computed(() => {
  const last  = meta.value.last_page
  const cur   = meta.value.current_page
  const delta = 2
  const range = []
  for (let i = Math.max(1, cur - delta); i <= Math.min(last, cur + delta); i++) {
    range.push(i)
  }
  return range
})

// ── Filtered list (recherche locale sur la page courante) ─────────────────
const filtered = computed(() => {
  if (!search.value.trim()) return assignments.value ?? []
  const q = search.value.trim().toLowerCase()
  return (assignments.value ?? []).filter(a =>
    a.incident?.title?.toLowerCase().includes(q) ||
    a.agents?.name?.toLowerCase().includes(q)
  )
})

// ── Badge variant maps ────────────────────────────────
const priorityVariant = {
  low: 'priority-low', medium: 'priority-medium',
  high: 'priority-high', critical: 'priority-critical',
}
const priorityLabel = {
  low: 'Faible', medium: 'Moyen', high: 'Élevé', critical: 'Critique',
}

// ── Chargement ────────────────────────────────────────
const loadPage = (page) => {
  currentPage.value = page
  fetchMyAssignments(page, perPage.value, filter.value, true)
}

onMounted(() => loadPage(1))

watch(filter,   () => loadPage(1))
watch(perPage,  () => loadPage(1))

// ── Handlers ─────────────────────────────────────────
const handleReassign = async (assignment) => {
  selectedAssignmentId.value = assignment.id
  showAssignModal.value      = true
  await fetchAgentsByCategory(assignment.incident?.category_id)
}

// ── Date formatter ────────────────────────────────────
const formatDate = (dateStr) => {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: '2-digit', month: 'short', year: 'numeric',
  })
}
</script>

<template>
  <div class="flex">
    <main class="flex-1 overflow-y-auto bg-gray-50">
      <div class="space-y-6 p-6">

        <!-- ── KPI Counters ──────────────────────────── -->
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center shrink-0">
              <Clock class="w-6 h-6 text-purple-600" />
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wide mb-0.5">Total affectations</p>
              <p class="text-2xl font-bold text-[#0F2356]">{{ meta.total }}</p>
            </div>
          </div>
          <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
              <CheckCircle2 class="w-6 h-6 text-green-600" />
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wide mb-0.5">Page {{ meta.current_page }} / {{ meta.last_page }}</p>
              <p class="text-2xl font-bold text-[#0F2356]">{{ meta.from }}–{{ meta.to }}</p>
            </div>
          </div>
        </div>

        <!-- ── Actions Bar ──────────────────────────── -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 flex items-center gap-3 shadow-sm">
          <div class="relative flex-1">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
            <input
              v-model="search"
              type="text"
              placeholder="Rechercher une affectation..."
              class="w-full bg-[#F7F8FB] border border-gray-200 rounded-lg pl-10 pr-4 py-2 text-sm text-[#0F2356] placeholder-gray-400 focus:outline-none focus:border-[#0F2356] focus:ring-2 focus:ring-[#0F2356]/20 transition-colors"
            />
          </div>

          <select
            v-model="filter"
            class="bg-[#F7F8FB] border border-gray-200 rounded-lg px-4 py-2 text-sm text-gray-600 focus:outline-none focus:border-[#0F2356] transition-colors"
          >
            <option v-for="opt in filterOptions" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </select>

          <!-- <Button variant="destructive" size="sm">
            <Download class="w-4 h-4" />
            Exporter
          </Button> -->
        </div>

        <!-- ── Assignments Card ──────────────────────── -->
        <Card>
          <CardHeader :grid="true">
            <h2 class="text-white text-lg mb-1">Affectations</h2>
            <p class="text-white/50 text-sm">Gérer les affectations des incidents aux agents</p>
          </CardHeader>

          <CardContent class="p-0">
            <div class="relative min-h-[420px]">
              <div v-if="listLoading" class="absolute inset-0 flex items-center justify-center">
                <LoadingSpinner />
              </div>

              <Table v-show="!listLoading">
                <TableHeader>
                  <TableRow>
                    <!-- <TableHead>ID</TableHead> -->
                    <TableHead>Incident</TableHead>
                    <TableHead class="hidden md:table-cell">Agent</TableHead>
                    <TableHead class="hidden md:table-cell">Statut</TableHead>
                    <TableHead class="hidden lg:table-cell">Date fin</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>

                <TableBody>
                  <TableRow v-for="assignment in filtered" :key="assignment.id">

                    <!-- <TableCell class="font-mono text-gray-500">#{{ assignment.id }}</TableCell> -->

                    <TableCell class="text-[#0F2356] font-medium max-w-[200px] truncate">
                      {{ assignment.incident?.title }}
                    </TableCell>

                    <TableCell class="hidden md:table-cell">
                      <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-[#0F2356] text-white text-xs font-bold flex items-center justify-center shrink-0">
                          {{ assignment.agents?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                        </div>
                        <span class="text-gray-600 text-sm">{{ assignment.agents?.name || '—' }}</span>
                      </div>
                    </TableCell>

                    <TableCell class="hidden md:table-cell">
                      <Badge :variant="assignment.end_time === null ? 'status-in_progress' : 'status-resolved'">
                        <component :is="assignment.end_time === null ? Clock : CheckCircle2" class="w-3.5 h-3.5" />
                        {{ assignment.end_time === null ? 'En cours' : 'Clôturé' }}
                      </Badge>
                    </TableCell>

                    <TableCell class="hidden lg:table-cell text-gray-500 text-sm">
                      {{ formatDate(assignment.end_time) }}
                    </TableCell>

                    <TableCell>
                      <Button
                        v-if="assignment.end_time === null"
                        variant="outline"
                        size="sm"
                        :disabled="actionLoading"
                        @click="handleReassign(assignment)"
                      >
                        <RefreshCw class="w-3.5 h-3.5" />
                        Réassigner
                      </Button>
                      <span v-else class="text-xs text-gray-400 italic">Clôturé</span>
                    </TableCell>

                  </TableRow>

                  <TableRow v-if="!listLoading && filtered.length === 0">
                    <TableCell colspan="6" class="py-12 text-center text-gray-400">
                      Aucune affectation trouvée
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </CardContent>

          <!-- ── Pagination Footer ──────────────────── -->
          <div class="border-t border-gray-200 px-6 py-3 flex items-center justify-between bg-[#F7F8FB]">
            <p class="text-sm text-gray-500">
              <template v-if="meta.total > 0">
                {{ meta.from }}–{{ meta.to }} sur {{ meta.total }} affectation{{ meta.total !== 1 ? 's' : '' }}
              </template>
              <template v-else>Aucune affectation</template>
            </p>

            <div class="flex items-center gap-1">
              <Button
                variant="outline" size="sm"
                :disabled="!meta.has_previous_pages || listLoading"
                @click="loadPage(meta.current_page - 1)"
              >
                <ChevronLeft class="w-4 h-4" />
              </Button>

              <Button
                v-for="n in pageNumbers"
                :key="n"
                :variant="n === meta.current_page ? 'default' : 'outline'"
                size="sm"
                :disabled="listLoading"
                @click="loadPage(n)"
              >
                {{ n }}
              </Button>

              <Button
                variant="outline" size="sm"
                :disabled="!meta.has_more_pages || listLoading"
                @click="loadPage(meta.current_page + 1)"
              >
                <ChevronRight class="w-4 h-4" />
              </Button>
            </div>

            <select
              v-model="perPage"
              class="text-sm border border-gray-200 rounded-lg px-2 py-1 bg-white text-gray-600 focus:outline-none focus:border-[#0F2356] transition-colors"
            >
              <option :value="10">10 / page</option>
              <option :value="15">15 / page</option>
              <option :value="25">25 / page</option>
              <option :value="50">50 / page</option>
            </select>
          </div>
        </Card>

      </div>

      <!-- Modal réassignation -->
      <AssignIncidentModal
        v-model="showAssignModal"
        :assignmentId="selectedAssignmentId"
        :agents="agentsByCategory"
        :loading="actionLoading"
        @assigned="loadPage(currentPage)"
      />
    </main>
  </div>
</template>