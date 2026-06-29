<template>
  <div class="min-h-screen flex bg-[#F7F8FB]" style="font-family: 'Inter', sans-serif">
    
    <!-- ── Panneau gauche (inchangé) ── -->
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
            <div>
                  <img :src="logo" alt="Logo" class="w-50">

        </div>
          </div>
          <RouterLink to="/" class="flex items-center gap-1.5 text-white/40 hover:text-white/70 text-xs transition-colors">
            <ArrowLeft :size="14" /> Retour
          </RouterLink>
        </div>

        <div class="flex-1 flex flex-col justify-center">
          <div class="flex items-center gap-2 mb-6">
            <div class="w-8 h-0.5 bg-[#CC1525]" />
            <span class="text-xs text-[#CC1525] uppercase tracking-widest">Espace Sécurisé</span>
          </div>
          <h1 class="text-4xl font-bold text-white leading-tight mb-4">
            Bienvenue sur<br />
            <span class="text-[#E8354A]">votre espace</span><br />
            {{ isSignUp ? 'd\'inscription' : 'personnel' }}
          </h1>
          <p class="text-white/45 text-sm leading-relaxed max-w-xs">
            Rejoignez la révolution Smart City. Signalez des incidents, suivez les résolutions et améliorez votre commune en temps réel.
          </p>

          <div class="w-24 h-px bg-white/15 my-8" />

          <div class="flex flex-col gap-4">
            <div v-for="item in trustItems" :key="item.text" class="flex items-center gap-3">
              <div class="w-7 h-7 rounded bg-white/[0.07] border border-white/10 flex items-center justify-center shrink-0">
                <component :is="item.icon" class="w-3.5 h-3.5 text-[#E8354A]" />
              </div>
              <span class="text-white/40 text-xs">{{ item.text }}</span>
            </div>
          </div>
        </div>

        <div class="border-t border-white/10 pt-8 grid grid-cols-3 gap-4">
          <div v-for="stat in stats" :key="stat.label">
            <p class="text-white text-lg font-semibold mb-0.5">{{ stat.value }}</p>
            <p class="text-white/30 text-[10px] uppercase tracking-tighter">{{ stat.label }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Panneau droit ── -->
    <div class="flex-1 bg-[#F7F8FB] flex flex-col">
      
      <div class="bg-[#0F2356] text-white/50 text-[10px] py-2 px-6 flex items-center justify-between uppercase tracking-wider">
        <span class="flex items-center gap-1.5">
          <Building2 :size="12" /> Plateforme Tunisienne Nationale
        </span>
        <div class="flex items-center gap-3">
          <span>العربية</span>
          <span class="text-white/20">|</span>
          <span class="text-white">Français</span>
        </div>
      </div>

      <div class="flex-1 flex items-center justify-center p-6 md:p-10">
        <div class="w-full max-w-[440px]">
          
          <div class="mb-8">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-5 h-0.5 bg-[#CC1525]" />
              <span class="text-xs text-[#CC1525] uppercase tracking-widest">
                {{ isSignUp ? 'Inscription' : 'Connexion' }}
              </span>
            </div>
            <h2 class="text-2xl font-bold text-[#0F2356] mb-1.5">
              {{ isSignUp ? "Créer un compte citoyen" : "Accédez à votre compte" }}
            </h2>
            <p class="text-gray-400 text-sm">
              {{ isSignUp ? "Remplissez les informations pour commencer." : "Entrez vos identifiants pour continuer." }}
            </p>
          </div>

          <!-- ── Formulaire email/password ── -->
          <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
            
            <div v-if="isSignUp" class="space-y-1.5">
              <label class="block text-xs font-semibold text-[#0F2356] uppercase tracking-wider">Nom Complet</label>
              <div class="relative">
                <User class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" :size="16" />
                <input
                  v-model="name"
                  type="text"
                  placeholder="Aya Ben Torkia"
                  required
                  class="w-full bg-white border border-gray-200 rounded-xl pl-10 pr-4 py-3 text-sm text-[#0F2356] outline-none focus:border-[#0F2356] transition-all shadow-sm"
                />
              </div>
            </div>

            <div class="space-y-1.5">
              <label class="block text-xs font-semibold text-[#0F2356] uppercase tracking-wider">Adresse Email</label>
              <div class="relative">
                <Mail class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" :size="16" />
                <input
                  v-model="email"
                  type="email"
                  placeholder="admin@email.tn"
                  required
                  class="w-full bg-white border border-gray-200 rounded-xl pl-10 pr-4 py-3 text-sm text-[#0F2356] outline-none focus:border-[#0F2356] transition-all shadow-sm"
                />
              </div>
            </div>

            <div class="space-y-1.5">
              <div class="flex items-center justify-between">
                <label class="block text-xs font-semibold text-[#0F2356] uppercase tracking-wider">Mot de passe</label>
<RouterLink to="/forgot-password" class="text-xs text-[#CC1525] hover:underline">
  Mot de passe oublié ?
</RouterLink>              </div>
              <div class="relative">
                <Lock class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" :size="16" />
                <input
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="••••••••"
                  required
                  class="w-full bg-white border border-gray-200 rounded-xl pl-10 pr-12 py-3 text-sm text-[#0F2356] outline-none focus:border-[#0F2356] transition-all shadow-sm"
                />
                <button type="button" @click="showPassword = !showPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#0F2356]">
                  <component :is="showPassword ? EyeOff : Eye" :size="16" />
                </button>
              </div>
            </div>

            <div v-if="authStore.error" class="bg-red-50 border border-red-100 text-[#CC1525] text-xs p-3 rounded-lg flex items-center gap-2">
              <AlertCircle :size="14" /> {{ authStore.error }}
            </div>

            <button
              type="submit"
              :disabled="authStore.loading || !isFormValid"
              class="w-full mt-2 bg-[#CC1525] hover:bg-[#a81120] disabled:bg-gray-300 disabled:cursor-not-allowed disabled:shadow-none text-white py-3.5 rounded-xl text-sm font-bold transition-all shadow-lg shadow-red-900/10 flex items-center justify-center gap-2"
            >
              <div v-if="authStore.loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
              {{ isSignUp ? "CRÉER MON COMPTE" : "SE CONNECTER" }}
              <ArrowRight v-if="!authStore.loading" :size="16" />
            </button>
          </form>

          <!-- ── Séparateur ── -->
          <div class="flex items-center gap-3 my-6">
            <div class="flex-1 h-px bg-gray-200" />
            <span class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Ou</span>
            <div class="flex-1 h-px bg-gray-200" />
          </div>

          <!-- ── Bouton Google ── -->
          <button
            @click="handleGoogleLogin"
            :disabled="googleLoading"
            class="w-full flex items-center justify-center gap-3 bg-white border border-gray-200 hover:border-gray-300 rounded-xl py-3 text-sm text-gray-600 transition-all shadow-sm disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <!-- Spinner pendant la redirection -->
            <div v-if="googleLoading" class="w-4 h-4 border-2 border-gray-300 border-t-gray-600 rounded-full animate-spin" />
            <img
              v-else
              src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png"
              class="w-4 h-4"
              alt="Google"
            />
            {{ googleLoading ? 'Redirection vers Google…' : 'Continuer avec Google' }}
          </button>

          <!-- Message d'erreur Google -->
          <p v-if="googleError" class="mt-3 text-center text-xs text-[#CC1525]">
            {{ googleError }}
          </p>

        </div>
      </div>

      <div class="px-8 pb-6 pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-gray-400 text-[10px] uppercase tracking-widest font-medium">© 2026 Incitya — Gouvernance Urbaine</p>
        <div class="flex items-center gap-4">
          <a v-for="link in ['CGU', 'Confidentialité', 'Support']" :key="link" href="#" class="text-gray-400 text-[10px] font-bold uppercase hover:text-[#CC1525] transition-colors">{{ link }}</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed  } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { Eye, EyeOff, Mail, Lock, ArrowLeft, ArrowRight, User, AlertCircle, Shield, Building2 } from 'lucide-vue-next'
import { useAuthStore }         from '../stores/AuthStore'
import { getGoogleRedirectUrl } from '../api/AuthApi.js'
import logo from "../assets/logo_incitya.png";

const authStore = useAuthStore()
const router    = useRouter()

// ── Redirection si déjà connecté ───────────────────────────────────────────
if (authStore.isAuthenticated) {
  router.replace('/dashboard')
}

const showPassword  = ref(false)
const isSignUp      = ref(false)
const email         = ref('')
const password      = ref('')
const name          = ref('')
const googleLoading = ref(false)
const googleError   = ref(null)

const stats = [
  { value: '10K+', label: 'Reports Resolved' },
  { value: '50+',  label: 'Cities Connected' },
  { value: '98%',  label: 'Satisfaction Rate' },
]

// ── Connexion email/password ────────────────────────────────────────────────
async function handleSubmit() {
  await authStore.login({ email: email.value, password: password.value })
}

// ── Connexion Google ────────────────────────────────────────────────────────
async function handleGoogleLogin() {
  googleLoading.value = true
  googleError.value   = null

  try {
    // Demander l'URL Google au backend Laravel
    const response = await getGoogleRedirectUrl()
    // Rediriger le navigateur vers Google
    window.location.href = response.data.url
  } catch (err) {
    googleLoading.value = false
    googleError.value   = 'Impossible de se connecter avec Google. Réessayez.'
    console.error('Google OAuth error:', err)
  }
}

const isFormValid = computed(() => {
  if (isSignUp.value) {
    return name.value.trim() && email.value.trim() && password.value.trim()
  }
  return email.value.trim() && password.value.trim()
})

</script>