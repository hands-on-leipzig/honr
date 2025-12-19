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
