<template>
  <div class="p-4 pb-32">
    <!-- Header: Nickname -->
    <div class="mb-4">
      <h1 class="text-2xl font-bold">{{ userStore.user?.nickname || 'Meine Auszeichnungen' }}</h1>
      <p v-if="isCurrentUser" class="text-xs text-gray-500 mt-1">Zum Ändern geh in die Einstellungen</p>
    </div>

    <!-- Short Bio -->
    <div v-if="userStore.user?.short_bio" class="bg-white rounded-lg shadow p-4 mb-4">
      <p class="text-gray-700">{{ userStore.user.short_bio }}</p>
      <p v-if="isCurrentUser" class="text-xs text-gray-500 mt-2">Zum Ändern geh in die Einstellungen</p>
    </div>

    <!-- Leaderboard Ranks -->
    <div v-if="hasAnyRank" class="bg-white rounded-lg shadow p-4 mb-4">
      <div class="space-y-2">
        <div v-if="volunteerRank" class="text-sm">
          <span class="font-medium">Volunteer Rang {{ volunteerRank }}</span>
        </div>
        <div v-if="regionalPartnerRank" class="text-sm">
          <span class="font-medium">Regional-Partner Rang {{ regionalPartnerRank }}</span>
        </div>
        <div v-if="coachRank" class="text-sm">
          <span class="font-medium">Coach Rang {{ coachRank }}</span>
        </div>
      </div>
    </div>

    <!-- Program Logos -->
    <div v-if="programsWithLogos.length > 0" class="bg-white rounded-lg shadow p-4 mb-4">
      <div class="flex flex-wrap gap-3">
        <img
          v-for="program in programsWithLogos"
          :key="program.id"
          :src="getLogoUrl(program.logo_path)"
          :alt="program.name"
          class="w-12 h-12 object-contain"
          @error="handleImageError"
        />
      </div>
    </div>

    <!-- Season Logos -->
    <div v-if="seasonsWithLogos.length > 0" class="bg-white rounded-lg shadow p-4 mb-4">
      <div class="flex flex-wrap gap-3">
        <img
          v-for="season in seasonsWithLogos"
          :key="season.id"
          :src="getLogoUrl(season.logo_path)"
          :alt="season.name"
          class="w-12 h-12 object-contain"
          @error="handleImageError"
        />
      </div>
    </div>

    <!-- Country Flags & Map -->
    <div v-if="uniqueCountries.length > 0 || engagementLocations.length > 0" class="bg-white rounded-lg shadow p-4 mb-4">
      <div v-if="uniqueCountries.length > 0" class="flex flex-wrap gap-3 mb-4">
        <img
          v-for="country in uniqueCountries"
          :key="country.id"
          :src="`https://flagcdn.com/w80/${country.iso_code?.toLowerCase()}.png`"
          :alt="country.name"
          class="w-6 h-6 object-cover border border-gray-200 rounded"
          @error="handleImageError"
        />
      </div>
      <!-- Mini Map -->
      <div v-if="engagementLocations.length > 0" class="border border-gray-300 rounded-md overflow-hidden" style="height: 200px;">
        <div ref="mapContainer" class="w-full h-full"></div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import { useUserStore } from '@/stores/user'
import apiClient from '@/api/client'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const userStore = useUserStore()
const engagements = ref<any[]>([])
const loading = ref(false)
const leaderboards = ref({
  volunteers: [] as any[],
  regionalPartners: [] as any[],
  coaches: [] as any[]
})
const mapContainer = ref<HTMLElement | null>(null)
const mapInstance = ref<L.Map | null>(null)
const markersLayer = ref<L.LayerGroup | null>(null)

const isCurrentUser = computed(() => {
  // For now, always true since we're viewing own profile
  // Later we'll check if viewing another user's profile
  return true
})

const uniquePrograms = computed(() => {
  const programMap = new Map()
  engagements.value
    .filter(eng => eng.is_recognized && eng.event?.first_program)
    .forEach(eng => {
      if (!programMap.has(eng.event.first_program.id)) {
        programMap.set(eng.event.first_program.id, eng.event.first_program)
      }
    })
  return Array.from(programMap.values())
})

const uniqueSeasons = computed(() => {
  const seasonMap = new Map()
  engagements.value
    .filter(eng => eng.is_recognized && eng.event?.season)
    .forEach(eng => {
      if (!seasonMap.has(eng.event.season.id)) {
        seasonMap.set(eng.event.season.id, eng.event.season)
      }
    })
  return Array.from(seasonMap.values())
})

const uniqueCountries = computed(() => {
  const countryMap = new Map()
  engagements.value
    .filter(eng => eng.is_recognized && eng.event?.location?.country)
    .forEach(eng => {
      if (!countryMap.has(eng.event.location.country.id)) {
        countryMap.set(eng.event.location.country.id, eng.event.location.country)
      }
    })
  return Array.from(countryMap.values())
})

const programsWithLogos = computed(() => {
  return uniquePrograms.value.filter(p => p.logo_path)
})

const seasonsWithLogos = computed(() => {
  return uniqueSeasons.value
    .filter(s => s.logo_path)
    .sort((a, b) => (a.start_year || 0) - (b.start_year || 0))
})

const volunteerRank = computed(() => {
  if (!userStore.user?.id) return null
  const entry = leaderboards.value.volunteers.find((e: any) => e.id === userStore.user?.id)
  return entry?.rank || null
})

const regionalPartnerRank = computed(() => {
  if (!userStore.user?.id) return null
  const entry = leaderboards.value.regionalPartners.find((e: any) => e.id === userStore.user?.id)
  return entry?.rank || null
})

