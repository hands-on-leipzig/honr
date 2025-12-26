<template>
  <div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <h2 class="text-xl font-semibold">Veranstaltungen</h2>
          <button @click="addItem" class="px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">+ Neu</button>
          <div v-if="hasPending" class="flex items-center space-x-2">
            <BellIcon :class="['w-4 h-4', STATUS_WARNING.icon]" />
            <span :class="['text-sm font-medium', STATUS_WARNING.text]">{{ pendingCount }}</span>
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
        v-for="item in filteredItems"
        :key="item.id"
        @click="editItem(item)"
        class="px-4 py-3 hover:bg-gray-50 cursor-pointer"
      >
        <div class="flex-1 min-w-0">
          <div class="flex items-center space-x-2">
            <span class="font-medium">{{ formatDate(item.date) }}</span>
            <BellIcon v-if="item.status === 'pending'" :class="['w-4 h-4', STATUS_WARNING.icon]" />
            <span :class="statusBadgeClass(item.status)" class="px-2 py-0.5 text-xs rounded-full">
              {{ statusLabel(item.status) }}
            </span>
          </div>
          <div class="text-sm text-gray-600 truncate">
            {{ item.level?.name }} · {{ item.location?.name }}<span v-if="item.location?.city">, {{ item.location.city }}</span>
          </div>
          <div class="text-xs text-gray-500 truncate">
            {{ item.first_program?.name }} · {{ item.season?.name }}
          </div>
          <div class="flex flex-wrap gap-1 mt-1 text-xs">
            <span class="px-2 py-0.5 bg-gray-100 rounded-full">{{ item.engagements_count }} Einsätze</span>
            <span v-if="item.proposed_by_user" class="px-2 py-0.5 bg-gray-100 rounded-full">
              von {{ item.proposed_by_user.nickname || item.proposed_by_user.email }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Modal -->
  <div v-if="editingItem" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-md max-h-[90vh] overflow-y-auto">
      <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white">
        <h3 class="text-lg font-semibold">{{ editingItem.id ? 'Veranstaltung bearbeiten' : 'Neue Veranstaltung' }}</h3>
        <button @click="editingItem = null" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>
      <form @submit.prevent="saveItem" class="p-4 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Programm *</label>
          <select v-model="form.first_program_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
            <option value="">Bitte wählen</option>
            <option v-for="p in options.programs" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Saison *</label>
          <select v-model="form.season_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
            <option value="">Bitte wählen</option>
            <option v-for="s in filteredSeasons" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Level *</label>
          <select v-model="form.level_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
            <option value="">Bitte wählen</option>
            <option v-for="l in options.levels" :key="l.id" :value="l.id">{{ l.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Standort *</label>
          <input v-model="locationSearch" type="text" placeholder="Standort suchen..." class="w-full px-3 py-2 border border-gray-300 rounded-md" />
          <div v-if="filteredLocationsSearch.length > 0 && locationSearch && !selectedLocation" class="mt-1 max-h-40 overflow-y-auto border border-gray-200 rounded-md">
            <button v-for="loc in filteredLocationsSearch" :key="loc.id" type="button" @click="selectLocation(loc)" class="w-full px-3 py-2 text-left hover:bg-gray-50 text-sm border-b border-gray-100 last:border-b-0">{{ loc.name }}{{ loc.city ? `, ${loc.city}` : '' }}</button>
          </div>
          <div v-if="selectedLocation" class="mt-2 p-2 bg-blue-50 rounded-md flex items-center justify-between">
            <span class="text-sm font-medium">{{ selectedLocation.name }}{{ selectedLocation.city ? `, ${selectedLocation.city}` : '' }}</span>
            <button type="button" @click="clearLocation" class="text-gray-400 hover:text-gray-600">✕</button>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Datum *</label>
          <input v-model="form.date" type="date" required class="w-full px-3 py-2 border border-gray-300 rounded-md" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
          <select v-model="form.status" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
            <option value="pending">Ausstehend</option>
            <option value="approved">Genehmigt</option>
            <option value="rejected">Abgelehnt</option>
          </select>
        </div>

        <div v-if="form.status === 'rejected'">
          <label class="block text-sm font-medium text-gray-700 mb-1">Grund für Ablehnung *</label>
          <textarea 
            v-model="form.rejection_reason" 
            required
            rows="3" 
            placeholder="Bitte gib einen Grund für die Ablehnung an..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md"
          ></textarea>
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
      <h3 class="text-lg font-semibold mb-4 text-red-600">Veranstaltung löschen?</h3>
      <p class="mb-4 text-gray-600">
        Möchtest du diese Veranstaltung wirklich löschen?
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
import { getStatusColorClass, STATUS_WARNING } from '@/constants/uiColors'

const emit = defineEmits(['close'])

const API_PATH = '/admin/events'

// State
const items = ref<any[]>([])
const options = reactive({
  programs: [] as any[],
  seasons: [] as any[],
  levels: [] as any[],
  locations: [] as any[],
})
const loading = ref(false)
const filterPending = ref(false)
const searchQuery = ref('')
const editingItem = ref<any | null>(null)
const form = reactive({
  first_program_id: '',
  season_id: '',
  level_id: '',
  location_id: '',
  date: '',
  status: 'approved',
  rejection_reason: '',
})
const saving = ref(false)
const deleting = ref(false)
const error = ref('')
const showDeleteConfirm = ref(false)

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
      i.level?.name?.toLowerCase().includes(q) ||
      i.location?.name?.toLowerCase().includes(q) ||
      i.location?.city?.toLowerCase().includes(q) ||
      i.first_program?.name?.toLowerCase().includes(q) ||
      i.season?.name?.toLowerCase().includes(q)
    )
  }
  return result
})

