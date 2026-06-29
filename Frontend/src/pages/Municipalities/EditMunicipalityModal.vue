<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { X, SquarePen, Shield } from 'lucide-vue-next'
import Input from '../../components/ui/Input.vue'
import { useMunicipalities } from '../../composables/useMunicipalities'
import { useCities }         from '../../composables/useCities'
import { useGovernorates }   from '../../composables/useGovernorates'

const props = defineProps({
  modelValue:   { type: Boolean, required: true },
  municipality: { type: Object,  default: null  },
})
const emit = defineEmits(['update:modelValue', 'updated'])

const { actionLoading, editMunicipality } = useMunicipalities()
const { cities, listLoading: citiesLoading, fetchCitiesByGovernorate } = useCities()
const { governorates, listLoading: govLoading, fetchGovernorates }     = useGovernorates()

const error = ref(null)
const form  = reactive({
  name: '', email: '', phone: '',
  governorate_id: '',
  city: '',
  address: '',
  number_of_inhabitants: '',
})

onMounted(() => fetchGovernorates())

watch(() => props.municipality, (mun) => {
  if (!mun) return
  const governorateId = mun.city?.governorate?.id ?? ''
  Object.assign(form, {
    name:                  mun.name                  ?? '',
    email:                 mun.email                 ?? '',
    phone:                 mun.phone                 ?? '',
    governorate_id:        governorateId,
    city:                  mun.city?.id              ?? '',
    address:               mun.address               ?? '',
    number_of_inhabitants: mun.number_of_inhabitants ?? '',
  })
  if (governorateId) fetchCitiesByGovernorate(governorateId)
}, { immediate: true })

watch(() => form.governorate_id, (id, prev) => {
  if (id !== prev) {
    form.city = ''
    if (id) fetchCitiesByGovernorate(id, true)
  }
})

const handleClose  = () => { error.value = null; emit('update:modelValue', false) }
const handleSubmit = async () => {
  error.value = null
  try {
    await editMunicipality(props.municipality.id, form)
    emit('updated')
    emit('update:modelValue', false)
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur lors de la mise à jour'
  }
}
</script>

<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="background:rgba(15,35,86,0.50); backdrop-filter:blur(4px);"
        @click.self="handleClose"
      >
        <div class="relative w-full max-w-lg bg-white rounded-xl shadow-2xl flex flex-col overflow-hidden max-h-[92vh]">

          <!-- HEADER -->
          <div class="relative px-6 py-4 flex-shrink-0 overflow-hidden" style="background:#0F2356;">
            <div class="absolute inset-0 opacity-[0.04] pointer-events-none"
              style="background-image:repeating-linear-gradient(0deg,#fff 0,#fff 1px,transparent 1px,transparent 32px),repeating-linear-gradient(90deg,#fff 0,#fff 1px,transparent 1px,transparent 32px);" />
            <div class="absolute left-0 top-0 bottom-0 w-1" style="background:#CC1525;" />
            <div class="relative z-10 flex items-start justify-between gap-4">
              <div>
                <div class="flex items-center gap-2 mb-1">
                  <SquarePen class="w-4 h-4" style="color:#CC1525;" />
                  <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Modification</span>
                </div>
                <h2 class="text-white text-lg font-semibold leading-snug">{{ municipality?.name ?? 'Modifier la municipalité' }}</h2>
                <p class="text-white/40 text-xs mt-0.5">Mettre à jour les informations de cette entité</p>
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

            <!-- Identité -->
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
              <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
                <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Identité</span>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Nom <span style="color:#CC1525;">*</span></label>
                  <Input v-model="form.name" type="text" required placeholder="Municipalité A"
                    class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0F2356] transition-colors"
                    style="color:#0F2356;" />
                </div>
                <div>
                  <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Email</label>
                  <Input v-model="form.email" type="email" placeholder="contact@mun.tn"
                    class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0F2356] transition-colors"
                    style="color:#0F2356;" />
                </div>
              </div>
            </div>

            <!-- Localisation -->
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
              <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
                <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Localisation</span>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                  <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Gouvernorat <span style="color:#CC1525;">*</span></label>
                  <select v-model="form.governorate_id" required :disabled="govLoading"
                    class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0F2356] transition-colors appearance-none disabled:opacity-50 disabled:cursor-not-allowed"
                    style="color:#0F2356;">
                    <option value="" disabled>{{ govLoading ? 'Chargement…' : 'Sélectionner' }}</option>
                    <option v-for="gov in governorates" :key="gov.id" :value="gov.id">{{ gov.name }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Ville <span style="color:#CC1525;">*</span></label>
                  <select v-model="form.city" required :disabled="!form.governorate_id || citiesLoading"
                    class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0F2356] transition-colors appearance-none disabled:opacity-50 disabled:cursor-not-allowed"
                    style="color:#0F2356;">
                    <option value="" disabled>{{ citiesLoading ? 'Chargement…' : form.governorate_id ? 'Sélectionner' : 'Choisir un gouvernorat' }}</option>
                    <option v-for="c in cities" :key="c.id" :value="c.id">{{ c.name }}</option>
                  </select>
                </div>
              </div>
              <div>
                <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Adresse</label>
                <Input v-model="form.address" type="text" placeholder="Rue principale"
                  class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0F2356] transition-colors"
                  style="color:#0F2356;" />
              </div>
            </div>

            <!-- Coordonnées -->
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
              <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
                <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Coordonnées & Données</span>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Téléphone</label>
                  <Input v-model="form.phone" type="tel" placeholder="+216 XX XXX XXX"
                    class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0F2356] transition-colors"
                    style="color:#0F2356;" />
                </div>
                <div>
                  <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Nombre d'habitants</label>
                  <Input v-model="form.number_of_inhabitants" type="number" placeholder="50 000"
                    class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0F2356] transition-colors"
                    style="color:#0F2356;" />
                </div>
              </div>
            </div>

            <!-- Error -->
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
                :disabled="actionLoading"
                class="px-5 py-2 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                style="background:#0F2356;"
                @mouseenter="$event.currentTarget.style.background='#162d63'"
                @mouseleave="$event.currentTarget.style.background='#0F2356'"
                @click="handleSubmit"
              >
                <span v-if="actionLoading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                {{ actionLoading ? 'Enregistrement…' : 'Enregistrer' }}
              </button>
            </div>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
</style>