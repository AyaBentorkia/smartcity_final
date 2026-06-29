<template>
  <div class="flex">
              <!-- <Sidebar /> -->

        <main class="flex-1 overflow-y-auto ">
          <!-- <DashboardLayout /> -->
            
          <Dashboard_MunicipalAdmin v-if="currentRole === UserRole.ADMIN_MUNICIPAL" />
          <Dashboard_SuperAdmin v-else-if="currentRole === UserRole.SUPER_ADMIN" />
          <Dashboard_Agent v-else-if="currentRole === UserRole.AGENT" />

</main>
  </div>
  
</template>

<script setup>
import { storeToRefs } from "pinia";
import DashboardLayout from "../../components/DashboardLayout.vue";
import Sidebar from "../../components/Sidebar/Sidebar.vue";
import { UserRole } from "../../constants/UserRole";
import { useAuthStore } from "../../stores/AuthStore";
import "../../style.css";
import Dashboard_MunicipalAdmin from "./Dashboard_MunicipalAdmin.vue";
import Dashboard_SuperAdmin from "./Dashboard_SuperAdmin.vue";
import { computed } from "vue";
import Dashboard_Agent from "./Dashboard_Agent.vue";
const authStore = useAuthStore()
const { userRole } = storeToRefs(authStore)
const currentRole = computed(() => userRole.value)
</script>

<style scoped>
.dashboard-container {
  display: flex;
  width: 100%;
  min-height: 100vh;
}

.dashboard-main {
  flex: 1;
  background-color: #f3f4f6;
}
@media (min-width: 768px) {
  .dashboard-content {
    margin-left: 16rem;
  }
}
</style>