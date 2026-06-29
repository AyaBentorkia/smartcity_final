<script setup>
import { computed } from 'vue';
import { cn } from "../../lib/utils";
import { UserRole } from '../../constants/UserRole'

const props = defineProps({
  variant: {
    type: String,
    default: 'default', // default, secondary, destructive, outline
  },
  class: { type: String, default: '' }
});

const badgeVariants = {
  default: "border-transparent bg-primary text-primary-foreground hover:bg-primary/80",
  secondary: "border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80",
  destructive: "border-transparent bg-destructive text-destructive-foreground hover:bg-destructive/80",
  outline: "text-foreground border-border",

  // 🔥 catégories métier
  incendie: "bg-red-500/15 text-red-400 border-red-500/30",
  voirie: "bg-orange-500/15 text-orange-400 border-orange-500/30",
  electricite: "bg-yellow-500/15 text-yellow-400 border-yellow-500/30",
  eau: "bg-blue-500/15 text-blue-400 border-blue-500/30",
  environnement: "bg-green-500/15 text-green-400 border-green-500/30",
  dechets: "bg-gray-500/15 text-gray-300 border-gray-500/30",
  mobilier: "bg-purple-500/15 text-purple-400 border-purple-500/30",
 // ── Status variants ──────────────────────────────
  'status-reported':    'bg-blue-50 text-blue-700',
  'status-validated':   'bg-blue-50 text-blue-700',
  'status-in_progress': 'bg-purple-50 text-purple-700',
  'status-resolved':    'bg-green-50 text-green-700',
  'status-rejected':    'bg-red-50 text-red-700',

 [UserRole.CITIZEN]: "bg-blue-500/15 text-blue-400 border-blue-500/30",
  [UserRole.AGENT]: "bg-violet-500/15 text-violet-400 border-violet-500/30",
  [UserRole.ADMIN_MUNICIPAL]: "bg-amber-500/15 text-amber-400 border-amber-500/30",
  [UserRole.SUPER_ADMIN]: "bg-rose-500/15 text-rose-400 border-rose-500/30",
};

</script>

<template>
 <div
    :class="cn(
      'inline-flex items-center gap-1.5 rounded px-2.5 py-1 text-xs font-medium transition-colors',
      badgeVariants[props.variant] ?? badgeVariants.default,
      props.class
    )"
    v-bind="$attrs"
  >
    <slot />
  </div>
</template>
 