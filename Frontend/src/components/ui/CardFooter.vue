<template>
  <div :class="cn('flex items-center p-6 pt-0', $attrs.class)" v-bind="attrsWithoutClass">
    <slot />
  </div>
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