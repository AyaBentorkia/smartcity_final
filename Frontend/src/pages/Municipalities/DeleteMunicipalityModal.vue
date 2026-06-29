<script setup>
import { Trash2 } from 'lucide-vue-next'

defineProps({
  modelValue:         { type: Boolean, required: true },
  municipalityName:   { type: String,  default: ''    },
  loading:            { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel'])

const handleCancel = () => {
  emit('cancel')
  emit('update:modelValue', false)
}

const handleConfirm = () => emit('confirm')
</script>

<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="handleCancel"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" />

        <!-- Dialog -->
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md p-6 space-y-5">

          <!-- Icon -->
          <div class="flex items-center justify-center w-14 h-14 rounded-full bg-red-100 mx-auto">
            <Trash2 class="w-7 h-7 text-red-600" />
          </div>

          <!-- Content -->
          <div class="text-center space-y-2">
            <h3 class="text-lg font-semibold text-gray-900">Supprimer la municipalité</h3>
            <p class="text-sm text-gray-500">
              Voulez-vous vraiment supprimer la municipalité
              <span class="font-semibold text-gray-800">&laquo;&nbsp;{{ municipalityName }}&nbsp;&raquo;</span>&nbsp;?
            </p>
            <p class="text-xs text-red-500 font-medium">Cette action est irréversible.</p>
          </div>

          <!-- Actions -->
          <div class="flex gap-3 pt-1">
            <button
              class="flex-1 px-4 py-2 text-sm rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors disabled:opacity-50"
              :disabled="loading"
              @click="handleCancel"
            >
              Annuler
            </button>
            <button
              class="flex-1 flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg px-4 py-2 transition-colors"
              :disabled="loading"
              @click="handleConfirm"
            >
              <svg v-if="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
              </svg>
              <Trash2 v-else class="w-4 h-4" />
              {{ loading ? 'Suppression...' : 'Supprimer' }}
            </button>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.modal-fade-enter-active .relative { transition: transform 0.2s ease; }
.modal-fade-enter-from .relative { transform: scale(0.95); }
</style>