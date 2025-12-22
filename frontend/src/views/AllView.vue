<template>
  <div class="p-4 pb-32">
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
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import TabNavigation from '@/components/common/TabNavigation.vue'
import LeaderboardTab from '@/components/all/LeaderboardTab.vue'
import MapTab from '@/components/all/MapTab.vue'

const route = useRoute()
const activeTab = ref<'leaderboard' | 'map'>('leaderboard')

const tabs = [
  { id: 'leaderboard', label: 'Bestenliste' },
  { id: 'map', label: 'Karte' }
]

function updateTabFromQuery() {
  if (route.query.tab === 'leaderboard' || route.query.tab === 'map') {
    activeTab.value = route.query.tab as 'leaderboard' | 'map'
  } else if (route.query.category) {
    // If category is provided, default to leaderboard tab
    activeTab.value = 'leaderboard'
  }
}

watch(() => route.query, () => {
  updateTabFromQuery()
}, { deep: true })

onMounted(() => {
  updateTabFromQuery()
})
</script>
