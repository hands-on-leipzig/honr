<template>
  <div class="p-4 pb-32">
    <h1 class="text-2xl font-bold mb-4">Personen</h1>

    <!-- User List -->
    <div v-if="loading" class="bg-white rounded-lg shadow p-6">
      <p class="text-gray-600 text-center py-8">Laden...</p>
    </div>

    <div v-else-if="users.length === 0" class="bg-white rounded-lg shadow p-6">
      <p class="text-gray-600 text-center py-8">
        {{ searchQuery ? 'Keine Benutzer gefunden.' : 'Noch keine Benutzer vorhanden.' }}
      </p>
    </div>

    <div v-else class="bg-white rounded-lg shadow divide-y divide-gray-200">
      <button
        v-for="user in users"
        :key="user.id"
        @click="selectUser(user)"
        class="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors"
      >
        <div class="font-medium text-gray-900">{{ user.nickname }}</div>
      </button>
    </div>

    <!-- Search Field (Fixed at Bottom) -->
    <div class="fixed bottom-24 left-0 right-0 px-4 pb-4">
      <div class="bg-white rounded-lg shadow-lg p-4">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Benutzer suchen..."
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '@/api/client'

const router = useRouter()
const users = ref<any[]>([])
const loading = ref(true)
const searchQuery = ref('')

async function loadUsers() {
  loading.value = true
  try {
    const params = new URLSearchParams()
    params.append('status', 'active')
    if (searchQuery.value.trim()) {
      params.append('search', searchQuery.value.trim())
    }
    const response = await apiClient.get(`/users?${params.toString()}`)
    users.value = response.data
  } catch (err) {
    console.error('Failed to load users', err)
    users.value = []
  } finally {
    loading.value = false
  }
}

function selectUser(user: any) {
  router.push(`/user/${user.id}`)
}

// Debounce search
let searchTimeout: ReturnType<typeof setTimeout> | null = null
watch(searchQuery, () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
  searchTimeout = setTimeout(() => {
    loadUsers()
  }, 300)
})

onMounted(() => {
  loadUsers()
})
</script>

