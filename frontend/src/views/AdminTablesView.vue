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
            <BellIcon
              v-if="table.hasPending"
              class="w-5 h-5 text-amber-500"
              title="Aufmerksamkeit erforderlich"
            />
          </div>
        </button>
      </div>
    </div>

    <!-- Table CRUD Components -->
    <AdminCrudUsers v-if="selectedTable === 'users'" @close="selectedTable = null" />
    <AdminCrudFirstPrograms v-if="selectedTable === 'first_programs'" @close="selectedTable = null" />

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
import { ref, computed } from 'vue'
import { BellIcon } from '@heroicons/vue/24/solid'
import AdminCrudUsers from '@/components/admin/AdminCrudUsers.vue'
import AdminCrudFirstPrograms from '@/components/admin/AdminCrudFirstPrograms.vue'

const selectedTable = ref<string | null>(null)

// Tables with implemented CRUD components
const implementedTables = ['users', 'first_programs']

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

const getTableLabel = (tableName: string) => {
  return tables.find(t => t.name === tableName)?.label || tableName
}
</script>
