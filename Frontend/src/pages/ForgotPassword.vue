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
          <div class="flex items-center gap-3 cursor-pointer" @click="$router.push('/')">
            <img :src="logo" alt="Logo" class="w-50" />
          </div>
          <RouterLink to="/login" class="flex items-center gap-1.5 text-white/40 hover:text-white/70 text-xs transition-colors">
            <ArrowLeft :size="14" /> Retour
          </RouterLink>
        </div>
        <div class="flex-1 flex flex-col justify-center">
          <div class="flex items-center gap-2 mb-6">
            <div class="w-8 h-0.5 bg-[#CC1525]" />
            <span class="text-xs text-[#CC1525] uppercase tracking-widest">Réinitialisation</span>
          </div>
          <h1 class="text-4xl font-bold text-white leading-tight mb-4">
            Mot de passe<br />
            <span class="text-[#E8354A]">oublié ?</span><br />
            Pas de panique.
          </h1>
          <p class="text-white/45 text-sm leading-relaxed max-w-xs">
            Entrez votre adresse email et nous vous enverrons un lien sécurisé pour réinitialiser votre mot de passe.
          </p>
        </div>
        <div class="border-t border-white/10 pt-8">
          <p class="text-white/25 text-xs">Lien valable pendant 60 minutes après réception.</p>
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

          <!-- Étape 1 : formulaire email -->
          <template v-if="!emailSent">
            <div class="mb-8">
              <div class="flex items-center gap-2 mb-2">
                <div class="w-5 h-0.5 bg-[#CC1525]" />
                <span class="text-xs text-[#CC1525] uppercase tracking-widest">Mot de passe oublié</span>
              </div>
              <h2 class="text-2xl font-bold text-[#0F2356] mb-1.5">Réinitialiser votre mot de passe</h2>
              <p class="text-gray-400 text-sm">Entrez l'email associé à votre compte.</p>
            </div>

            <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-[#0F2356] uppercase tracking-wider">Adresse Email</label>
                <div class="relative">
                  <Mail class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" :size="16" />
                  <input
                    v-model="email"
                    type="email"
                    placeholder="citoyen@email.tn"
                    required
                    class="w-full bg-white border border-gray-200 rounded-xl pl-10 pr-4 py-3 text-sm text-[#0F2356] outline-none focus:border-[#0F2356] transition-all shadow-sm"
                    :class="{ 'border-red-400': error }"
                  />
                </div>
                <p v-if="error" class="text-xs text-[#CC1525] flex items-center gap-1 mt-1">
                  <AlertCircle :size="12" /> {{ error }}
                </p>
              </div>

              <button
                type="submit"
                :disabled="loading"
                class="w-full mt-2 bg-[#CC1525] hover:bg-[#a81120] disabled:bg-gray-300 text-white py-3.5 rounded-xl text-sm font-bold transition-all shadow-lg shadow-red-900/10 flex items-center justify-center gap-2"
              >
                <div v-if="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                <template v-else>
                  ENVOYER LE LIEN
                  <ArrowRight :size="16" />
                </template>
              </button>
            </form>

            <div class="mt-6 text-center">
              <RouterLink to="/login" class="text-xs text-gray-400 hover:text-[#CC1525] transition-colors flex items-center justify-center gap-1">
                <ArrowLeft :size="12" /> Retour à la connexion
              </RouterLink>
            </div>
          </template>

          <!-- Étape 2 : confirmation envoi -->
          <template v-else>
            <div class="text-center">
              <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-6">
                <CheckCircle class="text-green-500" :size="32" />
              </div>
              <h2 class="text-2xl font-bold text-[#0F2356] mb-2">Email envoyé !</h2>
              <p class="text-gray-400 text-sm mb-1">
                Un lien de réinitialisation a été envoyé à
              </p>
              <p class="text-[#0F2356] font-semibold text-sm mb-6">{{ email }}</p>
              <p class="text-gray-400 text-xs mb-8">
                Vérifiez votre boîte de réception (et vos spams). Le lien est valable <strong>60 minutes</strong>.
              </p>
              <RouterLink
                to="/login"
                class="inline-flex items-center gap-2 text-sm text-[#CC1525] hover:underline font-semibold"
              >
                <ArrowLeft :size="14" /> Retour à la connexion
              </RouterLink>
            </div>
          </template>

        </div>
      </div>

      <div class="px-8 pb-6 pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-gray-400 text-[10px] uppercase tracking-widest font-medium">© 2026 Incitya — Gouvernance Urbaine</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import { Mail, ArrowLeft, ArrowRight, AlertCircle, Building2, CheckCircle } from 'lucide-vue-next'
import { useAuthStore } from '../stores/AuthStore'
import logo from '../assets/logo_incitya.png'

const authStore  = useAuthStore()
const email      = ref('')
const loading    = ref(false)
const error      = ref(null)
const emailSent  = ref(false)

const handleSubmit = async () => {
  error.value   = null
  loading.value = true
  try {
    await authStore.forgotPassword(email.value)
    emailSent.value = true
  } catch (err) {
    error.value = err?.response?.data?.message || 'Une erreur est survenue. Veuillez réessayer.'
  } finally {
    loading.value = false
  }
}
</script>