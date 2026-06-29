<template>
  <div class="flex">
    <main class="flex-1 overflow-y-auto">
      <div class="flex flex-col h-screen bg-background text-foreground">

        <div class="flex flex-1 overflow-hidden">

          <!-- ==================== SIDEBAR ==================== -->
          <aside class="w-80 min-w-[320px] flex flex-col gap-3 overflow-y-auto bg-card border-r border-border p-4">

            <!-- ── FORMULAIRE CRÉATION ── -->
            <div v-if="!editingZone" class="rounded-xl border border-border bg-background p-4">
              <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-4 flex items-center gap-2">
                <Plus class="w-3.5 h-3.5" />
                Nouvelle zone
              </p>

              <div class="mb-3">
                <label class="block text-xs text-muted-foreground mb-1">
                  Nom <span class="text-destructive">*</span>
                </label>
                <input
                  v-model="form.name"
                  placeholder="Zone Nord, Zone 1…"
                  class="w-full bg-card border border-border text-foreground rounded-lg px-3 py-2 text-sm outline-none focus:border-primary transition-colors"
                />
              </div>

              <div class="mb-3">
                <label class="block text-xs text-muted-foreground mb-1">Description</label>
                <textarea
                  v-model="form.description"
                  rows="2"
                  placeholder="Optionnel…"
                  class="w-full bg-card border border-border text-foreground rounded-lg px-3 py-2 text-sm outline-none focus:border-primary transition-colors resize-y"
                />
              </div>

              <div class="mb-3">
                <label class="block text-xs text-muted-foreground mb-1">
                  Rayon (km) <span class="text-destructive">*</span>
                </label>
                <div class="flex items-center gap-2">
                  <input type="range" v-model.number="form.rayon_km" min="0.1" max="20" step="0.1"
                    @input="updatePreviewCircle" class="flex-1 accent-primary" />
                  <input type="number" v-model.number="form.rayon_km" min="0.1" max="20" step="0.1"
                    @input="updatePreviewCircle"
                    class="w-20 bg-card border border-border text-foreground text-sm rounded-lg px-2 py-1.5 text-center outline-none focus:border-primary transition-colors" />
                  <span class="text-xs text-muted-foreground">km</span>
                </div>
              </div>

              <div v-if="form.latitude_center"
                class="flex items-center gap-2 text-xs px-3 py-2 bg-card border border-border rounded-lg mb-3 font-mono text-foreground">
                <MapPin class="w-3.5 h-3.5 text-primary flex-shrink-0" />
                <span>{{ form.latitude_center.toFixed(5) }}, {{ form.longitude_center.toFixed(5) }}</span>
              </div>
              <div v-else
                class="flex items-center gap-2 text-xs px-3 py-2 bg-card border border-border rounded-lg mb-3 text-muted-foreground italic">
                <MousePointerClick class="w-3.5 h-3.5 flex-shrink-0" />
                <span>Cliquez sur la carte pour placer le centre</span>
              </div>

              <p v-if="error" class="text-xs text-destructive mb-2">{{ error }}</p>

              <button
                :disabled="!canSubmitCreate || actionLoading"
                @click="submitCreate"
                class="w-full flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                style="background:#CC1525; color:#fff;"
                @mouseenter="$event.currentTarget.style.background = actionLoading ? '#CC1525' : '#a8101d'"
                @mouseleave="$event.currentTarget.style.background='#CC1525'"
              >
                <span v-if="actionLoading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                <Plus v-else class="w-4 h-4" />
                {{ actionLoading ? 'Enregistrement…' : 'Créer la zone' }}
              </button>
            </div>

            <!-- ── FORMULAIRE ÉDITION (remplace création) ── -->
            <div v-else class="rounded-xl border-2 bg-background p-4" style="border-color:#0F2356;">

              <!-- Header édition -->
              <div class="flex items-center justify-between mb-4">
                <div>
                  <div class="flex items-center gap-1.5 mb-0.5">
                    <PencilLine class="w-3.5 h-3.5" style="color:#CC1525;" />
                    <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Modification</span>
                  </div>
                  <p class="text-sm font-semibold text-foreground">{{ editingZone.name }}</p>
                </div>
                <button @click="cancelEdit"
                  class="w-7 h-7 rounded-lg flex items-center justify-center bg-muted hover:bg-muted/70 transition-colors">
                  <X class="w-3.5 h-3.5 text-muted-foreground" />
                </button>
              </div>

              <div class="mb-3">
                <label class="block text-xs text-muted-foreground mb-1">
                  Nom <span class="text-destructive">*</span>
                </label>
                <input
                  v-model="editForm.name"
                  placeholder="Zone Nord, Zone 1…"
                  class="w-full bg-card border border-border text-foreground rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0F2356] transition-colors"
                />
              </div>

              <div class="mb-3">
                <label class="block text-xs text-muted-foreground mb-1">Description</label>
                <textarea
                  v-model="editForm.description"
                  rows="2"
                  placeholder="Optionnel…"
                  class="w-full bg-card border border-border text-foreground rounded-lg px-3 py-2 text-sm outline-none transition-colors resize-y"
                />
              </div>

              <div class="mb-3">
                <label class="block text-xs text-muted-foreground mb-1">
                  Rayon (km) <span class="text-destructive">*</span>
                </label>
                <div class="flex items-center gap-2">
                  <input type="range" v-model.number="editForm.rayon_km" min="0.1" max="20" step="0.1"
                    @input="updateEditPreviewCircle" class="flex-1 accent-primary" />
                  <input type="number" v-model.number="editForm.rayon_km" min="0.1" max="20" step="0.1"
                    @input="updateEditPreviewCircle"
                    class="w-20 bg-card border border-border text-foreground text-sm rounded-lg px-2 py-1.5 text-center outline-none transition-colors" />
                  <span class="text-xs text-muted-foreground">km</span>
                </div>
              </div>

              <!-- Coordonnées live avec instruction carte -->
              <div class="flex items-center gap-2 text-xs px-3 py-2 bg-card border border-border rounded-lg mb-1 font-mono text-foreground">
                <MapPin class="w-3.5 h-3.5 flex-shrink-0" style="color:#CC1525;" />
                <span>{{ editForm.latitude_center?.toFixed(5) }}, {{ editForm.longitude_center?.toFixed(5) }}</span>
              </div>
              <p class="text-[11px] text-muted-foreground italic mb-3 flex items-center gap-1 px-1">
                <MousePointerClick class="w-3 h-3 flex-shrink-0" />
                Cliquez sur la carte pour déplacer le centre
              </p>

              <p v-if="error" class="text-xs text-destructive mb-2">{{ error }}</p>

              <div class="flex gap-2">
                <button @click="cancelEdit"
                  class="flex-1 border border-border text-muted-foreground rounded-lg px-3 py-2 text-sm hover:text-foreground hover:border-foreground transition-colors">
                  Annuler
                </button>
                <button
                  :disabled="!canSubmitEdit || actionLoading"
                  @click="submitEdit"
                  class="flex-1 flex items-center justify-center gap-2 text-white rounded-lg px-3 py-2 text-sm font-semibold transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                  style="background:#0F2356;"
                  @mouseenter="$event.currentTarget.style.background = actionLoading ? '#0F2356' : '#162d63'"
                  @mouseleave="$event.currentTarget.style.background='#0F2356'"
                >
                  <span v-if="actionLoading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                  <PencilLine v-else class="w-3.5 h-3.5" />
                  {{ actionLoading ? 'Enregistrement…' : 'Enregistrer' }}
                </button>
              </div>
            </div>

            <!-- ── LISTE DES ZONES ── -->
            <div class="rounded-xl border border-border bg-background p-4 flex-1">
              <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-4 flex items-center gap-2">
                <Layers class="w-3.5 h-3.5" />
                Mes zones
              </p>

              <p v-if="listLoading" class="text-sm text-muted-foreground text-center py-5">Chargement…</p>
              <p v-else-if="zones.length === 0" class="text-sm text-muted-foreground text-center py-5">Aucune zone créée.</p>

              <div
                v-for="zone in zones"
                :key="zone.id"
                class="flex items-center justify-between px-3 py-2 rounded-lg cursor-pointer transition-colors mb-1 hover:bg-muted group"
                :class="{ 'bg-muted ring-1 ring-primary': selectedZone?.id === zone.id }"
                @click="focusZone(zone)"
              >
                <div class="flex items-center gap-3">
                  <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{ background: zoneColor(zone.id) }"></div>
                  <div>
                    <p class="text-sm font-medium text-foreground">{{ zone.name }}</p>
                    <p class="text-xs text-muted-foreground">{{ zone.rayon_km }} km</p>
                  </div>
                </div>
                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click.stop="startEdit(zone)"
                    class="p-1.5 rounded-md hover:bg-blue-500/20 transition-colors" title="Modifier">
                    <PencilLine class="w-3.5 h-3.5 text-blue-500" />
                  </button>
                  <button @click.stop="openDeleteModal(zone)"
                    class="p-1.5 rounded-md hover:bg-red-500/20 transition-colors" title="Supprimer">
                    <Trash2 class="w-3.5 h-3.5 text-red-500" />
                  </button>
                </div>
              </div>
            </div>

          </aside>

          <!-- ==================== CARTE ==================== -->
          <div class="flex-1 relative">
            <div id="leaflet-map" class="w-full h-full"></div>

            <!-- Badge mode édition sur la carte -->
            <div v-if="editingZone"
              class="absolute top-3 left-1/2 -translate-x-1/2 z-[999] flex items-center gap-2 px-4 py-2 rounded-full text-white text-xs font-semibold shadow-lg"
              style="background:#0F2356;">
              <MousePointerClick class="w-3.5 h-3.5" />
              Cliquez sur la carte pour déplacer le centre de « {{ editingZone.name }} »
            </div>

            <div class="absolute bottom-5 right-3 z-[999] bg-card/90 backdrop-blur border border-border rounded-lg px-3 py-2 flex flex-col gap-1">
              <div class="flex items-center gap-2 text-xs text-muted-foreground">
                <span class="w-2.5 h-2.5 rounded-full bg-primary inline-block"></span>
                Zone en cours
              </div>
              <div class="flex items-center gap-2 text-xs text-muted-foreground">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span>
                Zone enregistrée
              </div>
            </div>
          </div>

        </div>

        <!-- Toast -->
        <transition name="toast">
          <div v-if="toast.show"
            class="fixed bottom-7 left-1/2 -translate-x-1/2 px-5 py-2.5 rounded-full text-sm font-medium z-[9999] shadow-lg text-white"
            :class="toast.type === 'success' ? 'bg-emerald-500' : 'bg-destructive'">
            {{ toast.msg }}
          </div>
        </transition>

      </div>
    </main>
  </div>

  <!-- ── Modal Supprimer ── -->
  <DeleteZoneModal
    v-model="showDeleteModal"
    :zone-name="zoneToDelete?.name"
    :loading="actionLoading"
    @confirm="handleConfirmDelete"
    @cancel="showDeleteModal = false"
  />
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '../../stores/AuthStore'
import { storeToRefs } from 'pinia'
import { useZones } from '../../composables/useZones'
import DeleteZoneModal from './DeleteZoneModal.vue'
import { Plus, MapPin, MousePointerClick, Layers, PencilLine, Trash2, X } from 'lucide-vue-next'

