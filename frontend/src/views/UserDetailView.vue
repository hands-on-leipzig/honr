<template>
  <div class="p-4 pb-32">
    <!-- Header with Back Button -->
    <div class="mb-4 flex items-center">
      <button
        @click="goBack"
        class="mr-3 text-gray-600 hover:text-gray-900"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <h1 class="text-2xl font-bold">{{ user?.nickname || 'Benutzer' }}</h1>
    </div>

    <!-- Summary Tab (Reusing AwardsSummaryTab) -->
    <AwardsSummaryTab
      v-if="user && engagements.length >= 0"
      :engagements="engagements"
      :leaderboards="leaderboards"
      :userId="userId"
      :user="user"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import apiClient from '@/api/client'
import AwardsSummaryTab from '@/components/awards/AwardsSummaryTab.vue'

const route = useRoute()
const router = useRouter()
const userId = parseInt(route.params.id as string)
const user = ref<any>(null)
const engagements = ref<any[]>([])
const leaderboards = ref({
  volunteers: [] as any[],
  regionalPartners: [] as any[],
  coaches: [] as any[]
})

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