const coachRank = computed(() => {
  if (!userStore.user?.id) return null
  const entry = leaderboards.value.coaches.find((e: any) => e.id === userStore.user?.id)
  return entry?.rank || null
})

const hasAnyRank = computed(() => {
  return volunteerRank.value !== null || regionalPartnerRank.value !== null || coachRank.value !== null
})

const engagementLocations = computed(() => {
  const locationMap = new Map()
  engagements.value
    .filter(eng => eng.is_recognized && eng.event?.location?.latitude && eng.event?.location?.longitude)
    .forEach(eng => {
      const loc = eng.event.location
      if (!locationMap.has(loc.id)) {
        locationMap.set(loc.id, {
          id: loc.id,
          name: loc.name,
          city: loc.city,
          latitude: parseFloat(loc.latitude),
          longitude: parseFloat(loc.longitude),
          count: 1
        })
      } else {
        locationMap.get(loc.id).count++
      }
    })
  return Array.from(locationMap.values())
})

function getLogoUrl(logoPath: string | null) {
  if (!logoPath) return ''
  const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8001/api'
  const backendUrl = apiUrl.replace('/api', '')
  return `${backendUrl}/storage/${logoPath}`
}

function handleImageError(event: Event) {
  const img = event.target as HTMLImageElement
  img.style.display = 'none'
}

async function loadEngagements() {
  loading.value = true
  try {
    const response = await apiClient.get('/engagements')
    engagements.value = response.data
  } catch (err) {
    console.error('Failed to load engagements', err)
  } finally {
    loading.value = false
  }
}

async function loadLeaderboards() {
  try {
    const [volunteersRes, regionalPartnersRes, coachesRes] = await Promise.all([
      apiClient.get('/leaderboard/volunteers'),
      apiClient.get('/leaderboard/regional-partners'),
      apiClient.get('/leaderboard/coaches')
    ])
    leaderboards.value = {
      volunteers: volunteersRes.data,
      regionalPartners: regionalPartnersRes.data,
      coaches: coachesRes.data
    }
  } catch (err) {
    console.error('Failed to load leaderboards', err)
  }
}

function initMap() {
  if (!mapContainer.value || mapInstance.value || engagementLocations.value.length === 0) {
    console.log('Map init skipped:', { 
      hasContainer: !!mapContainer.value, 
      hasInstance: !!mapInstance.value, 
      locationsCount: engagementLocations.value.length 
    })
    return
  }

  try {
    // Calculate center from locations
    const avgLat = engagementLocations.value.reduce((sum, loc) => sum + loc.latitude, 0) / engagementLocations.value.length
    const avgLng = engagementLocations.value.reduce((sum, loc) => sum + loc.longitude, 0) / engagementLocations.value.length

    mapInstance.value = L.map(mapContainer.value).setView([avgLat, avgLng], 6)

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(mapInstance.value)

    // Add markers
    markersLayer.value = L.layerGroup().addTo(mapInstance.value)
    engagementLocations.value.forEach(loc => {
      L.circleMarker([loc.latitude, loc.longitude], {
        radius: 6,
        fillColor: '#3b82f6',
        color: '#1d4ed8',
        weight: 1,
        opacity: 1,
        fillOpacity: 0.8
      })
      .bindPopup(`<strong>${loc.name}</strong>${loc.city ? '<br>' + loc.city : ''}<br>${loc.count} ${loc.count === 1 ? 'Einsatz' : 'Einsätze'}`)
      .addTo(markersLayer.value!)
    })

    // Force map to resize after initialization
    setTimeout(() => {
      mapInstance.value?.invalidateSize()
    }, 100)
  } catch (error) {
    console.error('Error initializing map:', error)
  }
}

function updateMap() {
  if (!mapInstance.value || engagementLocations.value.length === 0) return

  // Remove existing markers
  if (markersLayer.value) {
    mapInstance.value.removeLayer(markersLayer.value)
    markersLayer.value = null
  }

  // Add new markers
  markersLayer.value = L.layerGroup().addTo(mapInstance.value)
  engagementLocations.value.forEach(loc => {
    L.circleMarker([loc.latitude, loc.longitude], {
      radius: 6,
      fillColor: '#3b82f6',
      color: '#1d4ed8',
      weight: 1,
      opacity: 1,
      fillOpacity: 0.8
    })
    .bindPopup(`<strong>${loc.name}</strong>${loc.city ? '<br>' + loc.city : ''}<br>${loc.count} ${loc.count === 1 ? 'Einsatz' : 'Einsätze'}`)
    .addTo(markersLayer.value!)
  })

  // Update center if needed
  const avgLat = engagementLocations.value.reduce((sum, loc) => sum + loc.latitude, 0) / engagementLocations.value.length
  const avgLng = engagementLocations.value.reduce((sum, loc) => sum + loc.longitude, 0) / engagementLocations.value.length
  mapInstance.value.setView([avgLat, avgLng], 6)
}

watch(engagementLocations, async () => {
  if (engagementLocations.value.length > 0) {
    await nextTick()
    // Wait a bit more to ensure DOM is ready
    setTimeout(() => {
      if (!mapInstance.value && mapContainer.value) {
        initMap()
      } else if (mapInstance.value) {
        updateMap()
      }
    }, 200)
  }
}, { immediate: false })

onMounted(async () => {
  await userStore.fetchUser()
  await Promise.all([
    loadEngagements(),
    loadLeaderboards()
  ])
  await nextTick()
  // Wait a bit more to ensure DOM is ready
  setTimeout(() => {
    if (engagementLocations.value.length > 0 && mapContainer.value) {
      initMap()
    }
  }, 200)
})
</script>

<style>
.leaflet-container {
  font-family: inherit;
}
</style>