// ─── Auth ─────────────────────────────────────────────────────────────────────
const authStore = useAuthStore()
const { user }  = storeToRefs(authStore)

// ─── Composable ───────────────────────────────────────────────────────────────
const { zones, listLoading, actionLoading, error, fetchZones, createZone, updateZone, deleteZone } = useZones()

// ─── State création ───────────────────────────────────────────────────────────
const selectedZone = ref(null)
const form = reactive({
  name: '', description: '', latitude_center: null, longitude_center: null, rayon_km: 2,
})

// ─── State édition ────────────────────────────────────────────────────────────
const editingZone = ref(null)
const editForm = reactive({
  name: '', description: '', latitude_center: null, longitude_center: null, rayon_km: 2,
})

// ─── Modal suppression ────────────────────────────────────────────────────────
const showDeleteModal = ref(false)
const zoneToDelete    = ref(null)

const toast = reactive({ show: false, msg: '', type: 'success' })

// ─── Leaflet refs ─────────────────────────────────────────────────────────────
let map              = null
let previewCircle    = null   // cercle création
let previewMarker    = null   // marker création
let editCircle       = null   // cercle édition (preview rayon)
let editMarker       = null   // marker édition
const zoneCircles    = new Map()

// ─── Computed ─────────────────────────────────────────────────────────────────
const canSubmitCreate = computed(() =>
  form.name.trim() && form.latitude_center !== null && form.rayon_km > 0
)
const canSubmitEdit = computed(() =>
  editForm.name.trim() && editForm.latitude_center !== null && editForm.rayon_km > 0
)

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(async () => {
  await initMap()
  await fetchZones()
  zones.value.forEach(drawZoneOnMap)
  if (zones.value.length > 0) {
    const first = zones.value[0]
    map.setView([first.latitude_center, first.longitude_center], 12)
  }
})

