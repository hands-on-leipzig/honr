<template>
  <div class="p-4">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold">Volunteer-Einsätze</h1>
      <button @click="showAddModal = true" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
        + Hinzufügen
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="bg-white rounded-lg shadow p-6">
      <p class="text-gray-600 text-center py-8">Laden...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="engagements.length === 0" class="bg-white rounded-lg shadow p-6">
      <p class="text-gray-600 text-center py-8">
        Du hast noch keine Einsätze hinzugefügt.
      </p>
    </div>

    <!-- Engagements List -->
    <div v-else class="bg-white rounded-lg shadow divide-y divide-gray-200">
      <div v-for="item in engagements" :key="item.id" class="p-4">
        <div class="flex items-start justify-between">
          <div class="flex-1 grid grid-cols-2 gap-4">
            <!-- Role Column -->
            <div>
              <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Rolle</div>
              <div class="font-medium">{{ item.role?.name }}</div>
              <div class="text-xs text-gray-500">{{ item.role?.first_program?.name }}</div>
            </div>
            <!-- Event Column -->
            <div>
              <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Veranstaltung</div>
              <div class="font-medium">{{ formatDate(item.event?.date) }}</div>
              <div class="text-xs text-gray-500">{{ item.event?.season?.name }} · {{ item.event?.level?.name }}</div>
              <div class="text-xs text-gray-500">{{ item.event?.location?.name }}<span v-if="item.event?.location?.city">, {{ item.event.location.city }}</span></div>
            </div>
          </div>
          <!-- Delete Button -->
          <button
            @click="deleteEngagement(item.id)"
            :disabled="deletingId === item.id"
            class="ml-4 text-red-500 hover:text-red-700 disabled:opacity-50"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>
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
const engagements = ref<any[]>([])
const loading = ref(true)
const showAddModal = ref(false)
const roles = ref<any[]>([])
const events = ref<any[]>([])
const roleSearch = ref('')
const eventSearch = ref('')
const selectedRole = ref<any | null>(null)
const selectedEvent = ref<any | null>(null)
const saving = ref(false)
const error = ref('')
const deletingId = ref<number | null>(null)

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

async function loadEngagements() {
  loading.value = true
  try {
    const response = await apiClient.get('/engagements')
    engagements.value = response.data
  } catch (err) {
    console.error('Failed to load engagements', err)
  } finally {
    loading.value = false
  }
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
    await loadEngagements()
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Fehler beim Speichern.'
  } finally {
    saving.value = false
  }
}

async function deleteEngagement(id: number) {
  deletingId.value = id
  try {
    await apiClient.delete(`/engagements/${id}`)
    await loadEngagements()
  } catch (err) {
    console.error('Failed to delete engagement', err)
  } finally {
    deletingId.value = null
  }
}

onMounted(() => {
  loadEngagements()
  loadOptions()
})
</script>
