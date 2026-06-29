<template>
  <!-- Fixed background glows that stay in place during scroll -->
  <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
    <!-- Main blue glow (top center) -->
    <div class="absolute top-20 left-1/2 -translate-x-1/2 w-150 h-150 bg-[#0055ff]/8 rounded-full blur-3xl" />
    
    <!-- Orange glow (top right) -->
    <div class="absolute top-40 -right-40 w-125 h-125 bg-[#ff6820]/5 rounded-full blur-3xl" />
    
    <!-- Blue glow (middle left) -->
    <div class="absolute top-1/2 -left-48 w-150 h-150 bg-[#0055ff]/6 rounded-full blur-3xl" />
    
    <!-- Cyan glow (bottom right) -->
    <div class="absolute bottom-20 right-0 w-125 h-125 bg-[#00c8ff]/4 rounded-full blur-3xl" />
  </div>
</template>

<script setup>
</script>