onUnmounted(() => { map?.remove() })

// ─── Map init ─────────────────────────────────────────────────────────────────
async function initMap() {
  const L = window.L
  map = L.map('leaflet-map', { zoomControl: true }).setView([36.8065, 10.1815], 12)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/">OpenStreetMap</a>',
    maxZoom: 19,
  }).addTo(map)
  map.on('click', onMapClick)
}

// ─── Clic carte — comportement différent selon le mode ────────────────────────
function onMapClick(e) {
  const L = window.L
  if (editingZone.value) {
    // MODE ÉDITION : déplace le centre de la zone en cours d'édition
    editForm.latitude_center  = e.latlng.lat
    editForm.longitude_center = e.latlng.lng

    if (editMarker) editMarker.remove()
    editMarker = L.circleMarker(e.latlng, {
      radius: 6, color: '#fff', fillColor: '#0F2356', fillOpacity: 1, weight: 2,
    }).addTo(map)

    updateEditPreviewCircle()
  } else {
    // MODE CRÉATION
    form.latitude_center  = e.latlng.lat
    form.longitude_center = e.latlng.lng

    if (previewMarker) previewMarker.remove()
    previewMarker = L.circleMarker(e.latlng, {
      radius: 6, color: '#fff', fillColor: '#CC1525', fillOpacity: 1, weight: 2,
    }).addTo(map)

    updatePreviewCircle()
  }
}

