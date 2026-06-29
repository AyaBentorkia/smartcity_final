<template>
  <section id="workflow" class="relative py-28 overflow-hidden bg-white">
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-200 h-px bg-linear-to-r from-transparent via-[#0055ff]/50 to-transparent" />

    <div class="max-w-5xl mx-auto px-6">
      <!-- Header -->
      <div class="text-center mb-20">
        <p class="text-[#CC1525] text-xs tracking-[0.3em] uppercase mb-4">Processus Simple</p>
        <h2 class="text-4xl md:text-5xl text-[#0F2356] mb-4">
          Comment Ça
          <span
            class="text-transparent font-semibold"
            style="background-image: linear-gradient(90deg, #4da6ff, #0055ff); -webkit-background-clip: text; background-clip: text;"
          >
            Fonctionne
          </span>
        </h2>
        <p class="text-white/45 max-w-lg mx-auto">
          From citizen report to resolution — a seamless, AI-powered workflow that keeps your city running smoothly.
        </p>
      </div>

      <!-- Steps -->
      <div class="relative">
        <!-- Center line -->
        <div class="absolute left-1/2 -translate-x-1/2 top-8 bottom-8 w-px bg-linear-to-b from-[#0055ff]/60 via-[#0055ff]/30 to-transparent hidden md:block" />

        <div class="flex flex-col gap-10">
          <div
            v-for="(step, i) in steps"
            :key="step.title"
            :class="[
              'flex flex-col md:flex-row items-center gap-8',
              step.side === 'right' ? 'md:flex-row-reverse' : ''
            ]"
          >
            <!-- Card -->
            <div class="md:w-[45%] bg-[#070f28]/90 border border-white/[0.07] rounded-2xl p-7 hover:border-[#0055ff]/35 transition-all duration-300 group">
              <h3 class="text-white mb-3">{{ step.title }}</h3>
              <p class="text-white/45 text-sm leading-relaxed">{{ step.desc }}</p>
            </div>

            <!-- Center icon -->
            <div class="relative z-10 shrink-0 w-14 h-14 rounded-full bg-[#061228] border-2 border-[#0055ff]/50 flex items-center justify-center shadow-[0_0_20px_rgba(0,85,255,0.35)]">
              <component :is="step.icon" class="w-6 h-6 text-[#4da6ff]" />
              <span class="absolute -top-5 text-xs text-[#0055ff]/60 font-mono">0{{ i + 1 }}</span>
            </div>

            <!-- Spacer -->
            <div class="md:w-[45%]" />
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { Smartphone, Brain, Bell, CheckCircle2 } from 'lucide-vue-next'

const steps = [
  {
    icon: Smartphone,
    title: 'Le Citoyen Signale',
    desc: "Via l'application mobile ou le portail web, le citoyen prend une photo, ajoute une description — la localisation GPS est capturée automatiquement.",
    side: 'left',
  },
  {
    icon: Brain,
    title: "L'IA Analyse",
    desc: "Notre moteur d'IA classe l'incident, évalue la gravité, détecte les doublons et attribue un score de priorité en quelques secondes.",
    side: 'right',
  },
  {
    icon: Bell,
    title: 'Les Gestionnaires Notifiés',
    desc: "Le bon service reçoit une alerte instantanée avec tous les détails, les actions suggérées et l'affectation optimale des équipes.",
    side: 'left',
  },
  {
    icon: CheckCircle2,
    title: 'Incident Résolu',
    desc: 'Les équipes de terrain résolvent le problème et mettent à jour le statut en temps réel. Le citoyen reçoit une notification de clôture.',
    side: 'right',
  },
]
</script>