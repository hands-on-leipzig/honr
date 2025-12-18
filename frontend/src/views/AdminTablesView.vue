<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Tabellen</h1>

    <!-- Table Selection -->
    <div v-if="!selectedTable" class="bg-white rounded-lg shadow">
      <div class="divide-y divide-gray-200">
        <button
          v-for="table in sortedTables"
          :key="table.name"
          @click="selectedTable = table.name"
          class="w-full px-4 py-3 text-left hover:bg-gray-50 flex items-center justify-between"
        >
          <div class="flex items-center space-x-2">
            <span class="font-medium">{{ table.label }}</span>
            <template v-if="pendingCounts[table.name] > 0">
              <BellIcon class="w-5 h-5 text-amber-500" />
              <span class="text-xs text-amber-600 font-medium">{{ pendingCounts[table.name] }}</span>
            </template>
          </div>
        </button>
      </div>
    </div>

    <!-- Table CRUD Components -->
    <AdminCrudUsers v-if="selectedTable === 'users'" @close="selectedTable = null" />
    <AdminCrudFirstPrograms v-if="selectedTable === 'first_programs'" @close="selectedTable = null" />
    <AdminCrudSeasons v-if="selectedTable === 'seasons'" @close="selectedTable = null" />
    <AdminCrudLevels v-if="selectedTable === 'levels'" @close="selectedTable = null" />
    <AdminCrudCountries v-if="selectedTable === 'countries'" @close="selectedTable = null" />
    <AdminCrudLocations v-if="selectedTable === 'locations'" @close="selectedTable = null" />
    <AdminCrudRoles v-if="selectedTable === 'roles'" @close="selectedTable = null" />
    <AdminCrudEvents v-if="selectedTable === 'events'" @close="selectedTable = null" />
    <AdminCrudEngagements v-if="selectedTable === 'engagements'" @close="selectedTable = null" />
    <AdminCrudBadges v-if="selectedTable === 'badges'" @close="selectedTable = null" />

    <!-- Generic Table Placeholder -->
    <div v-if="selectedTable && !implementedTables.includes(selectedTable)" class="bg-white rounded-lg shadow p-4">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold">{{ getTableLabel(selectedTable) }}</h2>
        <button @click="selectedTable = null" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>
      <p class="text-gray-600">Tabelleneinträge für {{ getTableLabel(selectedTable) }} werden hier angezeigt.</p>
      <p class="text-sm text-gray-500 mt-2">(CRUD-Funktionalität folgt)</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { BellIcon } from '@heroicons/vue/24/solid'
import apiClient from '@/api/client'
import AdminCrudUsers from '@/components/admin/AdminCrudUsers.vue'
import AdminCrudFirstPrograms from '@/components/admin/AdminCrudFirstPrograms.vue'
import AdminCrudSeasons from '@/components/admin/AdminCrudSeasons.vue'
import AdminCrudLevels from '@/components/admin/AdminCrudLevels.vue'
import AdminCrudCountries from '@/components/admin/AdminCrudCountries.vue'
import AdminCrudLocations from '@/components/admin/AdminCrudLocations.vue'
import AdminCrudRoles from '@/components/admin/AdminCrudRoles.vue'
import AdminCrudEvents from '@/components/admin/AdminCrudEvents.vue'
import AdminCrudEngagements from '@/components/admin/AdminCrudEngagements.vue'
import AdminCrudBadges from '@/components/admin/AdminCrudBadges.vue'

const selectedTable = ref<string | null>(null)

// Tables with implemented CRUD components
const implementedTables = ['users', 'first_programs', 'seasons', 'levels', 'countries', 'locations', 'roles', 'events', 'engagements', 'badges']

// Pending counts for crowdsourced tables
const pendingCounts = reactive<Record<string, number>>({
  levels: 0,
  roles: 0,
  countries: 0,
  locations: 0,
  events: 0,
})

const tables = [
  { name: 'badges', label: 'Badges' },
  { name: 'badge_thresholds', label: 'Badge-Schwellenwerte' },
  { name: 'users', label: 'Benutzer' },
  { name: 'first_programs', label: 'FIRST Programme' },
  { name: 'countries', label: 'Länder' },
  { name: 'levels', label: 'Level' },
  { name: 'roles', label: 'Rollen' },
  { name: 'seasons', label: 'Saisons' },
  { name: 'locations', label: 'Standorte' },
  { name: 'events', label: 'Veranstaltungen' },
  { name: 'earned_badges', label: 'Verdiente Badges' },
  { name: 'engagements', label: 'Volunteer-Einsätze' },
]

const sortedTables = computed(() => {
  return [...tables].sort((a, b) => a.label.localeCompare(b.label, 'de'))
})

const getTableLabel = (tableName: string) => {
  return tables.find(t => t.name === tableName)?.label || tableName
}

async function loadPendingCounts() {
  try {
    // Load levels pending count
    const levelsRes = await apiClient.get('/admin/levels')
    pendingCounts.levels = levelsRes.data.filter((i: any) => i.status === 'pending').length

    // Load countries pending count
    const countriesRes = await apiClient.get('/admin/countries')
    pendingCounts.countries = countriesRes.data.filter((i: any) => i.status === 'pending').length

    // Load locations pending count
    const locationsRes = await apiClient.get('/admin/locations')
    pendingCounts.locations = locationsRes.data.filter((i: any) => i.status === 'pending').length

    // Load roles pending count
    const rolesRes = await apiClient.get('/admin/roles')
    pendingCounts.roles = rolesRes.data.filter((i: any) => i.status === 'pending').length

    // Load events pending count
    const eventsRes = await apiClient.get('/admin/events')
    pendingCounts.events = eventsRes.data.filter((i: any) => i.status === 'pending').length
  } catch (err) {
    console.error('Failed to load pending counts', err)
  }
}

// Reload counts when returning from a CRUD view
watch(selectedTable, (newVal) => {
  if (newVal === null) {
    loadPendingCounts()
  }
})

onMounted(loadPendingCounts)
</script>
