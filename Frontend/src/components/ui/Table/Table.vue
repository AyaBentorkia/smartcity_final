<script setup>
import { cn } from "../../../lib/utils";
const props = defineProps({ class: { type: String, default: "" } });
</script>

<template>
  <div class="relative w-full overflow-x-auto">
    <table
      :class="cn('w-full caption-bottom text-sm', props.class)"
      v-bind="$attrs"
    >
      <slot />
    </table>
  </div>
</template>