function updatePreviewCircle() {
  const L = window.L
  if (!form.latitude_center) return
  if (previewCircle) previewCircle.remove()
  previewCircle = L.circle([form.latitude_center, form.longitude_center], {
    radius: form.rayon_km * 1000, color: '#CC1525', fillColor: '#CC1525',
    fillOpacity: 0.15, weight: 2, dashArray: '6 4',
  }).addTo(map)
}

function updateEditPreviewCircle() {
  const L = window.L
  if (!editForm.latitude_center) return
  if (editCircle) editCircle.remove()
  editCircle = L.circle([editForm.latitude_center, editForm.longitude_center], {
    radius: editForm.rayon_km * 1000, color: '#0F2356', fillColor: '#0F2356',
    fillOpacity: 0.15, weight: 2, dashArray: '6 4',
  }).addTo(map)
}

const COLORS = ['#3b82f6','#10b981','#8b5cf6','#ef4444','#06b6d4','#f59e0b','#ec4899']
function zoneColor(id) { return COLORS[id % COLORS.length] }

function drawZoneOnMap(zone) {
  const L     = window.L
  const color = zoneColor(zone.id)
  const circle = L.circle([zone.latitude_center, zone.longitude_center], {
    radius: zone.rayon_km * 1000, color, fillColor: color, fillOpacity: 0.12, weight: 2,
  }).addTo(map)
  circle.bindPopup(`<strong>${zone.name}</strong><br>Rayon : ${zone.rayon_km} km`)
  circle.on('click', () => focusZone(zone))
  zoneCircles.set(zone.id, circle)
}

