<script setup>
import { ref, computed, onMounted, onUnmounted, toRef } from 'vue'

const props = defineProps({
  modelValue: [String, Number, null],
  options: { type: Array, default: () => [] },
  placeholder: { type: String, default: '— Sélectionner —' },
  disabled: { type: Boolean, default: false },
  error: { type: String, default: '' },
})
const options = toRef(props, 'options')
const modelValue = toRef(props, 'modelValue')
const placeholder = toRef(props, 'placeholder')
const disabled = toRef(props, 'disabled')
const error = toRef(props, 'error')
const emit = defineEmits(['update:modelValue'])

const open = ref(false)
const container = ref(null)

const selectedLabel = computed(() => {
  const opt = (options.value || []).find(o => o.value === modelValue.value)
  return opt ? opt.label : placeholder.value
})

const toggle = () => { if (!disabled.value) open.value = !open.value }
const select = (val) => { emit('update:modelValue', val); open.value = false }

const onDocClick = (e) => {
  if (!container.value) return
  if (!container.value.contains(e.target)) open.value = false
}
onMounted(() => document.addEventListener('click', onDocClick))
onUnmounted(() => document.removeEventListener('click', onDocClick))
</script>

<template>
  <div class="relative" ref="container">
    <button type="button" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0F2356] transition-colors flex items-center justify-between" :class="{ 'opacity-60 cursor-not-allowed': disabled, 'border-red-300 bg-red-50': error }" @click="toggle">
      <span class="truncate" :title="selectedLabel">{{ selectedLabel }}</span>
      <span class="ml-2 text-gray-400 pointer-events-none">&#9662;</span>
    </button>

    <ul v-if="open" class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-56 overflow-auto z-50 text-sm">
      <li v-if="options.length === 0" class="px-3 py-2 text-gray-500">Aucune option</li>
      <li v-for="opt in options" :key="opt.value" class="px-3 py-2 text-gray-700 hover:bg-gray-100 cursor-pointer" :class="{'bg-gray-100': opt.value === modelValue }" @click="select(opt.value)" role="option" :aria-selected="opt.value === modelValue">
        {{ opt.label }}
      </li>
    </ul>
  </div>
</template>

<style scoped>
/* match AssignIncidentModal styles */
</style>
