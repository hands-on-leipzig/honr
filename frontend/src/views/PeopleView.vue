<template>
  <div class="p-4 pb-32">
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

    <!-- Filter Label or Search Field (Fixed at Bottom) -->
    <div class="fixed bottom-24 left-0 right-0 px-4 pb-4">
      <div class="bg-white rounded-lg shadow-lg p-4">
        <!-- Filter Label -->
        <div v-if="hasFilter" class="flex items-center justify-between">
          <span class="text-sm font-medium text-gray-700">{{ filterLabel }}</span>
          <button
            @click="clearFilter"
            class="text-blue-600 hover:text-blue-800 text-sm font-medium"
          >
            Filter entfernen
          </button>
        </div>
        <!-- Search Field -->
        <input
          v-else
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
import { ref, watch, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import apiClient from '@/api/client'

const router = useRouter()
const route = useRoute()
const users = ref<any[]>([])
const loading = ref(true)
const searchQuery = ref('')

// Get filter from query params
const filterType = computed(() => route.query.filter_type as string | undefined)
const filterId = computed(() => route.query.filter_id ? Number(route.query.filter_id) : undefined)
const filterLabel = computed(() => route.query.filter_label as string | undefined)

const hasFilter = computed(() => !!filterType.value && !!filterId.value)

async function loadUsers() {
  loading.value = true
  try {
    const params = new URLSearchParams()
    params.append('status', 'active')
    
    // Apply filter if present
    if (hasFilter.value && filterType.value && filterId.value) {
      if (filterType.value === 'role') {
        params.append('role_id', filterId.value.toString())
      } else if (filterType.value === 'program') {
        params.append('first_program_id', filterId.value.toString())
      } else if (filterType.value === 'season') {
        params.append('season_id', filterId.value.toString())
      } else if (filterType.value === 'country') {
        params.append('country_id', filterId.value.toString())
      }
    }
    
    // Search only if no filter is active
    if (!hasFilter.value && searchQuery.value.trim()) {
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

function clearFilter() {
  router.push('/people')
}

// Debounce search (only when no filter)
let searchTimeout: ReturnType<typeof setTimeout> | null = null
watch(searchQuery, () => {
  if (hasFilter.value) return // Don't search when filter is active
  
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
  searchTimeout = setTimeout(() => {
    loadUsers()
  }, 300)
})

// Reload when route query changes (filter applied)
watch(() => route.query, () => {
  loadUsers()
}, { deep: true })

onMounted(() => {
  loadUsers()
})
</script>

