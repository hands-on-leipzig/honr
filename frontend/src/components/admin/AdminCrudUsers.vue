<template>
  <div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <h2 class="text-xl font-semibold">Benutzer</h2>
          <div v-if="hasRequested" class="flex items-center space-x-2">
            <BellIcon :class="['w-4 h-4', STATUS_WARNING.icon]" />
            <span :class="['text-sm font-medium', STATUS_WARNING.text]">{{ requestedCount }}</span>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="filterRequested" class="sr-only peer" />
              <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
        </div>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="p-4 text-center text-gray-500">Laden...</div>

    <!-- Users List -->
    <div v-else class="divide-y divide-gray-200">
      <div
        v-for="user in filteredUsers"
        :key="user.id"
        @click="editUser(user)"
        class="px-4 py-3 hover:bg-gray-50 cursor-pointer"
      >
        <div class="flex items-center justify-between">
          <div class="flex-1 min-w-0">
            <div class="flex items-center space-x-2">
              <span class="font-medium truncate">{{ user.nickname || '(kein Name)' }}</span>
              <BellIcon v-if="user.status === 'requested'" :class="['w-4 h-4', STATUS_WARNING.icon]" />
              <span
                :class="[
                  'px-2 py-1 text-xs rounded-full',
                  user.status === 'active' ? STATUS_SUCCESS.badge :
                  user.status === 'requested' ? STATUS_WARNING.badge :
                  user.status === 'disabled' ? STATUS_ERROR.badge :
                  'bg-gray-100 text-gray-800'
                ]"
              >
                {{ statusLabel(user.status) }}
              </span>
            </div>
            <div class="text-sm text-gray-500 truncate">{{ user.email }}</div>
            <div v-if="user.is_admin" class="mt-1">
              <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800">Admin</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- User Edit Modal -->
  <div v-if="editingUser" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-md max-h-[90vh] overflow-y-auto">
      <div class="p-4 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-lg font-semibold">Benutzer bearbeiten</h3>
        <button @click="editingUser = null" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>
      <div class="p-4 space-y-4">
        <!-- Current user hint -->
        <div v-if="isCurrentUser" class="p-3 bg-blue-50 border border-blue-200 rounded-md text-blue-800 text-sm">
          Dies ist dein eigenes Konto. Verwende die <strong>Einstellungen</strong>, um deine Daten zu ändern.
        </div>

        <!-- Read-only fields -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">E-Mail</label>
          <div class="px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-gray-700">{{ editingUser.email }}</div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
          <div class="px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-gray-700">{{ editingUser.nickname || '–' }}</div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Über mich</label>
          <div class="px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-gray-700 min-h-[60px]">{{ editingUser.short_bio || '–' }}</div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Regionalpartner Name</label>
          <div class="px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-gray-700">{{ editingUser.regional_partner_name || '–' }}</div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">E-Mail-Einstellungen</label>
          <div class="px-3 py-2 bg-gray-100 border border-gray-200 rounded-md space-y-2">
            <label class="flex items-center text-gray-700">
              <input
                type="checkbox"
                :checked="editingUser.email_notify_proposals"
                disabled
                class="rounded border-gray-300 text-blue-600 mr-2"
              />
              <span class="text-sm">Benachrichtigung zu angenommenen Vorschlägen</span>
            </label>
            <label class="flex items-center text-gray-700">
              <input
                type="checkbox"
                :checked="editingUser.email_tool_info"
                disabled
                class="rounded border-gray-300 text-blue-600 mr-2"
              />
              <span class="text-sm">Informationen zum Tool, Badges und Leaderboard</span>
            </label>
            <label class="flex items-center text-gray-700">
              <input
                type="checkbox"
                :checked="editingUser.email_volunteer_newsletter"
                disabled
                class="rounded border-gray-300 text-blue-600 mr-2"
              />
              <span class="text-sm">Volunteer-Newsletter von Hands On Technology</span>
            </label>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <div class="px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-gray-700">{{ statusLabel(editingUser.status) }}</div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Administrator</label>
          <div class="px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-gray-700">{{ editingUser.is_admin ? 'Ja' : 'Nein' }}</div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Letzter Login</label>
          <div class="px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-gray-700">{{ formatDate(editingUser.last_login_at) }}</div>
        </div>

        <!-- Close button for current user -->
        <div v-if="isCurrentUser" class="flex gap-2 pt-2">
          <button type="button" @click="editingUser = null" class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
            Schließen
          </button>
        </div>

        <!-- Editable form for other users -->
        <form v-if="!isCurrentUser" @submit.prevent="saveUser">
          <hr class="my-4" />

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status ändern</label>
              <div v-if="editingUser.status === 'requested'" class="px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-gray-500">
                Unbestätigt (wartet auf E-Mail-Bestätigung)
              </div>
              <select v-else v-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <option value="active">Aktiv</option>
                <option value="disabled">Gesperrt</option>
              </select>
            </div>
            <div>
              <label class="flex items-center space-x-2" :class="{ 'opacity-50': form.status !== 'active' }">
                <input 
                  v-model="form.is_admin" 
                  type="checkbox" 
                  class="rounded border-gray-300 text-blue-600" 
                  :disabled="form.status !== 'active'"
                />
                <span class="text-sm font-medium">Administrator</span>
                <span v-if="form.status !== 'active'" class="text-xs text-gray-500">(nur für aktive Benutzer)</span>
              </label>
            </div>

            <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>
            <div class="flex gap-2 pt-2">
              <button type="button" @click="editingUser = null" class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                Abbrechen
              </button>
              <button type="button" @click="showDeleteConfirm = true" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                Löschen
              </button>
              <button type="submit" :disabled="saving" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
                {{ saving ? 'Speichern...' : 'Speichern' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-sm p-6">
      <h3 class="text-lg font-semibold mb-4 text-red-600">Benutzer löschen?</h3>
      <p class="mb-4 text-gray-600">
        Möchtest du den Benutzer <strong>{{ editingUser?.nickname || editingUser?.email }}</strong> wirklich löschen?
      </p>
      <div class="flex gap-2">
        <button @click="showDeleteConfirm = false" class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
          Abbrechen
        </button>
        <button @click="deleteUser" :disabled="deleting" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50">
          {{ deleting ? 'Löschen...' : 'Löschen' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { BellIcon } from '@heroicons/vue/24/solid'
import apiClient from '@/api/client'
import { useUserStore } from '@/stores/user'
import { getStatusColorClass, STATUS_SUCCESS, STATUS_WARNING, STATUS_ERROR } from '@/constants/uiColors'

const emit = defineEmits(['close'])

const userStore = useUserStore()

// State
const items = ref<any[]>([])
const loading = ref(false)
const filterRequested = ref(false)
const editingUser = ref<any | null>(null)
const form = reactive({
  status: 'requested',
  is_admin: false,
})
const saving = ref(false)
const deleting = ref(false)
const error = ref('')
const showDeleteConfirm = ref(false)

// Computed
const isCurrentUser = computed(() => editingUser.value?.id === userStore.user?.id)

const requestedCount = computed(() => items.value.filter(u => u.status === 'requested').length)
const hasRequested = computed(() => requestedCount.value > 0)

const filteredUsers = computed(() => {
  let result = [...items.value]
  
  if (filterRequested.value) {
    result = result.filter(u => u.status === 'requested')
  }
  
  // Sort: requested first, then alphabetically
  return result.sort((a, b) => {
    if (a.status === 'requested' && b.status !== 'requested') return -1
    if (a.status !== 'requested' && b.status === 'requested') return 1
    const nameA = a.nickname || a.email
    const nameB = b.nickname || b.email
    return nameA.localeCompare(nameB, 'de')
  })
})

// Watch
watch(() => form.status, (newStatus) => {
  if (newStatus !== 'active') {
    form.is_admin = false
  }
})

// Methods
const statusLabel = (status: string) => {
  const labels: Record<string, string> = {
    requested: 'Unbestätigt',
    active: 'Aktiv',
    disabled: 'Gesperrt',
  }
  return labels[status] || status
}

const formatDate = (dateStr: string) => {
  if (!dateStr) return '–'
  return new Date(dateStr).toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

async function load() {
  loading.value = true
  try {
    const response = await apiClient.get('/admin/users')
    items.value = response.data
    // Default filter on if there are requested users
    filterRequested.value = items.value.some(u => u.status === 'requested')
  } catch (err) {
    console.error('Failed to load users', err)
  } finally {
    loading.value = false
  }
}

function editUser(user: any) {
  editingUser.value = user
  form.status = user.status
  form.is_admin = user.is_admin
  error.value = ''
}

async function saveUser() {
  error.value = ''
  saving.value = true
  try {
    await apiClient.put(`/admin/users/${editingUser.value.id}`, form)
    await load()
    editingUser.value = null
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Fehler beim Speichern.'
  } finally {
    saving.value = false
  }
}

async function deleteUser() {
  deleting.value = true
  try {
    await apiClient.delete(`/admin/users/${editingUser.value.id}`)
    await load()
    showDeleteConfirm.value = false
    editingUser.value = null
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Fehler beim Löschen.'
    showDeleteConfirm.value = false
  } finally {
    deleting.value = false
  }
}

onMounted(load)
</script>


