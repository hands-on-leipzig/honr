<template>
  <div>
    <!-- Header: Nickname with Back Button -->
    <div class="mb-4 flex items-center">
      <button
        v-if="!isCurrentUser"
        @click="handleBack"
        class="mr-3 text-gray-600 hover:text-gray-900"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <div class="flex-1">
        <div class="flex items-center gap-2">
          <h1 class="text-2xl font-bold">{{ displayUser?.nickname || 'Auszeichnungen' }}</h1>
          <!-- Contact Link Icon -->
          <a
            v-if="displayUser?.contact_link"
            :href="getContactLink(displayUser.contact_link)"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center text-gray-600 hover:text-blue-600 transition-colors"
            :title="displayUser.contact_link"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
          </a>
          <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 text-gray-300"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
          </svg>
        </div>
        <p v-if="isCurrentUser" class="text-xs text-gray-500 mt-1">Zum Ändern geh in die Einstellungen</p>
      </div>
    </div>

    <!-- Short Bio -->
    <div v-if="displayUser?.short_bio" class="bg-white rounded-lg shadow p-4 mb-4">
      <p class="text-gray-700">{{ displayUser.short_bio }}</p>
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

    <!-- Badges -->
    <div v-if="badges.length > 0" class="bg-white rounded-lg shadow p-4 mb-4">
      <div class="flex flex-wrap gap-3">
        <BadgeIcon
          v-for="badge in badges"
          :key="badge.role_name"
          :logo-path="badge.logo_path"
          :level="badge.level"
          :role-name="badge.role_name"
          @click="filterByRole(badge.role_id, badge.role_name)"
        />
      </div>
    </div>

    <!-- Program Logos -->
    <div v-if="programsWithLogos.length > 0" class="bg-white rounded-lg shadow p-4 mb-4">
      <div class="flex flex-wrap gap-3">
        <button
          v-for="program in programsWithLogos"
          :key="program.id"
          @click="filterByProgram(program.id, program.name)"
          class="w-12 h-12 object-contain cursor-pointer hover:opacity-80 transition-opacity"
        >
          <img
            :src="getLogoUrl(program.logo_path)"
            :alt="program.name"
            class="w-full h-full object-contain"
            @error="handleImageError"
          />
        </button>
      </div>
    </div>

    <!-- Season Logos -->
    <div v-if="seasonsWithLogos.length > 0" class="bg-white rounded-lg shadow p-4 mb-4">
      <div class="flex flex-wrap gap-3">
        <button
          v-for="season in seasonsWithLogos"
          :key="season.id"
          @click="filterBySeason(season.id, season.name)"
          class="w-12 h-12 object-contain cursor-pointer hover:opacity-80 transition-opacity"
        >
          <img
            :src="getLogoUrl(season.logo_path)"
            :alt="season.name"
            class="w-full h-full object-contain"
            @error="handleImageError"
          />
        </button>
      </div>
    </div>

    <!-- Country Flags & Map -->
    <div v-if="uniqueCountries.length > 0 || engagementLocations.length > 0" class="bg-white rounded-lg shadow p-4 mb-4">
      <div v-if="uniqueCountries.length > 0" class="flex flex-wrap gap-3 mb-4">
        <button
          v-for="country in uniqueCountries"
          :key="country.id"
          @click="filterByCountry(country.id, country.name)"
          class="w-6 h-6 object-cover border border-gray-200 rounded cursor-pointer hover:opacity-80 transition-opacity"
        >
          <img
            :src="`https://flagcdn.com/w80/${country.iso_code?.toLowerCase()}.png`"
            :alt="country.name"
            class="w-full h-full object-cover rounded"
            @error="handleImageError"
          />
        </button>
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
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import apiClient from '@/api/client'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import BadgeIcon from './BadgeIcon.vue'

interface Props {
  engagements: any[]
  leaderboards: {
    volunteers: any[]
    regionalPartners: any[]
    coaches: any[]
  }
  userId?: number
  user?: any
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'back'): void
}>()

const router = useRouter()
const userStore = useUserStore()

const displayUser = computed(() => {
  return props.user || userStore.user
})

