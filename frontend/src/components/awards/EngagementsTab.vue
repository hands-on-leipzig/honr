<template>
  <div>
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-bold">Volunteer-Einsätze</h2>
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
          <!-- Status Icon -->
          <div class="mr-3 pt-1">
            <CheckCircleIcon v-if="item.is_recognized" class="w-5 h-5 text-green-500" />
            <ClockIcon v-else class="w-5 h-5 text-amber-500" />
          </div>
          <div class="flex-1 grid grid-cols-2 gap-4">
            <!-- Role Column -->
            <div>
              <div class="text-xs text-gray-500 uppercase tracking-wide mb-1 flex items-center gap-2">
                <img
                  v-if="item.role?.logo_path"
                  :src="getLogoUrl(item.role.logo_path)"
                  :alt="item.role?.name"
                  class="h-4 w-4 object-contain flex-shrink-0"
                  @error="handleImageError"
                />
                <span>Rolle</span>
              </div>
              <div class="font-medium">{{ item.role?.name }}</div>
              <div class="text-xs text-gray-500">{{ item.role?.first_program?.name }}</div>
              <div v-if="item.role?.status !== 'approved'" class="text-xs text-amber-600">Von dir vorgeschlagen, noch nicht bestätigt</div>
            </div>
            <!-- Event Column -->
            <div>
              <div class="text-xs text-gray-500 uppercase tracking-wide mb-1 flex items-center gap-2">
                <img
                  v-if="item.event?.season?.logo_path"
                  :src="getLogoUrl(item.event.season.logo_path)"
                  :alt="item.event?.season?.name"
                  class="h-4 w-4 object-contain flex-shrink-0"
                  @error="handleImageError"
                />
                <span>Veranstaltung</span>
              </div>
              <button
                @click="filterByEvent(item.event)"
                class="text-left hover:opacity-80 transition-opacity w-full"
              >
                <div class="font-medium">{{ formatDate(item.event?.date) }}</div>
                <div class="text-xs text-gray-500">{{ item.event?.season?.name }} · {{ item.event?.level?.name }}</div>
                <div class="text-xs text-gray-500">{{ item.event?.location?.name }}<span v-if="item.event?.location?.city">, {{ item.event.location.city }}</span></div>
              </button>
              <div v-if="item.event?.status !== 'approved'" class="text-xs text-amber-600">Von dir vorgeschlagen, noch nicht bestätigt</div>
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
          <h3 class="text-lg font-semibold">Neuer Volunteer-Einsatz</h3>
          <button @click="closeModal" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>
        
        <div class="p-4 space-y-4">
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
                <div class="font-medium">{{ formatDate(event.date) }}<span v-if="event.user_proposed" class="text-amber-600"> (von dir vorgeschlagen)</span></div>
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
              <button @click="clearEvent" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <button
              v-if="!selectedEvent"
              @click="showProposeEventModal = true"
              class="mt-2 text-sm text-blue-600 hover:text-blue-800"
            >
              Fehlende Veranstaltung vorschlagen
            </button>
          </div>

          <!-- Role Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rolle *</label>
            <input
              v-model="roleSearch"
              type="text"
              placeholder="Rolle suchen..."
              :disabled="!selectedEvent"
              :class="[
                'w-full px-3 py-2 border border-gray-300 rounded-md text-sm',
                !selectedEvent ? 'bg-gray-100 cursor-not-allowed' : ''
              ]"
            />
            <div v-if="!selectedEvent" class="mt-1 text-xs text-gray-500">
              Bitte zuerst eine Veranstaltung auswählen
            </div>
            <div v-if="filteredRoles.length > 0 && roleSearch && selectedEvent" class="mt-1 max-h-40 overflow-y-auto border border-gray-200 rounded-md">
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
            <button
              v-if="!selectedRole && selectedEvent"
              @click="showProposeRoleModal = true"
              class="mt-2 text-sm text-blue-600 hover:text-blue-800"
            >
              Fehlende Rolle vorschlagen
            </button>
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

    <!-- Propose Role Modal -->
    <div v-if="showProposeRoleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg w-full max-w-md">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
          <h3 class="text-lg font-semibold">Fehlende Rolle vorschlagen</h3>
          <button @click="showProposeRoleModal = false" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>
        <div class="p-4 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rollenname *</label>
            <input v-model="proposeRoleForm.name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Welche Rolle fehlt?" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Programm *</label>
            <select v-model="proposeRoleForm.first_program_id" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
              <option value="">Bitte wählen</option>
              <option v-for="p in programs" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>
          <div v-if="proposeError" class="text-red-600 text-sm">{{ proposeError }}</div>
          <div class="flex gap-2 pt-2">
            <button @click="showProposeRoleModal = false" class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Abbrechen</button>
            <button @click="proposeRole" :disabled="proposeSaving" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
              {{ proposeSaving ? 'Senden...' : 'Vorschlagen' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Propose Event Modal -->
    <div v-if="showProposeEventModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg w-full max-w-md">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
          <h3 class="text-lg font-semibold">Fehlende Veranstaltung vorschlagen</h3>
          <button @click="showProposeEventModal = false" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>
        <div class="p-4 space-y-4">
          <!-- Saison with type-ahead -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Saison *</label>
            <input
              v-model="proposeSeasonSearch"
              type="text"
              placeholder="Saison suchen..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
            />
            <div v-if="filteredProposeSeasons.length > 0 && proposeSeasonSearch && !proposeEventForm.season_id" class="mt-1 max-h-40 overflow-y-auto border border-gray-200 rounded-md">
              <button
                v-for="s in filteredProposeSeasons"
                :key="s.id"
                @click="selectProposeSeason(s)"
                class="w-full px-3 py-2 text-left hover:bg-gray-50 text-sm border-b border-gray-100 last:border-b-0"
              >
                {{ s.name }}
              </button>
            </div>
            <div v-if="selectedProposeSeason" class="mt-2 p-2 bg-blue-50 rounded-md flex items-center justify-between">
              <span class="text-sm font-medium">{{ selectedProposeSeason.name }}</span>
              <button @click="clearProposeSeason" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
          </div>
          <!-- Level -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Level *</label>
            <select v-model="proposeEventForm.level_id" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
              <option value="">Bitte wählen</option>
              <option v-for="l in levels" :key="l.id" :value="l.id">{{ l.name }}</option>
            </select>
          </div>
          <!-- Datum -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Datum *</label>
            <input v-model="proposeEventForm.date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" />
          </div>
          <!-- Standort with type-ahead -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Standort *</label>
            <input
              v-model="proposeLocationSearch"
              type="text"
              placeholder="Standort suchen..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
            />
            <div v-if="filteredProposeLocations.length > 0 && proposeLocationSearch && !selectedProposeLocation" class="mt-1 max-h-40 overflow-y-auto border border-gray-200 rounded-md">
              <button
                v-for="loc in filteredProposeLocations"
                :key="loc.id"
                @click="selectProposeLocation(loc)"
                class="w-full px-3 py-2 text-left hover:bg-gray-50 text-sm border-b border-gray-100 last:border-b-0"
              >
                {{ loc.name }}<span v-if="loc.city">, {{ loc.city }}</span>
              </button>
            </div>
            <div v-if="selectedProposeLocation" class="mt-2 p-2 bg-blue-50 rounded-md flex items-center justify-between">
              <span class="text-sm font-medium">{{ selectedProposeLocation.name }}<span v-if="selectedProposeLocation.city">, {{ selectedProposeLocation.city }}</span></span>
              <button @click="clearProposeLocation" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <button @click="showProposeLocationModal = true; showProposeEventModal = false" class="mt-2 text-sm text-blue-600 hover:text-blue-800">
              Fehlenden Standort vorschlagen
            </button>
          </div>
          <div v-if="proposeError" class="text-red-600 text-sm">{{ proposeError }}</div>
          <div class="flex gap-2 pt-2">
            <button @click="showProposeEventModal = false" class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Abbrechen</button>
            <button @click="proposeEvent" :disabled="proposeSaving" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
              {{ proposeSaving ? 'Senden...' : 'Vorschlagen' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Propose Location Modal -->
    <div v-if="showProposeLocationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg w-full max-w-md">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
          <h3 class="text-lg font-semibold">Fehlenden Standort vorschlagen</h3>
          <button @click="showProposeLocationModal = false; showProposeEventModal = true" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>
        <div class="p-4 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
            <input v-model="proposeLocationForm.name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="z.B. Universität Heidelberg" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Straße</label>
            <input v-model="proposeLocationForm.street_address" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="z.B. Im Neuenheimer Feld 205" />
          </div>
          <div class="grid grid-cols-3 gap-2">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">PLZ</label>
              <input v-model="proposeLocationForm.postal_code" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="69120" />
            </div>
            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Stadt</label>
              <input v-model="proposeLocationForm.city" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Heidelberg" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Land *</label>
            <select v-model="proposeLocationForm.country_id" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
              <option value="">Bitte wählen</option>
              <option v-for="c in countries" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <div v-if="!showNewCountryInput" class="mt-1">
              <button @click="showNewCountryInput = true" class="text-sm text-blue-600 hover:text-blue-800">Land fehlt?</button>
            </div>
            <div v-else class="mt-2 flex gap-2">
              <input v-model="newCountryName" type="text" class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Neues Land eingeben" />
              <button @click="proposeCountry" :disabled="!newCountryName || proposingCountry" class="px-3 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700 disabled:opacity-50">
                {{ proposingCountry ? '...' : '+' }}
              </button>
            </div>
          </div>
          <div v-if="proposeError" class="text-red-600 text-sm">{{ proposeError }}</div>
          <div class="flex gap-2 pt-2">
            <button @click="showProposeLocationModal = false; showProposeEventModal = true" class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Zurück</button>
            <button @click="proposeLocation" :disabled="proposeSaving" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
              {{ proposeSaving ? 'Senden...' : 'Vorschlagen' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { CheckCircleIcon, ClockIcon } from '@heroicons/vue/24/solid'
import apiClient from '@/api/client'

const router = useRouter()

const emit = defineEmits<{
  (e: 'engagements-updated'): void
}>()

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

// Propose modals
const showProposeRoleModal = ref(false)
const showProposeEventModal = ref(false)
const showProposeLocationModal = ref(false)
const programs = ref<any[]>([])
const seasons = ref<any[]>([])
const levels = ref<any[]>([])
const locations = ref<any[]>([])
const proposeRoleForm = ref({ name: '', first_program_id: '' })
const proposeEventForm = ref({ date: '', season_id: '', level_id: '', location_id: '' })
const proposeLocationForm = ref({ name: '', street_address: '', postal_code: '', city: '', country_id: '' })
const countries = ref<any[]>([])
const proposeSaving = ref(false)
const proposeError = ref('')

// Type-ahead for propose event modal
const proposeSeasonSearch = ref('')
const proposeLocationSearch = ref('')
const selectedProposeSeason = ref<any>(null)
const selectedProposeLocation = ref<any>(null)

// Inline country proposal
const showNewCountryInput = ref(false)
const newCountryName = ref('')
const proposingCountry = ref(false)

// Computed
const filteredRoles = computed(() => {
  if (!roleSearch.value.trim() || !selectedEvent.value) return []
  const q = roleSearch.value.toLowerCase()
  const eventProgramId = selectedEvent.value.first_program_id || selectedEvent.value.season?.first_program_id
  return roles.value.filter(r => {
    const matchesSearch = r.name.toLowerCase().includes(q) ||
      r.first_program?.name?.toLowerCase().includes(q)
    const matchesProgram = !eventProgramId || r.first_program_id === eventProgramId
    return matchesSearch && matchesProgram
  }).slice(0, 10)
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

const filteredProposeSeasons = computed(() => {
  if (!proposeSeasonSearch.value.trim()) return []
  const q = proposeSeasonSearch.value.toLowerCase()
  return seasons.value.filter(s => s.name?.toLowerCase().includes(q)).slice(0, 10)
})

const filteredProposeLocations = computed(() => {
  if (!proposeLocationSearch.value.trim()) return []
  const q = proposeLocationSearch.value.toLowerCase()
  return locations.value.filter(loc =>
    loc.name?.toLowerCase().includes(q) ||
    loc.city?.toLowerCase().includes(q)
  ).slice(0, 10)
})

// Methods
const formatDate = (dateStr: string) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function getLogoUrl(logoPath: string | null) {
  if (!logoPath) return ''
  const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8001/api'
  const backendUrl = apiUrl.replace('/api', '')
  return `${backendUrl}/storage/${logoPath}`
}

function handleImageError(event: Event) {
  const img = event.target as HTMLImageElement
  img.style.display = 'none'
}

function filterByEvent(event: any) {
  if (!event) return
  
  const filterLabel = `${formatDate(event.date)} · ${event.location?.name || ''}`.replace(/^ · | · $/g, '').trim()
  
  router.push({
    path: '/people',
    query: {
      filter_type: 'event',
      filter_id: event.id,
      filter_label: filterLabel,
      filter_icon_type: 'logo',
      filter_icon_path: event.season?.logo_path || '',
    }
  })
}

function selectRole(role: any) {
  selectedRole.value = role
  roleSearch.value = ''
}

function selectEvent(event: any) {
  selectedEvent.value = event
  eventSearch.value = ''
  // Clear role if it doesn't match the event's program
  if (selectedRole.value) {
    const eventProgramId = event.first_program_id || event.season?.first_program_id
    if (selectedRole.value.first_program_id !== eventProgramId) {
      selectedRole.value = null
    }
  }
}

function clearEvent() {
  selectedEvent.value = null
  eventSearch.value = ''
  selectedRole.value = null
  roleSearch.value = ''
}

function selectProposeSeason(season: any) {
  selectedProposeSeason.value = season
  proposeEventForm.value.season_id = season.id
  proposeSeasonSearch.value = ''
}

function clearProposeSeason() {
  selectedProposeSeason.value = null
  proposeEventForm.value.season_id = ''
  proposeSeasonSearch.value = ''
}

function selectProposeLocation(location: any) {
  selectedProposeLocation.value = location
  proposeEventForm.value.location_id = location.id
  proposeLocationSearch.value = ''
}

function clearProposeLocation() {
  selectedProposeLocation.value = null
  proposeEventForm.value.location_id = ''
  proposeLocationSearch.value = ''
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
    programs.value = response.data.programs
    seasons.value = response.data.seasons
    levels.value = response.data.levels
    locations.value = response.data.locations
    countries.value = response.data.countries
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
    emit('engagements-updated')
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
    emit('engagements-updated')
  } catch (err) {
    console.error('Failed to delete engagement', err)
  } finally {
    deletingId.value = null
  }
}

async function proposeRole() {
  if (!proposeRoleForm.value.name || !proposeRoleForm.value.first_program_id) {
    proposeError.value = 'Bitte alle Pflichtfelder ausfüllen.'
    return
  }
  proposeError.value = ''
  proposeSaving.value = true
  try {
    await apiClient.post('/engagements/propose-role', proposeRoleForm.value)
    showProposeRoleModal.value = false
    proposeRoleForm.value = { name: '', first_program_id: '' }
    await loadOptions()
  } catch (err: any) {
    proposeError.value = err.response?.data?.message || 'Fehler beim Vorschlagen.'
  } finally {
    proposeSaving.value = false
  }
}

async function proposeEvent() {
  if (!proposeEventForm.value.date || !proposeEventForm.value.season_id || !proposeEventForm.value.level_id || !proposeEventForm.value.location_id) {
    proposeError.value = 'Bitte alle Pflichtfelder ausfüllen.'
    return
  }
  proposeError.value = ''
  proposeSaving.value = true
  try {
    await apiClient.post('/engagements/propose-event', proposeEventForm.value)
    showProposeEventModal.value = false
    proposeEventForm.value = { date: '', season_id: '', level_id: '', location_id: '' }
    await loadOptions()
  } catch (err: any) {
    proposeError.value = err.response?.data?.message || 'Fehler beim Vorschlagen.'
  } finally {
    proposeSaving.value = false
  }
}

async function proposeCountry() {
  if (!newCountryName.value) return
  proposingCountry.value = true
  try {
    const response = await apiClient.post('/engagements/propose-country', { name: newCountryName.value })
    await loadOptions()
    proposeLocationForm.value.country_id = response.data.id
    newCountryName.value = ''
    showNewCountryInput.value = false
  } catch (err: any) {
    proposeError.value = err.response?.data?.message || 'Fehler beim Vorschlagen.'
  } finally {
    proposingCountry.value = false
  }
}

async function proposeLocation() {
  if (!proposeLocationForm.value.name || !proposeLocationForm.value.country_id) {
    proposeError.value = 'Bitte alle Pflichtfelder ausfüllen.'
    return
  }
  proposeError.value = ''
  proposeSaving.value = true
  try {
    const response = await apiClient.post('/engagements/propose-location', proposeLocationForm.value)
    showProposeLocationModal.value = false
    showProposeEventModal.value = true
    proposeLocationForm.value = { name: '', street_address: '', postal_code: '', city: '', country_id: '' }
    await loadOptions()
    // Auto-select the newly created location
    proposeEventForm.value.location_id = response.data.id
  } catch (err: any) {
    proposeError.value = err.response?.data?.message || 'Fehler beim Vorschlagen.'
  } finally {
    proposeSaving.value = false
  }
}

onMounted(() => {
  loadEngagements()
  loadOptions()
})
</script>


