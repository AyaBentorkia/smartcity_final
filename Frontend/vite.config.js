import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  plugins: [
    vue(),
    tailwindcss(),
  ],
  build: {
    rollupOptions: {
      output: {
        manualChunks: {
          'vue-vendor':   ['vue', 'vue-router', 'pinia'],
          'chart-vendor': ['echarts', 'vue-echarts'],
          'map-vendor':   ['leaflet'],
          'ui-vendor':    ['lucide-vue-next'],
        }
      }
    },
    chunkSizeWarningLimit: 600,
  }
})