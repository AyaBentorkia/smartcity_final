<template>
  <aside 
    :class="[
      'fixed h-screen bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-700 flex flex-col  transition-all duration-200 ease-in-out',
      isOpen ? 'w-70' : 'w-20'
    ]"
  >
    <!-- Logo -->
    <div class="p-6">
      <div class="flex items-center gap-3 rounded-lg ">
        <img :src="logo" alt="Logo" class="w-50">
        <!-- <div v-if="isOpen" class="overflow-hidden whitespace-nowrap">
          <p class="text-sm text-slate-400 dark:text-slate-500">{{ userRole }}</p>
        </div> -->
      </div>
    </div>

    <!-- Navigation -->
    <div class="flex-1 px-3">
      <div v-if="isOpen" class="px-3 py-2 text-[12px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
        Navigation
      </div>

      <nav class="flex flex-col gap-1">
        <RouterLink
          v-for="item in menuItems"
          :key="item.label"
          :to="item.path"
          :class="[
            'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors duration-200 group',
            route.path === item.path
              ? 'bg-blue-950 dark:bg-blue-950 text-slate-100'
              : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60'
          ]"
        >
          <component
            :is="item.icon"
            :class="[
              'w-5 h-5 shrink-0',
              route.path === item.path
              ? 'bg-blue-950 dark:bg-blue-950 text-[#CC1525]'
                : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-700 dark:group-hover:text-slate-200'
            ]"
          />
          <span v-if="isOpen" class="text-[15px] whitespace-nowrap">{{ item.label }}</span>
        </RouterLink>
      </nav>
    </div>

    <!-- Footer -->
    <div class="p-3 border-t border-slate-100 dark:border-slate-700 space-y-1">

      <!-- Dark mode toggle -->
     

      <!-- Logout -->
      <button
        @click="authStore.logout"
        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors duration-200"
      >
        <LogOut class="w-5 h-5 shrink-0" />
        <span v-if="isOpen" class="text-[15px] whitespace-nowrap">Déconnexion</span>
      </button>
    </div>

    <!-- Resize handle -->
    <div
      @click="isOpen = !isOpen"
      class="absolute right-0 top-0 bottom-0 w-1 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
    ></div>
  </aside>
</template>

<script setup>
import { ref, computed } from 'vue'
import logo from "../../assets/logo_incitya.png";
import {
  LayoutGrid,
  AlertTriangle,
  ClipboardList,
  Users,
  BarChart3,
  ShieldCheck,
  LogOut,
  Sun,
  Moon,
  LocateFixed,
  Wrench,
} from 'lucide-vue-next'
import { UserRole } from "../../constants/UserRole";
import { useAuthStore } from '../../stores/AuthStore';
import { storeToRefs } from 'pinia'
import { useRoute } from 'vue-router'

const route = useRoute();
const authStore = useAuthStore();
const { userRole } = storeToRefs(authStore);
const isOpen = ref(true)

// Dark mode


// Menu items
const MunicipalAdmin_menuItems = [
  { label: "Dashboard",   icon: LayoutGrid,   path: "/dashboard" },
  { label: "Incidents",   icon: AlertTriangle, path: "/incidents" },
  { label: "Agents",      icon: Users,         path: "/agents" },
  { label: "Affectations", icon: ClipboardList, path: "/assignments" },
  { label: "Municipalité",icon: ShieldCheck,   path: "/municipality" },
  { label: "Zones",icon: LocateFixed,   path: "/zones" },
]
const SuperAdmin_menuItems = [
  { label: "Dashboard",     icon: LayoutGrid,   path: "/dashboard" },
  { label: "Incidents",     icon: AlertTriangle, path: "/incidents" },
  { label: "Users",         icon: Users,         path: "/users" },
  { label: "Municipalities",icon: ShieldCheck,   path: "/municipalities" },
  { label: "Categories",icon: Wrench,   path: "/services" },
]
const Agent_menuItems = [
  { label: "Dashboard",   icon: LayoutGrid,   path: "/dashboard" },
  { label: "Affectations", icon: ClipboardList, path: "/assignments" },
]

const menuItems = computed(() => {
  switch (authStore.userRole) {
    case UserRole.ADMIN_MUNICIPAL: return MunicipalAdmin_menuItems
    case UserRole.AGENT:           return Agent_menuItems
    default:                       return SuperAdmin_menuItems
  }
})
</script>
