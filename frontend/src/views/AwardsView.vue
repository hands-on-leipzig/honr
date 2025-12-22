<template>
  <div class="p-4 pb-32">
    <!-- Header: Nickname with Contact Link -->
    <div class="mb-4 flex items-center">
      <div class="flex-1">
        <div class="flex items-center gap-2">
          <h1 class="text-2xl font-bold">{{ userStore.user?.nickname || 'Auszeichnungen' }}</h1>
          <!-- Contact Link Icon -->
          <a
            v-if="userStore.user?.contact_link"
            :href="getContactLink(userStore.user.contact_link)"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center text-gray-600 hover:text-blue-600 transition-colors"
            :title="userStore.user.contact_link"
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
        <p class="text-xs text-gray-500 mt-1">Zum Ändern geh in die Einstellungen</p>
      </div>
    </div>

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
  await userStore.fetchUser()
  await Promise.all([
    loadEngagements(),
    loadLeaderboards()
  ])
})
</script>
