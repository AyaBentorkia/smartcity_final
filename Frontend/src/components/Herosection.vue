<template>
  <!-- ─── HERO ─── -->
  <section class="relative min-h-[88vh] flex items-center overflow-hidden">

    <!-- Fond photo -->
    <div class="absolute inset-0 z-0">
      <img
        :src="HERO_BG"
        alt="Tunisie"
        class="w-full h-full object-cover"
      />
      <div class="absolute inset-0 bg-gradient-to-r from-[#0F2356]/93 via-[#0F2356]/75 to-[#0F2356]/30" />
      <!-- Grille diagonale subtile -->
      <div
        class="absolute inset-0 opacity-[0.03]"
        style="background-image: repeating-linear-gradient(45deg, #fff 0px, #fff 1px, transparent 1px, transparent 60px);"
      />
    </div>

    <!-- Contenu -->
    <div class="relative z-10 max-w-7xl mx-auto px-6 py-24 grid lg:grid-cols-2 gap-12 items-center w-full">

      <!-- Colonne gauche -->
      <div>
        <!-- Badge -->
        <div class="inline-flex items-center gap-2 border border-white/20 bg-white/10 backdrop-blur-sm rounded px-3 py-1.5 mb-8">
          <div class="w-2 h-2 rounded-full bg-[#CC1525] animate-pulse" />
          <span class="text-xs text-white/80 tracking-widest uppercase">
            Gestion Urbaine Intelligente — Tunisie
          </span>
        </div>

        <!-- Titre -->
        <h1 class="text-4xl md:text-6xl text-white leading-[1.1] mb-6 font-light">
          Des Villes Plus Sûres,<br />
          <span class="text-[#E8354A] font-semibold">Des Communautés</span><br />
          <span class="text-white">Plus Fortes</span>
        </h1>

        <!-- Sous-titre -->
        <p class="text-white/65 text-base md:text-lg max-w-lg mb-10 leading-relaxed">
          Une plateforme intelligente de gestion préventive des incidents urbains.
          Les citoyens signalent, l'IA analyse, les communes agissent — en temps réel.
        </p>

        <!-- CTAs -->
        <div class="flex flex-wrap gap-3 mb-12">
          <button
            @click="router.push('/login')"
            class="flex items-center gap-2 bg-[#CC1525] hover:bg-[#a81120] text-white px-7 py-3 rounded text-sm tracking-wide transition-colors duration-200 shadow-md"
          >
            Signaler un Incident
            <ArrowRight class="w-4 h-4" />
          </button>
          <button
            @click="router.push('/login')"
            class="flex items-center gap-2 bg-white/10 hover:bg-white/20 border border-white/30 text-white px-7 py-3 rounded text-sm tracking-wide transition-colors duration-200 backdrop-blur-sm"
          >
            Accéder au Tableau de Bord
          </button>
        </div>

        <!-- Mini stats -->
        <div class="flex flex-wrap gap-8">
          <div
            v-for="stat in miniStats"
            :key="stat.val"
            class="border-l-2 border-[#CC1525] pl-4"
          >
            <p class="text-white text-sm font-medium">{{ stat.val }}</p>
            <p class="text-white/45 text-xs mt-0.5">{{ stat.label }}</p>
          </div>
        </div>
      </div>

      <!-- Colonne droite : cartes flottantes -->
      <div class="hidden lg:flex flex-col gap-3 items-end">

        <!-- Carte incident actif -->
        

        <!-- Carte analyse IA -->
        <div class="bg-[#0F2356] rounded-lg shadow-xl p-5 w-72">
          <div class="flex items-center gap-2 mb-3">
            <div class="w-7 h-7 rounded bg-[#CC1525]/20 flex items-center justify-center">
              <Brain class="w-4 h-4 text-[#E8354A]" />
            </div>
            <p class="text-white/80 text-xs">Analyse IA</p>
          </div>
          <p class="text-white text-sm mb-3">
            Zone à risque détectée : augmentation de 34% des signalements dans le secteur Nord-Ouest de Sfax.
          </p>
          <div class="w-full bg-white/10 rounded-full h-1.5">
            <div class="bg-[#CC1525] h-1.5 rounded-full" style="width: 72%" />
          </div>
          <p class="text-white/40 text-xs mt-1">Niveau de confiance : 72%</p>
        </div>

        
      </div>
    </div>

    <!-- Indicateur scroll -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-10 flex flex-col items-center gap-1 opacity-40">
      <span class="text-xs text-white/60 tracking-widest uppercase">Défiler</span>
      <ChevronDown class="w-4 h-4 text-white animate-bounce" />
    </div>
  </section>

 
</template>

<script setup>
import { ArrowRight, MapPin, Brain, CheckCircle2, ChevronDown, ChevronRight } from 'lucide-vue-next'
import { useRouter } from 'vue-router'

const router = useRouter()

const HERO_BG = 'https://images.unsplash.com/photo-1726428977822-cd86460ce390?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxUdW5pc2lhJTIwVHVuaXMlMjBjaXR5JTIwYXJjaGl0ZWN0dXJlJTIwbW9kZXJuJTIwZ292ZXJubWVudHxlbnwxfHx8fDE3NzY5NTQ0MTZ8MA&ixlib=rb-4.1.0&q=80&w=1080'

const miniStats = [
  { val: '24 Gouvernorats', label: 'Couverts' },
  { val: '+ 350 Communes',  label: 'Connectées' },
  { val: 'Temps Réel',      label: 'Traitement IA' },
]

</script>