<template>
  <div class="p-4 pb-32">
    <!-- Navigation Tabs -->
    <div class="flex border-b border-gray-200 mb-4">
      <button
        @click="activeTab = 'summary'"
        :class="[
          'flex-1 py-3 text-center font-medium border-b-2 transition-colors',
          activeTab === 'summary'
            ? 'border-blue-600 text-blue-600'
            : 'border-transparent text-gray-500 hover:text-gray-700'
        ]"
      >
        Zusammenfassung
      </button>
      <button
        @click="activeTab = 'engagements'"
        :class="[
          'flex-1 py-3 text-center font-medium border-b-2 transition-colors',
          activeTab === 'engagements'
            ? 'border-blue-600 text-blue-600'
            : 'border-transparent text-gray-500 hover:text-gray-700'
        ]"
      >
        Einsätze
      </button>
    </div>

    <!-- Summary Tab -->
    <AwardsSummaryTab
      v-if="activeTab === 'summary'"
      :engagements="engagements"
      :leaderboards="leaderboards"
    />

    <!-- Engagements Tab -->
    <EngagementsTab
      v-if="activeTab === 'engagements'"
      @engagements-updated="handleEngagementsUpdated"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useUserStore } from '@/stores/user'
import apiClient from '@/api/client'
import AwardsSummaryTab from '@/components/awards/AwardsSummaryTab.vue'
import EngagementsTab from '@/components/awards/EngagementsTab.vue'

const userStore = useUserStore()
const activeTab = ref<'summary' | 'engagements'>('summary')
const engagements = ref<any[]>([])
const leaderboards = ref({
  volunteers: [] as any[],
  regionalPartners: [] as any[],
  coaches: [] as any[]
})

async function loadEngagements() {
  try {
    const response = await apiClient.get('/engagements')
    engagements.value = response.data
  } catch (err) {
    console.error('Failed to load engagements', err)
  }
}

async function loadLeaderboards() {
  try {
    const [volunteersRes, regionalPartnersRes, coachesRes] = await Promise.all([
      apiClient.get('/leaderboard/volunteers'),
      apiClient.get('/leaderboard/regional-partners'),
      apiClient.get('/leaderboard/coaches')
    ])
    leaderboards.value = {
      volunteers: volunteersRes.data,
      regionalPartners: regionalPartnersRes.data,
      coaches: coachesRes.data
    }
  } catch (err) {
    console.error('Failed to load leaderboards', err)
  }
}

async function handleEngagementsUpdated() {
  await loadEngagements()
}

onMounted(async () => {
  await userStore.fetchUser()
  await Promise.all([
    loadEngagements(),
    loadLeaderboards()
  ])
})
</script>
