<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import { useCategories } from '../../composables/useCategories'
import { useIncidents } from '../../composables/useIncidents'

// ─── Constantes ──────────────────────────────────────────────────────────────

const statusColors = {
  pending:   '#ef4444',
  validated: '#f59e0b',
  rejected:  '#6b7280',
  assigned:  '#3b82f6',
  resolved:  '#22c55e',
}

const statusLabels = {
  pending:   'En attente',
  validated: 'Validé',
  rejected:  'Rejeté',
  assigned:  'Assigné',
  resolved:  'Résolu',
}

const priorityLabels = {
  low:      'Faible',
  medium:   'Moyen',
  high:     'Élevé',
  critical: 'Critique',
}

const periods = [
  { value: 'all', label: 'Toutes les périodes' },
  { value: '7',   label: '7 derniers jours' },
  { value: '30',  label: '30 derniers jours' },
  { value: '90',  label: '3 derniers mois' },
]

// ─── Composables ─────────────────────────────────────────────────────────────

const { categories, fetchCategories } = useCategories()
// BUG 1 CORRIGÉ : on utilise incidents (pas categories) pour la carte
const { incidents, fetchIncidentsNearBy } = useIncidents()

onMounted(() => {
  fetchCategories()
  fetchIncidentsNearBy()
})

// ─── État réactif ─────────────────────────────────────────────────────────────

const categoryFilter = ref('all')
const periodFilter   = ref('all')
const mapContainer   = ref(null)

let map   = null
let layer = null

// ─── Données filtrées ────────────────────────────────────────────────────────

const filtered = computed(() => {
  // BUG 1 CORRIGÉ : source = incidents (les incidents ont lat/lng, pas les catégories)
  let data = incidents.value || []

  if (categoryFilter.value !== 'all') {
    // BUG 2 CORRIGÉ : on compare avec l'id de catégorie (adapter selon votre API)
    data = data.filter((i) => i.category_id === categoryFilter.value || i.category === categoryFilter.value)
  }

  if (periodFilter.value !== 'all') {
    const days   = parseInt(periodFilter.value)
    const cutoff = new Date()
    cutoff.setDate(cutoff.getDate() - days)
    data = data.filter((i) => new Date(i.createdAt) >= cutoff)
  }

  // Sécurité : on exclut les incidents sans coordonnées valides
  return data.filter((i) => {
    const lat = parseFloat(i.lat ?? i.latitude)
    const lng = parseFloat(i.lng ?? i.longitude)
    return !isNaN(lat) && !isNaN(lng)
  })
})

// ─── Helpers cartographiques ─────────────────────────────────────────────────

function getCoords(inc) {
  // Normalise lat/lng selon le nom de champ retourné par l'API
  return {
    lat: parseFloat(inc.lat ?? inc.latitude),
    lng: parseFloat(inc.lng ?? inc.longitude),
  }
}

function createCircleIcon(color) {
  return L.divIcon({
    className: '',
    html: `<div style="width:14px;height:14px;border-radius:50%;background:${color};border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,0.3);"></div>`,
    iconSize:   [14, 14],
    iconAnchor: [7, 7],
  })
}

function createClusterIcon(count) {
  const size = count > 10 ? 44 : count > 5 ? 36 : 30
  return L.divIcon({
    className: '',
    html: `<div style="width:${size}px;height:${size}px;border-radius:50%;background:hsl(200,85%,40%);color:white;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);">${count}</div>`,
    iconSize:   [size, size],
    iconAnchor: [size / 2, size / 2],
  })
}

function buildPopup(inc) {
  const color  = statusColors[inc.status] ?? '#6b7280'
  const { lat, lng } = getCoords(inc)
  return `
    <div style="min-width:200px;font-family:inherit;">
      <p style="font-weight:600;font-size:13px;margin:0 0 6px;">${inc.title ?? 'Incident'}</p>
      <p style="font-size:12px;color:#666;margin:0 0 4px;">📍 ${inc.location ?? `${lat.toFixed(4)}, ${lng.toFixed(4)}`}</p>
      <p style="font-size:12px;margin:0 0 4px;">
        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:${color};margin-right:4px;"></span>
        ${statusLabels[inc.status] ?? inc.status}
      </p>
      <p style="font-size:12px;margin:0 0 4px;">🔴 Urgence: <strong>${priorityLabels[inc.urgency_level] ?? inc.urgency_level ?? 'N/A'}</strong></p>
      <p style="font-size:11px;color:#888;margin:0;">📅 ${new Date(inc.created_at).toLocaleDateString('fr-FR')}</p>
    </div>
  `
}

