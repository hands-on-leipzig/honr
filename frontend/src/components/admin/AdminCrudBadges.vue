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
            <span :class="item.type === 'grow' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'" class="px-2 py-0.5 text-xs rounded-full">
              {{ item.type === 'grow' ? 'Grow' : 'Tick' }}
            </span>
            <span :class="item.status === 'released' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="px-2 py-0.5 text-xs rounded-full">
              {{ item.status === 'released' ? 'Veröffentlicht' : 'Icon fehlt' }}
            </span>
          </div>
          <div class="text-sm text-gray-600 truncate">
            {{ getCriteriaLabel(item) }}
          </div>
          <div class="flex flex-wrap gap-1 mt-1 text-xs">
            <span v-if="item.type === 'grow'" class="px-2 py-0.5 bg-gray-100 rounded-full">{{ item.thresholds_count }} Schwellen</span>
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
          <label class="block text-sm font-medium text-gray-700 mb-1">Typ *</label>
          <select v-model="form.type" required class="w-full px-3 py-2 border border-gray-300 rounded-md" @change="onTypeChange">
            <option value="tick_box">Tick the box (einmalig)</option>
            <option value="grow">Grow (mit Schwellenwerten)</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
          <select v-model="form.status" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
            <option value="pending_icon">Icon fehlt</option>
            <option value="released">Veröffentlicht</option>
          </select>
        </div>

        <!-- Criteria selection -->
        <div class="border-t pt-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Kriterium * (genau eines wählen)</label>
          
          <div v-if="form.type === 'tick_box'" class="space-y-3">
            <div>
              <label class="block text-xs text-gray-500 mb-1">Programm</label>
              <input v-model="criteriaSearch.program" type="text" placeholder="Programm suchen..." class="w-full px-3 py-2 border border-gray-300 rounded-md" :disabled="!!selectedCriteria && selectedCriteria !== 'program'" />
              <div v-if="filteredCriteriaPrograms.length > 0 && criteriaSearch.program && !form.first_program_id" class="mt-1 max-h-32 overflow-y-auto border border-gray-200 rounded-md">
                <button v-for="p in filteredCriteriaPrograms" :key="p.id" type="button" @click="selectCriteriaProgram(p)" class="w-full px-3 py-2 text-left hover:bg-gray-50 text-sm border-b border-gray-100 last:border-b-0">{{ p.name }}</button>
              </div>
              <div v-if="selectedCriteriaProgram" class="mt-2 p-2 bg-blue-50 rounded-md flex items-center justify-between">
                <span class="text-sm font-medium">{{ selectedCriteriaProgram.name }}</span>
                <button type="button" @click="clearCriteriaProgram" class="text-gray-400 hover:text-gray-600">✕</button>
              </div>
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">Saison</label>
              <input v-model="criteriaSearch.season" type="text" placeholder="Saison suchen..." class="w-full px-3 py-2 border border-gray-300 rounded-md" :disabled="!!selectedCriteria && selectedCriteria !== 'season'" />
              <div v-if="filteredCriteriaSeasons.length > 0 && criteriaSearch.season && !form.season_id" class="mt-1 max-h-32 overflow-y-auto border border-gray-200 rounded-md">
                <button v-for="s in filteredCriteriaSeasons" :key="s.id" type="button" @click="selectCriteriaSeason(s)" class="w-full px-3 py-2 text-left hover:bg-gray-50 text-sm border-b border-gray-100 last:border-b-0">{{ s.name }}</button>
              </div>
              <div v-if="selectedCriteriaSeason" class="mt-2 p-2 bg-blue-50 rounded-md flex items-center justify-between">
                <span class="text-sm font-medium">{{ selectedCriteriaSeason.name }}</span>
                <button type="button" @click="clearCriteriaSeason" class="text-gray-400 hover:text-gray-600">✕</button>
              </div>
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">Level</label>
              <input v-model="criteriaSearch.level" type="text" placeholder="Level suchen..." class="w-full px-3 py-2 border border-gray-300 rounded-md" :disabled="!!selectedCriteria && selectedCriteria !== 'level'" />
              <div v-if="filteredCriteriaLevels.length > 0 && criteriaSearch.level && !form.level_id" class="mt-1 max-h-32 overflow-y-auto border border-gray-200 rounded-md">
                <button v-for="l in filteredCriteriaLevels" :key="l.id" type="button" @click="selectCriteriaLevel(l)" class="w-full px-3 py-2 text-left hover:bg-gray-50 text-sm border-b border-gray-100 last:border-b-0">{{ l.name }}</button>
              </div>
              <div v-if="selectedCriteriaLevel" class="mt-2 p-2 bg-blue-50 rounded-md flex items-center justify-between">
                <span class="text-sm font-medium">{{ selectedCriteriaLevel.name }}</span>
                <button type="button" @click="clearCriteriaLevel" class="text-gray-400 hover:text-gray-600">✕</button>
              </div>
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">Land</label>
              <input v-model="criteriaSearch.country" type="text" placeholder="Land suchen..." class="w-full px-3 py-2 border border-gray-300 rounded-md" :disabled="!!selectedCriteria && selectedCriteria !== 'country'" />
              <div v-if="filteredCriteriaCountries.length > 0 && criteriaSearch.country && !form.country_id" class="mt-1 max-h-32 overflow-y-auto border border-gray-200 rounded-md">
                <button v-for="c in filteredCriteriaCountries" :key="c.id" type="button" @click="selectCriteriaCountry(c)" class="w-full px-3 py-2 text-left hover:bg-gray-50 text-sm border-b border-gray-100 last:border-b-0">{{ c.name }}</button>
              </div>
              <div v-if="selectedCriteriaCountry" class="mt-2 p-2 bg-blue-50 rounded-md flex items-center justify-between">
                <span class="text-sm font-medium">{{ selectedCriteriaCountry.name }}</span>
                <button type="button" @click="clearCriteriaCountry" class="text-gray-400 hover:text-gray-600">✕</button>
              </div>
            </div>
          </div>

          <div v-if="form.type === 'grow'">
            <label class="block text-xs text-gray-500 mb-1">Rolle *</label>
            <input v-model="criteriaSearch.role" type="text" placeholder="Rolle suchen..." class="w-full px-3 py-2 border border-gray-300 rounded-md" />
            <div v-if="filteredCriteriaRoles.length > 0 && criteriaSearch.role && !form.role_id" class="mt-1 max-h-32 overflow-y-auto border border-gray-200 rounded-md">
              <button v-for="r in filteredCriteriaRoles" :key="r.id" type="button" @click="selectCriteriaRole(r)" class="w-full px-3 py-2 text-left hover:bg-gray-50 text-sm border-b border-gray-100 last:border-b-0">{{ r.name }}</button>
            </div>
            <div v-if="selectedCriteriaRole" class="mt-2 p-2 bg-blue-50 rounded-md flex items-center justify-between">
              <span class="text-sm font-medium">{{ selectedCriteriaRole.name }}</span>
              <button type="button" @click="clearCriteriaRole" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
          </div>
        </div>

        <div v-if="form.type === 'tick_box'">
          <label class="block text-sm font-medium text-gray-700 mb-1">Icon-Pfad</label>
          <input v-model="form.icon_path" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="/icons/badge.svg" />
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
  programs: [] as any[],
  seasons: [] as any[],
  levels: [] as any[],
  countries: [] as any[],
  roles: [] as any[],
})
const loading = ref(false)
const filterPendingIcon = ref(false)
const searchQuery = ref('')
const editingItem = ref<any | null>(null)
const form = reactive({
  name: '',
  type: 'tick_box',
  status: 'pending_icon',
  icon_path: '',
  first_program_id: '',
  season_id: '',
  level_id: '',
  country_id: '',
  role_id: '',
  description: '',
})
const saving = ref(false)
const deleting = ref(false)
const error = ref('')
const showDeleteConfirm = ref(false)

