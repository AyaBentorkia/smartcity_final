<template>
<div class="shrink-0 flex flex-col min-w-0">

    <!-- ── HEADER ── -->
    <header class="h-16 flex items-center justify-between border-b border-border bg-background px-6 sticky top-0 z-10">

      <!-- Left: Title + subtitle -->
      <div>
        <h1 class="text-xl text-[#CC1525] font-bold  leading-tight">{{ pageTitle }}</h1>
        <p class="text-xs text-muted-foreground">
          Commune de {{ municipalityName }} &bull; {{ todayLabel }}
        </p>
      </div>

      <!-- Right: search + notif + settings -->
      <div class="flex items-center gap-2">

        <!-- Search -->
        <div class="relative hidden md:flex items-center">
          <Search class="absolute left-3 w-4 h-4 text-muted-foreground pointer-events-none" />
          <input
            type="text"
            placeholder="Rechercher..."
            class="pl-9 pr-4 py-1.5 text-sm rounded-lg border border-border bg-muted/40 text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 w-52"
          />
        </div>

        <!-- Notifications -->
        <div class="relative" ref="notifRef">
          <button
            @click="notifOpen = !notifOpen"
            class="relative p-2 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
          >
            <Bell class="w-5 h-5" v-if="userRole !== UserRole.SUPER_ADMIN" />
            <span
              v-if="unreadCount > 0"
              class="absolute top-1 right-1 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center"
            >
              {{ unreadCount }}
            </span>
          </button>

          <Transition name="dropdown">
            <div
              v-if="notifOpen"
              class="absolute right-0 mt-2 w-80 bg-card border border-border rounded-xl shadow-xl z-50 overflow-hidden"
            >
              <div class="p-3 border-b border-border flex items-center justify-between">
                <h4 class="font-semibold text-sm text-foreground">
                  Notifications
                  <span v-if="unreadCount > 0" class="ml-1 text-xs text-red-500">({{ unreadCount }})</span>
                </h4>
                <span
                  @click="markAllRead"
                  class="text-xs text-primary cursor-pointer hover:underline"
                >
                  Tout lire
                </span>
              </div>

              <div class="max-h-72 overflow-y-auto divide-y divide-border">

                <!-- Chargement -->
                <div v-if="loading" class="p-4 text-center text-sm text-muted-foreground">
                  Chargement...
                </div>

                <!-- Vide -->
                <div v-else-if="notifications.length === 0" class="p-6 text-center text-sm text-muted-foreground">
                  Aucune notification
                </div>

                <!-- Liste -->
                <div
                  v-else
                  v-for="n in notifications"
                  :key="n.id"
                  @click="markAsRead(n)"
                  :class="['p-3 hover:bg-muted/50 transition-colors cursor-pointer flex items-start gap-2', !n.read_at ? 'bg-primary/5' : '']"
                >
                  <span v-if="!n.read_at" class="w-2 h-2 rounded-full bg-primary mt-1.5 shrink-0" />
                  <div :class="!n.read_at ? '' : 'ml-4'">
                    <p class="text-sm text-foreground font-medium">{{ n.title }}</p>
                    <p class="text-xs text-muted-foreground">{{ n.body }}</p>
                    <p class="text-xs text-muted-foreground mt-0.5">{{ timeAgo(n.created_at) }}</p>
                  </div>
                </div>

              </div>
            </div>
          </Transition>
        </div>

        <!-- Settings -->
        <button class="p-2 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition-colors">
          <Settings class="w-5 h-5" />
        </button>

        <div class="relative ml-1" ref="profileRef">
          <button
            @click="profileOpen = !profileOpen"
            class="flex items-center gap-2 p-1 rounded-lg hover:bg-muted transition-colors"
          >
            <div class="h-8 w-8 rounded-full bg-primary text-primary-foreground text-xs font-bold flex items-center justify-center">
              AM
            </div>
          </button>

          <Transition name="dropdown">
            <div
              v-if="profileOpen"
              class="absolute right-0 mt-2 w-48 bg-card border border-border rounded-lg shadow-lg z-50 overflow-hidden py-1"
            >
              <div class="px-3 py-2 border-b border-border">
                <p class="font-medium text-sm text-foreground">{{ userRole }}</p>
                <p class="text-xs text-muted-foreground">{{ user?.email }}</p>
              </div>

              <button class="w-full flex items-center gap-2 px-3 py-2 text-sm text-foreground hover:bg-muted transition-colors"
                @click="router.push('/profile')">
                <User class="h-4 w-4" /> My Profile
              </button>
              <button class="w-full flex items-center gap-2 px-3 py-2 text-sm text-foreground hover:bg-muted transition-colors"
                @click="router.push('/settings')">
                <Settings class="h-4 w-4" /> Settings
              </button>
            </div>
          </Transition>
        </div>

      </div>
    </header>

    <!-- ── KPI BAR ── -->
    <div class="flex items-center justify-between border-b border-border bg-background px-6 py-3">
      <div class="flex items-center gap-2"></div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { Bell, Settings, Search, User } from 'lucide-vue-next'
