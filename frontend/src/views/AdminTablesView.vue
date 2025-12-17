<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Tabellen</h1>

    <!-- Global Filter -->
    <div class="mb-4">
      <label class="flex items-center space-x-2">
        <input
          v-model="showPendingOnly"
          type="checkbox"
          class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
        />
        <span class="text-sm font-medium">Admin-Aktion erforderlich (nur ausstehende Einträge)</span>
      </label>
    </div>

    <!-- Table Selection -->
    <div class="bg-white rounded-lg shadow">
      <div class="divide-y divide-gray-200">
        <button
          v-for="table in filteredTables"
          :key="table.name"
          @click="selectTable(table.name)"
          class="w-full px-4 py-3 text-left hover:bg-gray-50 flex items-center justify-between"
        >
          <div class="flex items-center space-x-2">
            <span class="font-medium">{{ table.label }}</span>
            <ExclamationCircleIcon
              v-if="table.hasPending"
              class="w-5 h-5 text-red-600"
              title="Aktion erforderlich"
            />
          </div>
        </button>
      </div>
    </div>

    <!-- Table Entries Modal (placeholder for now) -->
    <div v-if="selectedTable" class="mt-4">
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-semibold">{{ getTableLabel(selectedTable) }}</h2>
          <button
            @click="selectedTable = null"
            class="text-gray-500 hover:text-gray-700"
          >
            ✕
          </button>
        </div>
        <p class="text-gray-600">Tabelleneinträge für {{ getTableLabel(selectedTable) }} werden hier angezeigt.</p>
        <p class="text-sm text-gray-500 mt-2">(CRUD-Funktionalität folgt)</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { ExclamationCircleIcon } from '@heroicons/vue/24/solid'

const showPendingOnly = ref(true)
const selectedTable = ref<string | null>(null)

const tables = [
  { name: 'first_programs', label: 'FIRST Programme', hasPending: false },
  { name: 'seasons', label: 'Saisons', hasPending: false },
  { name: 'levels', label: 'Ebenen', hasPending: true },
  { name: 'roles', label: 'Rollen', hasPending: true },
  { name: 'countries', label: 'Länder', hasPending: true },
  { name: 'locations', label: 'Standorte', hasPending: true },
  { name: 'events', label: 'Veranstaltungen', hasPending: true },
  { name: 'engagements', label: 'Engagements', hasPending: false },
  { name: 'badges', label: 'Abzeichen', hasPending: false },
  { name: 'badge_thresholds', label: 'Abzeichen-Schwellenwerte', hasPending: false },
  { name: 'earned_badges', label: 'Verdiente Abzeichen', hasPending: false },
  { name: 'users', label: 'Benutzer', hasPending: false },
]

const filteredTables = computed(() => {
  if (showPendingOnly.value) {
    return tables.filter(table => table.hasPending)
  }
  return tables
})

const selectTable = (tableName: string) => {
  selectedTable.value = tableName
}

const getTableLabel = (tableName: string) => {
  return tables.find(t => t.name === tableName)?.label || tableName
}
</script>

