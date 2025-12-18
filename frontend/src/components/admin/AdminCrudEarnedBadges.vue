<template>
  <div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <h2 class="text-xl font-semibold">Verdiente Badges</h2>
          <span class="text-sm text-gray-500">(nur Ansicht)</span>
        </div>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Suchen..."
        class="mt-3 w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
      />
    </div>

    <div v-if="loading" class="p-4 text-center text-gray-500">Laden...</div>

    <div v-else-if="filteredItems.length === 0" class="p-4 text-center text-gray-500">
      Keine verdienten Badges gefunden.
    </div>

    <div v-else class="divide-y divide-gray-200">
      <div
        v-for="item in filteredItems"
        :key="item.id"
        class="px-4 py-3"
      >
        <div class="flex-1 min-w-0">
          <div class="flex items-center space-x-2">
            <span class="font-medium">{{ item.user?.nickname || item.user?.email }}</span>
            <span :class="item.badge?.type === 'grow' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'" class="px-2 py-0.5 text-xs rounded-full">
              {{ item.badge?.type === 'grow' ? 'Grow' : 'Tick' }}
            </span>
          </div>
          <div class="text-sm text-gray-600">
            {{ item.badge?.name }}
            <span v-if="item.current_threshold" class="text-gray-500">
              · {{ item.current_threshold.level_name || `Stufe ${item.current_threshold.threshold_value}` }}
            </span>
          </div>
          <div class="text-xs text-gray-500">
            Verdient am {{ formatDate(item.earned_at) }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import apiClient from '@/api/client'

const emit = defineEmits(['close'])

const API_PATH = '/admin/earned-badges'

// State
const items = ref<any[]>([])
const loading = ref(false)
const searchQuery = ref('')

// Computed
const filteredItems = computed(() => {
  if (!searchQuery.value.trim()) return items.value
  const q = searchQuery.value.toLowerCase()
  return items.value.filter(i =>
    i.user?.nickname?.toLowerCase().includes(q) ||
    i.user?.email?.toLowerCase().includes(q) ||
    i.badge?.name?.toLowerCase().includes(q)
  )
})

// Methods
const formatDate = (dateStr: string) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

async function load() {
  loading.value = true
  try {
    const response = await apiClient.get(API_PATH)
    items.value = response.data
  } catch (err) {
    console.error('Failed to load', err)
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

