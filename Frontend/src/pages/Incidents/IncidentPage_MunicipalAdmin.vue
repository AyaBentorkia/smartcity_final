<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import { IncidentStatus } from "../../constants/IncidentStatus";
import {
  CheckCircle, XCircle, UserPlus, Eye,
  Clock11, CheckCircle2, Search,
  AlertCircle, Clock,
  ChevronLeft, ChevronRight,  // FIX : étaient manquants → erreurs console
} from "lucide-vue-next";

import { useIncidents }   from '../../composables/useIncidents'
import { useAssignments } from "../../composables/useAssignments";
import { useUsers }       from '../../composables/useUsers'

// ── UI Components ─────────────────────────────────────
import Card        from "../../components/ui/Card.vue";
import CardHeader  from "../../components/ui/CardHeader.vue";
import CardContent from "../../components/ui/CardContent.vue";
import Badge       from "../../components/ui/Badge.vue";
import Button      from "../../components/Button.vue";
import Table       from "../../components/ui/Table/Table.vue";
import TableHeader from "../../components/ui/Table/TableHeader.vue";
import TableHead   from "../../components/ui/Table/TableHead.vue";
import TableBody   from "../../components/ui/Table/TableBody.vue";
import TableRow    from "../../components/ui/Table/TableRow.vue";
import TableCell   from "../../components/ui/Table/TableCell.vue";

// ── Feature Components ────────────────────────────────
import ViewIncidentModal   from "./ViewIncidentModal.vue";
import AssignIncidentModal from "../Assignments/AssignIncidentModal.vue";
import LoadingSpinner      from "../../components/LoadingSpinner.vue";

// ── Composables ───────────────────────────────────────
const {
  incidents, incident, listLoading, detailLoading, actionLoading, error, meta,
  fetchIncidentById, fetchIncidentsNearBy, UpdateIncidentStatus,
} = useIncidents()

const { agentsByCategory, fetchAgentsByCategory } = useUsers()
const { AssignIncident } = useAssignments();

// ── State ─────────────────────────────────────────────
const showModal          = ref(false);
const showAssignModal    = ref(false);
const selectedIncidentId = ref(null);
const filter             = ref("all");
const search             = ref("");
const incidentStatus     = IncidentStatus;

// ── Pagination ────────────────────────────────────────
const currentPage = ref(1)
const perPage     = ref(10)

// ── Badge maps ────────────────────────────────────────
const statusVariant: Record<string, string> = {
  [incidentStatus.REPORTED]:    'status-reported',
  [incidentStatus.VALIDATED]:   'status-validated',
  [incidentStatus.IN_PROGRESS]: 'status-in_progress',
  [incidentStatus.RESOLVED]:    'status-resolved',
  [incidentStatus.REJECTED]:    'status-rejected',
};

const statusLabel: Record<string, string> = {
  [incidentStatus.REPORTED]:    'Signalé',
  [incidentStatus.VALIDATED]:   'Validé',
  [incidentStatus.IN_PROGRESS]: 'En cours',
  [incidentStatus.RESOLVED]:    'Résolu',
  [incidentStatus.REJECTED]:    'Rejeté',
};

const statusIcon: Record<string, any> = {
  [incidentStatus.REPORTED]:    AlertCircle,
  [incidentStatus.VALIDATED]:   AlertCircle,
  [incidentStatus.IN_PROGRESS]: Clock,
  [incidentStatus.RESOLVED]:    CheckCircle2,
  [incidentStatus.REJECTED]:    XCircle,
};

// ── Filtres ───────────────────────────────────────────
const filterOptions = [
  { value: 'all',                      label: 'Tous les statuts' },
  { value: incidentStatus.REPORTED,    label: 'Signalé'  },
  { value: incidentStatus.VALIDATED,   label: 'Validé'   },
  { value: incidentStatus.IN_PROGRESS, label: 'En cours' },
  { value: incidentStatus.RESOLVED,    label: 'Résolu'   },
  { value: incidentStatus.REJECTED,    label: 'Rejeté'   },
];

// FIX : filtre client uniquement sur la page courante
// Le total affiché reflète le filtrage local pour éviter l'incohérence
const filteredIncidents = computed(() => {
  let list = incidents.value ?? [];
  if (filter.value !== 'all') {
    list = list.filter((i) => i.status === filter.value);
  }
  if (search.value.trim()) {
    const q = search.value.trim().toLowerCase();
    list = list.filter(
      (i) =>
        i.title?.toLowerCase().includes(q) ||
        String(i.id).includes(q) ||
        i.category?.name?.toLowerCase().includes(q)
    );
  }
  return list;
});

