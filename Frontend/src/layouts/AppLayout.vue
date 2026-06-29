<template>
  <div class="flex h-screen overflow-hidden bg-background">

    <!-- Sidebar — montée UNE SEULE FOIS ici -->
    <Sidebar />

    <!-- Zone droite : header + contenu -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden ml-70">

      <!-- DashboardLayout = header uniquement, ne doit pas s'étirer -->
      <DashboardLayout />

      <!-- Contenu de la page courante -->
      <main class="flex-1 overflow-y-auto bg-background">
        <RouterView />
      </main>

    </div>
  </div>
</template>

<script setup>
import Sidebar         from '../components/Sidebar/Sidebar.vue'
import DashboardLayout from '../components/DashboardLayout.vue'
</script>