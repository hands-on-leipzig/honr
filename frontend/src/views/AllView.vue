<template>
  <div class="p-4 pb-32">
    <h1 class="text-2xl font-bold mb-4">Alle Volunteer-Einsätze</h1>

    <!-- Navigation Tabs -->
    <TabNavigation
      :tabs="tabs"
      v-model="activeTab"
    />

    <!-- Leaderboard Tab -->
    <LeaderboardTab v-if="activeTab === 'leaderboard'" />

    <!-- Map Tab -->
    <MapTab v-if="activeTab === 'map'" />

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
import { ref } from 'vue'
import TabNavigation from '@/components/common/TabNavigation.vue'
import LeaderboardTab from '@/components/all/LeaderboardTab.vue'
import MapTab from '@/components/all/MapTab.vue'

const activeTab = ref<'leaderboard' | 'map'>('leaderboard')

const tabs = [
  { id: 'leaderboard', label: 'Bestenliste' },
  { id: 'map', label: 'Karte' }
]
</script>
