<template>
    <!-- Sidebar cachée avec v-show au lieu de v-if pendant le chargement -->
      <RouterView />
</template>

<script setup>
import './style.css';

import { useRoute } from 'vue-router';

const route = useRoute();


</script>