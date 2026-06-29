<script setup>
import { cn } from "../../../lib/utils";
const props = defineProps({ class: { type: String, default: "" } });
</script>

<template>
  <tbody
    :class="cn('divide-y divide-gray-100 [&_tr:last-child]:border-0', props.class)"
    v-bind="$attrs"
  >
    <slot />
  </tbody>
</template>