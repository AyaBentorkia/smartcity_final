<script setup>
import { X, User, Mail, Phone, CreditCard, MapPin, Tag, Calendar, Shield } from 'lucide-vue-next'

const props = defineProps({
  modelValue: Boolean,
  user: Object,
})

const roleConfig = {
  admin:  { label: 'Administrateur', class: 'text-[#0F2356]' },
  agent:  { label: 'Agent',          class: 'text-emerald-600' },
  viewer: { label: 'Observateur',    class: 'text-amber-600' },
}

const emit = defineEmits(['update:modelValue'])
const handleClose = () => emit('update:modelValue', false)

const statusConfig = {
  active:   { label: 'Actif',      bg: 'background:#d1fae5; color:#059669;' },
  inactive: { label: 'Inactif',    bg: 'background:#fee2e2; color:#ef4444;' },
  pending:  { label: 'En attente', bg: 'background:#fef3c7; color:#d97706;' },
}

const formatDate = (d) => d ? new Date(d).toLocaleDateString('fr-TN', { day: '2-digit', month: 'long', year: 'numeric' }) : '—'
</script>

<template>
  <div
    v-if="modelValue && user"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="background:rgba(15,35,86,0.50); backdrop-filter:blur(4px);"
    @click.self="handleClose"
  >
    <div class="relative w-full max-w-lg bg-white rounded-xl shadow-2xl flex flex-col overflow-hidden max-h-[92vh]">

      <!-- HEADER -->
      <div class="relative px-6 py-4 flex-shrink-0 overflow-hidden" style="background:#0F2356;">
        <div class="absolute inset-0 opacity-[0.04] pointer-events-none"
          style="background-image:repeating-linear-gradient(0deg,#fff 0,#fff 1px,transparent 1px,transparent 32px),repeating-linear-gradient(90deg,#fff 0,#fff 1px,transparent 1px,transparent 32px);" />
        <div class="absolute left-0 top-0 bottom-0 w-1" style="background:#CC1525;" />
        <div class="relative z-10 flex items-start justify-between gap-4">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <User class="w-4 h-4" style="color:#CC1525;" />
              <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Fiche Utilisateur</span>
            </div>
            <h2 class="text-white text-lg font-semibold">{{ user.name ?? '—' }}</h2>
            <p class="text-white/40 text-xs mt-0.5">Détails du compte utilisateur</p>
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

        <!-- Avatar + statut -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm flex flex-col items-center gap-3">
          <div class="w-16 h-16 rounded-full flex items-center justify-center text-white text-2xl font-bold select-none" style="background:#0F2356;">
            {{ user.name?.charAt(0).toUpperCase() ?? '?' }}
          </div>
          <div class="text-center">
            <p class="text-base font-semibold" style="color:#0F2356;">{{ user.name ?? '—' }}</p>
            <div class="flex items-center justify-center gap-2 mt-1">
              <span class="text-xs font-mono text-gray-400">#{{ user.id ?? '—' }}</span>
              <span class="text-gray-300">·</span>
              <span :class="`text-xs font-medium ${roleConfig[user.role]?.class ?? 'text-gray-500'}`">
                {{ roleConfig[user.role]?.label ?? user.role ?? '—' }}
              </span>
            </div>
          </div>
          <span
            v-if="user.status"
            class="text-xs font-semibold px-3 py-1 rounded-full"
            :style="statusConfig[user.status]?.bg ?? 'background:#f1f5f9; color:#64748b;'"
          >
            {{ statusConfig[user.status]?.label ?? user.status }}
          </span>
        </div>

        <!-- Coordonnées -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
            <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Coordonnées</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Nom</label>
              <div class="flex items-center gap-2 rounded-lg px-3 py-2.5" style="background:#F7F8FB; border:1px solid #e5e7eb;">
                <User class="w-4 h-4 flex-shrink-0" style="color:#4A5B78;" />
                <span class="text-sm" style="color:#0F2356;">{{ user.name ?? '—' }}</span>
              </div>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Email</label>
              <div class="flex items-center gap-2 rounded-lg px-3 py-2.5" style="background:#F7F8FB; border:1px solid #e5e7eb;">
                <Mail class="w-4 h-4 flex-shrink-0" style="color:#4A5B78;" />
                <span class="text-sm truncate" style="color:#0F2356;">{{ user.email ?? '—' }}</span>
              </div>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">CIN</label>
              <div class="flex items-center gap-2 rounded-lg px-3 py-2.5" style="background:#F7F8FB; border:1px solid #e5e7eb;">
                <CreditCard class="w-4 h-4 flex-shrink-0" style="color:#4A5B78;" />
                <span class="text-sm" style="color:#0F2356;">{{ user.cin ?? '—' }}</span>
              </div>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Téléphone</label>
              <div class="flex items-center gap-2 rounded-lg px-3 py-2.5" style="background:#F7F8FB; border:1px solid #e5e7eb;">
                <Phone class="w-4 h-4 flex-shrink-0" style="color:#4A5B78;" />
                <span class="text-sm" style="color:#0F2356;">{{ user.phone ?? '—' }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Affectation -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
            <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Affectation</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Catégorie</label>
              <div class="flex items-center gap-2 rounded-lg px-3 py-2.5" style="background:#F7F8FB; border:1px solid #e5e7eb;">
                <Tag class="w-4 h-4 flex-shrink-0" style="color:#4A5B78;" />
                <span class="text-sm" style="color:#0F2356;">{{ user?.category?.name ?? '—' }}</span>
              </div>
            </div>
            <!-- <div>
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Ville</label>
              <div class="flex items-center gap-2 rounded-lg px-3 py-2.5" style="background:#F7F8FB; border:1px solid #e5e7eb;">
                <MapPin class="w-4 h-4 flex-shrink-0" style="color:#4A5B78;" />
                <span class="text-sm" style="color:#0F2356;">{{ user.city ?? '—' }}</span>
              </div>
            </div> -->
            <div>
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Municipalité</label>
              <div class="flex items-center gap-2 rounded-lg px-3 py-2.5" style="background:#F7F8FB; border:1px solid #e5e7eb;">
                <Shield class="w-4 h-4 flex-shrink-0" style="color:#4A5B78;" />
                <span class="text-sm" style="color:#0F2356;">{{ user.municipality?.name ?? '—' }}</span>
              </div>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1.5" style="color:#4A5B78;">Date de naissance</label>
              <div class="flex items-center gap-2 rounded-lg px-3 py-2.5" style="background:#F7F8FB; border:1px solid #e5e7eb;">
                <Calendar class="w-4 h-4 flex-shrink-0" style="color:#4A5B78;" />
                <span class="text-sm" style="color:#0F2356;">{{ formatDate(user.birthdate) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Membre depuis -->
        <p class="text-center text-xs" style="color:#9ca3af;">
          Membre depuis le {{ formatDate(user.created_at) }}
        </p>

      </div>

      <!-- FOOTER -->
      <div class="bg-white border-t border-gray-200 px-6 py-4 flex items-center justify-between gap-3 flex-shrink-0">
        <div class="flex items-center gap-1.5 text-xs text-gray-400">
          <Shield class="w-3.5 h-3.5 flex-shrink-0" style="color:#CC1525;" />
          <span>Plateforme Municipale</span>
        </div>
        <button
          class="px-5 py-2 border border-gray-200 hover:border-gray-300 rounded-lg text-sm transition-colors"
          style="color:#4A5B78;"
          @click="handleClose"
        >
          Fermer
        </button>
      </div>

    </div>
  </div>
</template>