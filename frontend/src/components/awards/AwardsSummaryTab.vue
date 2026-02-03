<template>
  <div>
    <!-- Short Bio -->
    <div v-if="displayUser?.short_bio" class="bg-white rounded-lg shadow p-4 mb-4">
      <p class="text-gray-700">{{ displayUser.short_bio }}</p>
    </div>

    <!-- Leaderboard Ranks -->
    <div v-if="hasAnyRank" class="bg-white rounded-lg shadow divide-y divide-gray-100 mb-4">
      <button
        v-if="volunteerEntry && (volunteerEntry.engagement_count || 0) > 0"
        @click="navigateToLeaderboard('volunteers')"
        class="w-full flex items-center p-4 hover:bg-gray-50 transition-colors text-left"
      >
        <!-- Rank -->
        <div class="w-10 text-center">
          <span
            :class="[
              'inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold',
              getRankColorClass(volunteerEntry.rank)
            ]"
          >
            {{ volunteerEntry.rank }}
          </span>
        </div>
        <!-- Label -->
        <div class="flex-1 ml-3 text-left">
          <span class="font-medium text-gray-900">Volunteer</span>
        </div>
        <!-- Count -->
        <div class="text-right">
          <div :class="['text-lg font-bold', PRIMARY_COLORS.link]">{{ volunteerEntry.engagement_count || 0 }}</div>
          <div class="text-xs text-gray-500">Einsätze</div>
        </div>
      </button>
      <button
        v-for="rpEntry in regionalPartnerEntries"
        :key="rpEntry.regional_partner_id"
        @click="navigateToLeaderboard('regional-partners')"
        class="w-full flex items-center p-4 hover:bg-gray-50 transition-colors text-left"
      >
        <!-- Rank -->
        <div class="w-10 text-center">
          <span
            v-if="rpEntry.rank"
            :class="[
              'inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold',
              getRankColorClass(rpEntry.rank)
            ]"
          >
            {{ rpEntry.rank }}
          </span>
          <span v-else class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold text-gray-400">–</span>
        </div>
        <!-- Label -->
        <div class="flex-1 ml-3 text-left">
          <span class="font-medium text-gray-900">Regionalpartner:in {{ rpEntry.regional_partner_name }}</span>
        </div>
        <!-- Count -->
        <div class="text-right">
          <div :class="['text-lg font-bold', PRIMARY_COLORS.link]">{{ rpEntry.engagement_count || 0 }}</div>
          <div class="text-xs text-gray-500">Einsätze</div>
        </div>
      </button>
      <button
        v-if="coachEntry && (coachEntry.season_count || 0) > 0"
        @click="navigateToLeaderboard('coaches')"
        class="w-full flex items-center p-4 hover:bg-gray-50 transition-colors text-left"
      >
        <!-- Rank -->
        <div class="w-10 text-center">
          <span
            :class="[
              'inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold',
              getRankColorClass(coachEntry.rank)
            ]"
          >
            {{ coachEntry.rank }}
          </span>
        </div>
        <!-- Label -->
        <div class="flex-1 ml-3 text-left">
          <span class="font-medium text-gray-900">Coach</span>
        </div>
        <!-- Count -->
        <div class="text-right">
          <div :class="['text-lg font-bold', PRIMARY_COLORS.link]">{{ coachEntry.season_count || 0 }}</div>
          <div class="text-xs text-gray-500">Saisons</div>
        </div>
      </button>
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
          :role-short-name="badge.role_short_name"
          :engagement-count="badge.engagement_count"
          @click="filterByRole(badge.role_id, badge.role_name, badge.logo_path)"
        />
      </div>
    </div>

    <!-- Program Logos -->
    <div v-if="programsWithLogos.length > 0" class="bg-white rounded-lg shadow p-4 mb-4">
      <div class="flex flex-wrap gap-3">
        <button
          v-for="program in programsWithLogos"
          :key="program.id"
          @click="filterByProgram(program.id, program.name, program.logo_path)"
          class="w-12 h-12 object-contain cursor-pointer hover:opacity-80 transition-opacity"
        >
          <img
            :src="getLogoUrl(program.logo_path)"
            :alt="program.name"
            @error="handleImageError"
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
          @click="filterBySeason(season.id, season.name, season.logo_path)"
          class="w-12 h-12 object-contain cursor-pointer hover:opacity-80 transition-opacity"
        >
          <img
            :src="getLogoUrl(season.logo_path)"
            :alt="season.name"
            @error="handleImageError"
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
          @click="filterByCountry(country.id, country.name, country.iso_code)"
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
import { getStorageUrl } from '@/api/storageUrl'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import BadgeIcon from './BadgeIcon.vue'
import { getRankColorClass, MAP_COLORS, PRIMARY_COLORS } from '@/constants/uiColors'

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
  return uniquePrograms.value
    .filter(p => p.logo_path)
    .sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0))
})

