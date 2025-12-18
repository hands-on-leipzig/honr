<template>
  <div class="p-4 pb-32">
    <h1 class="text-2xl font-bold mb-4">Alle Volunteer-Einsätze</h1>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-gray-200 mb-4">
      <button
        @click="activeTab = 'leaderboard'"
        :class="[
          'flex-1 py-3 text-center font-medium border-b-2 transition-colors',
          activeTab === 'leaderboard'
            ? 'border-blue-600 text-blue-600'
            : 'border-transparent text-gray-500 hover:text-gray-700'
        ]"
      >
        Bestenliste
      </button>
      <button
        @click="activeTab = 'map'"
        :class="[
          'flex-1 py-3 text-center font-medium border-b-2 transition-colors',
          activeTab === 'map'
            ? 'border-blue-600 text-blue-600'
            : 'border-transparent text-gray-500 hover:text-gray-700'
        ]"
      >
        Karte
      </button>
    </div>

    <!-- Leaderboard Tab -->
    <div v-if="activeTab === 'leaderboard'">
      <!-- Category Tabs -->
      <div class="flex bg-gray-100 rounded-lg p-1 mb-4">
        <button
          @click="leaderboardCategory = 'volunteers'"
          :class="[
            'flex-1 py-2 text-sm font-medium rounded-md transition-colors',
            leaderboardCategory === 'volunteers'
              ? 'bg-white shadow text-blue-600'
              : 'text-gray-600 hover:text-gray-800'
          ]"
        >
          Volunteers
        </button>
        <button
          @click="leaderboardCategory = 'regional-partners'"
          :class="[
            'flex-1 py-2 text-sm font-medium rounded-md transition-colors',
            leaderboardCategory === 'regional-partners'
              ? 'bg-white shadow text-blue-600'
              : 'text-gray-600 hover:text-gray-800'
          ]"
        >
          Regional Partner
        </button>
        <button
          @click="leaderboardCategory = 'coaches'"
          :class="[
            'flex-1 py-2 text-sm font-medium rounded-md transition-colors',
            leaderboardCategory === 'coaches'
              ? 'bg-white shadow text-blue-600'
              : 'text-gray-600 hover:text-gray-800'
          ]"
        >
          Coaches
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="bg-white rounded-lg shadow p-6">
        <p class="text-gray-600 text-center py-8">Laden...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="leaderboard.length === 0" class="bg-white rounded-lg shadow p-6">
        <p class="text-gray-600 text-center py-8">Noch keine Einträge vorhanden.</p>
      </div>

      <!-- Leaderboard List -->
      <div v-else class="bg-white rounded-lg shadow divide-y divide-gray-100">
        <div
          v-for="entry in leaderboard"
          :key="entry.id"
          class="flex items-center p-4"
        >
          <!-- Rank -->
          <div class="w-10 text-center">
            <span
              :class="[
                'inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold',
                entry.rank === 1 ? 'bg-yellow-100 text-yellow-700' :
                entry.rank === 2 ? 'bg-gray-200 text-gray-700' :
                entry.rank === 3 ? 'bg-orange-100 text-orange-700' :
                'bg-gray-100 text-gray-600'
              ]"
            >
              {{ entry.rank }}
            </span>
          </div>

          <!-- Name -->
          <div class="flex-1 ml-3">
            <div class="font-medium text-gray-900">
              {{ entry.display_name || entry.nickname }}
            </div>
          </div>

          <!-- Count -->
          <div class="text-right">
            <div class="text-lg font-bold text-blue-600">{{ entry.engagement_count || entry.season_count }}</div>
            <div class="text-xs text-gray-500">
              {{ leaderboardCategory === 'volunteers' ? 'Einsätze' : 'Saisons' }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Map Tab -->
    <div v-if="activeTab === 'map'">
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

      <!-- Map Container -->
      <div class="bg-white rounded-lg shadow overflow-hidden" style="height: 400px;">
        <div ref="mapContainer" class="w-full h-full"></div>
      </div>

      <!-- Legend -->
      <div v-if="heatmapData.length > 0" class="mt-4 bg-white rounded-lg shadow p-4">
        <h3 class="text-sm font-medium text-gray-700 mb-2">{{ heatmapData.length }} Standorte</h3>
        <div class="text-xs text-gray-500">
          Gesamt: {{ totalEngagements }} Einsätze
        </div>
      </div>
    </div>

    <!-- Search Overlay -->
    <div class="fixed bottom-24 left-0 right-0 px-4 pb-4">
      <div class="bg-white rounded-lg shadow-lg p-4">
        <input
          type="text"
          placeholder="Benutzer suchen..."
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>
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

const activeTab = ref<'leaderboard' | 'map'>('leaderboard')

// Leaderboard state
const leaderboardCategory = ref<'volunteers' | 'regional-partners' | 'coaches'>('volunteers')
const leaderboard = ref<any[]>([])
const loading = ref(false)

// Map state
const mapContainer = ref<HTMLElement | null>(null)
const mapInstance = ref<L.Map | null>(null)
const heatLayer = ref<any>(null)
const heatmapData = ref<any[]>([])
const mapOptions = ref<{ programs: any[], seasons: any[], levels: any[] }>({ programs: [], seasons: [], levels: [] })
const mapFilters = ref({ program_id: '', season_id: '', level_id: '' })
const mapLoading = ref(false)

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

async function loadLeaderboard() {
  loading.value = true
  try {
    const response = await apiClient.get(`/leaderboard/${leaderboardCategory.value}`)
    leaderboard.value = response.data
  } catch (err) {
    console.error('Failed to load leaderboard', err)
    leaderboard.value = []
  } finally {
    loading.value = false
  }
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
  mapLoading.value = true
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
  } finally {
    mapLoading.value = false
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

  // Remove existing heat layer
  if (heatLayer.value) {
    mapInstance.value.removeLayer(heatLayer.value)
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

  // Add markers for locations
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
    .addTo(mapInstance.value!)
  })
}

watch(leaderboardCategory, () => {
  loadLeaderboard()
})

watch(activeTab, async (newTab) => {
  if (newTab === 'map') {
    await loadMapOptions()
    await nextTick()
    initMap()
    loadHeatmapData()
  }
})

watch(mapFilters, () => {
  if (activeTab.value === 'map') {
    loadHeatmapData()
  }
}, { deep: true })

onMounted(() => {
  loadLeaderboard()
})
</script>

<style>
.leaflet-container {
  font-family: inherit;
}
</style>
