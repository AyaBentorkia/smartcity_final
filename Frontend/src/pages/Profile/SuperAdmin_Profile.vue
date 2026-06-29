<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import {
  User, Mail, Phone, CreditCard, Home, MapPin,
  Shield, Save, X, Edit3, Check, AlertCircle,
  Eye, EyeOff, Lock, Calendar, Globe, Server, Database
} from 'lucide-vue-next'
import { storeToRefs } from 'pinia'
import { useProfileStore } from '../../stores/ProfileStore'

const profileStore = useProfileStore()
const { profile, loading } = storeToRefs(profileStore)
const { fetchProfile, editProfile } = profileStore

onMounted(fetchProfile)

const editing = ref(false)
const saveSuccess = ref(false)
const globalError = ref(null)
const actionLoading = ref(false)
const activeTab = ref('profil')
const showPwd = ref(false)
const showConfirm = ref(false)

const form = reactive({
  name: '', email: '', phone: '', cin: '', city: '', address: '',
  password: '', confirmPassword: '',
})

const errors = reactive({
  name: '', email: '', phone: '', cin: '', city: '', address: '',
  password: '', confirmPassword: '',
})

watch(profile, (p) => {
  if (!p) return
  form.name    = p.name    ?? ''
  form.email   = p.email   ?? ''
  form.phone   = p.phone   ?? ''
  form.cin     = p.cin     ?? ''
  form.city    = p.city    ?? ''
  form.address = p.address ?? ''
  form.password = ''
  form.confirmPassword = ''
}, { immediate: true })

const initials = computed(() => {
  const parts = (profile.value?.name || '').split(' ').filter(Boolean)
  return ((parts[0]?.[0] || '') + (parts[1]?.[0] || '')).toUpperCase() || 'SA'
})

