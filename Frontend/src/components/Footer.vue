<template>
  <!-- ── CONTACT / CTA BAND ── -->
  <section id="contact" class="relative py-24 overflow-hidden bg-[#030b1a]">
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-200 h-px bg-linear-to-r from-transparent via-[#0055ff]/50 to-transparent" />

    <!-- Background glow -->
    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
      <div class="w-150 h-75 bg-[#0055ff]/8 rounded-full blur-3xl" />
    </div>

    <div class="relative z-10 max-w-4xl mx-auto px-6 text-center">
      <p class="text-[#CC1525] text-xs tracking-[0.3em] uppercase mb-5">Rejoignez la Plateforme</p>
      <h2 class="text-4xl md:text-6xl text-white mb-6 leading-tight">
        Modernisez la Gestion de
        <span
          class="text-transparent"
          style="background-image: linear-gradient(90deg, #CC1525, white); -webkit-background-clip: text; background-clip: text;"
        >
           Votre Commune
        </span>
      </h2>
      <p class="text-white/45 mb-10 max-w-xl mx-auto">
Rejoignez les communes tunisiennes qui utilisent Incitya pour gérer les incidents urbains plus rapidement, plus intelligemment et de manière plus transparente.      </p>
      <div class="flex flex-wrap justify-center gap-4">
        <button class="flex items-center gap-2 bg-[#CC1525] hover:bg-[#a81120]   text-white px-8 py-3.5 rounded text-sm tracking-wide transition-all duration-200 shadow-[0_0_30px_rgba(255,104,32,0.35)] hover:shadow-[0_0_40px_rgba(255,104,32,0.55)]">
          Request a Demo
          <ArrowRight class="w-4 h-4" />
        </button>
        <button class="flex items-center gap-2 bg-white/6 hover:bg-white/10 border border-white/20 text-white px-8 py-3.5 rounded text-sm tracking-wide transition-all duration-200">
          Contact Sales
        </button>
      </div>
    </div>
  </section>

  <!-- ── FOOTER ── -->
  <footer class="relative border-t border-white/6 bg-[#030b1a] py-16">
    <div class="max-w-7xl mx-auto px-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">

        <!-- Brand -->
        <div class="md:col-span-1">
          <div class="flex items-center gap-2 mb-4">
            <div>
                  <img :src="logo" alt="Logo" class="w-50">

        </div>
          </div>
          <p class="text-white/35 text-sm leading-relaxed mb-6">
            AI-powered urban management platform connecting citizens and city services for smarter, safer communities.
          </p>
          <div class="flex gap-3">
            <div
              v-for="(icon, i) in socialIcons"
              :key="i"
              class="w-8 h-8 rounded-full bg-white/5 border border-white/8 flex items-center justify-center hover:bg-[#0055ff]/20 hover:border-[#0055ff]/40 transition-all duration-200 cursor-pointer"
            >
              <component :is="icon" class="w-3.5 h-3.5 text-white/50" />
            </div>
          </div>
        </div>

        <!-- Product -->
        <div>
          <p class="text-white text-sm mb-5 tracking-wide">Product</p>
          <ul class="flex flex-col gap-3">
            <li v-for="item in productLinks" :key="item">
              <a href="#" class="text-white/35 text-sm hover:text-white/75 transition-colors">{{ item }}</a>
            </li>
          </ul>
        </div>

        <!-- Resources -->
        <div>
          <p class="text-white text-sm mb-5 tracking-wide">Resources</p>
          <ul class="flex flex-col gap-3">
            <li v-for="item in resourceLinks" :key="item">
              <a href="#" class="text-white/35 text-sm hover:text-white/75 transition-colors">{{ item }}</a>
            </li>
          </ul>
        </div>

        <!-- Contact -->
        <div>
          <p class="text-white text-sm mb-5 tracking-wide">Contact</p>
          <ul class="flex flex-col gap-4">
            <li class="flex items-center gap-3">
              <Mail class="w-4 h-4 text-[#4da6ff]" />
              <span class="text-white/35 text-sm">contact@incitya.io</span>
            </li>
            <li class="flex items-center gap-3">
              <Phone class="w-4 h-4 text-[#4da6ff]" />
              <span class="text-white/35 text-sm">+1 (555) 123-4567</span>
            </li>
            <li class="flex items-start gap-3">
              <MapPinned class="w-4 h-4 text-[#4da6ff] mt-0.5" />
              <span class="text-white/35 text-sm">Innovation Hub, Tech District<br />Smart City, SC 10001</span>
            </li>
          </ul>
        </div>

      </div>

      <!-- Bottom bar -->
      <div class="pt-8 border-t border-white/5 flex flex-col md:flex-row items-center justify-between gap-4">
        <p class="text-white/25 text-sm">© 2026 Incitya. All rights reserved.</p>
        <div class="flex gap-6">
          <a v-for="item in legalLinks" :key="item" href="#" class="text-white/25 text-xs hover:text-white/60 transition-colors">
            {{ item }}
          </a>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { ArrowRight, Shield, Mail, Phone, MapPinned, Twitter, Linkedin, Github } from 'lucide-vue-next'
import logo from "../assets/logo_incitya.png";

const socialIcons = [Twitter, Linkedin, Github]

const productLinks  = ['Features', 'Mobile App', 'AI Analytics', 'Dashboard', 'API Access', 'Pricing']
const resourceLinks = ['Documentation', 'Case Studies', 'Blog', 'Community', 'Support Center', 'Changelog']
const legalLinks    = ['Privacy Policy', 'Terms of Service', 'Cookie Policy']
</script>