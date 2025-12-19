<template>
  <div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <h2 class="text-xl font-semibold">Badges</h2>
          <button @click="addItem" class="px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">+ Neu</button>
          <div v-if="hasPendingIcon" class="flex items-center space-x-2">
            <BellIcon class="w-4 h-4 text-amber-500" />
            <span class="text-sm text-amber-600 font-medium">{{ pendingIconCount }}</span>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="filterPendingIcon" class="sr-only peer" />
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
            <span class="font-medium">{{ item.name }}</span>
            <BellIcon v-if="item.status === 'pending_icon'" class="w-4 h-4 text-amber-500" />
            <span :class="item.status === 'released' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="px-2 py-0.5 text-xs rounded-full">
              {{ item.status === 'released' ? 'Veröffentlicht' : 'Icon fehlt' }}
            </span>
          </div>
          <div class="text-sm text-gray-600 truncate">
            {{ item.role?.name || 'Keine Rolle' }}
          </div>
          <div class="flex flex-wrap gap-1 mt-1 text-xs">
            <span class="px-2 py-0.5 bg-gray-100 rounded-full">{{ item.thresholds_count }} Schwellen</span>
            <span class="px-2 py-0.5 bg-gray-100 rounded-full">{{ item.earned_badges_count }} vergeben</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Modal -->
  <div v-if="editingItem" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-md max-h-[90vh] overflow-y-auto">
      <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white">
        <h3 class="text-lg font-semibold">{{ editingItem.id ? 'Badge bearbeiten' : 'Neues Badge' }}</h3>
        <button @click="editingItem = null" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>
      <form @submit.prevent="saveItem" class="p-4 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
          <input v-model="form.name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
          <select v-model="form.status" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
            <option value="pending_icon">Icon fehlt</option>
            <option value="released">Veröffentlicht</option>
          </select>
        </div>

        <!-- Role selection -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Rolle *</label>
          <input v-model="criteriaSearch.role" type="text" placeholder="Rolle suchen..." class="w-full px-3 py-2 border border-gray-300 rounded-md" />
          <div v-if="filteredCriteriaRoles.length > 0 && criteriaSearch.role && !form.role_id" class="mt-1 max-h-32 overflow-y-auto border border-gray-200 rounded-md">
            <button v-for="r in filteredCriteriaRoles" :key="r.id" type="button" @click="selectCriteriaRole(r)" class="w-full px-3 py-2 text-left hover:bg-gray-50 text-sm border-b border-gray-100 last:border-b-0">{{ r.name }}</button>
          </div>
          <div v-if="selectedCriteriaRole" class="mt-2 p-2 bg-blue-50 rounded-md flex items-center justify-between">
            <span class="text-sm font-medium">{{ selectedCriteriaRole.name }}</span>
            <button type="button" @click="clearCriteriaRole" class="text-gray-400 hover:text-gray-600">✕</button>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Beschreibung</label>
          <textarea v-model="form.description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
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
      <h3 class="text-lg font-semibold mb-4 text-red-600">Badge löschen?</h3>
      <p class="mb-4 text-gray-600">
        Möchtest du das Badge <strong>{{ editingItem?.name }}</strong> wirklich löschen?
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

const API_PATH = '/admin/badges'

// State
const items = ref<any[]>([])
const options = reactive({
  roles: [] as any[],
})
const loading = ref(false)
const filterPendingIcon = ref(false)
const searchQuery = ref('')
const editingItem = ref<any | null>(null)
const form = reactive({
  name: '',
  status: 'pending_icon',
  role_id: '',
  description: '',
})
const saving = ref(false)
const deleting = ref(false)
const error = ref('')
const showDeleteConfirm = ref(false)

// Type-ahead for role criteria
const criteriaSearch = reactive({ role: '' })
const selectedCriteriaRole = ref<any>(null)

const filteredCriteriaRoles = computed(() => {
  if (!criteriaSearch.role.trim()) return options.roles
  const q = criteriaSearch.role.toLowerCase()
  return options.roles.filter((r: any) => r.name.toLowerCase().includes(q))
})

function selectCriteriaRole(r: any) {
  selectedCriteriaRole.value = r
  form.role_id = r.id
  criteriaSearch.role = ''
}
function clearCriteriaRole() {
  selectedCriteriaRole.value = null
  form.role_id = ''
  criteriaSearch.role = ''
}

// Computed
const pendingIconCount = computed(() => items.value.filter(i => i.status === 'pending_icon').length)
const hasPendingIcon = computed(() => pendingIconCount.value > 0)
const filteredItems = computed(() => {
  let result = items.value
  if (filterPendingIcon.value) {
    result = result.filter(i => i.status === 'pending_icon')
  }
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(i =>
      i.name.toLowerCase().includes(q) ||
      i.role?.name?.toLowerCase().includes(q)
    )
  }
  return result
})

async function load() {
  loading.value = true
  try {
    const [itemsRes, optionsRes] = await Promise.all([
      apiClient.get(API_PATH),
      apiClient.get(`${API_PATH}/options`)
    ])
    items.value = itemsRes.data
    options.roles = optionsRes.data.roles
    filterPendingIcon.value = items.value.some(i => i.status === 'pending_icon')
  } catch (err) {
    console.error('Failed to load', err)
  } finally {
    loading.value = false
  }
}

function addItem() {
  editingItem.value = {}
  form.name = ''
  form.status = 'pending_icon'
  form.role_id = ''
  form.description = ''
  selectedCriteriaRole.value = null
  criteriaSearch.role = ''
  error.value = ''
}

function editItem(item: any) {
  editingItem.value = item
  form.name = item.name
  form.status = item.status
  form.role_id = item.role_id || ''
  form.description = item.description || ''
  selectedCriteriaRole.value = item.role_id ? options.roles.find((r: any) => r.id === item.role_id) : null
  criteriaSearch.role = ''
  error.value = ''
}

async function saveItem() {
  error.value = ''
  saving.value = true
  try {
    const data = {
      name: form.name,
      status: form.status,
      role_id: form.role_id || null,
      description: form.description || null,
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

