<template>
    <div class="flex bg-white">
        <!-- <Sidebar /> -->
        <main class="flex-1 overflow-y-auto ">
            <!-- <DashboardLayout /> -->
            <MunicipalAdmin_Profile v-if="currentRole === UserRole.ADMIN_MUNICIPAL"  />
            <Agent_Profile v-else-if="currentRole === UserRole.AGENT"  />
            <SuperAdmin_Profile v-else-if="currentRole === UserRole.SUPER_ADMIN"  />
        </main>
    </div>
</template>
<script setup>
import { storeToRefs } from 'pinia';
import DashboardLayout from '../../components/DashboardLayout.vue';
import Sidebar from '../../components/Sidebar/Sidebar.vue';
import { UserRole } from '../../constants/UserRole';
import { useAuthStore } from '../../stores/AuthStore';
import Agent_Profile from './Agent_Profile.vue';
import MunicipalAdmin_Profile from './MunicipalAdmin_Profile.vue';
import SuperAdmin_Profile from './SuperAdmin_Profile.vue';
import { computed } from 'vue';

const authStore = useAuthStore()
const { userRole } = storeToRefs(authStore)
const currentRole = computed(() => userRole.value)
</script>