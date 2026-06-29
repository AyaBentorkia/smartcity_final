<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue'
import { X, UserPlus, AlertCircle, Shield } from 'lucide-vue-next'
import { useCategoryStore } from '../../stores/CategoryStore'
import { useMunicipalities } from '../../composables/useMunicipalities'
import { UserRole } from '../../constants/UserRole'
import Input from '../../components/ui/Input.vue'
import { useUsers } from '../../composables/useUsers'

const { CreateUser } = useUsers()
const categoryStore     = useCategoryStore()
const { municipalities, fetchMunicipalities } = useMunicipalities()

onMounted(() => {
  categoryStore.fetchCategories()
  fetchMunicipalities()
  document.addEventListener('click', onDocumentClick)
})
onUnmounted(() => document.removeEventListener('click', onDocumentClick))

defineProps({ modelValue: Boolean })
const emit = defineEmits(['update:modelValue', 'created'])

const loading     = ref(false)
const globalError = ref(null)

// ── Dropdown state ──────────────────────────────────────────────
const dropdownRole         = ref(null)
const dropdownMunicipality = ref(null)
const dropdownCategory     = ref(null)
const openDropdown         = ref(null) // 'role' | 'municipality' | 'category' | null

const toggleDropdown = (name) => {
  openDropdown.value = openDropdown.value === name ? null : name
}

const onDocumentClick = (e) => {
  const refs = { role: dropdownRole, municipality: dropdownMunicipality, category: dropdownCategory }
  const ref  = refs[openDropdown.value]
  if (ref?.value && !ref.value.contains(e.target)) openDropdown.value = null
}

// ── Form ────────────────────────────────────────────────────────
const form = reactive({
  name:            '',
  email:           '',
  phone:           '',
  password:        '',
  confirmPassword: '',
  role:            '',
  municipality:    '',
  category:        '',
  cin:             '',
})

const errors = reactive({
  name:            '',
  email:           '',
  phone:           '',
  password:        '',
  confirmPassword: '',
  role:            '',
  cin:             '',
})

const roleOptions = [
  { value: UserRole.SUPER_ADMIN,     label: 'Super Admin'     },
  { value: UserRole.ADMIN_MUNICIPAL, label: 'Admin Municipal' },
  { value: UserRole.AGENT,           label: 'Agent'           },
  { value: UserRole.CITIZEN,         label: 'Citoyen'         },
]

const showCategory     = computed(() => form.role === UserRole.AGENT)
const showMunicipality = computed(() => [UserRole.AGENT, UserRole.ADMIN_MUNICIPAL].includes(form.role))

const selectedRoleLabel = computed(() =>
  roleOptions.find(o => o.value === form.role)?.label ?? '— Sélectionner un rôle —'
)
const selectedMunicipalityLabel = computed(() =>
  municipalities.value?.find(m => m.name === form.municipality)?.name ?? '— Sélectionner une municipalité —'
)
const selectedCategoryLabel = computed(() =>
  categoryStore.categories?.find(c => c.name === form.category)?.name ?? '— Sélectionner une catégorie —'
)

watch(() => form.role, () => {
  form.category     = ''
  form.municipality = ''
  openDropdown.value = null
})

// ── Validation ──────────────────────────────────────────────────
const validateField = (field) => {
  errors[field] = ''
  if (field === 'name') {
    if (!form.name.trim())                errors.name = 'Le nom est requis.'
    else if (form.name.trim().length < 3) errors.name = 'Minimum 3 caractères.'
  }
  if (field === 'email') {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (!form.email.trim())        errors.email = "L'email est requis."
    else if (!re.test(form.email)) errors.email = 'Adresse email invalide.'
  }
  if (field === 'phone') {
    const re = /^(\+216)?[0-9]{8}$/
    if (form.phone && !re.test(form.phone.replace(/\s/g, '')))
      errors.phone = 'Format invalide. Ex : +21612345678 ou 12345678.'
  }
  if (field === 'password') {
    if (!form.password)                errors.password = 'Le mot de passe est requis.'
    else if (form.password.length < 8) errors.password = 'Minimum 8 caractères.'
    if (form.confirmPassword)          validateField('confirmPassword')
  }
  if (field === 'confirmPassword') {
    if (!form.confirmPassword)
      errors.confirmPassword = 'Veuillez confirmer le mot de passe.'
    else if (form.confirmPassword !== form.password)
      errors.confirmPassword = 'Les mots de passe ne correspondent pas.'
  }
  if (field === 'role') {
    if (!form.role) errors.role = 'Le rôle est requis.'
  }
  if (field === 'cin') {
    if (form.cin && !/^\d{8}$/.test(form.cin))
      errors.cin = 'Le CIN doit contenir exactement 8 chiffres.'
  }
}

