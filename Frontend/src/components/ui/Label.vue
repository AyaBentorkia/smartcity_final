<template>
  <label
    :class="cn('text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70', $attrs.class)"
    v-bind="attrsWithoutClass"
  >
    <slot />
  </label>
</template>

<script setup>
import { useAttrs, computed } from "vue";
import { twMerge } from "tailwind-merge";
import { clsx } from "clsx";

const cn = (...inputs) => twMerge(clsx(inputs));

defineOptions({ inheritAttrs: false });
const attrs = useAttrs();
const attrsWithoutClass = computed(() => {
  const { class: _, ...rest } = attrs;
  return rest;
});
</script>