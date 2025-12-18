<template>
  <div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <h2 class="text-xl font-semibold">Rollen</h2>
          <button @click="addItem" class="px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">+ Neu</button>
          <div v-if="hasPending" class="flex items-center space-x-2">
            <BellIcon class="w-4 h-4 text-amber-500" />
            <span class="text-sm text-amber-600 font-medium">{{ pendingCount }}</span>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="filterPending" class="sr-only peer" />
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
        v-for="(item, index) in filteredItems"
        :key="item.id"
        draggable="true"
        @dragstart="onDragStart($event, index)"
        @dragover.prevent="onDragOver($event, index)"
        @drop="onDrop($event, index)"
        @dragend="onDragEnd"
        @click="editItem(item)"
        :class="[
          'px-4 py-3 hover:bg-gray-50 cursor-pointer transition-all',
          dragOverIndex === index ? 'border-t-2 border-blue-500' : '',
          draggingIndex === index ? 'opacity-50' : ''
        ]"
      >
        <div class="flex items-center space-x-3">
          <span class="text-gray-400 cursor-grab">⋮⋮</span>
          <div class="flex-1 min-w-0">
            <div class="flex items-center space-x-2">
              <span class="font-medium truncate">{{ item.name }}</span>
              <BellIcon v-if="item.status === 'pending'" class="w-4 h-4 text-amber-500" />
              <span :class="statusBadgeClass(item.status)" class="px-2 py-0.5 text-xs rounded-full">
                {{ statusLabel(item.status) }}
              </span>
            </div>
            <div class="text-xs text-gray-500 truncate">
              {{ item.first_program?.name }}
              <span v-if="item.role_category" class="ml-2 text-gray-400">({{ categoryLabel(item.role_category) }})</span>
            </div>
            <div class="flex flex-wrap gap-1 mt-1 text-xs">
              <span class="px-2 py-0.5 bg-gray-100 rounded-full">{{ item.engagements_count }} Einsätze</span>
              <span class="px-2 py-0.5 bg-gray-100 rounded-full">{{ item.badges_count }} Badges</span>
              <span v-if="item.proposed_by_user" class="px-2 py-0.5 bg-gray-100 rounded-full">
                von {{ item.proposed_by_user.nickname || item.proposed_by_user.email }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Modal -->
  <div v-if="editingItem" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-md max-h-[90vh] overflow-y-auto">
      <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white">
        <h3 class="text-lg font-semibold">{{ editingItem.id ? 'Rolle bearbeiten' : 'Neue Rolle' }}</h3>
        <button @click="editingItem = null" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>
      <form @submit.prevent="saveItem" class="p-4 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
          <input v-model="form.name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="z.B. Schiedsrichter:in" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Programm *</label>
          <select v-model="form.first_program_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
            <option value="">Bitte wählen</option>
            <option v-for="p in programs" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Kategorie</label>
          <select v-model="form.role_category" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            <option value="">Keine</option>
            <option value="volunteer">Volunteer</option>
            <option value="regional_partner">Regional Partner</option>
            <option value="coach">Coach</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Beschreibung</label>
          <textarea v-model="form.description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Optionale Beschreibung"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
          <select v-model="form.status" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
            <option value="pending">Ausstehend</option>
            <option value="approved">Genehmigt</option>
            <option value="rejected">Abgelehnt</option>
          </select>
        </div>

        <div v-if="editingItem.proposed_by_user" class="text-sm text-gray-500">
          Vorgeschlagen von: {{ editingItem.proposed_by_user.nickname || editingItem.proposed_by_user.email }}
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
      <h3 class="text-lg font-semibold mb-4 text-red-600">Rolle löschen?</h3>
      <p class="mb-4 text-gray-600">
        Möchtest du die Rolle <strong>{{ editingItem?.name }}</strong> wirklich löschen?
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

const API_PATH = '/admin/roles'

// State
const items = ref<any[]>([])
const programs = ref<any[]>([])
const loading = ref(false)
const filterPending = ref(false)
const searchQuery = ref('')
const editingItem = ref<any | null>(null)
const form = reactive({
  name: '',
  description: '',
  first_program_id: '',
  role_category: '',
  status: 'approved',
})
const saving = ref(false)
const deleting = ref(false)
const error = ref('')
const showDeleteConfirm = ref(false)
const draggingIndex = ref<number | null>(null)
const dragOverIndex = ref<number | null>(null)


// Computed
const pendingCount = computed(() => items.value.filter(i => i.status === 'pending').length)
const hasPending = computed(() => pendingCount.value > 0)
const filteredItems = computed(() => {
  let result = items.value
  if (filterPending.value) {
    result = result.filter(i => i.status === 'pending')
  }
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(i =>
      i.name.toLowerCase().includes(q) ||
      i.first_program?.name?.toLowerCase().includes(q)
    )
  }
  return result
})

// Methods
const statusLabel = (status: string) => {
  const labels: Record<string, string> = {
    pending: 'Ausstehend',
    approved: 'Genehmigt',
    rejected: 'Abgelehnt',
  }
  return labels[status] || status
}

const statusBadgeClass = (status: string) => {
  const classes: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const categoryLabel = (cat: string) => {
  const labels: Record<string, string> = {
    volunteer: 'Volunteer',
    regional_partner: 'Regional Partner',
    coach: 'Coach',
  }
  return labels[cat] || cat
}

async function load() {
  loading.value = true
  try {
    const [itemsRes, programsRes] = await Promise.all([
      apiClient.get(API_PATH),
      apiClient.get(`${API_PATH}/programs`)
    ])
    items.value = itemsRes.data
    programs.value = programsRes.data
    filterPending.value = items.value.some(i => i.status === 'pending')
  } catch (err) {
    console.error('Failed to load', err)
  } finally {
    loading.value = false
  }
}

function addItem() {
  editingItem.value = {}
  form.name = ''
  form.description = ''
  form.first_program_id = ''
  form.role_category = ''
  form.status = 'approved'
  error.value = ''
}

function editItem(item: any) {
  editingItem.value = item
  form.name = item.name
  form.description = item.description || ''
  form.first_program_id = item.first_program_id
  form.role_category = item.role_category || ''
  form.status = item.status
  error.value = ''
}

async function saveItem() {
  error.value = ''
  saving.value = true
  try {
    const data = {
      name: form.name,
      description: form.description || null,
      first_program_id: form.first_program_id,
      role_category: form.role_category || null,
      status: form.status,
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

// Drag and drop
function onDragStart(e: DragEvent, index: number) {
  draggingIndex.value = index
  if (e.dataTransfer) {
    e.dataTransfer.effectAllowed = 'move'
  }
}

function onDragOver(e: DragEvent, index: number) {
  dragOverIndex.value = index
}

function onDragEnd() {
  draggingIndex.value = null
  dragOverIndex.value = null
}

async function onDrop(e: DragEvent, toIndex: number) {
  e.preventDefault()
  const fromIndex = draggingIndex.value
  if (fromIndex === null || fromIndex === toIndex) {
    onDragEnd()
    return
  }

  const itemsCopy = [...items.value]
  const [moved] = itemsCopy.splice(fromIndex, 1)
  itemsCopy.splice(toIndex, 0, moved)
  items.value = itemsCopy

  onDragEnd()

  try {
    await apiClient.put(`${API_PATH}/reorder`, {
      ids: items.value.map(i => i.id)
    })
  } catch (err) {
    console.error('Failed to reorder', err)
    await load()
  }
}

onMounted(load)
</script>

