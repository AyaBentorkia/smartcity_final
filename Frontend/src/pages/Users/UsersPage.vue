<script setup>
import { storeToRefs } from 'pinia'
import { ref, computed, onMounted, watch } from 'vue'
import {
  UserCheck, UserX, Search, UserPlus, Eye,
  ShieldCheck, Users, ChevronLeft, ChevronRight,
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

import { useUsers } from '../../composables/useUsers'
import { useAuthStore } from '../../stores/AuthStore'
import { UserRole } from '../../constants/UserRole'
import AddUserModal  from './AddUserModal.vue'
import ViewUserModal from './ViewUserModal.vue'

const {
  users, user, usersMeta,
  listLoading, detailLoading,
  fetchUsers, fetchUserById, CreateUser, toggleUserStatus,
} = useUsers()

const { userRole } = storeToRefs(useAuthStore())

// ── Pagination state ──────────────────────────────────
const currentPage = ref(1)
const perPage     = ref(10)

const pageNumbers = computed(() => {
  const last  = usersMeta.value.last_page
  const cur   = usersMeta.value.current_page
  const delta = 2
  const range = []
  for (let i = Math.max(1, cur - delta); i <= Math.min(last, cur + delta); i++) {
    range.push(i)
  }
  return range
})

const loadPage = (page) => {
  currentPage.value = page
  fetchUsers(page, perPage.value, true)
}

onMounted(() => loadPage(1))

// ── State ─────────────────────────────────────────────
const showAddModal  = ref(false)
const showViewModal = ref(false)
const roleFilter    = ref('all')
const search        = ref('')

// Retour page 1 si filtre local change
watch([search, roleFilter], () => { currentPage.value = 1 })

// ── Role filter options ───────────────────────────────
const roleOptions = [
  { value: 'all',                  label: 'Tous les rôles'        },
  { value: UserRole.SUPER_ADMIN,   label: 'Super admin'           },
  { value: UserRole.ADMIN_MUNICIPAL, label: 'Responsable municipal' },
  { value: UserRole.AGENT,         label: 'Agent'                 },
  { value: UserRole.CITIZEN,       label: 'Citoyen'               },
]

// ── Filtered list (filtre local sur la page courante) ─
const filteredUsers = computed(() =>
  (users.value || []).filter(u => {
    const matchesRole   = roleFilter.value === 'all' || u.role === roleFilter.value
    const q             = search.value.toLowerCase()
    const matchesSearch = u.name?.toLowerCase().includes(q) || u.email?.toLowerCase().includes(q)
    return matchesRole && matchesSearch
  })
)

// ── KPI (depuis meta serveur) ─────────────────────────
const totalUsers   = computed(() => usersMeta.value.total)
const activeUsers  = computed(() => (users.value || []).filter(u => u.status === 'active').length)

// ── Handlers ─────────────────────────────────────────
const handleView = async (id) => {
  showViewModal.value = true
  await fetchUserById(id)
}

// ── Helpers ───────────────────────────────────────────
const getInitials = (name) =>
  name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) ?? '?'
</script>

