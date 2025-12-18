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
    <div v-if="activeTab === 'map'" class="bg-white rounded-lg shadow p-6">
      <h2 class="text-lg font-semibold mb-4">Karte</h2>
      <p class="text-gray-600">Karte wird hier angezeigt...</p>
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
import { ref, watch, onMounted } from 'vue'
import apiClient from '@/api/client'

const activeTab = ref<'leaderboard' | 'map'>('leaderboard')
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
