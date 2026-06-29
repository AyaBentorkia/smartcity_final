<script setup>
import { computed, watch, nextTick, ref } from 'vue'
import {
  X, MapPin, Send, Pencil, Trash2, Calendar, User,
  ChevronRight, Download, Maximize2, ZoomIn, Image as ImageIcon,
  Shield, FileText, Tag, Phone, ExternalLink, Printer,
  MoreHorizontal, MessageSquare,
} from 'lucide-vue-next'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png'
import markerIcon from 'leaflet/dist/images/marker-icon.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'
import LoadingSpinner from '../../components/LoadingSpinner.vue'
import { useComments } from '../../composables/useComments'

delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
})

const props = defineProps({
  modelValue: Boolean,
  incident: Object,
  loading: Boolean,
  role: { type: String, default: 'agent' },
})
const emit = defineEmits(['update:modelValue'])

// ── Commentaires ────────────────────────────────────────────────────────────
const {
  comments, commentsLoading, commentActionLoading, commentError,
  fetchComments, AddComment, UpdateComment, DeleteComment, resetComments,
} = useComments()

const newComment     = ref('')
const editingId      = ref(null)
const editingContent = ref('')
const imageFullscreen = ref(false)
const imageError     = ref(false)
const imageLoaded    = ref(false)

// JWT → user id
const currentUserId = computed(() => {
  try {
    const token = localStorage.getItem('jwt_token')
    if (!token) return null
    return JSON.parse(atob(token.split('.')[1])).sub
  } catch { return null }
})

// ── Image ───────────────────────────────────────────────────────────────────
const incidentImageUrl = computed(
  () => props.incident?.medias?.url ?? props.incident?.media?.url ?? null,
)
watch(() => props.incident, () => {
  imageError.value = false
  imageLoaded.value = false
})

// ── Comment handlers ─────────────────────────────────────────────────────────
const handleSubmit = async () => {
  if (!newComment.value.trim() || !props.incident?.id) return
  await AddComment(props.incident.id, newComment.value)
  newComment.value = ''
}
const startEdit   = (c)  => { editingId.value = c.id; editingContent.value = c.content }
const cancelEdit  = ()   => { editingId.value = null;  editingContent.value = '' }
const confirmEdit = async (id) => { await UpdateComment(id, editingContent.value); cancelEdit() }
const handleDelete = async (id) => {
  if (confirm('Supprimer ce commentaire ?')) await DeleteComment(id)
}
const formatCommentDate = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr), now = new Date()
  const m = Math.floor((now - d) / 60000)
  if (m < 1) return "À l'instant"
  if (m < 60) return `il y a ${m} min`
  if (m < 1440) return `il y a ${Math.floor(m / 60)}h`
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' })
}

// ── Status config ────────────────────────────────────────────────────────────
const STATUS_MAP = {
  reported:    { label: 'Signalé',    bg: 'bg-blue-50',    text: 'text-blue-700',    border: 'border-blue-200',    dot: 'bg-blue-500'    },
  in_progress: { label: 'En cours',   bg: 'bg-blue-50',    text: 'text-blue-700',    border: 'border-blue-200',    dot: 'bg-blue-500'    },
  resolved:    { label: 'Résolu',     bg: 'bg-emerald-50', text: 'text-emerald-700', border: 'border-emerald-200', dot: 'bg-emerald-500' },
  rejected:    { label: 'Rejeté',     bg: 'bg-red-50',     text: 'text-red-700',     border: 'border-red-200',     dot: 'bg-red-500'     },
  validated:   { label: 'Validé',     bg: 'bg-green-50',   text: 'text-green-700',   border: 'border-green-200',   dot: 'bg-green-500'   },
  pending:     { label: 'En attente', bg: 'bg-amber-50',   text: 'text-amber-700',   border: 'border-amber-200',   dot: 'bg-amber-500'   },
}
const URGENCY_MAP = {
  critical: { label: 'Critique', pill: 'bg-red-600 text-white',    bar: 'bg-red-500',    width: 'w-full' },
  high:     { label: 'Élevé',    pill: 'bg-orange-500 text-white', bar: 'bg-orange-500', width: 'w-3/4'  },
  medium:   { label: 'Moyen',    pill: 'bg-amber-400 text-white',  bar: 'bg-amber-400',  width: 'w-1/2'  },
  low:      { label: 'Faible',   pill: 'bg-green-500 text-white',  bar: 'bg-green-500',  width: 'w-1/4'  },
}
const URGENCY_SCORE = { critical: '4/4', high: '3/4', medium: '2/4', low: '1/4' }

