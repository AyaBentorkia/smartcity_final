<template>
  <section id="mobile-app" class="relative py-28 overflow-hidden">
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-200 h-px bg-linear-to-r from-transparent via-[#ff6820]/30 to-transparent" />

    <!-- Background city image faded -->
    <div class="absolute inset-0 z-0">
      <img
        src="https://images.unsplash.com/photo-1556699317-efb1e8d1f640?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxjaXR5JTIwc2t5bGluZSUyMG5pZ2h0JTIwYmx1ZSUyMG5lb24lMjB1cmJhbnxlbnwxfHx8fDE3NzY4OTA2NjR8MA&ixlib=rb-4.1.0&q=80&w=1080"
        alt="City"
        class="w-full h-full object-cover opacity-10"
      />
      <div class="absolute inset-0 bg-linear-to-r from-[#040c1e] via-[#040c1e]/80 to-[#040c1e]" />
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-6">
      <div class="grid lg:grid-cols-2 gap-16 items-center">

        <!-- Left: phone image -->
        <div class="relative flex justify-center">
          <div class="absolute inset-0 bg-[#0055ff]/10 rounded-full blur-3xl scale-75" />
          <div class="relative w-64 md:w-80 rounded-3xl overflow-hidden border border-[#0055ff]/20 shadow-[0_0_60px_rgba(0,85,255,0.2)] -rotate-3">
            <img :src="MOBILE_IMG" alt="Mobile App" class="w-full h-auto" />
            <div class="absolute inset-0 bg-linear-to-t from-[#040c1e]/60 via-transparent to-transparent pointer-events-none" />
          </div>
        </div>

        <!-- Right: content -->
        <div>
          <p class="text-[#CC1525] text-xs tracking-[0.3em] uppercase mb-5">Application Mobile</p>
          <h2 class="text-4xl md:text-5xl text-white mb-5 leading-tight">
            Signalez des Incidents<br />Où que Vous Soyez
          </h2>
          <p class="text-white/45 mb-10 leading-relaxed">
            L'application mobile citoyenne facilite le signalement des problèmes urbains à tout moment, en tout lieu.
          </p>

          <!-- Feature list -->
          <div class="flex flex-col gap-4 mb-10">
            <div
              v-for="f in mobileFeatures"
              :key="f.label"
              class="flex items-center gap-4"
            >
              <div class="w-9 h-9 rounded-lg bg-[#0055ff]/15 border border-[#0055ff]/25 flex items-center justify-center shrink-0">
                <component :is="f.icon" class="w-4 h-4 text-[#4da6ff]" />
              </div>
              <span class="text-white/70 text-sm">{{ f.label }}</span>
            </div>
          </div>

          <!-- CTAs -->
          <div class="flex flex-wrap gap-4">
            <button class="flex items-center gap-2 bg-[#CC1525] hover:bg-[#a81120]  text-white px-7 py-3 rounded  text-sm transition-all duration-200 shadow-[0_0_20px_rgba(0,85,255,0.4)] hover:shadow-[0_0_30px_rgba(0,85,255,0.6)]">
              Télécharger l'Application
            </button>
            <button class="flex items-center gap-2 bg-white/6 hover:bg-white/10 border border-white/20 text-white px-7 py-3 rounded text-sm transition-all duration-200">
              En Savoir Plus
            </button>
          </div>
        </div>

      </div>
    </div>
  </section>
</template>

<script setup>
import { Camera, MapPin, Smartphone, Bell } from 'lucide-vue-next'

const MOBILE_IMG = 'https://images.unsplash.com/photo-1632660995990-f2012d0ba933?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxtb2JpbGUlMjBwaG9uZSUyMGFwcCUyMGRhcmslMjBuaWdodCUyMHVyYmFuJTIwbG9jYXRpb258ZW58MXx8fHwxNzc2ODkwNjcwfDA&ixlib=rb-4.1.0&q=80&w=1080'

const mobileFeatures = [
  { icon: Camera,     label: 'Photographier et décrire le problème' },
  { icon: MapPin,     label: 'Détection automatique de votre position' },
  { icon: Smartphone, label: 'Suivi du statut en temps réel' },
  { icon: Bell,       label: 'Notification à la résolution' },
]
</script>