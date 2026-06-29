<template>
  <div class="flex">
              <!-- <Sidebar /> -->

        <main class="flex-1 overflow-y-auto  ">
          <!-- <DashboardLayout /> -->
            
          <IncidentPage_MunicipalAdmin v-if="currentRole === UserRole.ADMIN_MUNICIPAL" />
          <IncidentPage_SuperAdmin v-else-if="currentRole === UserRole.SUPER_ADMIN" />

</main>
  </div>
  
</template>

<script setup>
import '../../style.css';

import { computed } from "vue";
import IncidentPage_MunicipalAdmin from './IncidentPage_MunicipalAdmin.vue';
import IncidentPage_SuperAdmin from './IncidentPage_SuperAdmin.vue';
import { useAuthStore } from '../../stores/AuthStore';
import { storeToRefs } from 'pinia';
import { UserRole } from '../../constants/UserRole';
import Sidebar from '../../components/Sidebar/Sidebar.vue';
import DashboardLayout from '../../components/DashboardLayout.vue';
const authStore = useAuthStore()
const { userRole } = storeToRefs(authStore)
const currentRole = computed(() => userRole.value)
</script>

