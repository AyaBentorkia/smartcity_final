<template>
  <component
    :is="as"
    v-bind="filteredAttrs"
    :class="classes"
  >
    <slot />
  </component>
</template>

<script setup>
import { computed, useAttrs } from "vue";
import { cva } from "class-variance-authority";
import { twMerge } from "tailwind-merge";

const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0F2356]/30 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0",
  {
    variants: {
      variant: {
        // ── Primary: navy ──────────────────────────────
        default:     "bg-[#0F2356] text-white hover:bg-[#162d63] shadow-sm",
        // ── Danger: red ───────────────────────────────
        destructive: "bg-[#CC1525] text-white hover:bg-[#a81120] shadow-sm",
        // ── Outline ───────────────────────────────────
        outline:     "border border-gray-200 bg-[#F7F8FB] text-[#0F2356] hover:border-[#0F2356] hover:bg-white",
        // ── Secondary / muted ─────────────────────────
        secondary:   "bg-gray-100 text-gray-700 hover:bg-gray-200",
        // ── Ghost ─────────────────────────────────────
        ghost:       "bg-transparent text-gray-500 hover:bg-gray-100 hover:text-[#0F2356]",
        // ── Link ──────────────────────────────────────
        link:        "bg-transparent text-[#CC1525] underline-offset-4 hover:underline p-0 h-auto",
      },
      size: {
        default: "h-10 px-4 py-2",
        sm:      "h-8 rounded px-3 text-xs",
        lg:      "h-11 rounded px-8",
        icon:    "h-8 w-8 rounded p-1.5",
      },
    },
    defaultVariants: {
      variant: "default",
      size:    "default",
    },
  }
);

defineOptions({ inheritAttrs: false });

const props = defineProps({
  variant: { type: String, default: "default" },
  size:    { type: String, default: "default" },
  as:      { type: [String, Object], default: "button" },
});

const attrs = useAttrs();

const filteredAttrs = computed(() => {
  const { class: _, ...rest } = attrs;
  return rest;
});

const classes = computed(() =>
  twMerge(
    buttonVariants({ variant: props.variant, size: props.size }),
    attrs.class
  )
);
</script>