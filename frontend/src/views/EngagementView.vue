<template>
  <div class="p-4">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold">Volunteer-Einsätze</h1>
      <button @click="showAddModal = true" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
        + Hinzufügen
      </button>
    </div>

    <!-- Placeholder Content -->
    <div class="bg-white rounded-lg shadow p-6">
      <p class="text-gray-600 text-center py-8">
        Du hast noch keine Einsätze hinzugefügt.
      </p>
    </div>

    <!-- Add Engagement Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white">
          <h3 class="text-lg font-semibold">Neuer Einsatz</h3>
          <button @click="closeModal" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>
        
        <div class="p-4 space-y-4">
          <!-- Role Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rolle *</label>
            <input
              v-model="roleSearch"
              type="text"
              placeholder="Rolle suchen..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
            />
            <div v-if="filteredRoles.length > 0 && roleSearch" class="mt-1 max-h-40 overflow-y-auto border border-gray-200 rounded-md">
              <button
                v-for="role in filteredRoles"
                :key="role.id"
                @click="selectRole(role)"
                class="w-full px-3 py-2 text-left hover:bg-gray-50 text-sm border-b border-gray-100 last:border-b-0"
              >
                <div class="font-medium">{{ role.name }}</div>
                <div class="text-xs text-gray-500">{{ role.first_program?.name }}</div>
              </button>
            </div>
            <div v-if="selectedRole" class="mt-2 p-2 bg-blue-50 rounded-md flex items-center justify-between">
              <div>
                <div class="text-sm font-medium">{{ selectedRole.name }}</div>
                <div class="text-xs text-gray-500">{{ selectedRole.first_program?.name }}</div>
              </div>
              <button @click="selectedRole = null" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
          </div>

          <!-- Event Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Veranstaltung *</label>
            <input
              v-model="eventSearch"
              type="text"
              placeholder="Veranstaltung suchen (Saison, Level, Ort)..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
            />
            <div v-if="filteredEvents.length > 0 && eventSearch" class="mt-1 max-h-48 overflow-y-auto border border-gray-200 rounded-md">
              <button
                v-for="event in filteredEvents"
                :key="event.id"
                @click="selectEvent(event)"
                class="w-full px-3 py-2 text-left hover:bg-gray-50 text-sm border-b border-gray-100 last:border-b-0"
              >
                <div class="font-medium">{{ formatDate(event.date) }}</div>
                <div class="text-xs text-gray-500">
                  {{ event.season?.name }} · {{ event.level?.name }}
                </div>
                <div class="text-xs text-gray-500">
                  {{ event.location?.name }}<span v-if="event.location?.city">, {{ event.location.city }}</span>
                </div>
              </button>
            </div>
            <div v-if="selectedEvent" class="mt-2 p-2 bg-blue-50 rounded-md flex items-center justify-between">
              <div>
                <div class="text-sm font-medium">{{ formatDate(selectedEvent.date) }}</div>
                <div class="text-xs text-gray-500">
                  {{ selectedEvent.season?.name }} · {{ selectedEvent.level?.name }}
                </div>
                <div class="text-xs text-gray-500">
                  {{ selectedEvent.location?.name }}<span v-if="selectedEvent.location?.city">, {{ selectedEvent.location.city }}</span>
                </div>
              </div>
              <button @click="selectedEvent = null" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
          </div>

          <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>

          <div class="flex gap-2 pt-2">
            <button @click="closeModal" class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
              Abbrechen
            </button>
            <button
              @click="saveEngagement"
              :disabled="!selectedRole || !selectedEvent || saving"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ saving ? 'Speichern...' : 'Speichern' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import apiClient from '@/api/client'

// State
const showAddModal = ref(false)
const roles = ref<any[]>([])
const events = ref<any[]>([])
const roleSearch = ref('')
const eventSearch = ref('')
const selectedRole = ref<any | null>(null)
const selectedEvent = ref<any | null>(null)
const saving = ref(false)
const error = ref('')

// Computed
const filteredRoles = computed(() => {
  if (!roleSearch.value.trim()) return []
  const q = roleSearch.value.toLowerCase()
  return roles.value.filter(r =>
    r.name.toLowerCase().includes(q) ||
    r.first_program?.name?.toLowerCase().includes(q)
  ).slice(0, 10)
})

const filteredEvents = computed(() => {
  if (!eventSearch.value.trim()) return []
  const q = eventSearch.value.toLowerCase()
  return events.value.filter(e =>
    e.season?.name?.toLowerCase().includes(q) ||
    e.level?.name?.toLowerCase().includes(q) ||
    e.location?.name?.toLowerCase().includes(q) ||
    e.location?.city?.toLowerCase().includes(q)
  ).slice(0, 10)
})

// Methods
const formatDate = (dateStr: string) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function selectRole(role: any) {
  selectedRole.value = role
  roleSearch.value = ''
}

function selectEvent(event: any) {
  selectedEvent.value = event
  eventSearch.value = ''
}

function closeModal() {
  showAddModal.value = false
  roleSearch.value = ''
  eventSearch.value = ''
  selectedRole.value = null
  selectedEvent.value = null
  error.value = ''
}

async function loadOptions() {
  try {
    const response = await apiClient.get('/engagements/options')
    roles.value = response.data.roles
    events.value = response.data.events
  } catch (err) {
    console.error('Failed to load options', err)
  }
}

async function saveEngagement() {
  if (!selectedRole.value || !selectedEvent.value) return
  
  error.value = ''
  saving.value = true
  try {
    await apiClient.post('/engagements', {
      role_id: selectedRole.value.id,
      event_id: selectedEvent.value.id,
    })
    closeModal()
    // TODO: reload user engagements list
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Fehler beim Speichern.'
  } finally {
    saving.value = false
  }
}

onMounted(loadOptions)
</script>
