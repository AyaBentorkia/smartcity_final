<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { X, UserPlus, AlertCircle, Shield } from 'lucide-vue-next'
import { useAssignments } from '../../composables/useAssignments'
import LoadingSpinner from '../../components/LoadingSpinner.vue'

const props = defineProps({
  modelValue:   Boolean,
  incidentId:   Number,
  agents:       Array,
  loading:      Boolean,
  assignmentId: Number,
})

const emit = defineEmits(['update:modelValue', 'assigned'])

const { AssignIncident, ReassignIncident, error: assignError } = useAssignments()

const selectedAgentId = ref(null)
const showDropdown = ref(false)
const dropdownRef = ref(null)

const selectedAgentLabel = computed(() => {
  const agent = (props.agents || []).find(a => a.id === selectedAgentId.value)
  return agent ? `${agent.name} — ${agent.email}` : '— Sélectionner un agent —'
})

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
}

const selectAgent = (id) => {
  selectedAgentId.value = id
  showDropdown.value = false
}

const onDocumentClick = (e) => {
  if (!dropdownRef.value) return
  if (!dropdownRef.value.contains(e.target)) showDropdown.value = false
}

onMounted(() => document.addEventListener('click', onDocumentClick))
onUnmounted(() => document.removeEventListener('click', onDocumentClick))
const assigning = ref(false)
const error = ref(null)

const handleAssign = async () => {
  if (!selectedAgentId.value) return
  assigning.value = true
  error.value = null
  try {
    await AssignIncident(props.incidentId, selectedAgentId.value)
    emit('assigned')
    emit('update:modelValue', false)
    selectedAgentId.value = null
  } catch (err) {
    error.value = assignError.value || "Erreur lors de l'assignation"
  } finally {
    assigning.value = false
  }
}

const handleReassign = async () => {
  if (!selectedAgentId.value) return
  assigning.value = true
  error.value = null
  try {
    await ReassignIncident(props.assignmentId, selectedAgentId.value)
    emit('assigned')
    emit('update:modelValue', false)
    selectedAgentId.value = null
  } catch (err) {
    error.value = assignError.value || "Erreur lors de l'assignation"
  } finally {
    assigning.value = false
  }
}

const handleSubmit = computed(() =>
  props.assignmentId ? handleReassign : handleAssign
)

const inputBase = 'w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0F2356] transition-colors appearance-none'
</script>

<template>
  <div
    v-if="modelValue"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="background:rgba(15,35,86,0.50); backdrop-filter:blur(4px);"
    @click.self="$emit('update:modelValue', false)"
  >
    <div class="relative w-full max-w-md bg-white rounded-xl shadow-2xl flex flex-col overflow-hidden max-h-[92vh]">

      <!-- HEADER -->
      <div class="relative px-6 py-4 flex-shrink-0 overflow-hidden" style="background:#0F2356;">
        <div class="absolute inset-0 opacity-[0.04] pointer-events-none"
          style="background-image:repeating-linear-gradient(0deg,#fff 0,#fff 1px,transparent 1px,transparent 32px),repeating-linear-gradient(90deg,#fff 0,#fff 1px,transparent 1px,transparent 32px);" />
        <div class="absolute left-0 top-0 bottom-0 w-1" style="background:#CC1525;" />
        <div class="relative z-10 flex items-start justify-between gap-4">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <UserPlus class="w-4 h-4" style="color:#CC1525;" />
              <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">
                {{ assignmentId ? 'Réassignation' : 'Assignation' }}
              </span>
            </div>
            <h2 class="text-white text-lg font-semibold">
              {{ assignmentId ? "Réassigner l'incident" : "Assigner l'incident" }}
            </h2>
            <p class="text-white/40 text-xs mt-0.5">Incident <span class="font-semibold text-white/60">#{{ incidentId }}</span></p>
          </div>
          <button class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors flex-shrink-0"
            style="background:rgba(255,255,255,0.10);"
            @mouseenter="$event.currentTarget.style.background='rgba(255,255,255,0.20)'"
            @mouseleave="$event.currentTarget.style.background='rgba(255,255,255,0.10)'"
            @click="$emit('update:modelValue', false)">
            <X class="w-4 h-4 text-white/70" />
          </button>
        </div>
      </div>

      <!-- BODY -->
      <div class="flex-1 overflow-y-auto p-6 flex flex-col gap-5" style="background:#F7F8FB;">

        <!-- Sélection agent -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
            <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Choisir un agent</span>
          </div>

          <LoadingSpinner v-if="loading" />

          <div v-else class="relative" ref="dropdownRef">
            <button
              type="button"
              @click="toggleDropdown"
              :class="inputBase + ' flex items-center justify-between'"
              style="color:#0F2356;"
            >
              <span class="truncate">{{ selectedAgentLabel }}</span>
              <span class="ml-2 text-gray-400 pointer-events-none">&#9662;</span>
            </button>

            <ul v-if="showDropdown" class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-56 overflow-auto z-50 text-sm">
              <li
                class="px-3 py-2 text-gray-700 hover:bg-gray-100 cursor-pointer"
                :class="{'bg-gray-100': agent.id === selectedAgentId}"
                v-for="agent in agents"
                :key="agent.id"
                @click="selectAgent(agent.id)"
                role="option"
                :aria-selected="agent.id === selectedAgentId"
              >
                {{ agent.name }} — {{ agent.email }}
              </li>
            </ul>
          </div>
        </div>

        <!-- Erreur -->
        <div v-if="error" class="flex items-start gap-3 bg-red-50 border border-red-100 text-red-600 text-sm px-4 py-3 rounded-lg">
          <AlertCircle class="w-4 h-4 shrink-0 mt-0.5" />
          <span>{{ error }}</span>
        </div>

      </div>

      <!-- FOOTER -->
      <div class="bg-white border-t border-gray-200 px-6 py-4 flex items-center justify-between gap-3 flex-shrink-0">
        <div class="flex items-center gap-1.5 text-xs text-gray-400">
          <Shield class="w-3.5 h-3.5 flex-shrink-0" style="color:#CC1525;" />
          <span>Plateforme Municipale</span>
        </div>
        <div class="flex items-center gap-2">
          <button
            class="px-5 py-2 border border-gray-200 hover:border-gray-300 rounded-lg text-sm transition-colors"
            style="color:#4A5B78;"
            @click="$emit('update:modelValue', false)"
          >
            Annuler
          </button>
          <button
            :disabled="!selectedAgentId || assigning"
            class="px-5 py-2 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            style="background:#0F2356;"
            @mouseenter="$event.currentTarget.style.background='#162d63'"
            @mouseleave="$event.currentTarget.style.background='#0F2356'"
            @click="handleSubmit()"
          >
            <span v-if="assigning" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
            {{ assigning ? 'En cours…' : assignmentId ? 'Réassigner' : 'Assigner' }}
          </button>
        </div>
      </div>

    </div>
  </div>
</template>