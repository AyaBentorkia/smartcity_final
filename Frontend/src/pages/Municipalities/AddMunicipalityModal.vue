<script setup>
import { ref, reactive, watch, onMounted, onUnmounted, computed } from 'vue'
import { X, HousePlus, Shield } from 'lucide-vue-next'
import Input from '../../components/ui/Input.vue'
import { useMunicipalities } from '../../composables/useMunicipalities'
import { useCities }         from '../../composables/useCities'
import { useGovernorates }   from '../../composables/useGovernorates'

const { addNewMunicipality } = useMunicipalities()
const { cities, listLoading: citiesLoading, fetchCitiesByGovernorate } = useCities()
const { governorates, listLoading: govLoading, fetchGovernorates }     = useGovernorates()

defineProps({ modelValue: Boolean })
const emit = defineEmits(['update:modelValue', 'created'])

const loading = ref(false)
const error   = ref(null)

const form = reactive({
  name: '', email: '', phone: '',
  governorate_id: '',
  city: '',
  address: '',
  number_of_inhabitants: '',
})

// ── Dropdown state ──────────────────────────────────────────────
const dropdownGov  = ref(null)
const dropdownCity = ref(null)
const openDropdown = ref(null) // 'gov' | 'city' | null

const toggleDropdown = (name) => {
  openDropdown.value = openDropdown.value === name ? null : name
}

const onDocumentClick = (e) => {
  const refs = { gov: dropdownGov, city: dropdownCity }
  const r    = refs[openDropdown.value]
  if (r?.value && !r.value.contains(e.target)) openDropdown.value = null
}

onMounted(() => {
  fetchGovernorates()
  document.addEventListener('click', onDocumentClick)
})
onUnmounted(() => document.removeEventListener('click', onDocumentClick))

watch(() => form.governorate_id, (id) => {
  form.city          = ''
  openDropdown.value = null
  if (id) fetchCitiesByGovernorate(id, true)
})

// ── Computed labels ─────────────────────────────────────────────
const selectedGovLabel = computed(() => {
  if (govLoading.value) return 'Chargement…'
  return governorates.value?.find(g => g.id === form.governorate_id)?.name ?? '— Sélectionner un gouvernorat —'
})

const selectedCityLabel = computed(() => {
  if (citiesLoading.value) return 'Chargement…'
  if (!form.governorate_id) return 'Choisir un gouvernorat d\'abord'
  return cities.value?.find(c => c.id === form.city)?.name ?? '— Sélectionner une ville —'
})

const cityDisabled = computed(() => !form.governorate_id || citiesLoading.value)

// ── Form helpers ────────────────────────────────────────────────
const resetForm = () => {
  Object.assign(form, {
    name: '', email: '', phone: '',
    governorate_id: '', city: '',
    address: '', number_of_inhabitants: '',
  })
  error.value        = null
  openDropdown.value = null
}

const handleSubmit = async () => {
  loading.value = true
  error.value   = null
  try {
    await addNewMunicipality(form)
    emit('created')
    emit('update:modelValue', false)
    resetForm()
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur lors de la création'
  } finally {
    loading.value = false
  }
}

const handleClose = () => { resetForm(); emit('update:modelValue', false) }

const isFormValid = computed(() =>
  form.name.trim() && form.email.trim() && form.governorate_id && form.city
)

const inputBase = 'w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0F2356] transition-colors'
</script>

<template>
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
              <HousePlus class="w-4 h-4" style="color:#CC1525;" />
              <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Nouvelle Municipalité</span>
            </div>
            <h2 class="text-white text-lg font-semibold leading-snug">Créer une municipalité</h2>
            <p class="text-white/40 text-xs mt-0.5">Renseignez les informations de la nouvelle entité</p>
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
              <Input v-model="form.name" type="text" placeholder="Municipalité A"
                :class="inputBase" style="color:#0F2356;" />
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Email <span style="color:#CC1525;">*</span></label>
              <Input v-model="form.email" type="email" placeholder="contact@mun.tn"
                :class="inputBase" style="color:#0F2356;" />
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

            <!-- Gouvernorat -->
            <div>
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Gouvernorat <span style="color:#CC1525;">*</span></label>
              <div class="relative" ref="dropdownGov">
                <button
                  type="button"
                  :class="[inputBase, 'flex items-center justify-between', govLoading ? 'opacity-50 cursor-not-allowed' : '']"
                  style="color:#0F2356;"
                  :disabled="govLoading"
                  @click="!govLoading && toggleDropdown('gov')"
                >
                  <span class="truncate">{{ selectedGovLabel }}</span>
                  <span class="ml-2 text-gray-400 pointer-events-none">&#9662;</span>
                </button>
                <ul v-if="openDropdown === 'gov'"
                  class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-56 overflow-auto z-50 text-sm">
                  <li
                    v-for="gov in governorates"
                    :key="gov.id"
                    class="px-3 py-2 text-gray-700 hover:bg-gray-100 cursor-pointer"
                    :class="{ 'bg-gray-100': form.governorate_id === gov.id }"
                    @click="form.governorate_id = gov.id; openDropdown = null"
                  >
                    {{ gov.name }}
                  </li>
                </ul>
              </div>
            </div>

            <!-- Ville -->
            <div>
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Ville <span style="color:#CC1525;">*</span></label>
              <div class="relative" ref="dropdownCity">
                <button
                  type="button"
                  :class="[inputBase, 'flex items-center justify-between', cityDisabled ? 'opacity-50 cursor-not-allowed' : '']"
                  style="color:#0F2356;"
                  :disabled="cityDisabled"
                  @click="!cityDisabled && toggleDropdown('city')"
                >
                  <span class="truncate">{{ selectedCityLabel }}</span>
                  <span class="ml-2 text-gray-400 pointer-events-none">&#9662;</span>
                </button>
                <ul v-if="openDropdown === 'city'"
                  class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-56 overflow-auto z-50 text-sm">
                  <li
                    v-for="c in cities"
                    :key="c.id"
                    class="px-3 py-2 text-gray-700 hover:bg-gray-100 cursor-pointer"
                    :class="{ 'bg-gray-100': form.city === c.id }"
                    @click="form.city = c.id; openDropdown = null"
                  >
                    {{ c.name }}
                  </li>
                </ul>
              </div>
            </div>

          </div>
          <div>
            <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Adresse</label>
            <Input v-model="form.address" type="text" placeholder="Rue principale"
              :class="inputBase" style="color:#0F2356;" />
          </div>
        </div>

        <!-- Coordonnées & Données -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
            <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Coordonnées & Données</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Téléphone</label>
              <Input v-model="form.phone" type="tel" placeholder="+216 XX XXX XXX"
                :class="inputBase" style="color:#0F2356;" />
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Nombre d'habitants</label>
              <Input v-model="form.number_of_inhabitants" type="number" placeholder="50 000"
                :class="inputBase" style="color:#0F2356;" />
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
            :disabled="loading || !isFormValid"
            class="px-5 py-2 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            style="background:#0F2356;"
            @mouseenter="!loading && isFormValid && ($event.currentTarget.style.background='#162d63')"
            @mouseleave="$event.currentTarget.style.background='#0F2356'"
            @click="handleSubmit"
          >
            <span v-if="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
            {{ loading ? 'Création…' : 'Créer la municipalité' }}
          </button>
        </div>
      </div>

    </div>
  </div>
</template>