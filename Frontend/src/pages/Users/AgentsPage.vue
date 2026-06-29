<script setup>
import { storeToRefs } from 'pinia'

import { ref, computed, onMounted, watch } from 'vue'
import {
  UserCheck, UserX, UserPlus, Search,
  Download, Users, ShieldCheck,
  ChevronLeft, ChevronRight,
} from 'lucide-vue-next'

// ── UI Components ─────────────────────────────────────
import Card        from '../../components/ui/Card.vue'
import CardHeader  from '../../components/ui/CardHeader.vue'
import CardContent from '../../components/ui/CardContent.vue'
import Badge       from '../../components/ui/Badge.vue'
import Button      from '../../components/Button.vue'
import Table       from '../../components/ui/Table/Table.vue'
import TableHeader from '../../components/ui/Table/TableHeader.vue'
import TableHead   from '../../components/ui/Table/TableHead.vue'
import TableBody   from '../../components/ui/Table/TableBody.vue'
import TableRow    from '../../components/ui/Table/TableRow.vue'
import TableCell   from '../../components/ui/Table/TableCell.vue'
import LoadingSpinner from '../../components/LoadingSpinner.vue'

// ── Feature Components ────────────────────────────────
import AddAgentModal from './AddAgentModal.vue'
import Sidebar       from '../../components/Sidebar/Sidebar.vue'
import DashboardLayout from '../../components/DashboardLayout.vue'

// ── Composables & Stores ──────────────────────────────
import { useUsers  } from '../../composables/useUsers'
import { useAuthStore } from '../../stores/AuthStore'

const {
  agents, agentsMeta,
  listLoading, actionLoading,
  fetchAgents, CreateAgent, toggleUserStatus,
} = useUsers()

// ── Pagination state ──────────────────────────────────
const currentPage = ref(1)
const perPage     = ref(10)

const pageNumbers = computed(() => {
  const last  = agentsMeta.value.last_page
  const cur   = agentsMeta.value.current_page
  const delta = 2
  const range = []
  for (let i = Math.max(1, cur - delta); i <= Math.min(last, cur + delta); i++) {
    range.push(i)
  }
  return range
})

const loadPage = (page) => {
  currentPage.value = page
  fetchAgents(page, perPage.value, true)
}

onMounted(() => loadPage(1))
watch(perPage, () => loadPage(1))
const { userRole } = storeToRefs(useAuthStore())


// ── State ─────────────────────────────────────────────
const showAddModal = ref(false)
const roleFilter   = ref('all')
const search       = ref('')

// ── Role filter options ───────────────────────────────
const roleOptions = [
  { value: 'all',             label: 'Tous les service'   },
  { value: 'Incendies et risques thermiques / gaz',         label: 'Incendies et risques thermiques / gaz'          },
  { value: 'Voirie et infrastructures routières',           label: 'Voirie et infrastructures routières'            },
  { value: 'Électricité et éclairage public', label: 'Électricité et éclairage public'     },
  { value: 'Eau et assainissement',     label: 'Eau et assainissement'      },
  { value: 'Espaces verts et environnement',     label: 'Espaces verts et environnement'      },
  { value: 'Déchets et propreté urbaine',     label: 'Déchets et propreté urbaine'      },
  { value: 'Mobilier urbain',     label: 'Mobilier urbain'      },
]

// ── Badge variant maps ────────────────────────────────
const categoryToVariant = {
  'Incendies et risques thermiques / gaz': 'incendie',
  'Voirie et infrastructures routières': 'voirie',
  'Électricité et éclairage public': 'electricite',
  'Eau et assainissement': 'eau',
  'Espaces verts et environnement': 'environnement',
  'Déchets et propreté urbaine': 'dechets',
  'Mobilier urbain': 'mobilier',
  'all': 'secondary'
}



// ── Filtered list ─────────────────────────────────────
const filteredUsers = computed(() => {
  return (agents.value || []).filter(u => {
    const matchesRole   = roleFilter.value === 'all' || u.role === roleFilter.value
    const q             = search.value.toLowerCase()
    const matchesSearch =
      u.name?.toLowerCase().includes(q) ||
      u.email?.toLowerCase().includes(q)
    return matchesRole && matchesSearch
  })
})

// ── KPI counts ────────────────────────────────────────
const totalAgents  = computed(() => agentsMeta.value.total)
const activeAgents = computed(() => (agents.value || []).filter(u => u.status === 'active').length)

// ── Helpers ───────────────────────────────────────────
const getInitials = (name) =>
  name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) ?? '?'
</script>