import { useAuthStore } from '../stores/AuthStore'
import { useNotificationStore } from '../stores/NotificationStore'
import { UserRole } from '../constants/UserRole'

const route     = useRoute()
const router    = useRouter()
const authStore = useAuthStore()
const { user, userRole } = storeToRefs(authStore)

// ── Notifications via store ────────────────────────────────────────────────
const notifStore = useNotificationStore()
const { notifications, loading, unreadCount } = storeToRefs(notifStore)

const municipalityName = computed(() => user.value?.city ?? '')

const pageTitle = computed(() => {
  const path = route.path.toLowerCase()
  if (path.includes('/dashboard'))      return 'Dashboard Overview'
  if (path.includes('/incidents'))      return 'Liste des Incidents'
  if (path.includes('/agents'))         return 'Gestion des Agents'
  if (path.includes('/users'))          return 'Gestion des Utilisateurs'
  if (path.includes('/assignments'))    return 'Affectations'
  if (path.includes('/profile'))        return 'Mon Profil'
  if (path.includes('/municipalities')) return 'Liste des Municipalités'
  if (path.includes('/municipality'))   return 'Municipalité'
  if (path.includes('/zones'))          return 'Gestion des Zones'
  if (path.includes('/services'))       return 'Catégories de Services'
  return 'Dashboard'
})

const todayLabel = computed(() =>
  new Date().toLocaleDateString('fr-FR', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
  })
)

// ── Dropdowns ──────────────────────────────────────────────────────────────
const notifOpen   = ref(false)
const notifRef    = ref(null)
const profileOpen = ref(false)
const profileRef  = ref(null)

function handleClickOutside(e) {
  if (notifRef.value   && !notifRef.value.contains(e.target))   notifOpen.value   = false
  if (profileRef.value && !profileRef.value.contains(e.target)) profileOpen.value = false
}

function timeAgo(dateStr) {
  if (!dateStr) return 'Date inconnue'
  const date = new Date(dateStr)
  if (isNaN(date.getTime())) return 'Date invalide'
  const diff = Math.floor((Date.now() - date) / 1000)
  if (diff < 60)    return "À l'instant"
  if (diff < 3600)  return `Il y a ${Math.floor(diff / 60)} min`
  if (diff < 86400) return `Il y a ${Math.floor(diff / 3600)}h`
  return `Il y a ${Math.floor(diff / 86400)}j`
}

// ── Lifecycle — uniquement fetchIfNeeded, Echo géré dans le store ──────────
onMounted(async () => {
  document.addEventListener('mousedown', handleClickOutside)
  await notifStore.fetchIfNeeded()
  if (user.value?.id) {
    notifStore.initEcho(user.value.id)
  }
  // ← PAS de window.Echo ici — initEcho() est appelé une seule fois au login
})

onUnmounted(() => {
  document.removeEventListener('mousedown', handleClickOutside)
  // ← PAS de Echo.leave() ici — leaveEcho() est appelé au logout
})

const markAsRead  = (n) => notifStore.markAsRead(n)
const markAllRead = ()  => notifStore.markAllRead()
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.dropdown-enter-from,
.dropdown-leave-to     { opacity: 0; transform: translateY(-6px) scale(0.97); }
</style>