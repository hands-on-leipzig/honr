<template>
  <div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <h2 class="text-xl font-semibold">Volunteer-Einsätze</h2>
          <button @click="addItem" class="px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">+ Neu</button>
          <div v-if="hasUnrecognized" class="flex items-center space-x-2">
            <BellIcon class="w-4 h-4 text-amber-500" />
            <span class="text-sm text-amber-600 font-medium">{{ unrecognizedCount }}</span>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="filterUnrecognized" class="sr-only peer" />
              <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
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

    <div v-else class="divide-y divide-gray-200">
      <div
        v-for="item in filteredItems"
        :key="item.id"
        @click="editItem(item)"
        class="px-4 py-3 hover:bg-gray-50 cursor-pointer"
      >
        <div class="flex-1 min-w-0">
          <div class="flex items-center space-x-2">
            <span class="font-medium">{{ item.user?.nickname || item.user?.email }}</span>
            <BellIcon v-if="!item.is_recognized" class="w-4 h-4 text-amber-500" />
            <span :class="item.is_recognized ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="px-2 py-0.5 text-xs rounded-full">
              {{ item.is_recognized ? 'Anerkannt' : 'Nicht anerkannt' }}
            </span>
          </div>
          <div class="text-sm text-gray-600 truncate">
            {{ item.role?.name }} · {{ formatDate(item.event?.date) }}
          </div>
          <div class="text-xs text-gray-500 truncate">
            {{ item.event?.level?.name }} · {{ item.event?.location?.name }}<span v-if="item.event?.location?.city">, {{ item.event.location.city }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Modal -->
  <div v-if="editingItem" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-md max-h-[90vh] overflow-y-auto">
      <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white">
        <h3 class="text-lg font-semibold">{{ editingItem.id ? 'Einsatz bearbeiten' : 'Neuer Einsatz' }}</h3>
        <button @click="editingItem = null" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>
      <form @submit.prevent="saveItem" class="p-4 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Benutzer *</label>
          <select v-model="form.user_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
            <option value="">Bitte wählen</option>
            <option v-for="u in options.users" :key="u.id" :value="u.id">
              {{ u.nickname || u.email }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Rolle *</label>
          <select v-model="form.role_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
            <option value="">Bitte wählen</option>
            <option v-for="r in options.roles" :key="r.id" :value="r.id">
              {{ r.name }} ({{ r.first_program?.name }})
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Veranstaltung *</label>
          <select v-model="form.event_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
            <option value="">Bitte wählen</option>
            <option v-for="e in options.events" :key="e.id" :value="e.id">
              {{ formatDate(e.date) }} · {{ e.level?.name }} · {{ e.location?.name }}
            </option>
          </select>
        </div>

        <div v-if="editingItem.id" class="text-sm text-gray-500 space-y-1">
          <div>
            Status: <span :class="editingItem.is_recognized ? 'text-green-600' : 'text-amber-600'">
              {{ editingItem.is_recognized ? 'Anerkannt' : 'Nicht anerkannt' }}
            </span>
          </div>
          <div v-if="editingItem.recognized_at">
            Anerkannt am: {{ formatDateTime(editingItem.recognized_at) }}
          </div>
        </div>

        <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>

        <div class="flex gap-2 pt-2">
          <button type="button" @click="editingItem = null" class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
            Abbrechen
          </button>
          <button v-if="editingItem.id" type="button" @click="showDeleteConfirm = true" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
            Löschen
          </button>
          <button type="submit" :disabled="saving" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
            {{ saving ? 'Speichern...' : 'Speichern' }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-sm p-6">
      <h3 class="text-lg font-semibold mb-4 text-red-600">Einsatz löschen?</h3>
      <p class="mb-4 text-gray-600">
        Möchtest du diesen Einsatz wirklich löschen?
      </p>
      <div class="flex gap-2">
        <button @click="showDeleteConfirm = false" class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
          Abbrechen
        </button>
        <button @click="deleteItem" :disabled="deleting" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50">
          {{ deleting ? 'Löschen...' : 'Löschen' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { BellIcon } from '@heroicons/vue/24/solid'
import apiClient from '@/api/client'

const emit = defineEmits(['close'])

const API_PATH = '/admin/engagements'

// State
const items = ref<any[]>([])
const options = reactive({
  users: [] as any[],
  roles: [] as any[],
  events: [] as any[],
})
const loading = ref(false)
const filterUnrecognized = ref(false)
const searchQuery = ref('')
const editingItem = ref<any | null>(null)
const form = reactive({
  user_id: '',
  role_id: '',
  event_id: '',
})
const saving = ref(false)
const deleting = ref(false)
const error = ref('')
const showDeleteConfirm = ref(false)

// Computed
const unrecognizedCount = computed(() => items.value.filter(i => !i.is_recognized).length)
const hasUnrecognized = computed(() => unrecognizedCount.value > 0)
const filteredItems = computed(() => {
  let result = items.value
  if (filterUnrecognized.value) {
    result = result.filter(i => !i.is_recognized)
  }
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(i =>
      i.user?.nickname?.toLowerCase().includes(q) ||
      i.user?.email?.toLowerCase().includes(q) ||
      i.role?.name?.toLowerCase().includes(q) ||
      i.event?.level?.name?.toLowerCase().includes(q) ||
      i.event?.location?.name?.toLowerCase().includes(q)
    )
  }
  return result
})

// Methods
const formatDate = (dateStr: string) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const formatDateTime = (dateStr: string) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

async function load() {
  loading.value = true
  try {
    const [itemsRes, optionsRes] = await Promise.all([
      apiClient.get(API_PATH),
      apiClient.get(`${API_PATH}/options`)
    ])
    items.value = itemsRes.data
    options.users = optionsRes.data.users
    options.roles = optionsRes.data.roles
    options.events = optionsRes.data.events
    filterUnrecognized.value = items.value.some(i => !i.is_recognized)
  } catch (err) {
    console.error('Failed to load', err)
  } finally {
    loading.value = false
  }
}

function addItem() {
  editingItem.value = {}
  form.user_id = ''
  form.role_id = ''
  form.event_id = ''
  error.value = ''
}

function editItem(item: any) {
  editingItem.value = item
  form.user_id = item.user_id
  form.role_id = item.role_id
  form.event_id = item.event_id
  error.value = ''
}

async function saveItem() {
  error.value = ''
  saving.value = true
  try {
    const data = {
      user_id: form.user_id,
      role_id: form.role_id,
      event_id: form.event_id,
    }
    if (editingItem.value.id) {
      await apiClient.put(`${API_PATH}/${editingItem.value.id}`, data)
    } else {
      await apiClient.post(API_PATH, data)
    }
    await load()
    editingItem.value = null
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Fehler beim Speichern.'
  } finally {
    saving.value = false
  }
}

async function deleteItem() {
  deleting.value = true
  try {
    await apiClient.delete(`${API_PATH}/${editingItem.value.id}`)
    await load()
    showDeleteConfirm.value = false
    editingItem.value = null
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Fehler beim Löschen.'
    showDeleteConfirm.value = false
  } finally {
    deleting.value = false
  }
}

onMounted(load)
</script>