const statusCfg = computed(() =>
  STATUS_MAP[props.incident?.status] ?? {
    label: props.incident?.status ?? '',
    bg: 'bg-gray-50', text: 'text-gray-600', border: 'border-gray-200', dot: 'bg-gray-400',
  },
)

const urgencyKey = computed(() => {
  const raw = (props.incident?.urgency_level ?? '').toLowerCase()
  if (raw.includes('critic') || raw === '4') return 'critical'
  if (raw.includes('haut') || raw.includes('high') || raw === '3') return 'high'
  if (raw.includes('moyen') || raw.includes('medium') || raw === '2') return 'medium'
  return 'low'
})
const urgencyCfg = computed(() => URGENCY_MAP[urgencyKey.value])

const formattedDate = computed(() => {
  if (!props.incident?.created_at) return ''
  return new Date(props.incident.created_at).toLocaleDateString('fr-FR', {
    day: '2-digit', month: 'short', year: 'numeric',
  })
})

const incidentId = computed(() =>
  props.incident?.id ? `INC-${String(props.incident.id).padStart(5, '0')}` : '—',
)

const googleMapsUrl = computed(() => {
  if (!props.incident?.latitude || !props.incident?.longitude) return null
  return `https://www.google.com/maps?q=${props.incident.latitude},${props.incident.longitude}`
})

// ── Map ──────────────────────────────────────────────────────────────────────
let map = null
watch(
  () => [props.modelValue, props.incident, props.loading],
  async ([open, incident, loading]) => {
    if (!open) { resetComments(); return }
    if (incident?.id) fetchComments(incident.id)
    if (loading || !incident?.latitude) return
    await nextTick()
    if (map) { map.remove(); map = null }
    map = L.map('incident-map').setView([incident.latitude, incident.longitude], 15)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap',
    }).addTo(map)
    L.marker([incident.latitude, incident.longitude])
      .addTo(map)
      .bindPopup(incident.address_text || 'Localisation')
      .openPopup()
  },
)
</script>

