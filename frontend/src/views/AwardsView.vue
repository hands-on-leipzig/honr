<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">{{ userStore.user?.nickname || 'Meine Auszeichnungen' }}</h1>
    
    <!-- Placeholder Content -->
    <div class="bg-white rounded-lg shadow p-6 mb-4">
      <div class="text-center mb-4">
        <div class="w-20 h-20 bg-blue-100 rounded-full mx-auto mb-3 flex items-center justify-center">
          <TrophyIcon class="w-12 h-12 text-blue-600" />
        </div>
        <h2 class="text-xl font-semibold">{{ userStore.user?.nickname || 'Nickname' }}</h2>
        <p class="text-gray-500">{{ userStore.user?.email || '' }}</p>
      </div>
    </div>

    <!-- Volunteer Section (Placeholder) -->
    <div class="bg-white rounded-lg shadow p-6 mb-4">
      <h3 class="text-lg font-semibold mb-3">Freiwilliger</h3>
      <p class="text-gray-600">Engagement Details werden hier angezeigt...</p>
    </div>

    <!-- Badges Section (Placeholder) -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold mb-3">Abzeichen</h3>
      <p class="text-gray-600">Abzeichen werden hier angezeigt...</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { TrophyIcon } from '@heroicons/vue/24/outline'
import { useUserStore } from '@/stores/user'

const userStore = useUserStore()

onMounted(() => {
  userStore.fetchUser()
})
</script>



