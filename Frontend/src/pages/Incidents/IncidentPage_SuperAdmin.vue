<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import { IncidentStatus } from "../../constants/IncidentStatus";
import {
  XCircle, Eye, CheckCircle2, Trash2, AlertCircle, Clock,
  ChevronLeft, ChevronRight,
} from "lucide-vue-next";
import { useIncidents } from "../../composables/useIncidents";

import CardHeader  from "../../components/ui/CardHeader.vue";
import CardContent from "../../components/ui/CardContent.vue";
import Card        from "../../components/ui/Card.vue";
import Badge       from "../../components/ui/Badge.vue";
import Button      from "../../components/Button.vue";
import Table       from "../../components/ui/Table/Table.vue";
import TableBody   from "../../components/ui/Table/TableBody.vue";
import TableCell   from "../../components/ui/Table/TableCell.vue";
import TableHead   from "../../components/ui/Table/TableHead.vue";
import TableHeader from "../../components/ui/Table/TableHeader.vue";
import TableRow    from "../../components/ui/Table/TableRow.vue";
import ViewIncidentModal   from "./ViewIncidentModal.vue";
import DeleteIncidentModal from "./DeleteIncidentModal.vue";
import LoadingSpinner      from "../../components/LoadingSpinner.vue";

// ── Store ─────────────────────────────────────────────
// storeToRefs → uniquement le state réactif (refs)
// les méthodes se déstructurent directement depuis le store
const {
  incidents, incident, meta, listLoading, detailLoading, actionLoading, error,
  fetchIncidentById, fetchAllIncidents, DeleteIncident,
} = useIncidents()

// ── Pagination state ──────────────────────────────────
const currentPage = ref(1)
const perPage     = ref(10)

// ── State ─────────────────────────────────────────────
const showViewModal    = ref(false);
const showDeleteModal  = ref(false);
const incidentToDelete = ref<{ id: number; title: string } | null>(null);
const filter           = ref("all");
const search           = ref("");
const incidentStatus   = IncidentStatus;

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

// ── Filter options ────────────────────────────────────
const filterOptions = [
  { value: 'all',                      label: 'Tous les statuts' },
  { value: incidentStatus.REPORTED,    label: 'Signalé'  },
  { value: incidentStatus.VALIDATED,   label: 'Validé'   },
  { value: incidentStatus.IN_PROGRESS, label: 'En cours' },
  { value: incidentStatus.RESOLVED,    label: 'Résolu'   },
  { value: incidentStatus.REJECTED,    label: 'Rejeté'   },
];

// ── Filtered list ─────────────────────────────────────
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

// ── Pages calculées ───────────────────────────────────
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
  fetchAllIncidents(page, perPage.value, true)
}

onMounted(() => loadPage(1))
watch([search, filter], () => { currentPage.value = 1 })

// ── Handlers ──────────────────────────────────────────
const handleView = async (id: number) => {
  showViewModal.value = true;
  await fetchIncidentById(id);
};

const handleDeleteClick = (inc: any) => {
  incidentToDelete.value = { id: inc.id, title: inc.title || `Incident #${inc.id}` };
  showDeleteModal.value = true;
};

const handleDeleteCancel = () => {
  showDeleteModal.value = false;
  incidentToDelete.value = null;
};

const handleDeleteConfirm = async () => {
  if (!incidentToDelete.value) return;
  await DeleteIncident(incidentToDelete.value.id);
  showDeleteModal.value = false;
  incidentToDelete.value = null;
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

        <!-- ── Error ──────────────────────────────────── -->
        <div
          v-if="error"
          class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-4 py-3 flex items-center gap-2"
        >
          <AlertCircle class="w-4 h-4 shrink-0" />
          {{ error }}
        </div>

        <!-- ── Incidents Card ──────────────────────────── -->
        <Card>
          <CardHeader :grid="true">
            <h2 class="text-white text-lg mb-1">Incidents</h2>
            <p class="text-white/50 text-sm">Gérer les incidents signalés par les citoyens</p>
          </CardHeader>

          <CardContent class="p-0">
            <div class="relative min-h-[500px]">
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
                    <!-- <TableCell class="font-mono text-gray-500">{{ inc.id }}</TableCell> -->

                    <TableCell class="text-[#0F2356] max-w-[200px] truncate font-medium">
                      {{ inc.title }}
                    </TableCell>

                    <TableCell class="hidden md:table-cell text-gray-500">
                      {{ inc?.category?.name }}
                    </TableCell>

                    <TableCell class="hidden md:table-cell text-gray-500">
                    {{ formatDate(inc?.created_at) }}
                  </TableCell>

                    <TableCell>
                      <Badge :variant="statusVariant[inc.status] ?? 'secondary'">
                        <component :is="statusIcon[inc.status] ?? AlertCircle" class="w-3.5 h-3.5" />
                        {{ statusLabel[inc.status] ?? inc.status }}
                      </Badge>
                    </TableCell>

                    <TableCell>
                      <div class="flex items-center gap-1">
                        <Button
                          variant="ghost" size="icon" title="Voir"
                          :disabled="detailLoading"
                          @click="handleView(inc.id)"
                        >
                          <Eye class="h-4 w-4" />
                        </Button>

                        <Button
                          variant="ghost" size="icon" title="Supprimer"
                          class="text-red-500 hover:text-red-700 hover:bg-red-50"
                          :disabled="actionLoading"
                          @click="handleDeleteClick(inc)"
                        >
                          <Trash2 class="h-4 w-4" />
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>

                  <TableRow v-if="!listLoading && filteredIncidents.length === 0">
                    <TableCell colspan="6" class="text-center py-10 text-muted-foreground">
                      Aucun incident trouvé
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </CardContent>

          <!-- ── Pagination Footer ──────────────────────── -->
          <div class="border-t border-gray-200 px-6 py-3 flex items-center justify-between bg-[#F7F8FB]">
            <p class="text-sm text-gray-500">
              <template v-if="meta.total > 0">
                {{ meta.from }}–{{ meta.to }} sur {{ meta.total }} incident{{ meta.total !== 1 ? 's' : '' }}
              </template>
              <template v-else>Aucun incident</template>
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

  <ViewIncidentModal
    v-model="showViewModal"
    :incident="incident"
    :loading="detailLoading"
    role="super admin"
  />

  <DeleteIncidentModal
    v-model="showDeleteModal"
    :incident-title="incidentToDelete?.title ?? ''"
    :loading="actionLoading"
    @confirm="handleDeleteConfirm"
    @cancel="handleDeleteCancel"
  />
</template>