<template>
  <div class="flex">
    <!-- <Sidebar /> -->
    <main class="flex-1 overflow-y-auto  bg-gray-50">
      <!-- <DashboardLayout /> -->

      <div class="space-y-6 p-6">

        <!-- ── KPI Counters ──────────────────────────── -->
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-[#0F2356]/10 flex items-center justify-center shrink-0">
              <Users class="w-6 h-6 text-[#0F2356]" />
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wide mb-0.5">Total agents</p>
              <p class="text-2xl font-bold text-[#0F2356]">{{ totalAgents }}</p>
            </div>
          </div>
          <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
              <ShieldCheck class="w-6 h-6 text-green-600" />
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wide mb-0.5">Agents actifs</p>
              <p class="text-2xl font-bold text-[#0F2356]">{{ activeAgents }}</p>
            </div>
          </div>
        </div>

        <!-- ── Actions Bar ──────────────────────────── -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 flex items-center gap-3 shadow-sm">
          <!-- Search -->
          <div class="relative flex-1">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
            <input
              v-model="search"
              type="text"
              placeholder="Rechercher un agent..."
              class="w-full bg-[#F7F8FB] border border-gray-200 rounded-lg pl-10 pr-4 py-2 text-sm text-[#0F2356] placeholder-gray-400 focus:outline-none focus:border-[#0F2356] focus:ring-2 focus:ring-[#0F2356]/20 transition-colors"
            />
          </div>

          <!-- Role filter (super admin only) -->
          <select
            v-if="userRole === 'super admin'"
            v-model="roleFilter"
            class="bg-[#F7F8FB] border border-gray-200 rounded-lg px-4 py-2 text-sm text-gray-600 focus:outline-none focus:border-[#0F2356] transition-colors"
          >
            <option v-for="opt in roleOptions" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </select>

          <!-- Export -->
          

          <!-- Add agent -->
          <Button variant="destructive" size="sm" @click="showAddModal = true">
            <UserPlus class="w-4 h-4" />
            Nouvel agent
          </Button>
        </div>

        <!-- ── Agents Card ───────────────────────────── -->
        <Card>
          <!-- Card Header -->
          <CardHeader :grid="true">
            <h2 class="text-white text-lg mb-1">Agents</h2>
            <p class="text-white/50 text-sm">Gérer les comptes agents de la municipalité</p>
          </CardHeader>

          <!-- Table -->
          <CardContent class="p-0">
                      <div class="relative min-h-[320px]">
  <div v-if="listLoading" class="absolute inset-0 flex items-center justify-center">
    <LoadingSpinner />
  </div>
<Table v-show="!listLoading">
              <TableHeader>
                <TableRow>
                  <TableHead>Agent</TableHead>
                  <TableHead class="hidden md:table-cell">Email</TableHead>
                  <TableHead>Service</TableHead>
                  <!-- <TableHead class="hidden lg:table-cell">Ville</TableHead> -->
                  <TableHead>Statut</TableHead>
                  <TableHead class="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>

              <TableBody>
                <TableRow v-for="user in filteredUsers" :key="user.id">

                  <!-- Agent (avatar + nom) -->
                  <TableCell>
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-full bg-[#0F2356] text-white text-xs font-bold flex items-center justify-center shrink-0 shadow-sm">
                        {{ getInitials(user.name) }}
                      </div>
                      <span class="text-[#0F2356] font-medium">{{ user.name }}</span>
                    </div>
                  </TableCell>

                  <!-- Email -->
                  <TableCell class="hidden md:table-cell text-gray-500">
                    {{ user.email }}
                  </TableCell>

                  <!-- Rôle -->
                  <TableCell>
                    <Badge :variant="categoryToVariant[user?.category?.name] ?? 'secondary'">
                      {{ user?.category?.name ??  '—' }}
                    </Badge>
                  </TableCell>

                  <!-- Ville -->
                  <!-- <TableCell class="hidden lg:table-cell text-gray-500">
                    {{ user.city || '—' }}
                  </TableCell> -->

                  <!-- Statut -->
                  <TableCell>
                    <Badge :variant="user.status === 'active' ? 'status-resolved' : 'status-rejected'">
                      <component
                        :is="user.status === 'active' ? UserCheck : UserX"
                        class="w-3.5 h-3.5"
                      />
                      {{ user.status === 'active' ? 'Actif' : 'Inactif' }}
                    </Badge>
                  </TableCell>

                  <!-- Actions -->
                  <TableCell class="text-right">
                    <Button
                      variant="ghost"
                      size="icon"
                      :title="user.status === 'active' ? 'Désactiver' : 'Activer'"
                      :class="user.status === 'active'
                        ? 'text-red-500 hover:text-red-700 hover:bg-red-50'
                        : 'text-emerald-500 hover:text-emerald-700 hover:bg-emerald-50'"
                      @click="toggleUserStatus(user.id, user.status)"
                    >
                      <UserX      v-if="user.status === 'active'" class="w-4 h-4" />
                      <UserCheck  v-else                          class="w-4 h-4" />
                    </Button>
                  </TableCell>

                </TableRow>

                <!-- Empty state -->
                <TableRow v-if="!listLoading && filteredUsers.length === 0">
                  <TableCell colspan="6" class="py-12 text-center text-gray-400">
                    Aucun agent trouvé
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
                      </div>
          </CardContent>

          <!-- ── Pagination Footer ──────────────────── -->
          <div class="border-t border-gray-200 px-6 py-3 flex items-center justify-between bg-[#F7F8FB]">
            <p class="text-sm text-gray-500">
              <template v-if="agentsMeta.total > 0">
                {{ agentsMeta.from }}–{{ agentsMeta.to }} sur {{ agentsMeta.total }}
                agent{{ agentsMeta.total !== 1 ? 's' : '' }}
              </template>
              <template v-else>Aucun agent</template>
            </p>

            <div class="flex items-center gap-1">
              <Button
                variant="outline" size="sm"
                :disabled="!agentsMeta.has_previous_pages || listLoading"
                @click="loadPage(agentsMeta.current_page - 1)"
              >
                <ChevronLeft class="w-4 h-4" />
              </Button>

              <Button
                v-for="n in pageNumbers" :key="n"
                :variant="n === agentsMeta.current_page ? 'default' : 'outline'"
                size="sm"
                :disabled="listLoading"
                @click="loadPage(n)"
              >
                {{ n }}
              </Button>

              <Button
                variant="outline" size="sm"
                :disabled="!agentsMeta.has_more_pages || listLoading"
                @click="loadPage(agentsMeta.current_page + 1)"
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

      <!-- Modal ajout agent -->
      <AddAgentModal
        v-model="showAddModal"
        @created="loadPage(currentPage)"
      />
    </main>
  </div>
</template>