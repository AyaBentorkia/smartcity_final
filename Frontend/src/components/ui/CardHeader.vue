<template>
  <div :class="cn(wrapperClasses, $attrs.class)" v-bind="attrsWithoutClass">
    <!-- Red left accent bar -->
    <div v-if="accent" class="absolute left-0 top-0 bottom-0 w-1 bg-[#CC1525]" />

    <!-- Subtle grid overlay -->
    <div
      v-if="grid"
      class="absolute inset-0 opacity-[0.04] pointer-events-none"
      style="background-image: repeating-linear-gradient(0deg,#fff 0px,#fff 1px,transparent 1px,transparent 48px),repeating-linear-gradient(90deg,#fff 0px,#fff 1px,transparent 1px,transparent 48px);"
    />

    <!-- Content -->
    <div class="relative z-10">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { computed, useAttrs } from "vue";
import { twMerge } from "tailwind-merge";
import { clsx } from "clsx";

const cn = (...inputs) => twMerge(clsx(inputs));

const props = defineProps({
  /**
   * variant:
   *  'solid'    → plain #0F2356 bg (default)
   *  'gradient' → gradient from-[#0F2356] to-[#162d63]
   */
  variant: { type: String, default: 'solid' },
  /** Show the red left accent bar */
  accent:  { type: Boolean, default: false },
  /** Show the subtle grid overlay */
  grid:    { type: Boolean, default: false },
  class:   { type: String, default: '' },
});

defineOptions({ inheritAttrs: false });
const attrs = useAttrs();
const attrsWithoutClass = computed(() => {
  const { class: _, ...rest } = attrs;
  return rest;
});

const wrapperClasses = computed(() => {
  const base = 'relative px-6 py-4 overflow-hidden';
  const bg = props.variant === 'gradient'
    ? 'bg-gradient-to-r from-[#0F2356] to-[#162d63]'
    : 'bg-[#0F2356]';
  return cn(base, bg);
});
</script>