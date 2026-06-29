<script setup>
import { ref } from 'vue'

defineProps({
  src: String,
  alt: String,
  class: String,
})

const error = ref(false)
</script>

<template>
  <img
    v-if="!error"
    :src="src"
    :alt="alt"
    :class="['aspect-square h-full w-full', $props.class]"
    @error="error = true"
  />
</template>