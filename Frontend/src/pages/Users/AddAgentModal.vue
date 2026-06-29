<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { X, UserPlus, AlertCircle, Shield } from 'lucide-vue-next'
import { createAgent } from '../../api/UserApi'
import { useCategoryStore } from '../../stores/CategoryStore'
import Input from '../../components/ui/Input.vue'

const categoryStore = useCategoryStore()
onMounted(() => categoryStore.fetchCategories())

defineProps({ modelValue: Boolean })
const emit = defineEmits(['update:modelValue', 'created'])

const loading     = ref(false)
const globalError = ref(null)

const form = reactive({ name: '', email: '', phone: '', password: '', confirmPassword: '', category: '', cin: '' })
const errors = reactive({ name: '', email: '', phone: '', password: '', confirmPassword: '', category: '', cin: '' })

const validateField = (field) => {
  errors[field] = ''
  if (field === 'name')    { if (!form.name.trim()) errors.name = 'Le nom est requis.'; else if (form.name.trim().length < 3) errors.name = 'Minimum 3 caractères.' }
  if (field === 'email')   { const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; if (!form.email.trim()) errors.email = "L'email est requis."; else if (!re.test(form.email)) errors.email = 'Adresse email invalide.' }
  if (field === 'phone')   { const re = /^(\+216)?[0-9]{8}$/; if (!form.phone.trim()) errors.phone = 'Le numéro est requis.'; else if (!re.test(form.phone.replace(/\s/g, ''))) errors.phone = 'Format invalide. Ex : +21612345678.' }
  if (field === 'category') { if (!form.category) errors.category = 'La catégorie est requise.' }
  if (field === 'password')  { if (!form.password) errors.password = 'Le mot de passe est requis.'; else if (form.password.length < 8) errors.password = 'Minimum 8 caractères.'; if (form.confirmPassword) validateField('confirmPassword') }
  if (field === 'confirmPassword') { if (!form.confirmPassword) errors.confirmPassword = 'Veuillez confirmer.'; else if (form.confirmPassword !== form.password) errors.confirmPassword = 'Les mots de passe ne correspondent pas.' }
  if (field === 'cin') { if (form.cin && !/^\d{8}$/.test(form.cin)) errors.cin = 'Le CIN doit contenir 8 chiffres.' }
}

const validateAll = () => { ['name','email','phone','password','confirmPassword','category','cin'].forEach(validateField); return Object.values(errors).every(e => !e) }

const FIELD_MAP = {
  name:     (msg) => msg.includes('required') ? 'Le nom est requis.' : msg,
  email:    (msg) => msg.includes('taken') ? 'Email déjà utilisé.' : msg.includes('required') ? "L'email est requis." : 'Email invalide.',
  phone:    (msg) => msg.includes('taken') ? 'Téléphone déjà utilisé.' : 'Numéro invalide.',
  cin:      (msg) => msg.includes('taken') ? 'CIN déjà utilisé.' : 'CIN invalide.',
  password: (msg) => msg.includes('required') ? 'Mot de passe requis.' : msg.includes('min') ? 'Minimum 8 caractères.' : msg,
  category: (msg) => msg.includes('required') ? 'La catégorie est requise.' : msg,
}

const applyBackendErrors = (apiErrors) => {
  Object.keys(errors).forEach(k => errors[k] = ''); globalError.value = null
  let hasField = false
  Object.entries(apiErrors).forEach(([field, messages]) => {
    const raw = Array.isArray(messages) ? messages[0] : messages
    const msg = FIELD_MAP[field] ? FIELD_MAP[field](raw) : raw
    if (field in errors) { errors[field] = msg; hasField = true }
  })
  if (!hasField) globalError.value = 'Des erreurs de validation ont été détectées.'
}

const handleSubmit = async () => {
  if (!validateAll()) return
  loading.value = true; globalError.value = null
  try {
    const payload = { ...form }; delete payload.confirmPassword
    await createAgent(payload); emit('created'); emit('update:modelValue', false); resetForm()
  } catch (err) {
    const status = err.response?.status, data = err.response?.data
    if (status === 422 && data?.errors) applyBackendErrors(data.errors)
    else if (status === 409) errors.email = 'Email déjà utilisé.'
    else globalError.value = data?.message || 'Une erreur inattendue est survenue.'
  } finally { loading.value = false }
}

const resetForm = () => { Object.assign(form, { name: '', email: '', phone: '', password: '', confirmPassword: '', category: '', cin: '' }); Object.keys(errors).forEach(k => errors[k] = ''); globalError.value = null }
const handleClose = () => { resetForm(); emit('update:modelValue', false) }

const inputBase  = 'w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0F2356] transition-colors'
const inputError = 'border-red-300 focus:border-red-400 bg-red-50'

const isFormValid = computed(() =>
  form.name.trim() &&
  form.email.trim() &&
  form.phone.trim() &&
  form.password.trim() &&
  form.confirmPassword.trim() &&
  form.category
)
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
              <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Nouvel Agent</span>
            </div>
            <h2 class="text-white text-lg font-semibold">Créer un agent</h2>
            <p class="text-white/40 text-xs mt-0.5">Renseignez les informations de l'agent municipal</p>
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
              <Input v-model="form.email" type="email" placeholder="agent@mun.tn"
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
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Téléphone <span style="color:#CC1525;">*</span></label>
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

        <!-- Affectation -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
            <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Affectation</span>
          </div>
          <div>
            <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Catégorie / Service <span style="color:#CC1525;">*</span></label>
            <div class="relative">
              <select v-model="form.category"
                :class="[inputBase, errors.category ? inputError : '', 'appearance-none']"
                style="color:#0F2356;" @change="validateField('category')">
                <option value="" disabled>— Sélectionner —</option>
                <option v-for="c in categoryStore.categories" :key="c.id" :value="c.name">{{ c.name }}</option>
              </select>
              <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">&#9662;</span>
            </div>
            <p v-if="errors.category" class="flex items-center gap-1 text-xs text-red-500 mt-1">
              <AlertCircle class="w-3 h-3 shrink-0" /> {{ errors.category }}
            </p>
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
            {{ loading ? 'Création…' : "Créer l'agent" }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>