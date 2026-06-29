<template>
  <section id="features" class="relative py-28 overflow-hidden bg-white">
    <!-- Section divider glow -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-200 h-px bg-linear-to-r from-transparent via-[#0055ff]/50 to-transparent" />

    <div class="max-w-7xl mx-auto px-6">
      <!-- Header -->
      <div class="text-center mb-16">
        <p class="text-[#CC1525] text-xs tracking-[0.3em] uppercase mb-4">Fonctionnalités</p>
        <h2 class="text-4xl md:text-5xl text-[#0F2356] mb-4">Tout ce dont votre commune a besoin</h2>
        <p class="text-gray-500 max-w-xl mx-auto">
Une plateforme complète reliant citoyens, IA et gestionnaires municipaux
          pour une gestion urbaine plus rapide et plus transparente.        </p>
      </div>

      <!-- Cards grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <div
          v-for="feature in features"
          :key="feature.title"
          class="group relative bg-white border border-gray-200 rounded-2xl p-7 hover:border-[#CC1525]/40  hover:shadow-md transition-all duration-200 cursor-pointer"
        >
          <!-- Hover glow -->
          <div class="absolute inset-0 bg-linear-to-br from-[#0055ff]/0 to-[#0055ff]/0 group-hover:from-red-300/30 group-hover:to-transparent transition-all duration-500 rounded-2xl" />

          <!-- Icon -->
          <div class="w-11 h-11 rounded-xl bg-[#FEF2F2]/15 border border-[#CC1525]/15 flex items-center justify-center mb-5 group-hover:bg-[#CC1525]/25 group-hover:shadow-[0_0_20px_rgba(0,85,255,0.3)] transition-colors duration-200">
            <component :is="feature.icon" class="w-5 h-5 text-[#CC1525]" />
          </div>

          <h3 class="text-[#0F2356] font-semibold mb-2">{{ feature.title }}</h3>
          <p class="text-gray-500 text-sm leading-relaxed">{{ feature.desc }}</p>

          <!-- Bottom accent line -->
          <div class="absolute bottom-0 left-0 right-0 h-px bg-linear-to-r from-transparent via-[#0055ff]/0 to-transparent group-hover:via-[#0055ff]/60 transition-all duration-500" />
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { Camera, MapPin, BarChart3, Bell, Users, Zap } from 'lucide-vue-next'

const features = [
  { icon: Camera,   title: 'Photo Reporting',    desc: 'Citizens capture and submit incidents with photos directly from their device.' },
  { icon: MapPin,   title: 'Geolocation',         desc: 'Automatic GPS tagging for precise incident location on city maps.' },
  { icon: BarChart3,title: 'AI Analytics',        desc: 'Machine learning models analyze patterns and predict future incidents.' },
  { icon: Bell,     title: 'Smart Alerts',        desc: 'Real-time notifications for city managers based on severity and proximity.' },
  { icon: Users,    title: 'Citizen Engagement',  desc: 'Track your reports, get updates, and contribute to a better city.' },
  { icon: Zap,      title: 'Priority Engine',     desc: 'AI-driven prioritization ensures critical issues are handled first.' },
]
</script>