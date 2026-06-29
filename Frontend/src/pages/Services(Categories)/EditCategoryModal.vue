<script setup>
import { storeToRefs } from 'pinia'
import { ref, reactive, watch } from 'vue'
import { X, SquarePen, Tag, Shield } from 'lucide-vue-next'
import Input from '../../components/ui/Input.vue'
import { useCategoryStore } from '../../stores/CategoryStore'

const props = defineProps({ modelValue: Boolean, category: { type: Object, default: null } })
const emit  = defineEmits(['update:modelValue', 'updated'])

const categoryStore     = useCategoryStore()
const { actionLoading } = storeToRefs(categoryStore)
const { updateCategory }= categoryStore

const error = ref(null)
const form  = reactive({ name: '', color: '', description: '' })

const presetColors = [
  '#EF4444', '#F97316', '#EAB308', '#22C55E',
  '#14B8A6', '#3B82F6', '#6366F1', '#8B5CF6',
  '#EC4899', '#0081c9', '#0F2356', '#64748B',
]

watch(() => [props.modelValue, props.category], ([open, cat]) => {
  if (open && cat) {
    form.name        = cat.name        ?? ''
    form.color       = cat.color       ?? '#0F2356'
    form.description = cat.description ?? ''
    error.value      = null
  }
}, { immediate: true })

const handleSubmit = async () => {
  if (!props.category) return
  error.value = null
  try {
    await updateCategory(props.category.id, { ...form })
    emit('updated'); emit('update:modelValue', false)
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur lors de la modification'
  }
}
const handleClose = () => { error.value = null; emit('update:modelValue', false) }
</script>

<template>
  <div
    v-if="modelValue"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="background:rgba(15,35,86,0.50); backdrop-filter:blur(4px);"
    @click.self="handleClose"
  >
    <div class="relative w-full max-w-md bg-white rounded-xl shadow-2xl flex flex-col overflow-hidden max-h-[92vh]">

      <!-- HEADER -->
      <div class="relative px-6 py-4 flex-shrink-0 overflow-hidden" style="background:#0F2356;">
        <div class="absolute inset-0 opacity-[0.04] pointer-events-none"
          style="background-image:repeating-linear-gradient(0deg,#fff 0,#fff 1px,transparent 1px,transparent 32px),repeating-linear-gradient(90deg,#fff 0,#fff 1px,transparent 1px,transparent 32px);" />
        <div class="absolute left-0 top-0 bottom-0 w-1" style="background:#CC1525;" />
        <div class="relative z-10 flex items-start justify-between gap-4">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <SquarePen class="w-4 h-4" style="color:#CC1525;" />
              <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Modification</span>
            </div>
            <h2 class="text-white text-lg font-semibold">{{ category?.name ?? 'Modifier la catégorie' }}</h2>
            <p class="text-white/40 text-xs mt-0.5">Mettre à jour les informations de cette catégorie</p>
          </div>
          <button class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors flex-shrink-0"
            style="background:rgba(255,255,255,0.10);"
            @mouseenter="$event.currentTarget.style.background='rgba(255,255,255,0.20)'"
            @mouseleave="$event.currentTarget.style.background='rgba(255,255,255,0.10)'"
            @click="handleClose">
            <X class="w-4 h-4 text-white/70" />
          </button>
        </div>
      </div>

      <!-- BODY -->
      <div class="flex-1 overflow-y-auto p-6 flex flex-col gap-5" style="background:#F7F8FB;">

        <!-- Informations -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
            <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Informations</span>
          </div>
          <div class="mb-4">
            <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Nom <span style="color:#CC1525;">*</span></label>
            <Input v-model="form.name" type="text" required placeholder="Nom de la catégorie"
              class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0F2356] transition-colors" style="color:#0F2356;" />
          </div>
          <div>
            <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Description</label>
            <textarea v-model="form.description" rows="3" placeholder="Description optionnelle…"
              class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-[#0F2356] transition-colors resize-none" style="color:#0F2356;" />
          </div>
        </div>

        <!-- Couleur -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
            <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Couleur d'identification</span>
          </div>
          <div class="flex flex-wrap gap-2 mb-4">
            <button v-for="color in presetColors" :key="color" type="button"
              @click="form.color = color" :style="{ backgroundColor: color }"
              :class="['w-8 h-8 rounded-full border-2 transition-all', form.color === color ? 'border-gray-700 scale-110 shadow-md' : 'border-transparent hover:scale-105']" />
            <label class="w-8 h-8 rounded-full border-2 border-dashed border-gray-300 flex items-center justify-center cursor-pointer hover:border-gray-500 transition-all">
              <span class="text-gray-400 text-lg leading-none">+</span>
              <input type="color" v-model="form.color" class="sr-only" />
            </label>
          </div>
          <div v-if="form.color" class="flex items-center gap-3 bg-gray-50 rounded-lg px-3 py-2.5 border border-gray-200">
            <div class="w-6 h-6 rounded-full shadow-sm flex-shrink-0" :style="{ backgroundColor: form.color }" />
            <div>
              <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Couleur sélectionnée</p>
              <p class="text-sm font-mono" style="color:#0F2356;">{{ form.color }}</p>
            </div>
          </div>
        </div>

        <!-- Error -->
        <div v-if="error" class="flex items-center gap-2 bg-red-50 border border-red-100 text-red-600 text-xs px-4 py-3 rounded-lg">
          <span class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0" />
          {{ error }}
        </div>

      </div>

      <!-- FOOTER -->
      <div class="bg-white border-t border-gray-200 px-6 py-4 flex items-center justify-between gap-3 flex-shrink-0">
        <div class="flex items-center gap-1.5 text-xs text-gray-400">
          <Shield class="w-3.5 h-3.5 flex-shrink-0" style="color:#CC1525;" />
          <span>Plateforme Municipale</span>
        </div>
        <div class="flex items-center gap-2">
          <button class="px-5 py-2 border border-gray-200 hover:border-gray-300 rounded-lg text-sm transition-colors" style="color:#4A5B78;" @click="handleClose">
            Annuler
          </button>
          <button
            :disabled="actionLoading"
            class="px-5 py-2 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            style="background:#0F2356;"
            @mouseenter="$event.currentTarget.style.background='#162d63'"
            @mouseleave="$event.currentTarget.style.background='#0F2356'"
            @click="handleSubmit"
          >
            <span v-if="actionLoading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
            {{ actionLoading ? 'Enregistrement…' : 'Enregistrer' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>