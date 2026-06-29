<script setup>
import { cn } from "../../../lib/utils";
const props = defineProps({ class: { type: String, default: "" } });
</script>

<template>
  <tr
    :class="cn(
      'transition-colors hover:bg-gray-50 data-[state=selected]:bg-[#0F2356]/5',
      props.class
    )"
    v-bind="$attrs"
  >
    <slot />
  </tr>
</template>