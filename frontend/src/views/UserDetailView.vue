<template>
  <div class="p-4 pb-32">
    <!-- Navigation Tabs -->
    <TabNavigation
      :tabs="tabs"
      v-model="activeTab"
    />

    <!-- Summary Tab -->
    <AwardsSummaryTab
      v-if="activeTab === 'summary' && user && engagements.length >= 0"
      :engagements="engagements"
      :leaderboards="leaderboards"
      :userId="userId"
      :user="user"
      @back="goBack"
    />

    <!-- Engagements Tab -->
    <EngagementsTab
      v-if="activeTab === 'engagements'"
      :read-only="true"
      :engagements="engagements"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import apiClient from '@/api/client'
import TabNavigation from '@/components/common/TabNavigation.vue'
import AwardsSummaryTab from '@/components/awards/AwardsSummaryTab.vue'
import EngagementsTab from '@/components/awards/EngagementsTab.vue'

const route = useRoute()
const router = useRouter()
const userId = parseInt(route.params.id as string)
const user = ref<any>(null)
const activeTab = ref<'summary' | 'engagements'>('summary')
const engagements = ref<any[]>([])
const leaderboards = ref({
  volunteers: [] as any[],
  regionalPartners: [] as any[],
  coaches: [] as any[]
})

const tabs = [
  { id: 'summary', label: 'Zusammenfassung' },
  { id: 'engagements', label: 'Einsätze' }
]

async function loadUser() {
  try {
    const response = await apiClient.get(`/users/${userId}`)
    user.value = response.data
  } catch (err) {
    console.error('Failed to load user', err)
    router.push('/people')
  }
}

async function loadEngagements() {
  try {
    const response = await apiClient.get(`/users/${userId}/engagements`)
    engagements.value = response.data
  } catch (err) {
    console.error('Failed to load engagements', err)
    engagements.value = []
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

function goBack() {
  router.push('/people')
}

onMounted(async () => {
  await Promise.all([
    loadUser(),
    loadEngagements(),
    loadLeaderboards()
  ])
})
</script>

