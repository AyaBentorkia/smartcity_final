<template>
  <div class="flex">
    <!-- <Sidebar /> -->
    <main class="flex-1 overflow-y-auto ">
      <!-- <DashboardLayout /> -->
      <AssignmentsList_Agents          v-if="userRole === UserRole.AGENT" />
      <AssignmentsList_MunicipalAdmin  v-else-if="userRole === UserRole.ADMIN_MUNICIPAL" />
    </main>
  </div>
</template>

<script setup>
import { storeToRefs } from 'pinia'
import { UserRole }    from '../../constants/UserRole'
import { useAuthStore } from '../../stores/AuthStore'
import Sidebar                    from '../../components/Sidebar/Sidebar.vue'
import DashboardLayout            from '../../components/DashboardLayout.vue'
import AssignmentsList_Agents         from './AssignmentsList_Agents.vue'
import AssignmentsList_MunicipalAdmin from './AssignmentsList_MunicipalAdmin.vue'

const { userRole } = storeToRefs(useAuthStore())
</script>