// Indique si un filtre local est actif (pour afficher le bon compteur)
const isFiltered = computed(() => filter.value !== 'all' || search.value.trim() !== '')

// ── Numéros de pages ──────────────────────────────────
const pageNumbers = computed(() => {
  const last  = meta.value.last_page
  const cur   = meta.value.current_page
  const delta = 2
  const range: number[] = []
  for (let i = Math.max(1, cur - delta); i <= Math.min(last, cur + delta); i++) {
    range.push(i)
  }
  return range
})

// ── Chargement ────────────────────────────────────────
const loadPage = (page: number) => {
  currentPage.value = page
  fetchIncidentsNearBy(page, perPage.value, true)
}

onMounted(() => loadPage(1))

// Quand search/filter change → reset à la page 1
watch([search, filter], () => { currentPage.value = 1 })

// ── Handlers ──────────────────────────────────────────
const handleView = async (id) => {
  showModal.value = true;
  await fetchIncidentById(id);
};

const handleUpdateIncidentStatus = async (id, status) =>
  await UpdateIncidentStatus(id, status);

const handleAssignIncident = async (incidentId, agentId) =>
  await AssignIncident(incidentId, agentId);

// FIX : toujours recharger les agents (la catégorie peut changer entre deux clics)
const handleAsssignModal = async (id, categoryId) => {
  selectedIncidentId.value = id;
  showAssignModal.value    = true;
  await fetchAgentsByCategory(categoryId);
};

function formatDate(dateStr) {
  if (!dateStr) return '—'
  return dateStr.split('T')[0]  // → "2026-05-25"
}
</script>

