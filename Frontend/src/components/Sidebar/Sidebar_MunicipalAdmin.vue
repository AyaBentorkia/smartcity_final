<template>
  <aside 
    :class="[
      'h-screen bg-white border-r border-slate-200 flex flex-col relative transition-all duration-200 ease-in-out',
      isOpen ? 'w-70' : 'w-20'
    ]"
  >
    <div class="p-6">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-[#0081c9] text-white rounded-lg flex items-center justify-center font-bold shrink-0">
          SC
        </div>
        <div v-if="isOpen" class="overflow-hidden whitespace-nowrap">
          <h1 class="text-base font-semibold text-slate-800 leading-tight">SmartCity</h1>
          <p class="text-sm text-slate-400">{{UserRole.ADMIN_MUNICIPAL}}</p>
        </div>
      </div>
    </div>

    <div class="flex-1 px-3">
      <div v-if="isOpen" class="px-3 py-2 text-[12px] font-bold text-slate-400 uppercase tracking-wider">
        Navigation
      </div>
      
      <nav class="flex flex-col gap-1">
        <a 
          v-for="item in menuItems" 
          :key="item.label" 
          href="#" 
          :class="[
            'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors duration-200 group',
            item.active ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50'
          ]"
        >
          <component 
            :is="item.icon" 
            :class="['w-5 h-5 shrink-0', item.active ? 'text-slate-900' : 'text-slate-500 group-hover:text-slate-700']" 
          />
          <span v-if="isOpen" class="text-[15px] whitespace-nowrap">{{ item.label }}</span>
        </a>
      </nav>
    </div>

    <div class="p-3 border-t border-slate-100">
      <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-500 hover:bg-slate-50 transition-colors duration-200">
        <LogOut class="w-5 h-5 shrink-0" />
        <span v-if="isOpen" class="text-[15px] whitespace-nowrap">Retour au site</span>
      </a>
    </div>

    <div 
      @click="isOpen = !isOpen" 
      class="absolute right-0 top-0 bottom-0 w-1 cursor-pointer hover:bg-slate-100 transition-colors"
    ></div>
  </aside>
</template>

<script setup>
import { ref } from 'vue'
import { 
  LayoutGrid, 
  AlertTriangle, 
  ClipboardList, 
  Users, 
  BarChart3, 
  ShieldCheck,
  LogOut 
} from 'lucide-vue-next'
import {UserRole} from "../../constants/UserRole";

const isOpen = ref(true)

const menuItems = [
  { label: "Dashboard", icon: LayoutGrid, active: true },
  { label: "Incidents", icon: AlertTriangle, active: false },
  { label: "Agents", icon: Users, active: false },
  { label: "Assignments", icon: ClipboardList, active: false },
  { label: "Statistics", icon: BarChart3, active: false },
  { label: "Municipality", icon: ShieldCheck, active: false },
]
</script>