<template>
  <p :class="cn('text-sm text-muted-foreground', $attrs.class)" v-bind="attrsWithoutClass">
    <slot />
  </p>
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