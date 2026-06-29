<template>
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    
    <div>
      <h1 class="text-2xl font-display font-bold text-foreground">
        {{ title }}
      </h1>
      <p class="text-muted-foreground">
        {{ subtitle }}
      </p>
    </div>

    <div class="flex items-center gap-3 flex-wrap">
      <slot name="actions"></slot>
    </div>

  </div>
</template>

<script setup>

defineProps({
  title: String,
  subtitle: String
})

</script>