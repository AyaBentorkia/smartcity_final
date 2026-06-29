<template>
  <div class="min-h-screen flex bg-[#F7F8FB]" style="font-family: 'Inter', sans-serif">

    <!-- ── Panneau gauche ── -->
    <div class="hidden lg:flex lg:w-[46%] relative flex-col overflow-hidden">
      <img
        src="https://images.unsplash.com/photo-1665083766545-a5b0b11fc4f3?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxUdW5pcyUyMGNpdHklMjBhZXJpYWwlMjBuaWdodCUyMGxpZ2h0cyUyMHVyYmFuJTIwVHVuaXNpYXxlbnwxfHx8fDE3NzY5NTUwODl8MA&ixlib=rb-4.1.0&q=80&w=1080"
        alt="Tunis Smart City"
        class="absolute inset-0 w-full h-full object-cover"
      />
      <div class="absolute inset-0 bg-gradient-to-br from-[#0F2356]/95 via-[#0F2356]/85 to-[#0F2356]/70" />
      <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#CC1525]" />
      <div
        class="absolute inset-0 opacity-[0.04]"
        style="background-image: repeating-linear-gradient(0deg, #fff 0px, #fff 1px, transparent 1px, transparent 48px), repeating-linear-gradient(90deg, #fff 0px, #fff 1px, transparent 1px, transparent 48px)"
      />
      <div class="relative z-10 flex flex-col h-full p-12">
        <div class="flex items-center justify-between">
          <img :src="logo" alt="Logo" class="w-50" />
          <RouterLink to="/login" class="flex items-center gap-1.5 text-white/40 hover:text-white/70 text-xs transition-colors">
            <ArrowLeft :size="14" /> Retour
          </RouterLink>
        </div>
        <div class="flex-1 flex flex-col justify-center">
          <div class="flex items-center gap-2 mb-6">
            <div class="w-8 h-0.5 bg-[#CC1525]" />
            <span class="text-xs text-[#CC1525] uppercase tracking-widest">Nouveau mot de passe</span>
          </div>
          <h1 class="text-4xl font-bold text-white leading-tight mb-4">
            Choisissez un<br />
            <span class="text-[#E8354A]">nouveau</span><br />
            mot de passe.
          </h1>
          <p class="text-white/45 text-sm leading-relaxed max-w-xs">
            Votre nouveau mot de passe doit contenir au moins 8 caractères.
          </p>
        </div>
      </div>
    </div>

    <!-- ── Panneau droit ── -->
    <div class="flex-1 bg-[#F7F8FB] flex flex-col">
      <div class="bg-[#0F2356] text-white/50 text-[10px] py-2 px-6 flex items-center justify-between uppercase tracking-wider">
        <span class="flex items-center gap-1.5">
          <Building2 :size="12" /> Ministère des Affaires Locales — Tunisie
        </span>
      </div>

      <div class="flex-1 flex items-center justify-center p-6 md:p-10">
        <div class="w-full max-w-[440px]">

          <!-- Token invalide / manquant -->
          <template v-if="!token">
            <div class="text-center">
              <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-6">
                <AlertCircle class="text-[#CC1525]" :size="32" />
              </div>
              <h2 class="text-2xl font-bold text-[#0F2356] mb-2">Lien invalide</h2>
              <p class="text-gray-400 text-sm mb-6">Ce lien est invalide ou a expiré. Veuillez faire une nouvelle demande.</p>
              <RouterLink to="/forgot-password" class="inline-flex items-center gap-2 text-sm text-[#CC1525] hover:underline font-semibold">
                Redemander un lien
              </RouterLink>
            </div>
          </template>

          <!-- Formulaire reset -->
          <template v-else-if="!success">
            <div class="mb-8">
              <div class="flex items-center gap-2 mb-2">
                <div class="w-5 h-0.5 bg-[#CC1525]" />
                <span class="text-xs text-[#CC1525] uppercase tracking-widest">Nouveau mot de passe</span>
              </div>
              <h2 class="text-2xl font-bold text-[#0F2356] mb-1.5">Choisissez votre mot de passe</h2>
              <p class="text-gray-400 text-sm">Minimum 8 caractères.</p>
            </div>

            <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">

              <!-- Mot de passe -->
              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-[#0F2356] uppercase tracking-wider">Nouveau mot de passe</label>
                <div class="relative">
                  <Lock class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" :size="16" />
                  <input
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    placeholder="••••••••"
                    required
                    class="w-full bg-white border border-gray-200 rounded-xl pl-10 pr-12 py-3 text-sm text-[#0F2356] outline-none focus:border-[#0F2356] transition-all shadow-sm"
                    :class="{ 'border-red-400': errors.password }"
                    @blur="validatePassword"
                  />
                  <button type="button" @click="showPassword = !showPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#0F2356]">
                    <component :is="showPassword ? EyeOff : Eye" :size="16" />
                  </button>
                </div>
                <p v-if="errors.password" class="text-xs text-[#CC1525] flex items-center gap-1">
                  <AlertCircle :size="12" /> {{ errors.password }}
                </p>
              </div>

              <!-- Confirmation -->
              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-[#0F2356] uppercase tracking-wider">Confirmer le mot de passe</label>
                <div class="relative">
                  <Lock class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" :size="16" />
                  <input
                    v-model="form.password_confirmation"
                    :type="showConfirm ? 'text' : 'password'"
                    placeholder="••••••••"
                    required
                    class="w-full bg-white border border-gray-200 rounded-xl pl-10 pr-12 py-3 text-sm text-[#0F2356] outline-none focus:border-[#0F2356] transition-all shadow-sm"
                    :class="{ 'border-red-400': errors.password_confirmation }"
                    @blur="validateConfirm"
                  />
                  <button type="button" @click="showConfirm = !showConfirm" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#0F2356]">
                    <component :is="showConfirm ? EyeOff : Eye" :size="16" />
                  </button>
                </div>
                <p v-if="errors.password_confirmation" class="text-xs text-[#CC1525] flex items-center gap-1">
                  <AlertCircle :size="12" /> {{ errors.password_confirmation }}
                </p>
              </div>

              <!-- Erreur globale -->
              <p v-if="globalError" class="text-xs text-[#CC1525] bg-red-50 border border-red-100 px-3 py-2 rounded-lg flex items-center gap-2">
                <AlertCircle :size="14" /> {{ globalError }}
              </p>

              <button
                type="submit"
                :disabled="loading"
                class="w-full mt-2 bg-[#CC1525] hover:bg-[#a81120] disabled:bg-gray-300 text-white py-3.5 rounded-xl text-sm font-bold transition-all shadow-lg shadow-red-900/10 flex items-center justify-center gap-2"
              >
                <div v-if="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                <template v-else>
                  RÉINITIALISER
                  <ArrowRight :size="16" />
                </template>
              </button>
            </form>
          </template>

          <!-- Succès -->
          <template v-else>
            <div class="text-center">
              <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-6">
                <CheckCircle class="text-green-500" :size="32" />
              </div>
              <h2 class="text-2xl font-bold text-[#0F2356] mb-2">Mot de passe mis à jour !</h2>
              <p class="text-gray-400 text-sm mb-8">
                Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.
              </p>
              <RouterLink
                to="/login"
                class="inline-flex items-center gap-2 bg-[#CC1525] text-white px-6 py-3 rounded-xl text-sm font-bold hover:bg-[#a81120] transition-all"
              >
                Se connecter <ArrowRight :size="14" />
              </RouterLink>
            </div>
          </template>

        </div>
      </div>

      <div class="px-8 pb-6 pt-4 border-t border-gray-100">
        <p class="text-gray-400 text-[10px] uppercase tracking-widest font-medium">© 2026 Incitya — Gouvernance Urbaine</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { Lock, Eye, EyeOff, ArrowLeft, ArrowRight, AlertCircle, Building2, CheckCircle } from 'lucide-vue-next'
import { useAuthStore } from '../stores/AuthStore'
import logo from '../assets/logo_incitya.png'

const route     = useRoute()
const router    = useRouter()
const authStore = useAuthStore()

const token   = ref(route.query.token || '')
const email   = ref(route.query.email || '')
const loading = ref(false)
const success = ref(false)
const globalError = ref(null)
const showPassword = ref(false)
const showConfirm  = ref(false)

const form = reactive({
  password:              '',
  password_confirmation: '',
})

const errors = reactive({
  password:              '',
  password_confirmation: '',
})

const validatePassword = () => {
  errors.password = form.password.length < 8 ? 'Minimum 8 caractères.' : ''
}

const validateConfirm = () => {
  errors.password_confirmation = form.password_confirmation !== form.password
    ? 'Les mots de passe ne correspondent pas.'
    : ''
}

const handleSubmit = async () => {
  validatePassword()
  validateConfirm()
  if (errors.password || errors.password_confirmation) return

  loading.value     = true
  globalError.value = null

  try {
    await authStore.resetPassword({
      token:                 token.value,
      email:                 email.value,
      password:              form.password,
      password_confirmation: form.password_confirmation,
    })
    success.value = true
  } catch (err) {
    const data = err?.response?.data
    if (data?.errors?.password) {
      errors.password = Array.isArray(data.errors.password)
        ? data.errors.password[0]
        : data.errors.password
    } else {
      globalError.value = data?.message || 'Une erreur est survenue. Le lien est peut-être expiré.'
    }
  } finally {
    loading.value = false
  }
}
</script>