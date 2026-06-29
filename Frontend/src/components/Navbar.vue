<template>
  <!-- ─── BARRE INFO SUPÉRIEURE ─── -->
  <div class="bg-[#0F2356] text-white/70 text-xs py-2 hidden md:block">
    <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">
      <div class="flex items-center gap-6">
        <span class="flex items-center gap-1.5">
          <Globe class="w-3 h-3" />
          Plateforme Tunisienne Nationale 
        </span>
        
      </div>
      <div class="flex items-center gap-4 text-xs">
        <span class="cursor-pointer hover:text-white transition-colors">العربية</span>
        <span class="text-white/30">|</span>
        <span class="text-white font-medium">Français</span>
        <span class="text-white/30">|</span>
        <span class="cursor-pointer hover:text-white transition-colors">English</span>
      </div>
    </div>
  </div>

  <!-- ─── NAVBAR ─── -->
  <nav
    :class="[
      'sticky top-0 left-0 right-0 z-50 transition-all duration-300 bg-white',
      scrolled
        ? 'shadow-[0_2px_12px_rgba(15,35,86,0.1)] border-b border-gray-100'
        : 'border-b border-gray-200'
    ]"
  >
    <div class="max-w-7xl mx-auto px-6 flex items-center justify-between h-16">

      <!-- Logo -->
      <div
        class="flex  gap-3 cursor-pointer "
        @click="scrollToTop"
      >
        
        <div>
                  <img :src="logo" alt="Logo" class="w-50">

        </div>
      </div>

      <!-- Navigation desktop -->
      <div class="hidden md:flex items-center gap-1">
        <button
          v-for="(link, i) in navLinks"
          :key="link.label"
          @click="scrollTo(link.id)"
          class="text-sm text-[#4A5B78] hover:text-[#0F2356] hover:bg-gray-50 px-4 py-2 rounded transition-colors duration-150"
        >
          {{ link.label }}
        </button>
      </div>

      <!-- Actions desktop -->
      <div class="hidden md:flex items-center gap-3">

        <!-- Non connecté -->
        <button
          v-if="!authStore.isAuthenticated"
          @click="router.push('/login')"
          class="text-sm bg-[#CC1525] hover:bg-[#a81120] text-white px-5 py-2 rounded transition-colors duration-200 shadow-sm"
        >
          Connexion
        </button>

        <!-- Connecté : bulle profil -->
        <div v-else class="relative" ref="profileRef">
          <button
            @click="profileOpen = !profileOpen"
            class="flex items-center gap-2 p-1 rounded-lg hover:bg-gray-100 transition-colors"
          >
            <div class="h-8 w-8 rounded-full bg-[#0F2356] text-white text-xs font-bold flex items-center justify-center uppercase">
              {{ userInitials }}
            </div>
            <ChevronDown class="w-4 h-4 text-[#4A5B78]" />
          </button>

          <Transition name="dropdown">
            <div
              v-if="profileOpen"
              class="absolute right-0 mt-2 w-48 bg-white border border-gray-100 rounded-lg shadow-lg z-50 overflow-hidden py-1"
            >
              <div class="px-3 py-2 border-b border-gray-100">
                <p class="font-medium text-sm text-[#0F2356]">{{ authStore.user?.name }}</p>
                <p class="text-xs text-gray-400">{{ authStore.user?.email }}</p>
              </div>
              <button
                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-[#0F2356] hover:bg-gray-50 transition-colors"
                @click="router.push('/dashboard')"
              >
                <LayoutDashboard class="h-4 w-4" /> Dashboard
              </button>
              <button
                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-[#0F2356] hover:bg-gray-50 transition-colors"
                @click="router.push('/profile')"
              >
                <UserIcon class="h-4 w-4" /> Mon Profil
              </button>
              <button
                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-[#CC1525] hover:bg-red-50 transition-colors"
                @click="authStore.logout()"
              >
                <LogOut class="h-4 w-4" /> Déconnexion
              </button>
            </div>
          </Transition>
        </div>

      </div>

      <!-- Bouton menu mobile -->
      <button class="md:hidden text-[#0F2356]" @click="menuOpen = !menuOpen">
        <X v-if="menuOpen" class="w-6 h-6" />
        <Menu v-else class="w-6 h-6" />
      </button>
    </div>

    <!-- Menu mobile -->
    <div
      v-if="menuOpen"
      class="md:hidden bg-white border-t border-gray-100 px-6 py-4 flex flex-col gap-1 shadow-lg"
    >
      <button
        v-for="link in navLinks"
        :key="link.label"
        @click="scrollTo(link.id)"
        class="text-left text-[#4A5B78] hover:text-[#0F2356] text-sm py-2.5 border-b border-gray-50 flex items-center justify-between"
      >
        {{ link.label }}
        <ChevronRight class="w-4 h-4 opacity-40" />
      </button>
      <button
        v-if="!authStore.isAuthenticated"
        @click="router.push('/login')"
        class="mt-3 bg-[#CC1525] text-white px-5 py-2.5 rounded text-sm"
      >
        Accès Commune
      </button>
      <div v-else class="mt-3 border-t border-gray-100 pt-3 flex flex-col gap-1">
        <div class="flex items-center gap-2 px-1 py-2">
          <div class="h-8 w-8 rounded-full bg-[#0F2356] text-white text-xs font-bold flex items-center justify-center uppercase shrink-0">
            {{ userInitials }}
          </div>
          <div class="min-w-0">
            <p class="text-sm font-medium text-[#0F2356] truncate">{{ authStore.user?.name }}</p>
            <p class="text-xs text-gray-400 truncate">{{ authStore.user?.email }}</p>
          </div>
        </div>
        <button @click="router.push('/dashboard'); menuOpen = false"
          class="text-left text-[#0F2356] hover:text-[#CC1525] text-sm py-2 flex items-center gap-2">
          <LayoutDashboard class="w-4 h-4" /> Dashboard
        </button>
        <button @click="router.push('/profile'); menuOpen = false"
          class="text-left text-[#0F2356] hover:text-[#CC1525] text-sm py-2 flex items-center gap-2">
          <UserIcon class="w-4 h-4" /> Mon Profil
        </button>
        <button @click="authStore.logout()"
          class="text-left text-[#CC1525] text-sm py-2 flex items-center gap-2">
          <LogOut class="w-4 h-4" /> Déconnexion
        </button>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Shield, Menu, X, Globe, Building2, ChevronRight, ChevronDown, LogOut, LayoutDashboard, User as UserIcon } from 'lucide-vue-next'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/AuthStore'
import logo from "../assets/logo_incitya.png";

const router    = useRouter()
const authStore = useAuthStore()
const menuOpen  = ref(false)
const scrolled  = ref(false)

// Bulle profil
const profileOpen = ref(false)
const profileRef  = ref(null)

const userInitials = computed(() => {
  const name = authStore.user?.name || ''
  return name.split(' ').map(w => w[0]).slice(0, 2).join('') || '?'
})

function handleClickOutside(e) {
  if (profileRef.value && !profileRef.value.contains(e.target)) profileOpen.value = false
}

const navLinks = [
  { label: 'Fonctionnalités', id: 'fonctionnalites' },
  { label: 'Plateforme',      id: 'platform' },
  { label: 'Processus',       id: 'workflow' },
  { label: 'Application',     id: 'mobile-app' },
  { label: 'Contact',         id: 'contact' },
]

function handleScroll() {
  scrolled.value = window.scrollY > 40
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function scrollTo(id) {
  const el = document.getElementById(id)
  if (el) el.scrollIntoView({ behavior: 'smooth' })
  menuOpen.value = false
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
  document.addEventListener('mousedown', handleClickOutside)
})
onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  document.removeEventListener('mousedown', handleClickOutside)
})
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.dropdown-enter-from,
.dropdown-leave-to     { opacity: 0; transform: translateY(-6px) scale(0.97); }
</style>