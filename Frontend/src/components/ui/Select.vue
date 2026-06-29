<script setup>
import { ref } from 'vue';
import { cn } from "../../lib/utils";
import { IncidentStatus } from '../../constants/IncidentStatus';
const props = defineProps({
  modelValue: String,
  options: Array
});
const incidentStatus = IncidentStatus;
const emit = defineEmits(["update:modelValue"]);

const statusStyles = {
  all: "bg-slate-50 text-slate-600 border-slate-200",
  [incidentStatus.REPORTED]: "bg-amber-50 text-amber-700 border-amber-200",
  [incidentStatus.IN_PROGRESS]: "bg-blue-50 text-blue-700 border-blue-200",
  [incidentStatus.RESOLVED]: "bg-red-50 text-red-700 border-red-200",
};
</script>

<template>
  <div class="flex flex-col gap-2">
    
    <select 
:value="modelValue"
  @change="emit('update:modelValue', $event.target.value)"
        :class="cn(
        'h-10 w-48 rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all',
        statusStyles[modelValue] // change la couleur selon la valeur
      )"
    >
      <option value="all">statuts</option>
      <option 
        v-for="(label, key) in options" 
        :key="key" 
        :value="label"
      >
        {{ label }}
      </option>
    </select>
  </div>
</template>

<style scoped>
select {
  appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22currentColor%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E");
  background-repeat: no-repeat;
  background-position: right 0.7rem center;
  background-size: 1em;
}
</style>