const filteredSeasons = computed(() => {
  if (!form.first_program_id) return options.seasons
  return options.seasons.filter(s => s.first_program_id == form.first_program_id)
})

// Type-ahead state for location
const locationSearch = ref('')
const selectedLocation = ref<any>(null)

const filteredLocationsSearch = computed(() => {
  if (!locationSearch.value.trim()) return options.locations
  const q = locationSearch.value.toLowerCase()
  return options.locations.filter((loc: any) => 
    loc.name.toLowerCase().includes(q) || loc.city?.toLowerCase().includes(q)
  )
})

function selectLocation(loc: any) {
  selectedLocation.value = loc
  form.location_id = loc.id
  locationSearch.value = ''
}
function clearLocation() {
  selectedLocation.value = null
  form.location_id = ''
  locationSearch.value = ''
}

// Methods
const formatDate = (dateStr: string) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const statusLabel = (status: string) => {
  const labels: Record<string, string> = {
    pending: 'Ausstehend',
    approved: 'Genehmigt',
    rejected: 'Abgelehnt',
  }
  return labels[status] || status
}

const statusBadgeClass = (status: string) => {
  return getStatusColorClass(status)
}

async function load() {
  loading.value = true
  try {
    const [itemsRes, optionsRes] = await Promise.all([
      apiClient.get(API_PATH),
      apiClient.get(`${API_PATH}/options`)
    ])
    items.value = itemsRes.data
    options.programs = optionsRes.data.programs
    options.seasons = optionsRes.data.seasons
    options.levels = optionsRes.data.levels
    options.locations = optionsRes.data.locations
    filterPending.value = items.value.some(i => i.status === 'pending')
  } catch (err) {
    console.error('Failed to load', err)
  } finally {
    loading.value = false
  }
}

function addItem() {
  editingItem.value = {}
  form.first_program_id = ''
  form.season_id = ''
  form.level_id = ''
  form.location_id = ''
  form.date = ''
  form.status = 'approved'
  form.rejection_reason = ''
  selectedLocation.value = null
  locationSearch.value = ''
  error.value = ''
}

function editItem(item: any) {
  editingItem.value = item
  form.first_program_id = item.first_program_id
  form.season_id = item.season_id
  form.level_id = item.level_id
  form.location_id = item.location_id
  form.date = item.date?.split('T')[0] || ''
  form.status = item.status
  form.rejection_reason = item.rejection_reason || ''
  selectedLocation.value = options.locations.find((loc: any) => loc.id === item.location_id) || null
  locationSearch.value = ''
  error.value = ''
}

async function saveItem() {
  error.value = ''
  saving.value = true
  try {
    const data = {
      first_program_id: form.first_program_id,
      season_id: form.season_id,
      level_id: form.level_id,
      location_id: form.location_id,
      date: form.date,
      status: form.status,
      rejection_reason: form.status === 'rejected' ? form.rejection_reason : null,
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

