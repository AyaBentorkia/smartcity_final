<script setup>
import { cn } from "../../../lib/utils";
const props = defineProps({ class: { type: String, default: "" } });
</script>

<template>
  <thead
    :class="cn('[&_tr]:bg-[#F7F8FB] [&_tr]:border-b [&_tr]:border-gray-200', props.class)"
    v-bind="$attrs"
  >
    <slot />
  </thead>
</template>