const isCurrentUser = computed(() => {
  if (props.userId && userStore.user) {
    return props.userId === userStore.user.id
  }
  return !props.userId && !props.user
})

const mapContainer = ref<HTMLElement | null>(null)
const mapInstance = ref<L.Map | null>(null)
const markersLayer = ref<L.LayerGroup | null>(null)
const badges = ref<any[]>([])

const uniquePrograms = computed(() => {
  const programMap = new Map()
  props.engagements
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
  props.engagements
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
  props.engagements
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
  const targetUserId = props.userId || userStore.user?.id
  if (!targetUserId) return null
  const entry = props.leaderboards.volunteers.find((e: any) => e.id === targetUserId)
  return entry?.rank || null
})

const regionalPartnerRank = computed(() => {
  const targetUserId = props.userId || userStore.user?.id
  if (!targetUserId) return null
  const entry = props.leaderboards.regionalPartners.find((e: any) => e.id === targetUserId)
  return entry?.rank || null
})

const coachRank = computed(() => {
  const targetUserId = props.userId || userStore.user?.id
  if (!targetUserId) return null
  const entry = props.leaderboards.coaches.find((e: any) => e.id === targetUserId)
  return entry?.rank || null
})

const hasAnyRank = computed(() => {
  return volunteerRank.value !== null || regionalPartnerRank.value !== null || coachRank.value !== null
})

const engagementLocations = computed(() => {
  const locationMap = new Map()
  props.engagements
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

function handleBack() {
  emit('back')
}

function getContactLink(link: string): string {
  if (!link) return '#'
  
  // If it's already a valid URL (starts with http, https, mailto, tel), return as is
  if (/^(https?|mailto|tel):/i.test(link)) {
    return link
  }
  
  // If it looks like an email address, convert to mailto:
  if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(link)) {
    return 'mailto:' + link
  }
  
  // Otherwise, assume it's a web URL and add https://
  return 'https://' + link
}

async function loadBadges() {
  const targetUserId = props.userId || userStore.user?.id
  if (!targetUserId) return

  try {
    const response = await apiClient.get(`/users/${targetUserId}/badges`)
    badges.value = response.data
  } catch (err) {
    console.error('Failed to load badges', err)
    badges.value = []
  }
}

function filterByRole(roleId: number, roleName: string) {
  router.push({
    path: '/people',
    query: { filter_type: 'role', filter_id: roleId, filter_label: `Rolle ${roleName}` }
  })
}

function filterByProgram(programId: number, programName: string) {
  router.push({
    path: '/people',
    query: { filter_type: 'program', filter_id: programId, filter_label: `Programm ${programName}` }
  })
}

function filterBySeason(seasonId: number, seasonName: string) {
  router.push({
    path: '/people',
    query: { filter_type: 'season', filter_id: seasonId, filter_label: `Saison ${seasonName}` }
  })
}

function filterByCountry(countryId: number, countryName: string) {
  router.push({
    path: '/people',
    query: { filter_type: 'country', filter_id: countryId, filter_label: `Land ${countryName}` }
  })
}

function initMap() {
  if (!mapContainer.value || mapInstance.value || engagementLocations.value.length === 0) {
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
    setTimeout(() => {
      if (!mapInstance.value && mapContainer.value) {
        initMap()
      } else if (mapInstance.value) {
        updateMap()
      }
    }, 200)
  }
}, { immediate: false })

watch(() => props.engagements, async () => {
  if (engagementLocations.value.length > 0) {
    await nextTick()
    setTimeout(() => {
      if (!mapInstance.value && mapContainer.value) {
        initMap()
      } else if (mapInstance.value) {
        updateMap()
      }
    }, 200)
  }
}, { deep: true })

onMounted(async () => {
  await loadBadges()
  await nextTick()
  setTimeout(() => {
    if (engagementLocations.value.length > 0 && mapContainer.value) {
      initMap()
    }
  }, 200)
})

watch(() => props.userId, () => {
  loadBadges()
})
</script>

<style scoped>
.leaflet-container {
  font-family: inherit;
  z-index: 1;
}

.leaflet-pane {
  z-index: 1;
}

.leaflet-top,
.leaflet-bottom {
  z-index: 1;
}

.leaflet-control {
  z-index: 2;
}
</style>

