<template>
  <div class="p-4 pb-32">
    <!-- Header: Nickname with Back Button -->
    <div class="mb-4 flex items-center">
      <button
        @click="goBack"
        class="mr-3 text-gray-600 hover:text-gray-900"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <div class="flex-1">
        <div class="flex items-center gap-2">
          <h1 class="text-2xl font-bold">{{ user?.nickname || 'Benutzer' }}</h1>
          <!-- Contact Link Icon -->
          <a
            v-if="user?.contact_link"
            :href="getContactLink(user.contact_link)"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center text-gray-600 hover:text-blue-600 transition-colors"
            :title="user.contact_link"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
          </a>
          <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 text-gray-300"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
          </svg>
        </div>
      </div>
    </div>

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

function getContactLink(link: string): string {
  if (!link) return '#'
  
  // If it's already a valid URL (starts with http, https, mailto, tel), return as is
  if (/^(https?|mailto|tel):/i.test(link)) {
    return link
  }
  
  // If it looks like an email address, convert to mailto:
  if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(link)) {
    return 'mailto:' + link
  }
  
  // Otherwise, assume it's a domain and prepend https://
  return 'https://' + link
}

onMounted(async () => {
  await Promise.all([
    loadUser(),
    loadEngagements(),
    loadLeaderboards()
  ])
})
</script>

