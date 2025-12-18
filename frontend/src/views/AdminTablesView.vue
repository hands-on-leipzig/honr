<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Tabellen</h1>

    <!-- Table Selection -->
    <div v-if="!selectedTable" class="bg-white rounded-lg shadow">
      <div class="divide-y divide-gray-200">
        <button
          v-for="table in sortedTables"
          :key="table.name"
          @click="selectTable(table.name)"
          class="w-full px-4 py-3 text-left hover:bg-gray-50 flex items-center justify-between"
        >
          <div class="flex items-center space-x-2">
            <span class="font-medium">{{ table.label }}</span>
            <BellIcon
              v-if="table.hasPending"
              class="w-5 h-5 text-amber-500"
              title="Aufmerksamkeit erforderlich"
            />
          </div>
        </button>
      </div>
    </div>

    <!-- Users Table CRUD -->
    <div v-if="selectedTable === 'users'" class="bg-white rounded-lg shadow">
      <div class="p-4 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-xl font-semibold">Benutzer</h2>
        <button @click="selectedTable = null" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>

      <!-- Loading -->
      <div v-if="usersLoading" class="p-4 text-center text-gray-500">Laden...</div>

      <!-- Users List -->
      <div v-else class="divide-y divide-gray-200">
        <div
          v-for="user in sortedUsers"
          :key="user.id"
          @click="editUser(user)"
          class="px-4 py-3 hover:bg-gray-50 cursor-pointer"
        >
          <div class="flex items-center justify-between">
            <div>
              <div class="font-medium">{{ user.nickname || '(kein Name)' }}</div>
              <div class="text-sm text-gray-500">{{ user.email }}</div>
            </div>
            <div class="flex items-center space-x-2">
              <span v-if="user.is_admin" class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Admin</span>
              <span
                :class="[
                  'px-2 py-1 text-xs rounded-full',
                  user.status === 'active' ? 'bg-green-100 text-green-800' :
                  user.status === 'requested' ? 'bg-yellow-100 text-yellow-800' :
                  user.status === 'disabled' ? 'bg-red-100 text-red-800' :
                  'bg-gray-100 text-gray-800'
                ]"
              >
                {{ statusLabel(user.status) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Generic Table Placeholder -->
    <div v-if="selectedTable && selectedTable !== 'users'" class="bg-white rounded-lg shadow p-4">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold">{{ getTableLabel(selectedTable) }}</h2>
        <button @click="selectedTable = null" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>
      <p class="text-gray-600">Tabelleneinträge für {{ getTableLabel(selectedTable) }} werden hier angezeigt.</p>
      <p class="text-sm text-gray-500 mt-2">(CRUD-Funktionalität folgt)</p>
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Newsletter</label>
            <div class="px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-gray-700">{{ editingUser.consent_to_newsletter ? 'Ja' : 'Nein' }}</div>
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
                <select v-else v-model="userForm.status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                  <option value="active">Aktiv</option>
                  <option value="disabled">Gesperrt</option>
                </select>
              </div>
              <div>
                <label class="flex items-center space-x-2" :class="{ 'opacity-50': userForm.status !== 'active' }">
                  <input 
                    v-model="userForm.is_admin" 
                    type="checkbox" 
                    class="rounded border-gray-300 text-blue-600" 
                    :disabled="userForm.status !== 'active'"
                  />
                  <span class="text-sm font-medium">Administrator</span>
                  <span v-if="userForm.status !== 'active'" class="text-xs text-gray-500">(nur für aktive Benutzer)</span>
                </label>
              </div>

              <div v-if="userError" class="text-red-600 text-sm">{{ userError }}</div>
              <div class="flex gap-2 pt-2">
                <button type="button" @click="editingUser = null" class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                  Abbrechen
                </button>
                <button type="button" @click="confirmDeleteUser" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                  Löschen
                </button>
                <button type="submit" :disabled="userSaving" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
                  {{ userSaving ? 'Speichern...' : 'Speichern' }}
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
          <button @click="deleteUser" :disabled="userDeleting" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50">
            {{ userDeleting ? 'Löschen...' : 'Löschen' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { BellIcon } from '@heroicons/vue/24/solid'
import apiClient from '@/api/client'
import { useUserStore } from '@/stores/user'

const userStore = useUserStore()

onMounted(() => {
  if (!userStore.user) {
    userStore.fetchUser()
  }
})

const selectedTable = ref<string | null>(null)

// Users state
const users = ref<any[]>([])
const usersLoading = ref(false)
const editingUser = ref<any | null>(null)
const userForm = reactive({
  status: 'requested',
  is_admin: false,
})
const userSaving = ref(false)
const userDeleting = ref(false)
const userError = ref('')
const showDeleteConfirm = ref(false)

const tables = [
  { name: 'badges', label: 'Badges', hasPending: false },
  { name: 'badge_thresholds', label: 'Badge-Schwellenwerte', hasPending: false },
  { name: 'users', label: 'Benutzer', hasPending: false },
  { name: 'first_programs', label: 'FIRST Programme', hasPending: false },
  { name: 'countries', label: 'Länder', hasPending: true },
  { name: 'levels', label: 'Level', hasPending: true },
  { name: 'roles', label: 'Rollen', hasPending: true },
  { name: 'seasons', label: 'Saisons', hasPending: false },
  { name: 'locations', label: 'Standorte', hasPending: true },
  { name: 'events', label: 'Veranstaltungen', hasPending: true },
  { name: 'earned_badges', label: 'Verdiente Badges', hasPending: false },
  { name: 'engagements', label: 'Volunteer-Einsätze', hasPending: false },
]

const sortedTables = computed(() => {
  return [...tables].sort((a, b) => a.label.localeCompare(b.label, 'de'))
})

const isCurrentUser = computed(() => {
  return editingUser.value?.id === userStore.user?.id
})

const sortedUsers = computed(() => {
  return [...users.value].sort((a, b) => {
    // Requested users at top
    if (a.status === 'requested' && b.status !== 'requested') return -1
    if (a.status !== 'requested' && b.status === 'requested') return 1
    // Then alphabetically
    const nameA = a.nickname || a.email
    const nameB = b.nickname || b.email
    return nameA.localeCompare(nameB, 'de')
  })
})

const selectTable = (tableName: string) => {
  selectedTable.value = tableName
}

const getTableLabel = (tableName: string) => {
  return tables.find(t => t.name === tableName)?.label || tableName
}

const formatDate = (dateStr: string) => {
  if (!dateStr) return '–'
  return new Date(dateStr).toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const statusLabel = (status: string) => {
  const labels: Record<string, string> = {
    requested: 'Unbestätigt',
    active: 'Aktiv',
    disabled: 'Gesperrt',
  }
  return labels[status] || status
}

// Watch for users table selection
watch(selectedTable, async (newVal) => {
  if (newVal === 'users') {
    await loadUsers()
  }
})

// Turn off admin when status is not active
watch(() => userForm.status, (newStatus) => {
  if (newStatus !== 'active') {
    userForm.is_admin = false
  }
})

async function loadUsers() {
  usersLoading.value = true
  try {
    const response = await apiClient.get('/admin/users')
    users.value = response.data
  } catch (err) {
    console.error('Failed to load users', err)
  } finally {
    usersLoading.value = false
  }
}

function editUser(user: any) {
  editingUser.value = user
  userForm.status = user.status
  userForm.is_admin = user.is_admin
  userError.value = ''
}

async function saveUser() {
  userError.value = ''
  userSaving.value = true
  try {
    await apiClient.put(`/admin/users/${editingUser.value.id}`, userForm)
    await loadUsers()
    editingUser.value = null
  } catch (err: any) {
    userError.value = err.response?.data?.message || 'Fehler beim Speichern.'
  } finally {
    userSaving.value = false
  }
}

function confirmDeleteUser() {
  showDeleteConfirm.value = true
}

async function deleteUser() {
  userDeleting.value = true
  try {
    await apiClient.delete(`/admin/users/${editingUser.value.id}`)
    await loadUsers()
    showDeleteConfirm.value = false
    editingUser.value = null
  } catch (err: any) {
    userError.value = err.response?.data?.message || 'Fehler beim Löschen.'
    showDeleteConfirm.value = false
  } finally {
    userDeleting.value = false
  }
}
</script>
