<script setup>
import { X, User, Mail, Phone, MapPin, Tag, Shield, Calendar, Building2 } from 'lucide-vue-next'

const props = defineProps({ modelValue: Boolean, municipality: Object })
const emit  = defineEmits(['update:modelValue'])

const handleClose = () => emit('update:modelValue', false)
const formatDate  = (d) => d ? new Date(d).toLocaleDateString('fr-TN', { day: '2-digit', month: 'long', year: 'numeric' }) : '—'

const initials = (name) => name?.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase() ?? '?'
</script>

<template>
  <div
    v-if="modelValue && municipality"
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
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <Building2 class="w-4 h-4" style="color:#CC1525;" />
              <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Fiche Municipalité</span>
              <span class="text-white/20 text-xs">·</span>
              <span class="text-white/40 text-xs font-mono">#{{ municipality.id }}</span>
            </div>
            <h2 class="text-white text-lg font-semibold leading-snug truncate">{{ municipality.name ?? '—' }}</h2>
            <p class="text-white/40 text-xs mt-0.5">{{ municipality.city?.governorate?.name ?? '' }}{{ municipality.city?.name ? ' · ' + municipality.city.name : '' }}</p>
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

      <!-- Avatar strip -->
      <div class="bg-white border-b border-gray-100 px-6 py-4 flex items-center gap-4 flex-shrink-0">
        <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0" style="background:#0F2356;">
          {{ initials(municipality.name) }}
        </div>
        <div>
          <p class="font-semibold text-sm" style="color:#0F2356;">{{ municipality.name ?? '—' }}</p>
          <p class="text-xs text-gray-400">Créée le {{ formatDate(municipality.created_at) }}</p>
        </div>
      </div>

      <!-- BODY -->
      <div class="flex-1 overflow-y-auto p-6 flex flex-col gap-5" style="background:#F7F8FB;">

        <!-- Identité -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
            <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Identité</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="flex items-center gap-3 bg-gray-50 rounded-lg px-3 py-2.5">
              <User class="w-4 h-4 text-gray-400 shrink-0" />
              <div>
                <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Nom</p>
                <p class="text-sm" style="color:#0F2356;">{{ municipality.name ?? '—' }}</p>
              </div>
            </div>
            <div class="flex items-center gap-3 bg-gray-50 rounded-lg px-3 py-2.5">
              <Mail class="w-4 h-4 text-gray-400 shrink-0" />
              <div class="min-w-0">
                <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Email</p>
                <p class="text-sm truncate" style="color:#0F2356;">{{ municipality.email ?? '—' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Localisation -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
            <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Localisation</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="flex items-center gap-3 bg-gray-50 rounded-lg px-3 py-2.5">
              <Tag class="w-4 h-4 text-gray-400 shrink-0" />
              <div>
                <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Gouvernorat</p>
                <p class="text-sm" style="color:#0F2356;">{{ municipality.city?.governorate?.name ?? '—' }}</p>
              </div>
            </div>
            <div class="flex items-center gap-3 bg-gray-50 rounded-lg px-3 py-2.5">
              <MapPin class="w-4 h-4 text-gray-400 shrink-0" />
              <div>
                <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Ville</p>
                <p class="text-sm" style="color:#0F2356;">{{ municipality?.city?.name ?? '—' }}</p>
              </div>
            </div>
            <div class="flex items-center gap-3 bg-gray-50 rounded-lg px-3 py-2.5 sm:col-span-2">
              <MapPin class="w-4 h-4 text-gray-400 shrink-0" />
              <div>
                <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Adresse</p>
                <p class="text-sm" style="color:#0F2356;">{{ municipality.address ?? '—' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Données -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-4 rounded-full flex-shrink-0" style="background:#CC1525;" />
            <span class="text-[10px] font-bold uppercase tracking-[0.12em]" style="color:#CC1525;">Données</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="flex items-center gap-3 bg-gray-50 rounded-lg px-3 py-2.5">
              <Phone class="w-4 h-4 text-gray-400 shrink-0" />
              <div>
                <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Téléphone</p>
                <p class="text-sm" style="color:#0F2356;">{{ municipality.phone ?? '—' }}</p>
              </div>
            </div>
            <div class="flex items-center gap-3 bg-gray-50 rounded-lg px-3 py-2.5">
              <Shield class="w-4 h-4 text-gray-400 shrink-0" />
              <div>
                <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Habitants</p>
                <p class="text-sm" style="color:#0F2356;">{{ municipality.number_of_inhabitants?.toLocaleString() ?? '—' }}</p>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- FOOTER -->
      <div class="bg-white border-t border-gray-200 px-6 py-4 flex items-center justify-between gap-3 flex-shrink-0">
        <div class="flex items-center gap-1.5 text-xs text-gray-400">
          <Shield class="w-3.5 h-3.5 flex-shrink-0" style="color:#CC1525;" />
          <span>Plateforme Municipale</span>
        </div>
        <button class="px-5 py-2 border border-gray-200 hover:border-gray-300 rounded-lg text-sm transition-colors" style="color:#4A5B78;" @click="handleClose">
          Fermer
        </button>
      </div>
    </div>
  </div>
</template>