const validateAll = () => {
  ['name', 'email', 'phone', 'password', 'confirmPassword', 'role', 'cin'].forEach(validateField)
  return Object.values(errors).every(e => !e)
}

const FIELD_MAP = {
  name:     (msg) => msg.includes('required') ? 'Le nom est requis.' : msg,
  email:    (msg) => msg.includes('taken')    ? 'Cet email est déjà utilisé par un autre compte.'
                   : msg.includes('required') ? "L'email est requis."
                   : 'Adresse email invalide.',
  phone:    (msg) => msg.includes('taken')    ? 'Ce numéro de téléphone est déjà utilisé.'
                   : msg.includes('required') ? 'Le téléphone est requis.'
                   : 'Numéro de téléphone invalide.',
  cin:      (msg) => msg.includes('taken')    ? 'Ce numéro CIN est déjà utilisé.'
                   : 'Le CIN doit contenir 8 chiffres.',
  password: (msg) => msg.includes('required') ? 'Le mot de passe est requis.'
                   : msg.includes('min')       ? 'Minimum 8 caractères.'
                   : msg,
  role:     (msg) => msg.includes('required') ? 'Le rôle est requis.' : msg,
}

const applyBackendErrors = (apiErrors) => {
  Object.keys(errors).forEach(k => errors[k] = '')
  globalError.value = null
  let hasFieldError = false
  Object.entries(apiErrors).forEach(([field, messages]) => {
    const raw        = Array.isArray(messages) ? messages[0] : messages
    const translator = FIELD_MAP[field]
    const message    = translator ? translator(raw) : raw
    if (field in errors) { errors[field] = message; hasFieldError = true }
  })
  if (!hasFieldError)
    globalError.value = "Des erreurs de validation ont été détectées. Vérifiez les champs du formulaire."
}

