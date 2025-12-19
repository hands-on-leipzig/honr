<template>
  <div>
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
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import apiClient from '@/api/client'

const leaderboardCategory = ref<'volunteers' | 'regional-partners' | 'coaches'>('volunteers')
const leaderboard = ref<any[]>([])
const loading = ref(false)

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

watch(leaderboardCategory, () => {
  loadLeaderboard()
})

onMounted(() => {
  loadLeaderboard()
})
</script>

