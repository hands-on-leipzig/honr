<template>
  <div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <h2 class="text-xl font-semibold">Saisons</h2>
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
        v-for="item in filteredItems"
        :key="item.id"
        @click="editItem(item)"
        class="px-4 py-3 hover:bg-gray-50 cursor-pointer"
      >
        <div class="flex items-center justify-between">
          <div class="flex-1 min-w-0">
            <div class="font-medium truncate">{{ item.name }}</div>
            <div class="text-xs text-gray-500">
              {{ item.start_year }}/{{ item.start_year + 1 }} · {{ item.first_program?.name }}
            </div>
            <div class="flex flex-wrap gap-1 mt-1 text-xs">
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
        <h3 class="text-lg font-semibold">{{ editingItem.id ? 'Saison bearbeiten' : 'Neue Saison' }}</h3>
        <button @click="editingItem = null" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>
      <form @submit.prevent="saveItem" class="p-4 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
          <input v-model="form.name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="z.B. CARGO CONNECT" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Startjahr *</label>
          <input v-model.number="form.start_year" type="number" required min="1998" max="2100" class="w-full px-3 py-2 border border-gray-300 rounded-md" />
          <p class="text-xs text-gray-500 mt-1">Wird als {{ form.start_year }}/{{ form.start_year + 1 }} angezeigt</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Programm *</label>
          <input
            v-model="programSearch"
            type="text"
            placeholder="Programm suchen..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md"
          />
          <div v-if="filteredPrograms.length > 0 && programSearch && !selectedProgram" class="mt-1 max-h-40 overflow-y-auto border border-gray-200 rounded-md">
            <button
              v-for="prog in filteredPrograms"
              :key="prog.id"
              type="button"
              @click="selectProgram(prog)"
              class="w-full px-3 py-2 text-left hover:bg-gray-50 text-sm border-b border-gray-100 last:border-b-0"
            >
              {{ prog.name }}
            </button>
          </div>
          <div v-if="selectedProgram" class="mt-2 p-2 bg-blue-50 rounded-md flex items-center justify-between">
            <span class="text-sm font-medium">{{ selectedProgram.name }}</span>
            <button type="button" @click="clearProgram" class="text-gray-400 hover:text-gray-600">✕</button>
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
      <h3 class="text-lg font-semibold mb-4 text-red-600">Saison löschen?</h3>
      <p class="mb-4 text-gray-600">
        Möchtest du die Saison <strong>{{ editingItem?.name }} ({{ editingItem?.start_year }}/{{ editingItem?.start_year + 1 }})</strong> wirklich löschen?
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

const API_PATH = '/admin/seasons'

// State
const items = ref<any[]>([])
const programs = ref<any[]>([])
const loading = ref(false)
const searchQuery = ref('')
const editingItem = ref<any | null>(null)

// Computed
const filteredItems = computed(() => {
  if (!searchQuery.value.trim()) return items.value
  const q = searchQuery.value.toLowerCase()
  return items.value.filter(i =>
    i.name.toLowerCase().includes(q) ||
    i.first_program?.name?.toLowerCase().includes(q) ||
    String(i.start_year).includes(q)
  )
})
const form = reactive({
  name: '',
  start_year: new Date().getFullYear(),
  first_program_id: null as number | null,
})
const saving = ref(false)
const deleting = ref(false)
const error = ref('')
const showDeleteConfirm = ref(false)

// Type-ahead for program
const programSearch = ref('')
const selectedProgram = ref<any>(null)

const filteredPrograms = computed(() => {
  if (!programSearch.value.trim()) return programs.value
  const q = programSearch.value.toLowerCase()
  return programs.value.filter((p: any) => p.name.toLowerCase().includes(q))
})

function selectProgram(prog: any) {
  selectedProgram.value = prog
  form.first_program_id = prog.id
  programSearch.value = ''
}

function clearProgram() {
  selectedProgram.value = null
  form.first_program_id = null
  programSearch.value = ''
}

// Methods
async function load() {
  loading.value = true
  try {
    const [seasonsRes, programsRes] = await Promise.all([
      apiClient.get(API_PATH),
      apiClient.get('/admin/first-programs'),
    ])
    items.value = seasonsRes.data
    programs.value = programsRes.data
  } catch (err) {
    console.error('Failed to load', err)
  } finally {
    loading.value = false
  }
}

function addItem() {
  editingItem.value = {}
  form.name = ''
  form.start_year = new Date().getFullYear()
  form.first_program_id = null
  selectedProgram.value = null
  programSearch.value = ''
  error.value = ''
}

function editItem(item: any) {
  editingItem.value = item
  form.name = item.name
  form.start_year = item.start_year
  form.first_program_id = item.first_program_id
  selectedProgram.value = programs.value.find((p: any) => p.id === item.first_program_id) || null
  programSearch.value = ''
  error.value = ''
}

async function saveItem() {
  error.value = ''
  saving.value = true
  try {
    const data = {
      name: form.name,
      start_year: form.start_year,
      first_program_id: form.first_program_id,
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

