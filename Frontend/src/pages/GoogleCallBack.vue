<template>
  <div class="min-h-screen flex items-center justify-center bg-[#F7F8FB]" style="font-family: 'Inter', sans-serif">
    <div class="text-center">
      <!-- Chargement -->
      <div v-if="status === 'loading'" class="flex flex-col items-center gap-4">
        <div class="w-12 h-12 border-4 border-[#CC1525]/20 border-t-[#CC1525] rounded-full animate-spin" />
        <p class="text-[#0F2356] font-semibold">Connexion avec Google en cours…</p>
      </div>

      <!-- Erreur -->
      <div v-else-if="status === 'error'" class="flex flex-col items-center gap-4">
        <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center">
          <span class="text-3xl">❌</span>
        </div>
        <p class="text-[#CC1525] font-semibold">Échec de la connexion Google</p>
        <p class="text-gray-400 text-sm">{{ errorMessage }}</p>
        <button
          @click="$router.push('/login')"
          class="mt-2 px-6 py-2.5 bg-[#CC1525] text-white text-sm font-bold rounded-xl hover:bg-[#a81120] transition-all"
        >
          Retour à la connexion
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter }      from 'vue-router'
import { useAuthStore }   from '../stores/AuthStore'
import { getMe }          from '../api/AuthApi.js'

const router    = useRouter()
const authStore = useAuthStore()
const status    = ref('loading')
const errorMessage = ref('')

onMounted(async () => {
  const params = new URLSearchParams(window.location.search)
  const token  = params.get('token')
  const error  = params.get('error')

  if (error || !token) {
    status.value       = 'error'
    errorMessage.value = 'Google a refusé l\'accès ou la session a expiré.'
    return
  }

  try {
    // 1. Stocker le token JWT
    localStorage.setItem('jwt_token', token)

    // 2. Récupérer le profil utilisateur
    const response = await getMe()
    authStore.setUser(response.data.data, token)

    // 3. Rediriger selon le rôle
    authStore.redirectByRole()
  } catch (err) {
    status.value       = 'error'
    errorMessage.value = 'Impossible de récupérer votre profil.'
    localStorage.removeItem('jwt_token')
  }
})
</script>