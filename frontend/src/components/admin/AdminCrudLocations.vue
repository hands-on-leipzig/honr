<template>
  <div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <h2 class="text-xl font-semibold">Standorte</h2>
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
        v-for="item in filteredItems"
        :key="item.id"
        @click="editItem(item)"
        class="px-4 py-3 hover:bg-gray-50 cursor-pointer"
      >
        <div class="flex items-center space-x-3">
          <div class="flex-1 min-w-0">
            <div class="flex items-center space-x-2">
              <span class="font-medium truncate">{{ item.name }}</span>
              <BellIcon v-if="item.status === 'pending'" class="w-4 h-4 text-amber-500" />
              <span :class="statusBadgeClass(item.status)" class="px-2 py-0.5 text-xs rounded-full">
                {{ statusLabel(item.status) }}
              </span>
            </div>
            <div class="text-sm text-gray-500 truncate">
              {{ item.city }}<span v-if="item.city && item.country">, </span>{{ item.country?.name }}
            </div>
            <div class="flex flex-wrap gap-1 mt-1 text-xs">
              <span class="px-2 py-0.5 bg-gray-100 rounded-full">{{ item.events_count }} Veranstaltungen</span>
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
        <h3 class="text-lg font-semibold">{{ editingItem.id ? 'Standort bearbeiten' : 'Neuer Standort' }}</h3>
        <button @click="editingItem = null" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>
      <form @submit.prevent="saveItem" class="p-4 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
          <input v-model="form.name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="z.B. Universität Heidelberg" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Land *</label>
          <select v-model="form.country_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
            <option value="">Bitte wählen</option>
            <option v-for="c in countries" :key="c.id" :value="c.id">{{ c.name }} {{ c.iso_code ? `(${c.iso_code})` : '' }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Straße</label>
          <input v-model="form.street_address" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="z.B. Im Neuenheimer Feld 205" />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">PLZ</label>
            <input v-model="form.postal_code" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="z.B. 69120" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Stadt</label>
            <input v-model="form.city" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="z.B. Heidelberg" />
          </div>
        </div>
        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="block text-sm font-medium text-gray-700">Koordinaten</label>
            <button 
              type="button" 
              @click="geocodeAddress" 
              :disabled="geocoding || !hasAddressData"
              class="text-xs text-blue-600 hover:text-blue-800 disabled:text-gray-400 disabled:cursor-not-allowed"
            >
              {{ geocoding ? 'Berechne...' : 'Aus Adresse berechnen' }}
            </button>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <input v-model="form.latitude" type="number" step="any" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Breitengrad" />
            </div>
            <div>
              <input v-model="form.longitude" type="number" step="any" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Längengrad" />
            </div>
          </div>
          <p v-if="geocodeError" class="text-xs text-red-500 mt-1">{{ geocodeError }}</p>
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
      <h3 class="text-lg font-semibold mb-4 text-red-600">Standort löschen?</h3>
      <p class="mb-4 text-gray-600">
        Möchtest du den Standort <strong>{{ editingItem?.name }}</strong> wirklich löschen?
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

const API_PATH = '/admin/locations'

// State
const items = ref<any[]>([])
const countries = ref<any[]>([])
const loading = ref(false)
const filterPending = ref(false)
const searchQuery = ref('')

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
      i.city?.toLowerCase().includes(q) ||
      i.country?.name?.toLowerCase().includes(q)
    )
  }
  return result
})
const editingItem = ref<any | null>(null)
const form = reactive({
  name: '',
  country_id: '',
  street_address: '',
  city: '',
  postal_code: '',
  latitude: null as number | null,
  longitude: null as number | null,
  status: 'approved',
})
const saving = ref(false)
const deleting = ref(false)
const error = ref('')
const showDeleteConfirm = ref(false)

// Geocoding
const geocoding = ref(false)
const geocodeError = ref('')

const hasAddressData = computed(() => {
  return form.street_address || form.city || form.postal_code
})

async function geocodeAddress() {
  geocodeError.value = ''
  
  // Build address string
  const parts = []
  if (form.street_address) parts.push(form.street_address)
  if (form.postal_code) parts.push(form.postal_code)
  if (form.city) parts.push(form.city)
  
  // Get country name
  const country = countries.value.find((c: any) => c.id === form.country_id)
  if (country) parts.push(country.name)
  
  if (parts.length === 0) {
    geocodeError.value = 'Keine Adressdaten vorhanden'
    return
  }
  
  const address = parts.join(', ')
  geocoding.value = true
  
  try {
    // Use Nominatim (OpenStreetMap) geocoding API - free, no API key needed
    const response = await fetch(
      `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`,
      { headers: { 'User-Agent': 'HONR-App' } }
    )
    const data = await response.json()
    
    if (data && data.length > 0) {
      form.latitude = parseFloat(data[0].lat)
      form.longitude = parseFloat(data[0].lon)
    } else {
      geocodeError.value = 'Adresse nicht gefunden'
    }
  } catch (err) {
    console.error('Geocoding failed', err)
    geocodeError.value = 'Fehler bei der Berechnung'
  } finally {
    geocoding.value = false
  }
}

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

async function load() {
  loading.value = true
  try {
    const [itemsRes, countriesRes] = await Promise.all([
      apiClient.get(API_PATH),
      apiClient.get(`${API_PATH}/countries`)
    ])
    items.value = itemsRes.data
    countries.value = countriesRes.data
    // Default filter on if there are pending items
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
  form.country_id = ''
  form.street_address = ''
  form.city = ''
  form.postal_code = ''
  form.latitude = null
  form.longitude = null
  form.status = 'approved'
  error.value = ''
}

function editItem(item: any) {
  editingItem.value = item
  form.name = item.name
  form.country_id = item.country_id
  form.street_address = item.street_address || ''
  form.city = item.city || ''
  form.postal_code = item.postal_code || ''
  form.latitude = item.latitude
  form.longitude = item.longitude
  form.status = item.status
  error.value = ''
}

async function saveItem() {
  error.value = ''
  saving.value = true
  try {
    const data = {
      name: form.name,
      country_id: form.country_id,
      street_address: form.street_address || null,
      city: form.city || null,
      postal_code: form.postal_code || null,
      latitude: form.latitude,
      longitude: form.longitude,
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

onMounted(load)
</script>

