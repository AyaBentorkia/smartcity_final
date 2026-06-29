<script setup>
import { ref, computed, defineExpose } from "vue";

// Similaire à `cn` pour concaténer les classes
const cn = (...classes) => classes.filter(Boolean).join(" ");

// Props
const props = defineProps({
  modelValue: String,
  class: String,
  type: {
    type: String,
    default: "text",
  },
});
const emit = defineEmits(["update:modelValue"]);

// Ref pour l'input
const inputRef = ref(null);
defineExpose({ inputRef });

// Classes calculées
const classes = computed(() =>
  cn(
    "flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm",
    props.class
  )
);

</script>

<template>
  <input
   ref="inputRef"
    :type="props.type"
    :class="classes"
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
    v-bind="$attrs"
    
  />
</template>