const memberSince = computed(() => {
  if (!profile.value?.created_at) return '—'
  return new Date(profile.value.created_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
})

const validateField = (field) => {
  errors[field] = ''
  if (field === 'name') {
    if (!form.name.trim()) errors.name = 'Le nom est requis.'
    else if (form.name.trim().length < 3) errors.name = 'Minimum 3 caractères.'
  }
  if (field === 'email') {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (!form.email.trim()) errors.email = "L'email est requis."
    else if (!re.test(form.email)) errors.email = 'Adresse email invalide.'
  }
  if (field === 'phone') {
    const re = /^(\+216)?[0-9]{8}$/
    if (form.phone && !re.test(form.phone.replace(/\s/g, '')))
      errors.phone = 'Format invalide. Ex : +21612345678'
  }
  if (field === 'cin') {
    if (form.cin && !/^\d{8}$/.test(form.cin))
      errors.cin = 'Le CIN doit contenir 8 chiffres.'
  }
  if (field === 'password') {
    if (form.password && form.password.length < 8)
      errors.password = 'Minimum 8 caractères.'
    if (form.confirmPassword) validateField('confirmPassword')
  }
  if (field === 'confirmPassword') {
    if (form.password && form.confirmPassword !== form.password)
      errors.confirmPassword = 'Les mots de passe ne correspondent pas.'
  }
}

const validateAll = () => {
  ['name', 'email', 'phone', 'cin', 'password', 'confirmPassword'].forEach(validateField)
  return Object.values(errors).every(e => !e)
}

const handleCancel = () => {
  editing.value = false
  if (profile.value) {
    form.name    = profile.value.name    ?? ''
    form.email   = profile.value.email   ?? ''
    form.phone   = profile.value.phone   ?? ''
    form.cin     = profile.value.cin     ?? ''
    form.city    = profile.value.city    ?? ''
    form.address = profile.value.address ?? ''
    form.password = ''
    form.confirmPassword = ''
  }
  Object.keys(errors).forEach(k => errors[k] = '')
  globalError.value = null
}

const handleSave = async () => {
  if (!validateAll()) return
  actionLoading.value = true
  globalError.value = null
  saveSuccess.value = false
  try {
    const payload = {
      name: form.name, email: form.email,
      phone: form.phone || undefined,
      cin: form.cin || undefined,
      city: form.city || undefined,
      address: form.address || undefined,
    }
    if (form.password) {
      payload.password = form.password
      payload.password_confirmation = form.confirmPassword
    }
    await editProfile(payload)
    editing.value = false
    saveSuccess.value = true
    form.password = ''
    form.confirmPassword = ''
    setTimeout(() => (saveSuccess.value = false), 3500)
  } catch (err) {
    const status = err?.response?.status
    const data = err?.response?.data
    if (status === 422 && data?.errors) {
      Object.entries(data.errors).forEach(([field, msgs]) => {
        if (field in errors) errors[field] = Array.isArray(msgs) ? msgs[0] : msgs
      })
    } else {
      globalError.value = data?.message || 'Une erreur inattendue est survenue.'
    }
  } finally {
    actionLoading.value = false
  }
}

const pwdStrength = computed(() => {
  const len = form.password.length
  if (!len) return 0
  if (len < 6) return 1
  if (len < 10) return 2
  return 3
})
const pwdLabel = computed(() => ['', 'Faible', 'Moyen', 'Fort'][pwdStrength.value])
</script>

<template>
  <div class="space-y-5 max-w-6xl mx-auto relative min-h-[1000px]">

    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="w-6 h-6 border-2 border-[#0F2356] border-t-transparent rounded-full animate-spin" />
    </div>

    <template v-else-if="profile">

      <!-- ═══ HERO CARD ═══ (Super Admin — accent rouge + badge distinctif) -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <!-- Banner: dégradé navy → légèrement plus sombre pour le super admin -->
        <div class="h-20 relative overflow-hidden" style="background: linear-gradient(135deg, #0a1a3e 0%, #0F2356 60%, #1a0a1e 100%);">
          <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#CC1525]" />
          <div class="absolute inset-0 opacity-[0.04]"
            style="background-image: repeating-linear-gradient(0deg,#fff 0,#fff 1px,transparent 1px,transparent 28px),repeating-linear-gradient(90deg,#fff 0,#fff 1px,transparent 1px,transparent 28px);" />
          <!-- Badge Super Admin top right -->
          <div class="absolute top-3 right-5 flex items-center gap-1.5 bg-[#CC1525]/20 border border-[#CC1525]/30 rounded px-2.5 py-1">
            <Shield class="w-3 h-3 text-[#CC1525]" />
            <span class="text-[#CC1525] text-[10px] uppercase tracking-widest">Super Admin</span>
          </div>
        </div>

        <div class="px-7 pb-6 relative">
          <div class="absolute -top-7 left-7">
            <!-- Avatar avec ring rouge pour super admin -->
            <div class="w-14 h-14 rounded-full flex items-center justify-center shadow-md"
              style="background: linear-gradient(135deg, #0F2356, #1a3a7a); border: 3px solid white; outline: 2px solid #CC1525; outline-offset: 2px;">
              <span class="text-white text-lg select-none">{{ initials }}</span>
            </div>
          </div>

          <div class="flex justify-end pt-3 gap-2">
            <Transition name="fade">
              <div v-if="saveSuccess"
                class="flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-3 py-1.5 rounded-lg text-xs">
                <Check class="w-3.5 h-3.5" /> Enregistré avec succès
              </div>
            </Transition>
            <template v-if="editing">
              <button @click="handleCancel"
                class="flex items-center gap-1.5 border border-gray-200 hover:border-gray-300 text-[#4A5B78] px-4 py-1.5 rounded-lg text-xs transition-colors">
                <X class="w-3.5 h-3.5" /> Annuler
              </button>
              <button @click="handleSave" :disabled="actionLoading"
                class="flex items-center gap-1.5 bg-[#0F2356] hover:bg-[#162d63] disabled:opacity-50 text-white px-4 py-1.5 rounded-lg text-xs transition-colors shadow-sm">
                <span v-if="actionLoading" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin" />
                <Save v-else class="w-3.5 h-3.5" />
                {{ actionLoading ? 'Enregistrement...' : 'Enregistrer' }}
              </button>
            </template>
            <button v-else @click="editing = true"
              class="flex items-center gap-1.5 border border-gray-200 hover:border-[#0F2356] text-[#4A5B78] hover:text-[#0F2356] px-4 py-1.5 rounded-lg text-xs transition-colors">
              <Edit3 class="w-3.5 h-3.5" /> Modifier
            </button>
          </div>

          <div class="mt-1">
            <div class="flex items-center gap-3 flex-wrap">
              <h1 class="text-[#0F2356] text-lg leading-snug">{{ profile.name }}</h1>
              <!-- Badge Super Admin plus visible -->
              <span class="flex items-center gap-1 bg-[#CC1525]/8 border border-[#CC1525]/25 rounded px-2.5 py-0.5">
                <Shield class="w-3 h-3 text-[#CC1525]" />
                <span class="text-xs text-[#CC1525]">Super Administrateur</span>
              </span>
              <span class="flex items-center gap-1.5 bg-emerald-50 border border-emerald-100 rounded px-2.5 py-0.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" />
                <span class="text-xs text-emerald-600">Actif</span>
              </span>
            </div>
            <div class="flex items-center gap-5 mt-2 flex-wrap">
              <span class="flex items-center gap-1.5 text-gray-400 text-xs">
                <Calendar class="w-3 h-3" /> Membre depuis {{ memberSince }}
              </span>
              <!-- <span class="flex items-center gap-1.5 text-gray-400 text-xs">
                <MapPin class="w-3 h-3 text-[#CC1525]" /> {{ profile.city ?? 'Tunisie' }}
              </span> -->
              <span class="flex items-center gap-1.5 text-gray-400 text-xs">
                <Globe class="w-3 h-3" /> Accès national
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Global error -->
      <Transition name="fade">
        <div v-if="globalError"
          class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-3 rounded-lg">
          <AlertCircle class="w-4 h-4 shrink-0" /> {{ globalError }}
        </div>
      </Transition>

      <!-- ═══ TABS ═══ -->
      <div class="flex items-center gap-1 bg-white border border-gray-200 rounded-lg p-1 w-fit shadow-sm">
        <button v-for="t in [{ key: 'profil', label: 'Informations personnelles' }, { key: 'securite', label: 'Sécurité' }]"
          :key="t.key" @click="activeTab = t.key"
          :class="['px-5 py-1.5 rounded text-xs transition-all', activeTab === t.key ? 'bg-[#0F2356] text-white shadow-sm' : 'text-[#4A5B78] hover:text-[#0F2356] hover:bg-gray-50']">
          {{ t.label }}
        </button>
      </div>

      <!-- ═══ TAB PROFIL ═══ -->
      <div v-if="activeTab === 'profil'" class="grid lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 flex flex-col gap-4">

          <!-- Informations personnelles -->
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-1">
              <div class="w-1 h-4 bg-[#CC1525] rounded-full" />
              <span class="text-[10px] text-[#CC1525] uppercase tracking-[0.13em]">Informations personnelles</span>
            </div>
            <p class="text-gray-400 text-xs mb-5 ml-3">Vos données de contact et d'identité</p>

            <div class="grid md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <label class="text-[10px] text-gray-400 uppercase tracking-widest block mb-1.5">Nom complet</label>
                <div class="relative">
                  <User class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-300" />
                  <input v-model="form.name" type="text" :disabled="!editing" @blur="editing && validateField('name')"
                    :class="['w-full rounded-lg pl-9 pr-4 py-2.5 text-sm text-[#0F2356] outline-none transition-colors', editing ? 'bg-white border focus:border-[#0F2356]' : 'bg-[#F7F8FB] border border-gray-100', editing && errors.name ? 'border-[#CC1525]' : 'border-gray-200']" />
                </div>
                <p v-if="editing && errors.name" class="text-[10px] text-[#CC1525] mt-1 flex items-center gap-1">
                  <AlertCircle class="w-3 h-3" /> {{ errors.name }}
                </p>
              </div>
              <div>
                <label class="text-[10px] text-gray-400 uppercase tracking-widest block mb-1.5">Email</label>
                <div class="relative">
                  <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-300" />
                  <input v-model="form.email" type="email" :disabled="!editing" @blur="editing && validateField('email')"
                    :class="['w-full rounded-lg pl-9 pr-4 py-2.5 text-sm text-[#0F2356] outline-none transition-colors', editing ? 'bg-white border focus:border-[#0F2356]' : 'bg-[#F7F8FB] border border-gray-100', editing && errors.email ? 'border-[#CC1525]' : 'border-gray-200']" />
                </div>
                <p v-if="editing && errors.email" class="text-[10px] text-[#CC1525] mt-1 flex items-center gap-1">
                  <AlertCircle class="w-3 h-3" /> {{ errors.email }}
                </p>
              </div>
              <div>
                <label class="text-[10px] text-gray-400 uppercase tracking-widest block mb-1.5">CIN</label>
                <div class="relative">
                  <CreditCard class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-300" />
                  <input v-model="form.cin" type="text" maxlength="8" :disabled="!editing" @blur="editing && validateField('cin')"
                    :class="['w-full rounded-lg pl-9 pr-4 py-2.5 text-sm text-[#0F2356] outline-none transition-colors', editing ? 'bg-white border focus:border-[#0F2356]' : 'bg-[#F7F8FB] border border-gray-100', editing && errors.cin ? 'border-[#CC1525]' : 'border-gray-200']" />
                </div>
                <p v-if="editing && errors.cin" class="text-[10px] text-[#CC1525] mt-1 flex items-center gap-1">
                  <AlertCircle class="w-3 h-3" /> {{ errors.cin }}
                </p>
              </div>
              <div>
                <label class="text-[10px] text-gray-400 uppercase tracking-widest block mb-1.5">Téléphone</label>
                <div class="relative">
                  <Phone class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-300" />
                  <input v-model="form.phone" type="tel" placeholder="+216 XX XXX XXX" :disabled="!editing" @blur="editing && validateField('phone')"
                    :class="['w-full rounded-lg pl-9 pr-4 py-2.5 text-sm text-[#0F2356] outline-none transition-colors', editing ? 'bg-white border focus:border-[#0F2356]' : 'bg-[#F7F8FB] border border-gray-100', editing && errors.phone ? 'border-[#CC1525]' : 'border-gray-200']" />
                </div>
                <p v-if="editing && errors.phone" class="text-[10px] text-[#CC1525] mt-1 flex items-center gap-1">
                  <AlertCircle class="w-3 h-3" /> {{ errors.phone }}
                </p>
              </div>
              <!-- <div>
                <label class="text-[10px] text-gray-400 uppercase tracking-widest block mb-1.5">Ville</label>
                <input v-model="form.city" type="text" :disabled="!editing"
                  :class="['w-full rounded-lg px-4 py-2.5 text-sm text-[#0F2356] outline-none transition-colors', editing ? 'bg-white border border-gray-200 focus:border-[#0F2356]' : 'bg-[#F7F8FB] border border-gray-100']" />
              </div> -->
              <div class="md:col-span-2">
                <label class="text-[10px] text-gray-400 uppercase tracking-widest block mb-1.5">Adresse</label>
                <div class="relative">
                  <Home class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-300" />
                  <input v-model="form.address" type="text" :disabled="!editing"
                    :class="['w-full rounded-lg pl-9 pr-4 py-2.5 text-sm text-[#0F2356] outline-none transition-colors', editing ? 'bg-white border border-gray-200 focus:border-[#0F2356]' : 'bg-[#F7F8FB] border border-gray-100']" />
                </div>
              </div>
            </div>

            <div v-if="editing" class="mt-5 pt-4 border-t border-gray-100 flex justify-end gap-2">
              <button @click="handleCancel"
                class="flex items-center gap-1.5 border border-gray-200 hover:border-gray-300 text-[#4A5B78] px-5 py-2 rounded-lg text-xs transition-colors">
                <X class="w-3.5 h-3.5" /> Annuler
              </button>
              <button @click="handleSave" :disabled="actionLoading"
                class="flex items-center gap-1.5 bg-[#0F2356] hover:bg-[#162d63] disabled:opacity-50 text-white px-5 py-2 rounded-lg text-xs transition-colors shadow-sm">
                <span v-if="actionLoading" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin" />
                <Save v-else class="w-3.5 h-3.5" />
                {{ actionLoading ? 'Enregistrement...' : 'Enregistrer les modifications' }}
              </button>
            </div>
          </div>

          <!-- Périmètre d'accès (spécifique super admin — read-only) -->
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-4">
              <div class="w-1 h-4 bg-[#CC1525] rounded-full" />
              <span class="text-[10px] text-[#CC1525] uppercase tracking-[0.13em]">Périmètre d'accès</span>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
              <div v-for="f in [
                { label: 'Périmètre', value: 'National — Tunisie', icon: Globe },
                { label: 'Rôle système', value: 'Super Administrateur', icon: Shield },
                { label: 'Accès données', value: 'Toutes communes', icon: Database },
                { label: 'Type de compte', value: 'Compte système protégé', icon: Server },
              ]" :key="f.label">
                <label class="text-[10px] text-gray-400 uppercase tracking-widest block mb-1.5">{{ f.label }}</label>
                <div class="flex items-center gap-2.5 bg-[#F7F8FB] border border-gray-100 rounded-lg py-2.5 pl-3 pr-4">
                  <component :is="f.icon" class="w-3.5 h-3.5 text-gray-300 shrink-0" />
                  <span class="text-[#1A2B4A] text-sm">{{ f.value }}</span>
                </div>
              </div>
            </div>
            <!-- Note d'avertissement -->
            <div class="mt-4 flex items-start gap-2.5 bg-amber-50 border border-amber-100 rounded-lg p-3">
              <AlertCircle class="w-3.5 h-3.5 text-amber-500 shrink-0 mt-0.5" />
              <p class="text-[10px] text-amber-700 leading-relaxed">
                Ce compte bénéficie d'un accès complet à la plateforme. Toute modification doit être effectuée avec précaution.
              </p>
            </div>
          </div>
        </div>

        <!-- Right sidebar -->
        <div class="flex flex-col gap-4">
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center gap-2 mb-4">
              <div class="w-1 h-4 bg-[#CC1525] rounded-full" />
              <span class="text-[10px] text-[#CC1525] uppercase tracking-[0.13em]">Statut du compte</span>
            </div>
            <div class="flex flex-col gap-3">
              <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400">Statut</span>
                <span class="flex items-center gap-1.5 text-xs text-emerald-600 bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" /> Actif
                </span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400">Rôle</span>
                <span class="text-xs text-[#CC1525] bg-[#CC1525]/8 border border-[#CC1525]/20 px-2 py-0.5 rounded flex items-center gap-1">
                  <Shield class="w-2.5 h-2.5" /> Super Admin
                </span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400">Email </span>
                <span :class="profile.email_verified_at ? 'text-emerald-600' : 'text-amber-500'" class="text-xs">
                  {{ profile.email_verified_at ? '✓ Oui' : '✗ Non' }}
                </span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400">Membre depuis</span>
                <span class="text-xs text-gray-500">{{ memberSince }}</span>
              </div>
            </div>
          </div>

          <!-- Accès système -->
          <div class="bg-[#0F2356] rounded-xl p-5 relative overflow-hidden">
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#CC1525] rounded-l-xl" />
            <div class="absolute inset-0 opacity-[0.04]"
              style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 20px 20px;" />
            <div class="relative z-10">
              <Shield class="w-5 h-5 text-[#CC1525] mb-3" />
              <p class="text-white text-xs mb-1">Accès Super Administrateur</p>
              <p class="text-white/40 text-[10px] leading-relaxed">
                Vous disposez d'un accès total à la plateforme Incitya : communes, agents, incidents et configuration système.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══ TAB SÉCURITÉ ═══ -->
      <div v-if="activeTab === 'securite'" class="grid lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2">
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-1">
              <div class="w-1 h-4 bg-[#CC1525] rounded-full" />
              <span class="text-[10px] text-[#CC1525] uppercase tracking-[0.13em]">Changer le mot de passe</span>
            </div>
            <p class="text-gray-400 text-xs mb-5 ml-3">Laisser vide pour conserver votre mot de passe actuel</p>

            <div class="flex flex-col gap-4">
              <div>
                <label class="text-[10px] text-gray-400 uppercase tracking-widest block mb-1.5">Nouveau mot de passe</label>
                <div class="relative">
                  <Lock class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-300" />
                  <input v-model="form.password" :type="showPwd ? 'text' : 'password'" placeholder="••••••••"
                    @blur="validateField('password')"
                    :class="['w-full bg-white border rounded-lg pl-9 pr-10 py-2.5 text-sm text-[#0F2356] outline-none transition-colors', errors.password ? 'border-[#CC1525]' : 'border-gray-200 focus:border-[#0F2356]']" />
                  <button @click="showPwd = !showPwd" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-500">
                    <Eye v-if="!showPwd" class="w-3.5 h-3.5" /><EyeOff v-else class="w-3.5 h-3.5" />
                  </button>
                </div>
                <p v-if="errors.password" class="text-[10px] text-[#CC1525] mt-1 flex items-center gap-1">
                  <AlertCircle class="w-3 h-3" /> {{ errors.password }}
                </p>
              </div>

              <div>
                <label class="text-[10px] text-gray-400 uppercase tracking-widest block mb-1.5">Confirmer le mot de passe</label>
                <div class="relative">
                  <Lock class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-300" />
                  <input v-model="form.confirmPassword" :type="showConfirm ? 'text' : 'password'" placeholder="••••••••"
                    @blur="validateField('confirmPassword')"
                    :class="['w-full bg-white border rounded-lg pl-9 pr-10 py-2.5 text-sm text-[#0F2356] outline-none transition-colors', errors.confirmPassword ? 'border-[#CC1525]' : 'border-gray-200 focus:border-[#0F2356]']" />
                  <button @click="showConfirm = !showConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-500">
                    <Eye v-if="!showConfirm" class="w-3.5 h-3.5" /><EyeOff v-else class="w-3.5 h-3.5" />
                  </button>
                </div>
                <p v-if="errors.confirmPassword" class="text-[10px] text-[#CC1525] mt-1 flex items-center gap-1">
                  <AlertCircle class="w-3 h-3" /> {{ errors.confirmPassword }}
                </p>
              </div>

              <div v-if="form.password">
                <div class="flex justify-between items-center mb-1.5">
                  <span class="text-[10px] text-gray-400 uppercase tracking-widest">Force du mot de passe</span>
                  <span class="text-[10px] text-gray-400">{{ pwdLabel }}</span>
                </div>
                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden flex gap-1">
                  <div v-for="i in 3" :key="i"
                    :class="['flex-1 rounded-full transition-all',
                      i === 1 && pwdStrength >= 1 ? (pwdStrength === 1 ? 'bg-[#CC1525]' : pwdStrength === 2 ? 'bg-amber-400' : 'bg-emerald-500') :
                      i === 2 && pwdStrength >= 2 ? (pwdStrength === 2 ? 'bg-amber-400' : 'bg-emerald-500') :
                      i === 3 && pwdStrength >= 3 ? 'bg-emerald-500' : 'bg-gray-100']" />
                </div>
              </div>

              <div class="pt-2 flex justify-end">
                <button @click="handleSave"
                  :disabled="!form.password || form.password !== form.confirmPassword || actionLoading"
                  class="flex items-center gap-2 bg-[#0F2356] hover:bg-[#162d63] disabled:opacity-40 disabled:cursor-not-allowed text-white px-6 py-2 rounded-lg text-xs transition-colors shadow-sm">
                  <Save class="w-3.5 h-3.5" /> Mettre à jour le mot de passe
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-col gap-4">
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center gap-2 mb-4">
              <div class="w-1 h-4 bg-[#CC1525] rounded-full" />
              <span class="text-[10px] text-[#CC1525] uppercase tracking-[0.13em]">Niveau de sécurité</span>
            </div>
            <div class="flex flex-col gap-3">
              <div v-for="item in [
                { label: 'Email ', done: !!profile.email_verified_at },
                { label: 'CIN enregistré', done: !!profile.cin },
                { label: 'Téléphone renseigné', done: !!profile.phone },
                { label: 'Adresse renseignée', done: !!profile.address },
              ]" :key="item.label" class="flex items-center gap-3">
                <div :class="['w-5 h-5 rounded-full flex items-center justify-center shrink-0', item.done ? 'bg-emerald-100 border border-emerald-200' : 'bg-gray-100 border border-gray-200']">
                  <Check v-if="item.done" class="w-3 h-3 text-emerald-600" />
                  <AlertCircle v-else class="w-3 h-3 text-gray-300" />
                </div>
                <span :class="['text-xs', item.done ? 'text-[#0F2356]' : 'text-gray-400']">{{ item.label }}</span>
              </div>
            </div>
          </div>

          <!-- Avertissement sécurité compte sensible -->
          <div class="bg-amber-50 border border-amber-100 rounded-xl p-5">
            <div class="flex items-center gap-2 mb-3">
              <AlertCircle class="w-4 h-4 text-amber-500 shrink-0" />
              <span class="text-xs text-amber-700">Compte privilégié</span>
            </div>
            <p class="text-[10px] text-amber-600 leading-relaxed">
              Ce compte Super Admin a des droits étendus. Utilisez un mot de passe unique et robuste, et ne partagez jamais vos identifiants.
            </p>
          </div>
        </div>
      </div>

    </template>
  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-4px); }
</style>