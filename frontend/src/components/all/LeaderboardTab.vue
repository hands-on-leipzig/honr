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
              'bg-white border border-gray-300 text-gray-600'
            ]"
          >
            {{ entry.rank }}
          </span>
        </div>

        <!-- Name -->
        <div class="flex-1 ml-3">
          <button
            @click="viewUser(entry.id)"
            class="font-medium text-gray-900 hover:text-blue-600 text-left"
          >
            {{ entry.display_name || entry.nickname }}
          </button>
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
</template>

<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import apiClient from '@/api/client'

const router = useRouter()
const route = useRoute()

const leaderboardCategory = ref<'volunteers' | 'regional-partners' | 'coaches'>(
  (route.query.category as 'volunteers' | 'regional-partners' | 'coaches') || 'volunteers'
)
const leaderboard = ref<any[]>([])
const loading = ref(false)
const filterOptions = ref<{ programs: any[], seasons: any[], levels: any[] }>({ programs: [], seasons: [], levels: [] })
const filters = ref({ program_id: '', season_id: '', level_id: '' })

const filteredSeasons = computed(() => {
  if (!filters.value.program_id) return filterOptions.value.seasons
  return filterOptions.value.seasons.filter((s: any) => s.first_program_id == filters.value.program_id)
})

function onProgramChange() {
  filters.value.season_id = ''
}

async function loadFilterOptions() {
  try {
    const response = await apiClient.get('/leaderboard/options')
    filterOptions.value = response.data
  } catch (err) {
    console.error('Failed to load filter options', err)
  }
}

async function loadLeaderboard() {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (filters.value.program_id) params.append('program_id', filters.value.program_id)
    if (filters.value.season_id) params.append('season_id', filters.value.season_id)
    if (filters.value.level_id) params.append('level_id', filters.value.level_id)
    
    const response = await apiClient.get(`/leaderboard/${leaderboardCategory.value}?${params.toString()}`)
    leaderboard.value = response.data
  } catch (err) {
    console.error('Failed to load leaderboard', err)
    leaderboard.value = []
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

