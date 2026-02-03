<template>
  <div>
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-4">
      <div class="grid grid-cols-3 gap-2">
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Programm</label>
          <select v-model="filters.program_id" @change="onProgramChange" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
            <option value="">Alle</option>
            <option v-for="p in filterOptions.programs" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Saison</label>
          <select v-model="filters.season_id" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
            <option value="">Alle</option>
            <option v-for="s in filteredSeasons" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Level</label>
          <select v-model="filters.level_id" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
            <option value="">Alle</option>
            <option v-for="l in filterOptions.levels" :key="l.id" :value="l.id">{{ l.name }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Category Tabs -->
    <div class="flex bg-gray-100 rounded-lg p-1 mb-4">
      <button
        @click="leaderboardCategory = 'volunteers'"
        :class="[
          'flex-1 py-2 text-sm font-medium rounded-md transition-colors',
          leaderboardCategory === 'volunteers'
            ? primaryColors.activeTab
            : `${neutralColors.text.tertiary} hover:text-gray-800`
        ]"
      >
        Volunteers
      </button>
      <button
        @click="leaderboardCategory = 'regional-partners'"
        :class="[
          'flex-1 py-2 text-sm font-medium rounded-md transition-colors',
          leaderboardCategory === 'regional-partners'
            ? primaryColors.activeTab
            : `${neutralColors.text.tertiary} hover:text-gray-800`
        ]"
      >
        Regional Partner
      </button>
      <button
        @click="leaderboardCategory = 'coaches'"
        :class="[
          'flex-1 py-2 text-sm font-medium rounded-md transition-colors',
          leaderboardCategory === 'coaches'
            ? primaryColors.activeTab
            : `${neutralColors.text.tertiary} hover:text-gray-800`
        ]"
      >
        Coaches
      </button>
    </div>

    <!-- Top Count Selection -->
    <div v-if="!loading && leaderboard.length > 0" class="flex gap-2 mb-4">
      <button
        v-for="count in [5, 10, 50, 100]"
        :key="count"
        @click="topCount = count"
        :class="[
          'px-3 py-1 text-sm font-medium rounded-md transition-colors',
          topCount === count
            ? 'bg-blue-600 text-white'
            : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
        ]"
      >
        Top {{ count }}
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="bg-white rounded-lg shadow p-6">
      <p class="text-gray-600 text-center py-8">Laden...</p>
    </div>

    <!-- Load error -->
    <div v-else-if="loadError" class="bg-white rounded-lg shadow p-6">
      <p class="text-amber-800 text-center py-8">{{ LOAD_ERROR_MESSAGE }}</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="leaderboard.length === 0" class="bg-white rounded-lg shadow p-6">
      <p class="text-gray-600 text-center py-8">Noch keine Einträge vorhanden.</p>
    </div>

    <!-- Leaderboard List -->
    <div v-else class="bg-white rounded-lg shadow divide-y divide-gray-100">
      <div
        v-for="entry in displayedLeaderboard"
        :key="entry.id"
        class="flex items-center p-4"
      >
        <!-- Rank -->
        <div class="w-10 text-center">
          <span
            :class="[
              'inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold',
              getRankColorClass(entry.rank)
            ]"
          >
            {{ entry.rank }}
          </span>
        </div>

        <!-- Name -->
        <div class="flex-1 ml-3">
          <button
            v-if="leaderboardCategory !== 'regional-partners'"
            @click="viewUser(entry.id)"
            class="font-medium text-gray-900 hover:text-blue-600 text-left"
          >
            {{ entry.display_name || entry.nickname }}
          </button>
          <span
            v-else
            class="font-medium text-gray-900"
          >
            {{ entry.display_name || entry.name }}
          </span>
        </div>

        <!-- Count -->
        <div class="text-right">
          <div :class="['text-lg font-bold', primaryColors.link]">{{ entry.engagement_count || entry.season_count }}</div>
          <div class="text-xs text-gray-500">
            {{ leaderboardCategory === 'volunteers' ? 'Einsätze' : 'Saisons' }}
          </div>
        </div>
      </div>

      <!-- Current User Below Top X -->
      <div v-if="showCurrentUserBelow" class="mt-4 pt-4 border-t-2 border-gray-300">
        <div class="flex items-center p-4">
          <!-- Rank -->
          <div class="w-10 text-center">
            <span
              :class="[
                'inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold',
                getRankColorClass(currentUserEntry.rank)
              ]"
            >
              {{ currentUserEntry.rank }}
            </span>
          </div>

          <!-- Name -->
          <div class="flex-1 ml-3">
            <button
              v-if="leaderboardCategory !== 'regional-partners'"
              @click="viewUser(currentUserEntry.id)"
              class="font-medium text-gray-900 hover:text-blue-600 text-left"
            >
              {{ currentUserEntry.display_name || currentUserEntry.nickname }}
            </button>
            <span
              v-else
              class="font-medium text-gray-900"
            >
              {{ currentUserEntry.display_name || currentUserEntry.name }}
            </span>
          </div>

          <!-- Count -->
          <div class="text-right">
            <div :class="['text-lg font-bold', primaryColors.link]">{{ currentUserEntry.engagement_count || currentUserEntry.season_count }}</div>
            <div class="text-xs text-gray-500">
              {{ leaderboardCategory === 'volunteers' ? 'Einsätze' : 'Saisons' }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import apiClient from '@/api/client'
import { isJsonArray, isFilterOptionsShape, LOAD_ERROR_MESSAGE } from '@/api/responseGuard'
import { getRankColorClass, PRIMARY_COLORS, NEUTRAL_COLORS } from '@/constants/uiColors'
import { useUserStore } from '@/stores/user'

// Make constants available in template
const primaryColors = PRIMARY_COLORS
const neutralColors = NEUTRAL_COLORS

const router = useRouter()
const route = useRoute()
const userStore = useUserStore()

const leaderboardCategory = ref<'volunteers' | 'regional-partners' | 'coaches'>(
  (route.query.category as 'volunteers' | 'regional-partners' | 'coaches') || 'volunteers'
)
const leaderboard = ref<any[]>([])
const loading = ref(false)
const loadError = ref(false)
const topCount = ref(5)
const filterOptions = ref<{ programs: any[], seasons: any[], levels: any[] }>({ programs: [], seasons: [], levels: [] })
const filters = ref({ program_id: '', season_id: '', level_id: '' })

const filteredSeasons = computed(() => {
  if (!filters.value.program_id) return filterOptions.value.seasons
  return filterOptions.value.seasons.filter((s: any) => s.first_program_id == filters.value.program_id)
})

// Display all entries with rank <= topCount (includes all users tied at rank topCount)
const displayedLeaderboard = computed(() => {
  return leaderboard.value.filter((entry: any) => entry.rank <= topCount.value)
})

// Find current user's entry in the full leaderboard
const currentUserEntry = computed(() => {
  if (!userStore.user?.id) return null
  
  // For regional partners, we need to find the regional partner from user's engagements
  if (leaderboardCategory.value === 'regional-partners') {
    // This will be handled by AwardsSummaryTab, but for now return null
    // The leaderboard shows regional partners (entities), not users
    return null
  }
  
  return leaderboard.value.find((entry: any) => entry.id === userStore.user?.id) || null
})

// Check if current user is in the displayed top X
const showCurrentUserBelow = computed(() => {
  if (!currentUserEntry.value || !userStore.user?.id) return false
  // Check if current user is already in the displayed top X
  const isInTopX = displayedLeaderboard.value.some((entry: any) => entry.id === userStore.user?.id)
  return !isInTopX
})

function onProgramChange() {
  filters.value.season_id = ''
}

async function loadFilterOptions() {
  try {
    const response = await apiClient.get('/leaderboard/options')
    const data = response.data
    if (isFilterOptionsShape(data)) {
      filterOptions.value = data
    }
  } catch (err) {
    console.error('Failed to load filter options', err)
  }
}

async function loadLeaderboard() {
  loading.value = true
  loadError.value = false
  try {
    const params = new URLSearchParams()
    if (filters.value.program_id) params.append('program_id', filters.value.program_id)
    if (filters.value.season_id) params.append('season_id', filters.value.season_id)
    if (filters.value.level_id) params.append('level_id', filters.value.level_id)
    
    const response = await apiClient.get(`/leaderboard/${leaderboardCategory.value}?${params.toString()}`)
    const data = response.data
    if (isJsonArray(data)) {
      leaderboard.value = data
    } else {
      leaderboard.value = []
      loadError.value = true
    }
  } catch (err) {
    console.error('Failed to load leaderboard', err)
    leaderboard.value = []
    loadError.value = true
  } finally {
    loading.value = false
  }
}

function viewUser(userId: number) {
  router.push(`/user/${userId}`)
}

watch(leaderboardCategory, () => {
  loadLeaderboard()
})

watch(filters, () => {
  loadLeaderboard()
}, { deep: true })

// Watch for route query changes to update category
watch(() => route.query.category, (newCategory) => {
  if (newCategory && ['volunteers', 'regional-partners', 'coaches'].includes(newCategory as string)) {
    leaderboardCategory.value = newCategory as 'volunteers' | 'regional-partners' | 'coaches'
  }
})

onMounted(async () => {
  await loadFilterOptions()
  loadLeaderboard()
})
</script>