const seasonsWithLogos = computed(() => {
  return uniqueSeasons.value
    .filter(s => s.logo_path)
    .sort((a, b) => (a.start_year || 0) - (b.start_year || 0))
})

const volunteerEntry = computed(() => {
  const targetUserId = props.userId || userStore.user?.id
  if (!targetUserId) return null
  return props.leaderboards.volunteers.find((e: any) => e.id === targetUserId) || null
})

const regionalPartnerEntries = computed(() => {
  // Get all regional partner engagements
  const regionalPartnerEngagements = props.engagements.filter((eng: any) => 
    eng.is_recognized && 
    eng.role?.role_category === 'regional_partner' &&
    eng.event?.location?.regional_partner_id &&
    eng.event?.location?.regional_partner
  )
  
  if (regionalPartnerEngagements.length === 0) return []
  
  // Group by regional_partner_id and count engagements
  const rpMap = new Map()
  regionalPartnerEngagements.forEach((eng: any) => {
    const rpId = eng.event.location.regional_partner_id
    const rpName = eng.event.location.regional_partner.name
    
    if (!rpMap.has(rpId)) {
      rpMap.set(rpId, {
        regional_partner_id: rpId,
        regional_partner_name: rpName,
        engagement_count: 0,
        rank: null
      })
    }
    rpMap.get(rpId).engagement_count++
  })
  
  // Find ranks from leaderboard for each regional partner
  const entries = Array.from(rpMap.values())
  entries.forEach((entry: any) => {
    const leaderboardEntry = props.leaderboards.regionalPartners.find((e: any) => e.id === entry.regional_partner_id)
    if (leaderboardEntry) {
      entry.rank = leaderboardEntry.rank
    }
  })
  
  // Sort by engagement count descending
  return entries.sort((a: any, b: any) => b.engagement_count - a.engagement_count)
})

const coachEntry = computed(() => {
  const targetUserId = props.userId || userStore.user?.id
  if (!targetUserId) return null
  return props.leaderboards.coaches.find((e: any) => e.id === targetUserId) || null
})

const hasAnyRank = computed(() => {
  return (volunteerEntry.value && (volunteerEntry.value.engagement_count || 0) > 0) ||
         (regionalPartnerEntries.value.length > 0) ||
         (coachEntry.value && (coachEntry.value.season_count || 0) > 0)
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
  return getStorageUrl(logoPath)
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

function filterByRole(roleId: number, roleName: string, logoPath: string | null) {
  router.push({
    path: '/people',
    query: {
      filter_type: 'role',
      filter_id: roleId,
      filter_label: roleName,
      filter_icon_type: 'logo',
      filter_icon_path: logoPath || ''
    }
  })
}

function filterByProgram(programId: number, programName: string, logoPath: string | null) {
  router.push({
    path: '/people',
    query: {
      filter_type: 'program',
      filter_id: programId,
      filter_label: programName,
      filter_icon_type: 'logo',
      filter_icon_path: logoPath || ''
    }
  })
}

function filterBySeason(seasonId: number, seasonName: string, logoPath: string | null) {
  router.push({
    path: '/people',
    query: {
      filter_type: 'season',
      filter_id: seasonId,
      filter_label: seasonName,
      filter_icon_type: 'logo',
      filter_icon_path: logoPath || ''
    }
  })
}

function filterByCountry(countryId: number, countryName: string, isoCode: string | null) {
  router.push({
    path: '/people',
    query: {
      filter_type: 'country',
      filter_id: countryId,
      filter_label: countryName,
      filter_icon_type: 'flag',
      filter_icon_code: isoCode || ''
    }
  })
}

function navigateToLeaderboard(category: 'volunteers' | 'regional-partners' | 'coaches') {
  router.push({
    path: '/all',
    query: {
      tab: 'leaderboard',
      category: category
    }
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
        fillColor: MAP_COLORS.fill,
        color: MAP_COLORS.border,
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
      fillColor: MAP_COLORS.fill,
      color: MAP_COLORS.border,
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

