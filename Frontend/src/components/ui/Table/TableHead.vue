<script setup>
import { cn } from "../../../lib/utils";
const props = defineProps({ class: { type: String, default: "" } });
</script>

<template>
  <th
    :class="cn(
      'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider [&:has([role=checkbox])]:pr-0',
      props.class
    )"
    v-bind="$attrs"
  >
    <slot />
  </th>
</template>