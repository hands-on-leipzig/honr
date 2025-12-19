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

    <!-- Program Logos -->
    <div v-if="programsWithLogos.length > 0" class="bg-white rounded-lg shadow p-4 mb-4">
      <div class="flex flex-wrap gap-3">
        <img
          v-for="program in programsWithLogos"
          :key="program.id"
          :src="getLogoUrl(program.logo_path)"
          :alt="program.name"
          class="h-12 object-contain"
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
          class="h-12 object-contain"
          @error="handleImageError"
        />
      </div>
    </div>

    <!-- Country Flags -->
    <div v-if="uniqueCountries.length > 0" class="bg-white rounded-lg shadow p-4 mb-4">
      <div class="flex flex-wrap gap-3">
        <img
          v-for="country in uniqueCountries"
          :key="country.id"
          :src="`https://flagcdn.com/w80/${country.iso_code?.toLowerCase()}.png`"
          :alt="country.name"
          class="h-12 object-contain border border-gray-200 rounded"
          @error="handleImageError"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useUserStore } from '@/stores/user'
import apiClient from '@/api/client'

const userStore = useUserStore()
const engagements = ref<any[]>([])
const loading = ref(false)

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
  return uniqueSeasons.value.filter(s => s.logo_path)
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

onMounted(async () => {
  await userStore.fetchUser()
  await loadEngagements()
})
</script>