function clusterMarkers(list, zoom) {
  if (zoom >= 14) return list.map((i) => ({ type: 'single', incident: i }))

  const gridSize = 0.01 * Math.pow(2, 13 - zoom)
  const clusters = new Map()

  list.forEach((inc) => {
    const { lat, lng } = getCoords(inc)
    const key = `${Math.floor(lat / gridSize)}_${Math.floor(lng / gridSize)}`
    if (!clusters.has(key)) clusters.set(key, [])
    clusters.get(key).push(inc)
  })

  return Array.from(clusters.values()).map((group) => {
    if (group.length === 1) return { type: 'single', incident: group[0] }
    const lat = group.reduce((s, i) => s + parseFloat(i.lat ?? i.latitude), 0) / group.length
    const lng = group.reduce((s, i) => s + parseFloat(i.lng ?? i.longitude), 0) / group.length
    return { type: 'cluster', lat, lng, count: group.length }
  })
}

// ─── Logique carte ────────────────────────────────────────────────────────────

function addMarkers() {
  if (!map) return
  if (layer) map.removeLayer(layer)
  layer = L.layerGroup()

  const zoom  = map.getZoom()
  const items = clusterMarkers(filtered.value, zoom)

  items.forEach((item) => {
    if (item.type === 'single') {
      const inc = item.incident
      const { lat, lng } = getCoords(inc)
      const marker = L.marker([lat, lng], {
        icon: createCircleIcon(statusColors[inc.status] ?? '#6b7280'),
      })
      marker.bindPopup(buildPopup(inc))
      marker.bindTooltip(inc.title ?? 'Incident', { direction: 'top', offset: [0, -8] })
      layer.addLayer(marker)
    } else {
      const marker = L.marker([item.lat, item.lng], {
        icon: createClusterIcon(item.count),
      })
      marker.bindTooltip(`${item.count} incidents`, { direction: 'top', offset: [0, -8] })
      marker.on('click', () => map.setView([item.lat, item.lng], zoom + 2))
      layer.addLayer(marker)
    }
  })

  layer.addTo(map)
}

function fitBounds() {
  if (map && filtered.value.length > 0) {
    const coords = filtered.value.map((i) => {
      const { lat, lng } = getCoords(i)
      return [lat, lng]
    })
    const bounds = L.latLngBounds(coords)
    map.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 })
  }
}

// ─── Cycle de vie ─────────────────────────────────────────────────────────────

onMounted(() => {
  map = L.map(mapContainer.value).setView([48.856, 2.347], 13)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap',
  }).addTo(map)
  map.on('zoomend', addMarkers)
  addMarkers()
  fitBounds()
})

onUnmounted(() => {
  if (map) { map.remove(); map = null }
})

watch(filtered, () => {
  addMarkers()
  fitBounds()
})
</script>

<template>
  <div class="rounded-xl border border-border bg-card text-card-foreground shadow-sm">

    <div class="flex flex-col space-y-1.5 p-6 pb-3">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

        <h3 class="text-base font-semibold flex items-center gap-2 leading-none tracking-tight">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-primary" viewBox="0 0 24 24"
               fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z"/>
            <circle cx="12" cy="10" r="3"/>
          </svg>
          Carte des incidents
        </h3>

        <div class="flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-muted-foreground" viewBox="0 0 24 24"
               fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
          </svg>

          <select
            v-model="categoryFilter"
            class="h-8 w-[150px] rounded-md border border-input bg-background px-2 text-xs
                   focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
          >
            <option value="all">Toutes catégories</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">
              {{ c.name }}
            </option>
          </select>

          <select
            v-model="periodFilter"
            class="h-8 w-[150px] rounded-md border border-input bg-background px-2 text-xs
                   focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
          >
            <option v-for="p in periods" :key="p.value" :value="p.value">
              {{ p.label }}
            </option>
          </select>
        </div>
      </div>

      <div class="flex flex-wrap gap-3 mt-2">
        <div
          v-for="(label, status) in statusLabels"
          :key="status"
          class="flex items-center gap-1.5 text-xs text-muted-foreground"
        >
          <span class="inline-block w-2.5 h-2.5 rounded-full" :style="{ background: statusColors[status] }" />
          {{ label }}
        </div>
      </div>
    </div>

    <div class="p-6 pt-0">
      <div ref="mapContainer" class="w-full rounded-lg overflow-hidden border border-border" style="height: 420px" />
      <p class="text-xs text-muted-foreground mt-2">
        {{ filtered.length }} incident{{ filtered.length > 1 ? 's' : '' }}
        affiché{{ filtered.length > 1 ? 's' : '' }}
      </p>
    </div>
  </div>
</template>