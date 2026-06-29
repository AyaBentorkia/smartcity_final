<template>
  <h3 :class="cn('text-2xl font-semibold leading-none tracking-tight', $attrs.class)" v-bind="attrsWithoutClass">
    <slot />
  </h3>
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