<template>
  <div class="flex bg-gray-50">
    <main class="flex-1 overflow-y-auto">
      <div class="space-y-6 p-6">

        <!-- ── Actions Bar ──────────────────────────────── -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 flex items-center gap-3 shadow-sm">
          <div class="relative flex-1">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
            <input
              v-model="search"
              type="text"
              placeholder="Rechercher un incident..."
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
        </div>

        <!-- ── Error ────────────────────────────────────── -->
        <div
          v-if="error"
          class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-4 py-3 flex items-center gap-2"
        >
          <AlertCircle class="w-4 h-4 shrink-0" />
          {{ error }}
        </div>

        <!-- ── Incidents Card ────────────────────────────── -->
        <Card>
          <CardHeader :grid="true">
            <h2 class="text-white text-lg mb-1">Incidents</h2>
            <p class="text-white/50 text-sm">Gérer les incidents signalés par les citoyens</p>
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
                    <TableHead>Titre</TableHead>
                    <TableHead class="hidden md:table-cell">Catégorie</TableHead>
                    <TableHead>Date de Signalisation</TableHead>
                    <TableHead>Statut</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>

                <TableBody>
                  <TableRow v-for="inc in filteredIncidents" :key="inc.id">
                    <!-- ID -->
                    <!-- <TableCell class="font-mono text-gray-500">
                      {{ inc.id }}
                    </TableCell> -->

                    <!-- Titre -->
                    <TableCell class="text-[#0F2356] max-w-[200px] truncate font-medium">
                      {{ inc.title }}
                    </TableCell>

                    <!-- Catégorie -->
                    <TableCell class="hidden md:table-cell text-gray-500">
                      {{ inc?.category?.name }}
                    </TableCell>

                    <!-- Date -->
                    <TableCell class="hidden md:table-cell text-gray-500">
                        {{ formatDate(inc?.created_at) }}
                    </TableCell>

                    <!-- Statut -->
                    <TableCell>
                      <Badge :variant="statusVariant[inc.status] ?? 'secondary'">
                        <component :is="statusIcon[inc.status] ?? AlertCircle" class="w-3.5 h-3.5" />
                        {{ statusLabel[inc.status] ?? inc.status }}
                      </Badge>
                    </TableCell>

                    <!-- Actions -->
                    <TableCell>
                      <div class="flex items-center gap-1">
                        <!-- Voir -->
                        <Button
                          variant="ghost"
                          size="icon"
                          title="Voir"
                          :disabled="detailLoading"
                          @click="handleView(inc.id)"
                        >
                          <Eye class="w-4 h-4" />
                        </Button>

                        <!-- Valider (REPORTED) -->
                        <template v-if="inc.status === incidentStatus.REPORTED">
                          <Button
                            variant="ghost"
                            size="icon"
                            title="Valider"
                            :disabled="actionLoading"
                            class="text-emerald-500 hover:text-emerald-700 hover:bg-emerald-50"
                            @click="handleUpdateIncidentStatus(inc.id, incidentStatus.VALIDATED)"
                          >
                            <CheckCircle class="w-4 h-4" />
                          </Button>
                        </template>

                        <!-- Résoudre (IN_PROGRESS) -->
                        <template v-if="inc.status === incidentStatus.IN_PROGRESS">
                          <Button
                            variant="ghost"
                            size="icon"
                            title="Résoudre"
                            :disabled="actionLoading"
                            class="text-emerald-500 hover:text-emerald-700 hover:bg-emerald-50"
                            @click="handleUpdateIncidentStatus(inc.id, incidentStatus.RESOLVED)"
                          >
                            <CheckCircle2 class="w-4 h-4" />
                          </Button>
                        </template>

                        <!-- Mettre en cours + Rejeter + Assigner (VALIDATED) -->
                        <template v-if="inc.status === incidentStatus.VALIDATED">
                          <Button
                            variant="ghost"
                            size="icon"
                            title="Mettre en cours"
                            :disabled="actionLoading"
                            class="text-purple-500 hover:text-purple-700 hover:bg-purple-50"
                            @click="handleUpdateIncidentStatus(inc.id, incidentStatus.IN_PROGRESS)"
                          >
                            <Clock11 class="w-4 h-4" />
                          </Button>

                          <!-- FIX : @click manquant sur Rejeter -->
                          <Button
                            variant="ghost"
                            size="icon"
                            title="Rejeter"
                            :disabled="actionLoading"
                            class="text-red-500 hover:text-red-700 hover:bg-red-50"
                            @click="handleUpdateIncidentStatus(inc.id, incidentStatus.REJECTED)"
                          >
                            <XCircle class="w-4 h-4" />
                          </Button>

                          <Button
                            variant="ghost"
                            size="icon"
                            title="Assigner"
                            class="text-[#0F2356] hover:bg-[#0F2356]/10"
                            @click="handleAsssignModal(inc.id, inc.category_id)"
                          >
                            <UserPlus class="w-4 h-4" />
                          </Button>
                        </template>
                      </div>
                    </TableCell>
                  </TableRow>

                  <!-- Empty state -->
                  <TableRow v-if="!listLoading && filteredIncidents.length === 0">
                    <TableCell colspan="6" class="py-12 text-center text-gray-400">
                      Aucun incident trouvé
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </CardContent>

          <!-- ── Pagination Footer ──────────────────────── -->
          <div class="border-t border-gray-200 px-6 py-3 flex items-center justify-between bg-[#F7F8FB]">
            <!-- Compteur
                 FIX : si un filtre local est actif, affiche le compte filtré
                 pour éviter l'incohérence avec meta.total (qui est le total serveur) -->
            <p class="text-sm text-gray-500">
              <template v-if="isFiltered">
                {{ filteredIncidents.length }} incident{{ filteredIncidents.length !== 1 ? 's' : '' }} (filtrés)
              </template>
              <template v-else-if="meta.total > 0">
                {{ meta.from }}–{{ meta.to }} sur {{ meta.total }} incident{{ meta.total !== 1 ? 's' : '' }}
              </template>
              <template v-else>
                Aucun incident
              </template>
            </p>

            <!-- Contrôles de page -->
            <div class="flex items-center gap-1">
              <!-- Précédent — FIX : utilise has_previous_pages du meta (fourni par le backend) -->
              <Button
                variant="outline"
                size="sm"
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

              <!-- Suivant -->
              <Button
                variant="outline"
                size="sm"
                :disabled="!meta.has_more_pages || listLoading"
                @click="loadPage(meta.current_page + 1)"
              >
                <ChevronRight class="w-4 h-4" />
              </Button>
            </div>

            <!-- Par page -->
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

      <!-- Modals -->
      <ViewIncidentModal
        v-model="showModal"
        :incident="incident"
        :loading="detailLoading"
        role="municipal admin"
      />
      <AssignIncidentModal
        v-model="showAssignModal"
        :incidentId="selectedIncidentId"
        :agents="agentsByCategory"
        :loading="actionLoading"
        @assigned="handleAssignIncident"
      />
    </main>
  </div>
</template>