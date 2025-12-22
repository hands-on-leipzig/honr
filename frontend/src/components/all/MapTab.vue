<template>
  <div>
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-4">
      <div class="grid grid-cols-3 gap-2">
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Programm</label>
          <select v-model="mapFilters.program_id" @change="onProgramChange" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
            <option value="">Alle</option>
            <option v-for="p in mapOptions.programs" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Saison</label>
          <select v-model="mapFilters.season_id" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
            <option value="">Alle</option>
            <option v-for="s in filteredSeasons" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Level</label>
          <select v-model="mapFilters.level_id" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
            <option value="">Alle</option>
            <option v-for="l in mapOptions.levels" :key="l.id" :value="l.id">{{ l.name }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="bg-white rounded-lg shadow p-3 mb-4 flex justify-between text-sm">
      <span class="text-gray-700"><strong>{{ heatmapData.length }}</strong> Standorte</span>
      <span class="text-gray-700"><strong>{{ totalEngagements }}</strong> Einsätze</span>
    </div>

    <!-- Map Container -->
    <div class="bg-white rounded-lg shadow overflow-hidden" style="height: 400px;">
      <div ref="mapContainer" class="w-full h-full"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, computed, nextTick } from 'vue'
import apiClient from '@/api/client'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
// @ts-ignore
import 'leaflet.heat'

const mapContainer = ref<HTMLElement | null>(null)
const mapInstance = ref<L.Map | null>(null)
const heatLayer = ref<any>(null)
const markersLayer = ref<L.LayerGroup | null>(null)
const heatmapData = ref<any[]>([])
const mapOptions = ref<{ programs: any[], seasons: any[], levels: any[] }>({ programs: [], seasons: [], levels: [] })
const mapFilters = ref({ program_id: '', season_id: '', level_id: '' })

const filteredSeasons = computed(() => {
  if (!mapFilters.value.program_id) return mapOptions.value.seasons
  return mapOptions.value.seasons.filter((s: any) => s.first_program_id == mapFilters.value.program_id)
})

const totalEngagements = computed(() => {
  return heatmapData.value.reduce((sum, loc) => sum + loc.engagement_count, 0)
})

function onProgramChange() {
  mapFilters.value.season_id = ''
}

async function loadMapOptions() {
  try {
    const response = await apiClient.get('/heatmap/options')
    mapOptions.value = response.data
  } catch (err) {
    console.error('Failed to load map options', err)
  }
}

async function loadHeatmapData() {
  try {
    const params = new URLSearchParams()
    if (mapFilters.value.program_id) params.append('program_id', mapFilters.value.program_id)
    if (mapFilters.value.season_id) params.append('season_id', mapFilters.value.season_id)
    if (mapFilters.value.level_id) params.append('level_id', mapFilters.value.level_id)
    
    const response = await apiClient.get(`/heatmap?${params.toString()}`)
    heatmapData.value = response.data
    updateHeatmap()
  } catch (err) {
    console.error('Failed to load heatmap data', err)
    heatmapData.value = []
  }
}

function initMap() {
  if (!mapContainer.value || mapInstance.value) return

  // Center on D-A-CH region
  mapInstance.value = L.map(mapContainer.value).setView([48.5, 10.5], 5)

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(mapInstance.value)
}

function updateHeatmap() {
  if (!mapInstance.value) return

  // Remove existing layers
  if (heatLayer.value) {
    mapInstance.value.removeLayer(heatLayer.value)
    heatLayer.value = null
  }
  if (markersLayer.value) {
    mapInstance.value.removeLayer(markersLayer.value)
    markersLayer.value = null
  }

  if (heatmapData.value.length === 0) return

  // Create heat data: [lat, lng, intensity]
  const maxCount = Math.max(...heatmapData.value.map(d => d.engagement_count))
  const heatData = heatmapData.value.map(loc => [
    parseFloat(loc.latitude),
    parseFloat(loc.longitude),
    loc.engagement_count / maxCount // Normalize intensity
  ])

  // @ts-ignore
  heatLayer.value = L.heatLayer(heatData, {
    radius: 25,
    blur: 15,
    maxZoom: 10,
    max: 1.0,
    gradient: { 0.4: 'blue', 0.6: 'cyan', 0.7: 'lime', 0.8: 'yellow', 1: 'red' }
  }).addTo(mapInstance.value)

  // Add markers for locations in a layer group
  markersLayer.value = L.layerGroup().addTo(mapInstance.value)
  heatmapData.value.forEach(loc => {
    L.circleMarker([parseFloat(loc.latitude), parseFloat(loc.longitude)], {
      radius: 6,
      fillColor: '#3b82f6',
      color: '#1d4ed8',
      weight: 1,
      opacity: 1,
      fillOpacity: 0.8
    })
    .bindPopup(`<strong>${loc.name}</strong>${loc.city ? '<br>' + loc.city : ''}<br>${loc.engagement_count} Einsätze`)
    .addTo(markersLayer.value!)
  })
}

watch(mapFilters, () => {
  loadHeatmapData()
}, { deep: true })

onMounted(async () => {
  await loadMapOptions()
  await nextTick()
  initMap()
  loadHeatmapData()
})
</script>

<style scoped>
.leaflet-container {
  font-family: inherit;
}
</style>


