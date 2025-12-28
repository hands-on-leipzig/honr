<template>
  <div class="p-4 space-y-6">
    <h1 class="text-2xl font-bold">Statistiken</h1>

    <div v-if="loading" class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
      Laden...
    </div>

    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-600">
      {{ error }}
    </div>

    <div v-else-if="stats" class="space-y-6">
      <!-- Grid Layout: 3 columns on desktop, 1 on mobile -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- 1. Users -->
        <div class="bg-white rounded-lg shadow p-4">
          <h2 class="text-lg font-semibold mb-3 text-gray-800">Benutzer</h2>
          <div class="space-y-2">
            <div class="text-3xl font-bold text-gray-900">{{ stats.users.total }}</div>
            <div class="text-sm text-gray-600">Gesamt</div>
            <div class="pt-3 border-t border-gray-200 space-y-1">
              <div class="text-xs text-blue-600 font-medium">30 Tage:</div>
              <div class="text-sm text-gray-700">• Neu: {{ stats.users.new_30_days }}</div>
              <div class="text-sm text-gray-700">• Eingeloggt: {{ stats.users.logged_30_days }}</div>
            </div>
          </div>
        </div>

        <!-- 2. Engagements -->
        <div class="bg-white rounded-lg shadow p-4">
          <h2 class="text-lg font-semibold mb-3 text-gray-800">Einsätze</h2>
          <div class="space-y-2">
            <div class="text-3xl font-bold text-gray-900">{{ stats.engagements.total }}</div>
            <div class="text-sm text-gray-600">Gesamt</div>
            <div class="text-sm text-gray-700">Ø {{ stats.engagements.avg_per_user }} pro Benutzer</div>
            <div class="pt-3 border-t border-gray-200">
              <div class="text-xs text-blue-600 font-medium">30 Tage:</div>
              <div class="text-sm text-gray-700">• Neu: {{ stats.engagements.new_30_days }}</div>
            </div>
          </div>
        </div>

        <!-- 4. Roles -->
        <div class="bg-white rounded-lg shadow p-4">
          <h2 class="text-lg font-semibold mb-3 text-gray-800">Rollen</h2>
          <div class="space-y-2">
            <div class="text-sm text-gray-600 mb-2">Top 5:</div>
            <ol class="space-y-1 text-sm">
              <li v-for="(role, index) in stats.roles.top" :key="role.id" class="flex items-center gap-2">
                <span class="text-gray-500 w-4">{{ index + 1 }}.</span>
                <span class="flex-1 truncate">{{ role.name }}</span>
                <span class="text-gray-600 font-medium">({{ role.count }})</span>
              </li>
            </ol>
            <div class="pt-3 border-t border-gray-200">
              <div class="text-sm text-gray-700">Ø {{ stats.roles.avg_per_user }} pro Benutzer</div>
            </div>
          </div>
        </div>

        <!-- 7. Geography -->
        <div class="bg-white rounded-lg shadow p-4">
          <h2 class="text-lg font-semibold mb-3 text-gray-800">Geografie</h2>
          <div class="space-y-2">
            <div class="text-sm text-gray-700">
              <div>Länder: {{ stats.geography.countries_count }}</div>
              <div>Standorte: {{ stats.geography.locations_count }}</div>
            </div>
            <div class="text-sm text-gray-600 mt-3 mb-2">Top 5 Standorte:</div>
            <ol class="space-y-1 text-sm">
              <li v-for="(location, index) in stats.geography.top_locations" :key="location.id" class="flex items-center gap-2">
                <span class="text-gray-500 w-4">{{ index + 1 }}.</span>
                <span class="flex-1 truncate">{{ location.name }}<span v-if="location.city">, {{ location.city }}</span></span>
                <span class="text-gray-600 font-medium">({{ location.count }})</span>
              </li>
            </ol>
            <div class="pt-3 border-t border-gray-200">
              <div class="text-sm text-gray-700">Ø {{ stats.geography.avg_per_location }} pro Standort</div>
            </div>
          </div>
        </div>

        <!-- 8. Programs -->
        <div class="bg-white rounded-lg shadow p-4">
          <h2 class="text-lg font-semibold mb-3 text-gray-800">Programme</h2>
          <div class="space-y-2">
            <div class="text-sm text-gray-600 mb-2">Top 5:</div>
            <ol class="space-y-1 text-sm">
              <li v-for="(program, index) in stats.programs.top" :key="program.id" class="flex items-center gap-2">
                <span class="text-gray-500 w-4">{{ index + 1 }}.</span>
                <span class="flex-1 truncate">{{ program.name }}</span>
                <span class="text-gray-600 font-medium">({{ program.count }})</span>
              </li>
            </ol>
            <div class="pt-3 border-t border-gray-200">
              <div class="text-sm text-gray-700">Ø {{ stats.programs.avg_per_program }} pro Programm</div>
            </div>
          </div>
        </div>

        <!-- 8. Seasons -->
        <div class="bg-white rounded-lg shadow p-4">
          <h2 class="text-lg font-semibold mb-3 text-gray-800">Saisons</h2>
          <div class="space-y-2">
            <div class="text-sm text-gray-600 mb-2">Top 5:</div>
            <ol class="space-y-1 text-sm">
              <li v-for="(season, index) in stats.seasons.top" :key="season.id" class="flex items-center gap-2">
                <span class="text-gray-500 w-4">{{ index + 1 }}.</span>
                <span class="flex-1 truncate">{{ season.name }}</span>
                <span class="text-gray-600 font-medium">({{ season.count }})</span>
              </li>
            </ol>
            <div class="pt-3 border-t border-gray-200">
              <div class="text-sm text-gray-700">Ø {{ stats.seasons.avg_per_season }} pro Saison</div>
            </div>
          </div>
        </div>
      </div>

      <!-- 9. Badges - Full Width -->
      <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-semibold mb-4 text-gray-800">Badges</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
          <div class="text-center">
            <div class="text-3xl font-bold text-gray-900">{{ stats.badges.counts.basic }}</div>
            <div class="text-sm text-gray-600">Basic</div>
          </div>
          <div class="text-center">
            <div class="text-3xl font-bold text-gray-900">{{ stats.badges.counts.bronze }}</div>
            <div class="text-sm text-gray-600">Bronze</div>
          </div>
          <div class="text-center">
            <div class="text-3xl font-bold text-gray-900">{{ stats.badges.counts.silver }}</div>
            <div class="text-sm text-gray-600">Silber</div>
          </div>
          <div class="text-center">
            <div class="text-3xl font-bold text-gray-900">{{ stats.badges.counts.gold }}</div>
            <div class="text-sm text-gray-600">Gold</div>
          </div>
        </div>
        <div class="space-y-3">
          <div v-if="stats.badges.most_awarded" class="text-sm">
            <span class="text-gray-600">Am häufigsten vergeben:</span>
            <span class="font-medium text-gray-900 ml-2">
              {{ stats.badges.most_awarded.role_name }} {{ getLevelName(stats.badges.most_awarded.level) }}
            </span>
            <span class="text-gray-600 ml-2">({{ stats.badges.most_awarded.count }})</span>
          </div>
          <div class="flex gap-6 text-sm">
            <div>
              <span class="text-gray-600">Max pro Benutzer:</span>
              <span class="font-medium text-gray-900 ml-2">{{ stats.badges.max_per_user }}</span>
            </div>
            <div>
              <span class="text-gray-600">Ø pro Benutzer:</span>
              <span class="font-medium text-gray-900 ml-2">{{ stats.badges.avg_per_user }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import apiClient from '@/api/client'

const stats = ref<any>(null)
const loading = ref(true)
const error = ref('')

function getLevelName(level: number): string {
  const names: Record<number, string> = {
    1: 'Basic',
    2: 'Bronze',
    3: 'Silber',
    4: 'Gold',
  }
  return names[level] || ''
}

async function loadStatistics() {
  loading.value = true
  error.value = ''
  try {
    const response = await apiClient.get('/admin/statistics')
    stats.value = response.data
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Fehler beim Laden der Statistiken.'
    console.error('Failed to load statistics', err)
  } finally {
    loading.value = false
  }
}

onMounted(loadStatistics)
</script>
