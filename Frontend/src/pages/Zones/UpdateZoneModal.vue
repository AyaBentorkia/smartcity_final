<script setup>
import { ref, reactive, watch } from 'vue'
import { X, MapPin, Shield, PencilLine } from 'lucide-vue-next'

const props = defineProps({
  modelValue: Boolean,
  zone: { type: Object, default: null },
  loading: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel'])

const error = ref(null)
const form  = reactive({
  name            : '',
  description     : '',
  latitude_center : null,
  longitude_center: null,
  rayon_km        : 2,
})

watch(() => [props.modelValue, props.zone], ([open, z]) => {
  if (open && z) {
    form.name             = z.name             ?? ''
    form.description      = z.description      ?? ''
    form.latitude_center  = z.latitude_center  ?? null
    form.longitude_center = z.longitude_center ?? null
    form.rayon_km         = z.rayon_km         ?? 2
    error.value           = null
  }
}, { immediate: true })

const canSubmit = () => form.name.trim() && form.latitude_center !== null && form.rayon_km > 0

const handleSubmit = () => {
  if (!canSubmit()) { error.value = 'Veuillez remplir tous les champs obligatoires.'; return }
  error.value = null
  emit('confirm', { ...form })
}

const handleClose = () => {
  error.value = null
  emit('cancel')
  emit('update:modelValue', false)
}
</script>

<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div
        v-if="modelValue"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
        style="background:rgba(15,35,86,0.50); backdrop-filter:blur(4px);"
        @click.self="handleClose"
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
                  <PencilLine class="w-4 h-4" style="color:#CC1525;" />
                  <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Modification</span>
                </div>
                <h2 class="text-white text-lg font-semibold">{{ zone?.name ?? 'Modifier la zone' }}</h2>
                <p class="text-white/40 text-xs mt-0.5">Mettre à jour les informations de cette zone</p>
              </div>
              <button
                class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors flex-shrink-0"
                style="background:rgba(255,255,255,0.10);"
                @mouseenter="$event.currentTarget.style.background='rgba(255,255,255,0.20)'"
                @mouseleave="$event.currentTarget.style.background='rgba(255,255,255,0.10)'"
                @click="handleClose"
              >
                <X class="w-4 h-4 text-white/70" />
              </button>
            </div>
          </div>

          <!-- BODY -->
          <div class="flex-1 overflow-y-auto p-6 flex flex-col gap-5" style="background:#F7F8FB;">

            <!-- Informations -->
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
              <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
                <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Informations</span>
              </div>
              <div class="mb-4">
                <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">
                  Nom <span style="color:#CC1525;">*</span>
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  placeholder="Zone Nord, Zone 1…"
                  class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0F2356] transition-colors"
                  style="color:#0F2356;"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Description</label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  placeholder="Description optionnelle…"
                  class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0F2356] transition-colors resize-none"
                  style="color:#0F2356;"
                />
              </div>
            </div>

            <!-- Rayon -->
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
              <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
                <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Rayon de couverture</span>
              </div>
              <label class="block text-xs font-semibold mb-2" style="color:#4A5B78;">
                Rayon (km) <span style="color:#CC1525;">*</span>
              </label>
              <div class="flex items-center gap-3">
                <input
                  type="range"
                  v-model.number="form.rayon_km"
                  min="0.1" max="20" step="0.1"
                  class="flex-1 accent-[#CC1525]"
                />
                <input
                  type="number"
                  v-model.number="form.rayon_km"
                  min="0.1" max="20" step="0.1"
                  class="w-20 bg-gray-50 border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-center outline-none focus:border-[#0F2356] transition-colors"
                  style="color:#0F2356;"
                />
                <span class="text-xs font-medium" style="color:#4A5B78;">km</span>
              </div>
            </div>

            <!-- Coordonnées -->
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
              <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
                <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Localisation</span>
              </div>
              <div class="flex items-center gap-3 bg-gray-50 rounded-lg px-3 py-2.5 border border-gray-200">
                <MapPin class="w-4 h-4 flex-shrink-0" style="color:#CC1525;" />
                <div v-if="form.latitude_center">
                  <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Coordonnées</p>
                  <p class="text-sm font-mono" style="color:#0F2356;">
                    {{ Number(form.latitude_center).toFixed(5) }}, {{ Number(form.longitude_center).toFixed(5) }}
                  </p>
                </div>
                <p v-else class="text-sm text-gray-400 italic">Aucune coordonnée définie</p>
              </div>
              <p class="text-xs text-gray-400 mt-2">Pour modifier la position, utilisez la carte directement.</p>
            </div>

            <!-- Erreur -->
            <div v-if="error" class="flex items-center gap-2 bg-red-50 border border-red-100 text-red-600 text-xs px-4 py-3 rounded-lg">
              <span class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0" />
              {{ error }}
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
                @click="handleClose"
              >
                Annuler
              </button>
              <button
                :disabled="loading"
                class="px-5 py-2 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                style="background:#0F2356;"
                @mouseenter="$event.currentTarget.style.background='#162d63'"
                @mouseleave="$event.currentTarget.style.background='#0F2356'"
                @click="handleSubmit"
              >
                <span v-if="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                <PencilLine v-else class="w-4 h-4" />
                {{ loading ? 'Enregistrement…' : 'Enregistrer' }}
              </button>
            </div>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active { transition: opacity 0.2s ease; }
.modal-fade-enter-from,
.modal-fade-leave-to     { opacity: 0; }
.modal-fade-enter-active .relative { transition: transform 0.2s ease; }
.modal-fade-enter-from .relative   { transform: scale(0.95); }
</style>