<template>
  <!-- Backdrop -->
  <div
    v-if="modelValue"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="background:rgba(15,35,86,0.50); backdrop-filter:blur(4px);"
    @click.self="emit('update:modelValue', false)"
  >

    <!-- ── Modal shell ─────────────────────────────────────────────────────── -->
    <div class="relative w-full max-w-2xl bg-white rounded-xl shadow-2xl flex flex-col overflow-hidden max-h-[92vh]">

      <!-- ══════════ HEADER ══════════ -->
      <div class="relative px-6 py-4 flex-shrink-0 overflow-hidden" style="background:#0F2356;">
        <!-- Grid texture -->
        <div
          class="absolute inset-0 opacity-[0.04] pointer-events-none"
          style="background-image:
            repeating-linear-gradient(0deg,#fff 0,#fff 1px,transparent 1px,transparent 32px),
            repeating-linear-gradient(90deg,#fff 0,#fff 1px,transparent 1px,transparent 32px);"
        />
        <!-- Red left bar -->
        <div class="absolute left-0 top-0 bottom-0 w-1" style="background:#CC1525;" />

        <!-- Loading skeleton -->
        <div v-if="loading" class="relative z-10 flex items-center justify-between">
          <div class="space-y-2">
            <div class="h-3 w-20 rounded bg-white/10 animate-pulse" />
            <div class="h-5 w-48 rounded bg-white/10 animate-pulse" />
          </div>
          <button
            class="w-8 h-8 rounded-lg flex items-center justify-center"
            style="background:rgba(255,255,255,0.10);"
            @click="emit('update:modelValue', false)"
          >
            <X class="w-4 h-4 text-white/70" />
          </button>
        </div>

        <!-- Incident header -->
        <div v-else-if="incident" class="relative z-10 flex items-start justify-between gap-4">
          <div class="flex-1 min-w-0">
            <!-- Tags row -->
            <div class="flex items-center gap-2 mb-2 flex-wrap">
              <span class="text-white/40 text-xs font-mono tracking-widest">#{{ incidentId }}</span>
              <span class="text-white/20 text-xs">·</span>
              <div
                v-if="incident.category?.name"
                class="flex items-center gap-1 rounded px-2 py-0.5 border"
                style="background:rgba(255,255,255,0.10); border-color:rgba(255,255,255,0.15);"
              >
                <Tag class="w-3 h-3 text-white/50" />
                <span class="text-white/60 text-xs">{{ incident.category.name }}</span>
              </div>
              <span
                v-if="incident.urgency_level"
                :class="['text-xs px-2 py-0.5 rounded font-medium', urgencyCfg.pill]"
              >
                {{ urgencyCfg.label }}
              </span>
            </div>
            <!-- Title -->
            <h2 class="text-white text-lg leading-snug truncate font-semibold">{{ incident.title }}</h2>
            <!-- Date · Zone -->
            <div class="flex items-center gap-4 mt-1.5 flex-wrap">
              <span class="flex items-center gap-1 text-white/40 text-xs">
                <Calendar class="w-3 h-3" /> {{ formattedDate }}
              </span>
              <span v-if=" incident.zone?.name" class="flex items-center gap-1 text-white/40 text-xs">
                <MapPin class="w-3 h-3" /> {{ incident.zone?.name }}
              </span>
            </div>
          </div>

          <!-- Status + close -->
          <div class="flex items-center gap-3 flex-shrink-0">
            <div :class="['flex items-center gap-1.5 border rounded px-2.5 py-1', statusCfg.border, statusCfg.bg]">
              <div :class="['w-1.5 h-1.5 rounded-full', statusCfg.dot]" />
              <span :class="['text-xs font-medium', statusCfg.text]">{{ statusCfg.label }}</span>
            </div>
            <button
              class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
              style="background:rgba(255,255,255,0.10);"
              @mouseenter="$event.currentTarget.style.background='rgba(255,255,255,0.20)'"
              @mouseleave="$event.currentTarget.style.background='rgba(255,255,255,0.10)'"
              @click="emit('update:modelValue', false)"
            >
              <X class="w-4 h-4 text-white/70" />
            </button>
          </div>
        </div>
      </div>

      <!-- ══════════ META STRIP ══════════ -->
      <div
        v-if="!loading && incident"
        class="bg-white border-b border-gray-100 px-6 py-2.5 flex items-center gap-6 text-xs overflow-x-auto flex-shrink-0"
      >
        <div class="flex items-center gap-2 shrink-0">
          <User class="w-3.5 h-3.5 text-gray-400" />
          <span class="text-gray-500">Signalé par</span>
          <span class="font-semibold" style="color:#0F2356;">{{ incident.citizen?.name ?? '—' }}</span>
        </div>
        <div v-if="incident.citizen?.phone" class="flex items-center gap-2 shrink-0">
          <Phone class="w-3.5 h-3.5 text-gray-400" />
          <span style="color:#0F2356;">{{ incident.citizen.phone }}</span>
        </div>
        <div class="ml-auto flex items-center gap-3 shrink-0">
          <!-- <button class="flex items-center gap-1.5 text-gray-400 hover:text-gray-700 transition-colors">
            <Printer class="w-3.5 h-3.5" /> Imprimer
          </button> -->
          <!-- <span class="text-gray-200">|</span> -->
          <!-- <button class="flex items-center gap-1.5 text-gray-400 hover:text-gray-700 transition-colors">
            <MoreHorizontal class="w-3.5 h-3.5" /> Actions
          </button> -->
        </div>
      </div>

      <!-- ══════════ SCROLLABLE BODY ══════════ -->
      <div class="flex-1 overflow-y-auto" style="background:#F7F8FB;">

        <!-- Loading -->
        <div v-if="loading" class="flex flex-col items-center justify-center gap-3 py-16">
          <LoadingSpinner />
          <p class="text-sm text-gray-400">Chargement du dossier…</p>
        </div>

        <div v-else-if="incident" class="p-6 flex flex-col gap-5">

          <!-- ─ Description ─ -->
          <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-3">
              <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
              <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Description</span>
            </div>
            <p class="text-sm leading-relaxed" style="color:#1A2B4A;">{{ incident.description }}</p>
          </div>

          <!-- ─ Urgency level ─ -->
          <div v-if="incident.urgency_level" class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-3">
              <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
              <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Niveau d'urgence</span>
            </div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-semibold" style="color:#0F2356;">{{ urgencyCfg.label }}</span>
              <span class="text-xs text-gray-400">{{ URGENCY_SCORE[urgencyKey] }}</span>
            </div>
            <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
              <div :class="['h-full rounded-full transition-all', urgencyCfg.bar, urgencyCfg.width]" />
            </div>
            <div class="flex justify-between mt-1.5">
              <span v-for="l in ['Faible','Moyen','Élevé','Critique']" :key="l" class="text-[10px] text-gray-300">{{ l }}</span>
            </div>
          </div>

          <!-- ─ Photo de l'incident ─ -->
          <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 pt-5 pb-3">
              <div class="flex items-center gap-2">
                <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
                <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Photo de l'Incident</span>
              </div>
            </div>

            <!-- Image present -->
            <div v-if="incidentImageUrl && !imageError" class="relative group">
              <img
                :src="incidentImageUrl"
                alt="Photo de l'incident"
                class="w-full object-cover"
                style="height:224px;"
                @load="imageLoaded = true"
                @error="imageError = true"
              />
              <!-- Hover overlay -->
              <div
                class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-3"
                style="background:rgba(15,35,86,0.40);"
              >
                <button
                  class="bg-white/90 hover:bg-white rounded-lg px-3 py-2 flex items-center gap-2 text-xs shadow-md transition-colors"
                  style="color:#0F2356;"
                  @click="imageFullscreen = true"
                >
                  <ZoomIn class="w-3.5 h-3.5" /> Plein écran
                </button>
                <!-- <a
                  :href="incidentImageUrl"
                  download="incident.jpg"
                  class="bg-white/90 hover:bg-white rounded-lg px-3 py-2 flex items-center gap-2 text-xs shadow-md transition-colors"
                  style="color:#0F2356;"
                >
                  <Download class="w-3.5 h-3.5" /> Télécharger
                </a> -->
              </div>
            </div>

            <!-- No image placeholder -->
            <div v-else class="mx-5 mb-5 h-44 border-2 border-dashed border-gray-200 rounded-lg flex flex-col items-center justify-center gap-3 bg-gray-50">
              <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center">
                <ImageIcon class="w-6 h-6 text-gray-300" />
              </div>
              <div class="text-center">
                <p class="text-gray-400 text-sm">Aucune photo disponible</p>
                <p class="text-gray-300 text-xs mt-1">Le citoyen n'a pas joint de photo à ce signalement</p>
              </div>
            </div>

            <!-- Image metadata bar -->
            <div
              v-if="incidentImageUrl && !imageError"
              class="px-5 py-3 border-t border-gray-100 flex items-center justify-between"
              style="background:#F7F8FB;"
            >
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded flex items-center justify-center" style="background:rgba(15,35,86,0.08);">
                  <FileText class="w-3 h-3" style="color:#0F2356;" />
                </div>
                <div>
                  <p class="text-[10px] font-medium" style="color:#0F2356;">incident_{{ incident.id }}.jpg</p>
                  <p class="text-[10px] text-gray-400">Soumise le {{ formattedDate }}</p>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <button
                  class="flex items-center gap-1.5 text-xs transition-colors hover:opacity-80"
                  style="color:rgba(15,35,86,0.60);"
                  @click="imageFullscreen = true"
                >
                  <Maximize2 class="w-3.5 h-3.5" /> Agrandir
                </button>
                <span class="text-gray-200">|</span>
                <a
                  :href="incidentImageUrl"
                  download
                  class="flex items-center gap-1.5 text-xs transition-colors hover:opacity-80"
                  style="color:#CC1525;"
                >
                  <Download class="w-3.5 h-3.5" /> Télécharger
                </a>
              </div>
            </div>
          </div>

          <!-- ─ Localisation ─ -->
          <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
              <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
              <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Localisation</span>
            </div>
            <!-- Address -->
            <div class="flex items-center gap-3 mb-4">
              <div
                class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 border"
                style="background:#FEF2F2; border-color:rgba(204,21,37,0.15);"
              >
                <MapPin class="w-3.5 h-3.5" style="color:#CC1525;" />
              </div>
              <span class="text-sm" style="color:#0F2356;">{{ incident.address_text || 'Adresse non disponible' }}</span>
            </div>
            <!-- Map -->
            <div class="rounded-lg overflow-hidden border border-gray-200 relative" style="height:200px;">
              <div id="incident-map" class="w-full h-full z-0" />
              <div v-if="googleMapsUrl" class="absolute bottom-3 left-3 z-10">
                <a
                  :href="googleMapsUrl"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="flex items-center gap-1.5 bg-white border border-gray-200 hover:border-gray-400 rounded px-3 py-1.5 text-xs shadow-sm transition-colors"
                  style="color:#0F2356;"
                >
                  <ExternalLink class="w-3 h-3" style="color:#CC1525;" />
                  Ouvrir dans Google Maps
                  <ChevronRight class="w-3 h-3 opacity-40" />
                </a>
              </div>
            </div>
          </div>

          <!-- ─ Commentaires techniques ─ -->
          <div   v-if="role !== 'citizen' && role !== 'super admin'" 
           class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="px-5 pt-5 pb-4 border-b border-gray-100 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
                <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">
                  Commentaires Techniques
                </span>
                <span
                  class="text-[10px] px-2 py-0.5 rounded-full border"
                  style="background:rgba(15,35,86,0.06); color:#0F2356; border-color:rgba(15,35,86,0.10);"
                >
                  {{ comments.length }}
                </span>
              </div>
              <MessageSquare class="w-4 h-4 text-gray-300" />
            </div>

            <!-- Input -->
            <div v-if="role==='agent'" class="px-5 py-4 flex items-center gap-3 border-b border-gray-100" style="background:#F7F8FB;">
              <div
                class="w-8 h-8 rounded-full flex items-center justify-center text-white text-[11px] font-bold shrink-0"
                style="background:#0F2356;"
              >
                ME
              </div>
              <div class="flex-1 relative">
                <input
                  v-model="newComment"
                  type="text"
                  placeholder="Ajouter une note technique…"
                  class="w-full bg-white border border-gray-200 rounded-lg px-4 py-2.5 text-sm placeholder-gray-300 outline-none transition-colors pr-10"
                  style="color:#0F2356;"
                  @focus="$event.target.style.borderColor='#0F2356'"
                  @blur="$event.target.style.borderColor='rgb(229,231,235)'"
                  @keydown.enter.exact.prevent="handleSubmit"
                />
                <button
                  :disabled="commentActionLoading || !newComment.trim()"
                  class="absolute right-3 top-1/2 -translate-y-1/2 transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                  style="color:#CC1525;"
                  @click="handleSubmit"
                >
                  <Send class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Error -->
            <p v-if="commentError" class="px-5 pt-3 pb-0 text-xs text-red-500">{{ commentError }}</p>

            <!-- Comments list -->
            <div class="px-5 py-4">
              <div v-if="commentsLoading" class="flex justify-center py-8">
                <LoadingSpinner />
              </div>
              <div v-else-if="comments.length === 0" class="py-8 flex flex-col items-center gap-2">
                <MessageSquare class="w-8 h-8 text-gray-200" />
                <p class="text-gray-400 text-sm">Aucun commentaire pour l'instant</p>
              </div>
              <div v-else class="flex flex-col gap-4">
                <div v-for="comment in comments" :key="comment.id" class="flex gap-3 group">
                  <!-- Avatar -->
                  <div
                    class="w-8 h-8 rounded-full border flex items-center justify-center text-xs font-bold shrink-0"
                    style="background:rgba(15,35,86,0.08); color:#0F2356; border-color:rgba(15,35,86,0.12);"
                  >
                    {{ comment.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                  </div>
                  <div class="flex-1 min-w-0">
                    <!-- Meta -->
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                      <span class="text-xs font-semibold" style="color:#0F2356;">{{ comment.user?.name || 'Agent' }}</span>
                      <span class="text-gray-300 text-xs">·</span>
                      <span class="text-gray-400 text-xs">{{ comment.user?.role ?? '' }}</span>
                      <div class="ml-auto flex items-center gap-1.5">
                        <span class="text-gray-400 text-xs">{{ formatCommentDate(comment.created_at) }}</span>
                        <template v-if="comment.user?.id === currentUserId">
                          <button
                            class="hidden group-hover:flex text-gray-300 hover:text-blue-500 transition-colors"
                            @click="startEdit(comment)"
                          >
                            <Pencil class="w-3 h-3" />
                          </button>
                          <button
                            class="hidden group-hover:flex text-gray-300 hover:text-red-500 transition-colors"
                            @click="handleDelete(comment.id)"
                          >
                            <Trash2 class="w-3 h-3" />
                          </button>
                        </template>
                      </div>
                    </div>
                    <!-- Bubble -->
                    <div class="rounded-lg px-3 py-2.5 border border-gray-100" style="background:#F7F8FB;">
                      <p v-if="editingId !== comment.id" class="text-sm leading-relaxed" style="color:#4A5B78;">
                        {{ comment.content }}
                      </p>
                      <div v-else>
                        <textarea
                          v-model="editingContent"
                          rows="2"
                          class="w-full text-sm rounded-lg px-2 py-1.5 resize-none outline-none bg-white border"
                          style="color:#0F2356; border-color:#0F2356;"
                          @keydown.enter.exact.prevent="confirmEdit(comment.id)"
                          @keydown.escape="cancelEdit"
                        />
                        <div class="flex gap-2 mt-2">
                          <button
                            class="text-xs font-semibold px-3 py-1 rounded border transition-colors"
                            style="color:#0F2356; border-color:rgba(15,35,86,0.25); background:rgba(15,35,86,0.06);"
                            @click="confirmEdit(comment.id)"
                          >Enregistrer</button>
                          <button class="text-xs text-gray-400 hover:text-gray-600 transition-colors" @click="cancelEdit">
                            Annuler
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- ══════════ FOOTER ══════════ -->
      <div class="bg-white border-t border-gray-200 px-6 py-4 flex items-center justify-between gap-3 flex-shrink-0">
        <div class="flex items-center gap-1.5 text-xs text-gray-400">
          <Shield class="w-3.5 h-3.5 flex-shrink-0" style="color:#CC1525;" />
          <span>{{ incidentId }} · Plateforme Municipale</span>
        </div>
        <div class="flex items-center gap-2">
          <button
            class="px-5 py-2 border border-gray-200 hover:border-gray-300 rounded-lg text-sm transition-colors"
            style="color:#4A5B78;"
            @click="emit('update:modelValue', false)"
          >
            Fermer
          </button>
          <!-- <button
            class="px-5 py-2 text-white rounded-lg text-sm transition-colors shadow-sm flex items-center gap-2"
            style="background:#0F2356;"
            @mouseenter="$event.currentTarget.style.background='#162d63'"
            @mouseleave="$event.currentTarget.style.background='#0F2356'"
          >
            Mettre à jour <ChevronRight class="w-3.5 h-3.5" />
          </button> -->
        </div>
      </div>
    </div>

    <!-- ── Lightbox ──────────────────────────────────────────────────────────── -->
    <Transition name="lb-fade">
      <div
        v-if="imageFullscreen && incidentImageUrl"
        class="fixed inset-0 z-[60] flex items-center justify-center p-6"
        style="background:rgba(0,0,0,0.92);"
        @click.self="imageFullscreen = false"
      >
        <button
          class="absolute top-4 right-4 w-10 h-10 rounded-full flex items-center justify-center transition-colors"
          style="background:rgba(255,255,255,0.10);"
          @mouseenter="$event.currentTarget.style.background='rgba(255,255,255,0.20)'"
          @mouseleave="$event.currentTarget.style.background='rgba(255,255,255,0.10)'"
          @click="imageFullscreen = false"
        >
          <X class="w-5 h-5 text-white" />
        </button>
        <div class="text-center" @click.stop>
          <img
            :src="incidentImageUrl"
            alt="Incident plein écran"
            class="max-w-full rounded-lg shadow-2xl object-contain"
            style="max-height:80vh;"
          />
          <div class="mt-4 flex items-center justify-center gap-3">
            <p class="text-white/50 text-sm">{{ incident?.title }}</p>
            <a
              :href="incidentImageUrl"
              download
              class="flex items-center gap-2 border text-white px-4 py-2 rounded-lg text-sm transition-colors"
              style="background:rgba(255,255,255,0.10); border-color:rgba(255,255,255,0.20);"
            >
              <Download class="w-4 h-4" /> Télécharger
            </a>
          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<style scoped>
.lb-fade-enter-active,
.lb-fade-leave-active { transition: opacity 0.18s ease; }
.lb-fade-enter-from,
.lb-fade-leave-to     { opacity: 0; }
</style>