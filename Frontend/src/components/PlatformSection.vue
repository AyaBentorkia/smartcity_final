<template>
  <section id="platform" class="relative py-28 overflow-hidden bg-[#030b1a]">
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-200 h-px bg-linear-to-r from-transparent via-[#ff6820]/30 to-transparent" />

    <div class="max-w-7xl mx-auto px-6">
      <div class="grid lg:grid-cols-2 gap-16 items-center">

        <!-- Left: text -->
        <div>
          <p class="text-[#CC1525] text-xs tracking-[0.3em] uppercase mb-5">Platform</p>
          <h2 class="text-4xl md:text-5xl text-white mb-6 leading-tight">
            Tableau de Bord IA<br />pour les Responsables Municipaux
          </h2>
          <p class="text-white/45 mb-10 leading-relaxed">
            Les responsables municipaux disposent d'une vue complète de tous les incidents, d'analyses générées par l'IA, de cartes thermiques et d'analyses prédictives — pour des décisions fondées sur les données.
          </p>

          <!-- Stats -->
          <div class="flex flex-col gap-6">
            <div
              v-for="s in stats"
              :key="s.value"
              class="flex items-center gap-5"
            >
              <span
                class="text-4xl min-w-18 text-transparent"
                style="background-image: linear-gradient(135deg, #4da6ff, #0055ff); -webkit-background-clip: text; background-clip: text;"
              >
                {{ s.value }}
              </span>
              <div class="h-px flex-1 bg-white/[0.07]" />
              <span class="text-white/55 text-sm max-w-xs text-right">{{ s.label }}</span>
            </div>
          </div>
        </div>

        <!-- Right: dashboard mockup -->
        <div class="relative">
          <!-- Glow halo -->
          <div class="absolute -inset-4 bg-[#0055ff]/10 rounded-3xl blur-2xl" />
          <div class="relative rounded-2xl overflow-hidden border border-[#0055ff]/20 shadow-[0_0_60px_rgba(0,85,255,0.15)]">
            <!-- Fake browser bar -->
            <div class="bg-[#061228] px-4 py-2.5 flex items-center gap-2 border-b border-white/6">
              <div class="flex gap-1.5">
                <div class="w-2.5 h-2.5 rounded-full bg-[#ff5f57]" />
                <div class="w-2.5 h-2.5 rounded-full bg-[#ffbd2e]" />
                <div class="w-2.5 h-2.5 rounded-full bg-[#28c840]" />
              </div>
              <div class="flex-1 mx-4 bg-white/5 rounded px-3 py-0.5">
                <span class="text-white/30 text-xs">app.incitya.io/dashboard</span>
              </div>
            </div>
            <img :src="DASHBOARD_IMG" alt="Dashboard" class="w-full h-auto" />
            <div class="absolute inset-0 top-9 bg-linear-to-t from-[#040c1e]/30 via-transparent to-transparent pointer-events-none" />
          </div>
        </div>

      </div>
    </div>
  </section>
</template>

<script setup>
const DASHBOARD_IMG = 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxkYXNoYm9hcmQlMjBhbmFseXRpY3MlMjBkYXJrJTIwVUklMjB0ZWNobm9sb2d5JTIwc2NyZWVufGVufDF8fHx8MTc3Njg5MDY2N3ww&ixlib=rb-4.1.0&q=80&w=1080'

const stats = [
  { value: '87%', label: 'Temps de réponse réduit ' },
  { value: '3x',  label: "Plus d'incidents résolus par jour en moyenne" },
  { value: '94%', label: 'Satisfaction citoyenne ' },
]
</script>