// Type-ahead for criteria
const criteriaSearch = reactive({
  program: '',
  season: '',
  level: '',
  country: '',
  role: ''
})
const selectedCriteriaProgram = ref<any>(null)
const selectedCriteriaSeason = ref<any>(null)
const selectedCriteriaLevel = ref<any>(null)
const selectedCriteriaCountry = ref<any>(null)
const selectedCriteriaRole = ref<any>(null)

const selectedCriteria = computed(() => {
  if (form.first_program_id) return 'program'
  if (form.season_id) return 'season'
  if (form.level_id) return 'level'
  if (form.country_id) return 'country'
  return null
})

const filteredCriteriaPrograms = computed(() => {
  if (!criteriaSearch.program.trim()) return options.programs
  const q = criteriaSearch.program.toLowerCase()
  return options.programs.filter((p: any) => p.name.toLowerCase().includes(q))
})
const filteredCriteriaSeasons = computed(() => {
  if (!criteriaSearch.season.trim()) return options.seasons
  const q = criteriaSearch.season.toLowerCase()
  return options.seasons.filter((s: any) => s.name.toLowerCase().includes(q))
})
const filteredCriteriaLevels = computed(() => {
  if (!criteriaSearch.level.trim()) return options.levels
  const q = criteriaSearch.level.toLowerCase()
  return options.levels.filter((l: any) => l.name.toLowerCase().includes(q))
})
const filteredCriteriaCountries = computed(() => {
  if (!criteriaSearch.country.trim()) return options.countries
  const q = criteriaSearch.country.toLowerCase()
  return options.countries.filter((c: any) => c.name.toLowerCase().includes(q))
})
const filteredCriteriaRoles = computed(() => {
  if (!criteriaSearch.role.trim()) return options.roles
  const q = criteriaSearch.role.toLowerCase()
  return options.roles.filter((r: any) => r.name.toLowerCase().includes(q))
})

