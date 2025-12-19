<template>
  <div class="p-4 pb-32">
    <!-- Navigation Tabs -->
    <TabNavigation
      :tabs="tabs"
      v-model="activeTab"
    />

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
import TabNavigation from '@/components/common/TabNavigation.vue'
import AwardsSummaryTab from '@/components/awards/AwardsSummaryTab.vue'
import EngagementsTab from '@/components/awards/EngagementsTab.vue'

const userStore = useUserStore()
const activeTab = ref<'summary' | 'engagements'>('summary')

const tabs = [
  { id: 'summary', label: 'Zusammenfassung' },
  { id: 'engagements', label: 'Einsätze' }
]
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
