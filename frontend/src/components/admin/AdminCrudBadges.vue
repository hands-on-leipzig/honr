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
              <select v-model="form.first_program_id" class="w-full px-3 py-2 border border-gray-300 rounded-md" @change="clearOtherCriteria('first_program_id')">
                <option value="">-</option>
                <option v-for="p in options.programs" :key="p.id" :value="p.id">{{ p.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">Saison</label>
              <select v-model="form.season_id" class="w-full px-3 py-2 border border-gray-300 rounded-md" @change="clearOtherCriteria('season_id')">
                <option value="">-</option>
                <option v-for="s in options.seasons" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">Level</label>
              <select v-model="form.level_id" class="w-full px-3 py-2 border border-gray-300 rounded-md" @change="clearOtherCriteria('level_id')">
                <option value="">-</option>
                <option v-for="l in options.levels" :key="l.id" :value="l.id">{{ l.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">Land</label>
              <select v-model="form.country_id" class="w-full px-3 py-2 border border-gray-300 rounded-md" @change="clearOtherCriteria('country_id')">
                <option value="">-</option>
                <option v-for="c in options.countries" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
          </div>

          <div v-if="form.type === 'grow'">
            <label class="block text-xs text-gray-500 mb-1">Rolle *</label>
            <select v-model="form.role_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
              <option value="">Bitte wählen</option>
              <option v-for="r in options.roles" :key="r.id" :value="r.id">{{ r.name }}</option>
            </select>
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
    result = result.filter(i => i.name.toLowerCase().includes(q))
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

