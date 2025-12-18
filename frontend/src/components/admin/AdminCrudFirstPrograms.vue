<template>
  <div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <h2 class="text-xl font-semibold">FIRST Programme</h2>
          <button @click="addItem" class="px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">+ Neu</button>
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
            <div class="font-medium truncate">{{ item.name }}</div>
            <div class="text-xs text-gray-500">
              {{ formatDateRange(item.valid_from, item.valid_to) }}
            </div>
            <div class="flex flex-wrap gap-1 mt-1 text-xs">
              <span class="px-2 py-0.5 bg-gray-100 rounded-full">{{ item.seasons_count }} Saisons</span>
              <span class="px-2 py-0.5 bg-gray-100 rounded-full">{{ item.roles_count }} Rollen</span>
              <span class="px-2 py-0.5 bg-gray-100 rounded-full">{{ item.events_count }} Veranst.</span>
              <span class="px-2 py-0.5 bg-gray-100 rounded-full">{{ item.badges_count }} Badges</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Modal -->
  <div v-if="editingItem" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-md">
      <div class="p-4 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-lg font-semibold">{{ editingItem.id ? 'Programm bearbeiten' : 'Neues Programm' }}</h3>
        <button @click="editingItem = null" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>
      <form @submit.prevent="saveItem" class="p-4 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
          <input v-model="form.name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Gültig ab</label>
          <input v-model="form.valid_from" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Gültig bis</label>
          <input v-model="form.valid_to" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md" />
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
      <h3 class="text-lg font-semibold mb-4 text-red-600">Programm löschen?</h3>
      <p class="mb-4 text-gray-600">
        Möchtest du das Programm <strong>{{ editingItem?.name }}</strong> wirklich löschen?
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
import apiClient from '@/api/client'

const emit = defineEmits(['close'])

const API_PATH = '/admin/first-programs'

// State
const items = ref<any[]>([])
const loading = ref(false)
const searchQuery = ref('')
const editingItem = ref<any | null>(null)

// Computed
const filteredItems = computed(() => {
  if (!searchQuery.value.trim()) return items.value
  const q = searchQuery.value.toLowerCase()
  return items.value.filter(i => i.name.toLowerCase().includes(q))
})
const form = reactive({
  name: '',
  sort_order: 1,
  valid_from: '',
  valid_to: '',
})
const saving = ref(false)
const deleting = ref(false)
const error = ref('')
const showDeleteConfirm = ref(false)
const draggingIndex = ref<number | null>(null)
const dragOverIndex = ref<number | null>(null)

// Methods
const formatDateRange = (from: string, to: string) => {
  if (!from && !to) return 'Unbegrenzt gültig'
  const f = from ? new Date(from).toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '–'
  const t = to ? new Date(to).toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '–'
  return `${f} bis ${t}`
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

function addItem() {
  editingItem.value = {}
  form.name = ''
  form.sort_order = items.value.length + 1
  form.valid_from = ''
  form.valid_to = ''
  error.value = ''
}

function editItem(item: any) {
  editingItem.value = item
  form.name = item.name
  form.sort_order = item.sort_order
  form.valid_from = item.valid_from ? item.valid_from.split('T')[0] : ''
  form.valid_to = item.valid_to ? item.valid_to.split('T')[0] : ''
  error.value = ''
}

async function saveItem() {
  error.value = ''
  saving.value = true
  try {
    const data = {
      name: form.name,
      sort_order: form.sort_order,
      valid_from: form.valid_from || null,
      valid_to: form.valid_to || null,
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
  e.dataTransfer!.effectAllowed = 'move'
}

function onDragOver(e: DragEvent, index: number) {
  dragOverIndex.value = index
}

function onDrop(e: DragEvent, toIndex: number) {
  const fromIndex = draggingIndex.value
  if (fromIndex === null || fromIndex === toIndex) return
  
  const list = [...items.value]
  const [moved] = list.splice(fromIndex, 1)
  list.splice(toIndex, 0, moved)
  items.value = list
  
  updateOrder()
}

function onDragEnd() {
  draggingIndex.value = null
  dragOverIndex.value = null
}

async function updateOrder() {
  const updates = items.value.map((p, i) => ({ id: p.id, sort_order: i + 1 }))
  try {
    await apiClient.put(`${API_PATH}/reorder`, { programs: updates })
  } catch (err) {
    console.error('Failed to update order', err)
    await load()
  }
}

onMounted(load)
</script>