const handleSubmit = async () => {
  if (!validateAll()) return
  loading.value     = true
  globalError.value = null
  try {
    const payload = { ...form }
    delete payload.confirmPassword
    if (!showCategory.value)     delete payload.category
    if (!showMunicipality.value) delete payload.municipality
    await CreateUser(payload)
    emit('created')
    emit('update:modelValue', false)
    resetForm()
  } catch (err) {
    const status = err.response?.status
    const data   = err.response?.data
    if (status === 422 && data?.errors) applyBackendErrors(data.errors)
    else if (status === 409) errors.email = 'Cet email est déjà utilisé par un autre compte.'
    else globalError.value = data?.message || 'Une erreur inattendue est survenue. Veuillez réessayer.'
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  Object.assign(form, { name: '', email: '', phone: '', password: '', confirmPassword: '', role: '', municipality: '', category: '', cin: '' })
  Object.keys(errors).forEach(k => errors[k] = '')
  globalError.value  = null
  openDropdown.value = null
}

const handleClose = () => { resetForm(); emit('update:modelValue', false) }

const inputBase  = 'w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0F2356] transition-colors'
const inputError = 'border-red-300 focus:border-red-400 bg-red-50'

const isFormValid = computed(() => {
  const base = form.name.trim() && form.email.trim() && form.password.trim() && form.confirmPassword.trim() && form.role
  if (!base) return false
  if (showMunicipality.value && !form.municipality) return false
  if (showCategory.value && !form.category) return false
  return true
})
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
              <UserPlus class="w-4 h-4" style="color:#CC1525;" />
              <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Nouvel Utilisateur</span>
            </div>
            <h2 class="text-white text-lg font-semibold">Créer un utilisateur</h2>
            <p class="text-white/40 text-xs mt-0.5">Renseignez les informations du nouvel utilisateur</p>
          </div>
          <button class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors flex-shrink-0"
            style="background:rgba(255,255,255,0.10);"
            @mouseenter="$event.currentTarget.style.background='rgba(255,255,255,0.20)'"
            @mouseleave="$event.currentTarget.style.background='rgba(255,255,255,0.10)'"
            @click="handleClose">
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
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Nom complet <span style="color:#CC1525;">*</span></label>
              <Input v-model="form.name" type="text" placeholder="Ahmed Ben Ali"
                :class="[inputBase, errors.name ? inputError : '']" style="color:#0F2356;" @blur="validateField('name')" />
              <p v-if="errors.name" class="flex items-center gap-1 text-xs text-red-500 mt-1">
                <AlertCircle class="w-3 h-3 shrink-0" /> {{ errors.name }}
              </p>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Email <span style="color:#CC1525;">*</span></label>
              <Input v-model="form.email" type="email" placeholder="user@example.com"
                :class="[inputBase, errors.email ? inputError : '']" style="color:#0F2356;" @blur="validateField('email')" />
              <p v-if="errors.email" class="flex items-center gap-1 text-xs text-red-500 mt-1">
                <AlertCircle class="w-3 h-3 shrink-0" /> {{ errors.email }}
              </p>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">CIN</label>
              <Input v-model="form.cin" type="text" placeholder="12345678" maxlength="8"
                :class="[inputBase, errors.cin ? inputError : '']" style="color:#0F2356;" @blur="validateField('cin')" />
              <p v-if="errors.cin" class="flex items-center gap-1 text-xs text-red-500 mt-1">
                <AlertCircle class="w-3 h-3 shrink-0" /> {{ errors.cin }}
              </p>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Téléphone</label>
              <Input v-model="form.phone" type="tel" placeholder="+216 XX XXX XXX"
                :class="[inputBase, errors.phone ? inputError : '']" style="color:#0F2356;" @blur="validateField('phone')" />
              <p v-if="errors.phone" class="flex items-center gap-1 text-xs text-red-500 mt-1">
                <AlertCircle class="w-3 h-3 shrink-0" /> {{ errors.phone }}
              </p>
            </div>
          </div>
        </div>

        <!-- Sécurité -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
            <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Sécurité</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Mot de passe <span style="color:#CC1525;">*</span></label>
              <Input v-model="form.password" type="password" placeholder="••••••••"
                :class="[inputBase, errors.password ? inputError : '']" style="color:#0F2356;" @blur="validateField('password')" />
              <p v-if="errors.password" class="flex items-center gap-1 text-xs text-red-500 mt-1">
                <AlertCircle class="w-3 h-3 shrink-0" /> {{ errors.password }}
              </p>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Confirmer <span style="color:#CC1525;">*</span></label>
              <Input v-model="form.confirmPassword" type="password" placeholder="••••••••"
                :class="[inputBase, errors.confirmPassword ? inputError : '']" style="color:#0F2356;" @blur="validateField('confirmPassword')" />
              <p v-if="errors.confirmPassword" class="flex items-center gap-1 text-xs text-red-500 mt-1">
                <AlertCircle class="w-3 h-3 shrink-0" /> {{ errors.confirmPassword }}
              </p>
            </div>
          </div>
        </div>

        <!-- Rôle & Affectation -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
            <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Rôle & Affectation</span>
          </div>
          <div class="flex flex-col gap-4">

            <!-- Rôle -->
            <div>
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Rôle <span style="color:#CC1525;">*</span></label>
              <div class="relative" ref="dropdownRole">
                <button
                  type="button"
                  :class="[inputBase, errors.role ? inputError : '', 'flex items-center justify-between']"
                  style="color:#0F2356;"
                  @click="toggleDropdown('role')"
                  @blur="validateField('role')"
                >
                  <span class="truncate">{{ selectedRoleLabel }}</span>
                  <span class="ml-2 text-gray-400 pointer-events-none">&#9662;</span>
                </button>
                <ul v-if="openDropdown === 'role'"
                  class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-56 overflow-auto z-50 text-sm">
                  <li
                    v-for="opt in roleOptions"
                    :key="opt.value"
                    class="px-3 py-2 text-gray-700 hover:bg-gray-100 cursor-pointer"
                    :class="{ 'bg-gray-100': form.role === opt.value }"
                    @click="form.role = opt.value; openDropdown = null"
                  >
                    {{ opt.label }}
                  </li>
                </ul>
              </div>
              <p v-if="errors.role" class="flex items-center gap-1 text-xs text-red-500 mt-1">
                <AlertCircle class="w-3 h-3 shrink-0" /> {{ errors.role }}
              </p>
            </div>

            <!-- Municipalité (Agent + Admin Municipal) -->
            <div v-if="showMunicipality">
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Municipalité</label>
              <div class="relative" ref="dropdownMunicipality">
                <button
                  type="button"
                  :class="[inputBase, 'flex items-center justify-between']"
                  style="color:#0F2356;"
                  @click="toggleDropdown('municipality')"
                >
                  <span class="truncate">{{ selectedMunicipalityLabel }}</span>
                  <span class="ml-2 text-gray-400 pointer-events-none">&#9662;</span>
                </button>
                <ul v-if="openDropdown === 'municipality'"
                  class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-56 overflow-auto z-50 text-sm">
                  <li
                    v-for="m in municipalities"
                    :key="m.id"
                    class="px-3 py-2 text-gray-700 hover:bg-gray-100 cursor-pointer"
                    :class="{ 'bg-gray-100': form.municipality === m.name }"
                    @click="form.municipality = m.name; openDropdown = null"
                  >
                    {{ m.name }}
                  </li>
                </ul>
              </div>
            </div>

            <!-- Catégorie (Agent seulement) -->
            <div v-if="showCategory">
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Catégorie / Service</label>
              <div class="relative" ref="dropdownCategory">
                <button
                  type="button"
                  :class="[inputBase, 'flex items-center justify-between']"
                  style="color:#0F2356;"
                  @click="toggleDropdown('category')"
                >
                  <span class="truncate">{{ selectedCategoryLabel }}</span>
                  <span class="ml-2 text-gray-400 pointer-events-none">&#9662;</span>
                </button>
                <ul v-if="openDropdown === 'category'"
                  class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-56 overflow-auto z-50 text-sm">
                  <li
                    v-for="c in categoryStore.categories"
                    :key="c.id"
                    class="px-3 py-2 text-gray-700 hover:bg-gray-100 cursor-pointer"
                    :class="{ 'bg-gray-100': form.category === c.name }"
                    @click="form.category = c.name; openDropdown = null"
                  >
                    {{ c.name }}
                  </li>
                </ul>
              </div>
            </div>

          </div>
        </div>

        <!-- Global error -->
        <div v-if="globalError" class="flex items-start gap-3 bg-red-50 border border-red-100 text-red-600 text-sm px-4 py-3 rounded-lg">
          <AlertCircle class="w-4 h-4 shrink-0 mt-0.5" />
          <span>{{ globalError }}</span>
        </div>

      </div>

      <!-- FOOTER -->
      <div class="bg-white border-t border-gray-200 px-6 py-4 flex items-center justify-between gap-3 flex-shrink-0">
        <div class="flex items-center gap-1.5 text-xs text-gray-400">
          <Shield class="w-3.5 h-3.5 flex-shrink-0" style="color:#CC1525;" />
          <span>Plateforme Municipale</span>
        </div>
        <div class="flex items-center gap-2">
          <button class="px-5 py-2 border border-gray-200 hover:border-gray-300 rounded-lg text-sm transition-colors" style="color:#4A5B78;" @click="handleClose">
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
            {{ loading ? 'Création…' : "Créer l'utilisateur" }}
          </button>
        </div>
      </div>

    </div>
  </div>
</template>