<template>
  <div class="flex">
    <main class="flex-1 overflow-y-auto bg-gray-50">
      <div class="space-y-6 p-6">

        <!-- ── KPI Counters ──────────────────────────── -->
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-[#0F2356]/10 flex items-center justify-center shrink-0">
              <Users class="w-6 h-6 text-[#0F2356]" />
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wide mb-0.5">Total utilisateurs</p>
              <p class="text-2xl font-bold text-[#0F2356]">{{ totalUsers }}</p>
            </div>
          </div>
          <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
              <ShieldCheck class="w-6 h-6 text-green-600" />
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wide mb-0.5">Actifs (page)</p>
              <p class="text-2xl font-bold text-[#0F2356]">{{ activeUsers }}</p>
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
              placeholder="Rechercher un utilisateur..."
              class="w-full bg-[#F7F8FB] border border-gray-200 rounded-lg pl-10 pr-4 py-2 text-sm text-[#0F2356] placeholder-gray-400 focus:outline-none focus:border-[#0F2356] focus:ring-2 focus:ring-[#0F2356]/20 transition-colors"
            />
          </div>

          <select
            v-model="roleFilter"
            class="bg-[#F7F8FB] border border-gray-200 rounded-lg px-4 py-2 text-sm text-gray-600 focus:outline-none focus:border-[#0F2356] transition-colors"
          >
            <option v-for="opt in roleOptions" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </select>

          <Button
            size="sm"
            class="flex items-center gap-2 px-4 py-2 bg-[#CC1525] text-white text-sm font-medium rounded hover:bg-[#a81220] transition-colors"
            @click="showAddModal = true"
          >
            <UserPlus class="w-4 h-4" />
            Nouvel utilisateur
          </Button>
        </div>

        <!-- ── Table Card ─────────────────────────────── -->
        <Card>
          <CardHeader :grid="true">
            <h2 class="text-white text-lg mb-1">Utilisateurs</h2>
            <p class="text-white/50 text-sm">Gérer les comptes utilisateurs</p>
          </CardHeader>

          <CardContent class="p-0">
            <div class="relative min-h-[400px]">
              <div v-if="listLoading" class="absolute inset-0 flex items-center justify-center">
                <LoadingSpinner />
              </div>

              <Table v-show="!listLoading">
                <TableHeader>
                  <TableRow>
                    <TableHead>Utilisateur</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Rôle</TableHead>
                    <!-- <TableHead class="hidden md:table-cell">Ville</TableHead> -->
                    <TableHead>Statut</TableHead>
                    <TableHead class="text-right">Actions</TableHead>
                  </TableRow>
                </TableHeader>

                <TableBody>
                  <TableRow v-for="u in filteredUsers" :key="u.id">

                    <TableCell>
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-[#0F2356] text-white text-xs font-bold flex items-center justify-center shrink-0 shadow-sm">
                          {{ getInitials(u.name) }}
                        </div>
                        <span class="text-[#0F2356] font-medium">{{ u.name }}</span>
                      </div>
                    </TableCell>

                    <TableCell class="hidden md:table-cell text-gray-500">
                      {{ u.email }}
                    </TableCell>

                    <TableCell>
                      <Badge>{{ u.role }}</Badge>
                    </TableCell>

                    <!-- <TableCell class="hidden lg:table-cell text-gray-500">
                      {{ u.city || '—' }}
                    </TableCell> -->

                    <TableCell>
                      <Badge :variant="u.status === 'active' ? 'status-resolved' : 'status-rejected'">
                        <component :is="u.status === 'active' ? UserCheck : UserX" class="w-3.5 h-3.5" />
                        {{ u.status === 'active' ? 'Actif' : 'Inactif' }}
                      </Badge>
                    </TableCell>

                    <TableCell class="text-right">
                      <div class="flex items-center justify-end gap-1">
                        <Button
                          variant="ghost" size="icon" title="Voir"
                          :disabled="detailLoading"
                          @click="handleView(u.id)"
                        >
                          <Eye class="h-4 w-4" />
                        </Button>

                        <Button
                          variant="ghost" size="icon"
                          :title="u.status === 'active' ? 'Désactiver' : 'Activer'"
                          :class="u.status === 'active'
                            ? 'text-red-500 hover:text-red-700 hover:bg-red-50'
                            : 'text-emerald-500 hover:text-emerald-700 hover:bg-emerald-50'"
                          @click="toggleUserStatus(u.id, u.status)"
                        >
                          <UserX     v-if="u.status === 'active'" class="w-4 h-4" />
                          <UserCheck v-else                       class="w-4 h-4" />
                        </Button>
                      </div>
                    </TableCell>

                  </TableRow>

                  <TableRow v-if="!listLoading && filteredUsers.length === 0">
                    <TableCell colspan="6" class="py-12 text-center text-gray-400">
                      Aucun utilisateur trouvé
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </CardContent>

          <!-- ── Pagination Footer ──────────────────── -->
          <div class="border-t border-gray-200 px-6 py-3 flex items-center justify-between bg-[#F7F8FB]">
            <p class="text-sm text-gray-500">
              <template v-if="usersMeta.total > 0">
                {{ usersMeta.from }}–{{ usersMeta.to }} sur {{ usersMeta.total }}
                utilisateur{{ usersMeta.total !== 1 ? 's' : '' }}
              </template>
              <template v-else>Aucun utilisateur</template>
            </p>

            <div class="flex items-center gap-1">
              <Button
                variant="outline" size="sm"
                :disabled="!usersMeta.has_previous_pages || listLoading"
                @click="loadPage(usersMeta.current_page - 1)"
              >
                <ChevronLeft class="w-4 h-4" />
              </Button>

              <Button
                v-for="n in pageNumbers" :key="n"
                :variant="n === usersMeta.current_page ? 'default' : 'outline'"
                size="sm"
                :disabled="listLoading"
                @click="loadPage(n)"
              >
                {{ n }}
              </Button>

              <Button
                variant="outline" size="sm"
                :disabled="!usersMeta.has_more_pages || listLoading"
                @click="loadPage(usersMeta.current_page + 1)"
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

      <AddUserModal
        v-model="showAddModal"
        @created="loadPage(currentPage)"
      />
      <ViewUserModal
        v-model="showViewModal"
        :user="user"
        :loading="detailLoading"
      />
    </main>
  </div>
</template>