function selectCriteriaProgram(p: any) {
  selectedCriteriaProgram.value = p
  form.first_program_id = p.id
  criteriaSearch.program = ''
  clearOtherCriteria('first_program_id')
}
function clearCriteriaProgram() {
  selectedCriteriaProgram.value = null
  form.first_program_id = ''
  criteriaSearch.program = ''
}
function selectCriteriaSeason(s: any) {
  selectedCriteriaSeason.value = s
  form.season_id = s.id
  criteriaSearch.season = ''
  clearOtherCriteria('season_id')
}
function clearCriteriaSeason() {
  selectedCriteriaSeason.value = null
  form.season_id = ''
  criteriaSearch.season = ''
}
function selectCriteriaLevel(l: any) {
  selectedCriteriaLevel.value = l
  form.level_id = l.id
  criteriaSearch.level = ''
  clearOtherCriteria('level_id')
}
function clearCriteriaLevel() {
  selectedCriteriaLevel.value = null
  form.level_id = ''
  criteriaSearch.level = ''
}
function selectCriteriaCountry(c: any) {
  selectedCriteriaCountry.value = c
  form.country_id = c.id
  criteriaSearch.country = ''
  clearOtherCriteria('country_id')
}
function clearCriteriaCountry() {
  selectedCriteriaCountry.value = null
  form.country_id = ''
  criteriaSearch.country = ''
}
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

function clearAllCriteriaSelections() {
  selectedCriteriaProgram.value = null
  selectedCriteriaSeason.value = null
  selectedCriteriaLevel.value = null
  selectedCriteriaCountry.value = null
  selectedCriteriaRole.value = null
  criteriaSearch.program = ''
  criteriaSearch.season = ''
  criteriaSearch.level = ''
  criteriaSearch.country = ''
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
      i.first_program?.name?.toLowerCase().includes(q) ||
      i.season?.name?.toLowerCase().includes(q) ||
      i.level?.name?.toLowerCase().includes(q) ||
      i.country?.name?.toLowerCase().includes(q) ||
      i.role?.name?.toLowerCase().includes(q)
    )
  }
  return result
})

// Methods
const getCriteriaLabel = (item: any) => {
  if (item.first_program) return `Programm: ${item.first_program.name}`
  if (item.season) return `Saison: ${item.season.name}`
  if (item.level) return `Level: ${item.level.name}`
  if (item.country) return `Land: ${item.country.name}`
  if (item.role) return `Rolle: ${item.role.name}`
  return 'Kein Kriterium'
}

function onTypeChange() {
  // Clear all criteria when type changes
  form.first_program_id = ''
  form.season_id = ''
  form.level_id = ''
  form.country_id = ''
  form.role_id = ''
}

function clearOtherCriteria(keep: string) {
  if (keep !== 'first_program_id') form.first_program_id = ''
  if (keep !== 'season_id') form.season_id = ''
  if (keep !== 'level_id') form.level_id = ''
  if (keep !== 'country_id') form.country_id = ''
  if (keep !== 'role_id') form.role_id = ''
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
    options.countries = optionsRes.data.countries
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
  form.type = 'tick_box'
  form.status = 'pending_icon'
  form.icon_path = ''
  form.first_program_id = ''
  form.season_id = ''
  form.level_id = ''
  form.country_id = ''
  form.role_id = ''
  form.description = ''
  clearAllCriteriaSelections()
  error.value = ''
}

function editItem(item: any) {
  editingItem.value = item
  form.name = item.name
  form.type = item.type
  form.status = item.status
  form.icon_path = item.icon_path || ''
  form.first_program_id = item.first_program_id || ''
  form.season_id = item.season_id || ''
  form.level_id = item.level_id || ''
  form.country_id = item.country_id || ''
  form.role_id = item.role_id || ''
  form.description = item.description || ''
  // Set selected criteria based on what's set
  selectedCriteriaProgram.value = item.first_program_id ? options.programs.find((p: any) => p.id === item.first_program_id) : null
  selectedCriteriaSeason.value = item.season_id ? options.seasons.find((s: any) => s.id === item.season_id) : null
  selectedCriteriaLevel.value = item.level_id ? options.levels.find((l: any) => l.id === item.level_id) : null
  selectedCriteriaCountry.value = item.country_id ? options.countries.find((c: any) => c.id === item.country_id) : null
  selectedCriteriaRole.value = item.role_id ? options.roles.find((r: any) => r.id === item.role_id) : null
  criteriaSearch.program = ''
  criteriaSearch.season = ''
  criteriaSearch.level = ''
  criteriaSearch.country = ''
  criteriaSearch.role = ''
  error.value = ''
}

async function saveItem() {
  error.value = ''
  saving.value = true
  try {
    const data = {
      name: form.name,
      type: form.type,
      status: form.status,
      icon_path: form.icon_path || null,
      first_program_id: form.first_program_id || null,
      season_id: form.season_id || null,
      level_id: form.level_id || null,
      country_id: form.country_id || null,
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

