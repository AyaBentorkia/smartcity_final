<script setup>
import { cn } from "@/lib/utils";

// Déclari el prop 'class' kima fel React component
const props = defineProps({
  class: { type: String, default: "" },
});
</script>

<template>
  <tfoot
    :class="cn('border-t bg-muted/50 font-medium [&>tr]:last:border-b-0', props.class)"
    v-bind="$attrs"
  >
    <slot />
  </tfoot>
</template>