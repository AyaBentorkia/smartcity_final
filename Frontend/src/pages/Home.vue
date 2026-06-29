<template>
  <BackgroundGlows />
  <div class="relative z-10">
    <Navbar />
    <Herosection />
    <Features />
    <PlatformSection />
    <HowItWorksSection />
    <MobileSection />
    <Footer />
  </div>
</template>

<script>
import BackgroundGlows from "../components/BackgroundGlows.vue";
import Features from "../components/Features.vue";
import Herosection from "../components/Herosection.vue";
import HowItWorksSection from "../components/HowItWorksSection.vue";
import Navbar from "../components/Navbar.vue";
import PlatformSection from "../components/PlatformSection.vue";
import MobileSection from "../components/MobileAppSection.vue";
import Footer from "../components/Footer.vue";
import "../style.css";

export default {
  components: {
    Navbar,
    Herosection,
    Features,
    PlatformSection,
    HowItWorksSection,
    MobileSection,
    Footer,
  },
}
</script>