function removeZoneFromMap(zoneId) {
  const c = zoneCircles.get(zoneId)
  if (c) { c.remove(); zoneCircles.delete(zoneId) }
}

// ─── Création ─────────────────────────────────────────────────────────────────
async function submitCreate() {
  if (!canSubmitCreate.value) return
  try {
    await createZone({
      name: form.name, description: form.description,
      latitude_center: form.latitude_center, longitude_center: form.longitude_center,
      rayon_km: form.rayon_km,
    })
    // Redessine toutes les zones depuis le state rechargé
    zoneCircles.forEach(c => c.remove())
    zoneCircles.clear()
    zones.value.forEach(drawZoneOnMap)
    resetCreateForm()
    showToast('Zone créée ✓')
  } catch {
    showToast("Erreur lors de la création", 'error')
  }
}

function resetCreateForm() {
  form.name = ''; form.description = ''
  form.latitude_center = null; form.longitude_center = null; form.rayon_km = 2
  if (previewCircle) { previewCircle.remove(); previewCircle = null }
  if (previewMarker) { previewMarker.remove(); previewMarker = null }
}

// ─── Édition ──────────────────────────────────────────────────────────────────
function startEdit(zone) {
  // Nettoyer mode création si actif
  resetCreateForm()

  editingZone.value        = zone
  editForm.name            = zone.name
  editForm.description     = zone.description ?? ''
  editForm.latitude_center = zone.latitude_center
  editForm.longitude_center= zone.longitude_center
  editForm.rayon_km        = zone.rayon_km

  // Afficher le cercle d'édition
  if (editCircle)  { editCircle.remove();  editCircle  = null }
  if (editMarker)  { editMarker.remove();  editMarker  = null }
  updateEditPreviewCircle()

  focusZone(zone)
}

async function submitEdit() {
  if (!canSubmitEdit.value) return
  try {
    const updated = await updateZone(editingZone.value.id, { ...editForm })
    removeZoneFromMap(editingZone.value.id)
    drawZoneOnMap(updated)
    cancelEdit()
    showToast('Zone mise à jour ✓')
  } catch {
    showToast("Erreur lors de la modification", 'error')
  }
}

function cancelEdit() {
  editingZone.value = null
  editForm.name = ''; editForm.description = ''
  editForm.latitude_center = null; editForm.longitude_center = null; editForm.rayon_km = 2
  if (editCircle)  { editCircle.remove();  editCircle  = null }
  if (editMarker)  { editMarker.remove();  editMarker  = null }
}

// ─── Suppression ──────────────────────────────────────────────────────────────
function openDeleteModal(zone) {
  zoneToDelete.value  = zone
  showDeleteModal.value = true
}

async function handleConfirmDelete() {
  if (!zoneToDelete.value) return
  try {
    await deleteZone(zoneToDelete.value.id)
    removeZoneFromMap(zoneToDelete.value.id)
    if (selectedZone.value?.id === zoneToDelete.value.id) selectedZone.value = null
    if (editingZone.value?.id  === zoneToDelete.value.id) cancelEdit()
    showDeleteModal.value = false
    showToast('Zone supprimée')
  } catch {
    showToast('Erreur lors de la suppression', 'error')
  }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function focusZone(zone) {
  selectedZone.value = zone
  map.flyTo([zone.latitude_center, zone.longitude_center], 14, { duration: 0.8 })
  zoneCircles.get(zone.id)?.openPopup()
}

function showToast(msg, type = 'success') {
  toast.msg = msg; toast.type = type; toast.show = true
  setTimeout(() => { toast.show = false }, 3000)
}
</script>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all .3s; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(-50%) translateY(10px); }
</style>