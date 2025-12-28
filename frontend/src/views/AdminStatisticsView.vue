<template>
  <div class="p-4 pb-32 space-y-4">
    <h1 class="text-2xl font-bold">Statistiken</h1>

    <div v-if="loading" class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
      Laden...
    </div>

    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-600">
      {{ error }}
    </div>

    <div v-else-if="stats" class="space-y-4">
      <!-- Grid Layout: 3 columns on desktop, 1 on mobile -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- 1. Users -->
        <div class="bg-white rounded-lg shadow p-4">
          <h2 class="text-lg font-semibold mb-4 text-gray-800">Benutzer</h2>
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Gesamt</span>
              <span :class="['text-4xl font-bold', PRIMARY_COLORS.link]">{{ stats.users.total }}</span>
            </div>
            <div class="pt-3 border-t divide-y divide-gray-100">
              <div class="py-2">
                <div class="text-xs text-blue-600 font-medium mb-1">30 Tage</div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-700">Neu</span>
                  <span :class="['text-lg font-bold', PRIMARY_COLORS.link]">{{ stats.users.new_30_days }}</span>
                </div>
              </div>
              <div class="py-2">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-700">Eingeloggt</span>
                  <span :class="['text-lg font-bold', PRIMARY_COLORS.link]">{{ stats.users.logged_30_days }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. Engagements -->
        <div class="bg-white rounded-lg shadow p-4">
          <h2 class="text-lg font-semibold mb-4 text-gray-800">Einsätze</h2>
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Gesamt</span>
              <span :class="['text-4xl font-bold', PRIMARY_COLORS.link]">{{ stats.engagements.total }}</span>
            </div>
            <div class="pt-3 border-t">
              <div class="py-2">
                <div class="text-xs text-blue-600 font-medium mb-1">30 Tage</div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-700">Neu</span>
                  <span :class="['text-lg font-bold', PRIMARY_COLORS.link]">{{ stats.engagements.new_30_days }}</span>
                </div>
              </div>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-700">Ø pro Benutzer</span>
              <span :class="['text-lg font-bold', PRIMARY_COLORS.link]">{{ stats.engagements.avg_per_user }}</span>
            </div>
          </div>
        </div>

        <!-- 4. Roles -->
        <div class="bg-white rounded-lg shadow p-4">
          <h2 class="text-lg font-semibold mb-4 text-gray-800">Rollen</h2>
          <div class="space-y-3">
            <div class="text-sm text-gray-600 font-medium mb-2">Top 5</div>
            <div class="divide-y divide-gray-100">
              <div
                v-for="(role, index) in stats.roles.top"
                :key="role.id"
                class="py-2 flex items-center justify-between hover:bg-gray-50 transition-colors"
              >
                <div class="flex items-center gap-2 flex-1 min-w-0">
                  <span
                    :class="[
                      'inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold flex-shrink-0',
                      getRankColorClass(index + 1)
                    ]"
                  >
                    {{ index + 1 }}
                  </span>
                  <span class="text-sm font-medium text-gray-900 truncate">{{ role.name }}</span>
                </div>
                <span :class="['text-lg font-bold', PRIMARY_COLORS.link]">{{ role.count }}</span>
              </div>
            </div>
            <div class="pt-3 border-t">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-700">Ø pro Benutzer</span>
                <span :class="['text-sm font-bold', PRIMARY_COLORS.link]">{{ stats.roles.avg_per_user }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- 7. Geography -->
        <div class="bg-white rounded-lg shadow p-4">
          <h2 class="text-lg font-semibold mb-4 text-gray-800">Geografie</h2>
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-700">Länder</span>
              <span :class="['text-lg font-bold', PRIMARY_COLORS.link]">{{ stats.geography.countries_count }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-700">Standorte</span>
              <span :class="['text-lg font-bold', PRIMARY_COLORS.link]">{{ stats.geography.locations_count }}</span>
            </div>
            <div class="pt-3 border-t">
              <div class="text-sm text-gray-600 font-medium mb-2">Top 5 Standorte</div>
              <div class="divide-y divide-gray-100">
                <div
                  v-for="(location, index) in stats.geography.top_locations"
                  :key="location.id"
                  class="py-2 flex items-center justify-between hover:bg-gray-50 transition-colors"
                >
                  <div class="flex items-center gap-2 flex-1 min-w-0">
                    <span
                      :class="[
                        'inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold flex-shrink-0',
                        getRankColorClass(index + 1)
                      ]"
                    >
                      {{ index + 1 }}
                    </span>
                    <span class="text-sm font-medium text-gray-900 truncate">
                      {{ location.name }}<span v-if="location.city" class="text-gray-600">, {{ location.city }}</span>
                    </span>
                  </div>
                  <span :class="['text-lg font-bold', PRIMARY_COLORS.link]">{{ location.count }}</span>
                </div>
              </div>
            </div>
            <div class="pt-3 border-t">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-700">Ø pro Standort</span>
                <span :class="['text-sm font-bold', PRIMARY_COLORS.link]">{{ stats.geography.avg_per_location }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- 8. Programs -->
        <div class="bg-white rounded-lg shadow p-4">
          <h2 class="text-lg font-semibold mb-4 text-gray-800">Programme</h2>
          <div class="space-y-3">
            <div class="text-sm text-gray-600 font-medium mb-2">Top 5</div>
            <div class="divide-y divide-gray-100">
              <div
                v-for="(program, index) in stats.programs.top"
                :key="program.id"
                class="py-2 flex items-center justify-between hover:bg-gray-50 transition-colors"
              >
                <div class="flex items-center gap-2 flex-1 min-w-0">
                  <span
                    :class="[
                      'inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold flex-shrink-0',
                      getRankColorClass(index + 1)
                    ]"
                  >
                    {{ index + 1 }}
                  </span>
                  <span class="text-sm font-medium text-gray-900 truncate">{{ program.name }}</span>
                </div>
                <span :class="['text-lg font-bold', PRIMARY_COLORS.link]">{{ program.count }}</span>
              </div>
            </div>
            <div class="pt-3 border-t">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-700">Ø pro Programm</span>
                <span :class="['text-sm font-bold', PRIMARY_COLORS.link]">{{ stats.programs.avg_per_program }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- 8. Seasons -->
        <div class="bg-white rounded-lg shadow p-4">
          <h2 class="text-lg font-semibold mb-4 text-gray-800">Saisons</h2>
          <div class="space-y-3">
            <div class="text-sm text-gray-600 font-medium mb-2">Top 5</div>
            <div class="divide-y divide-gray-100">
              <div
                v-for="(season, index) in stats.seasons.top"
                :key="season.id"
                class="py-2 flex items-center justify-between hover:bg-gray-50 transition-colors"
              >
                <div class="flex items-center gap-2 flex-1 min-w-0">
                  <span
                    :class="[
                      'inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold flex-shrink-0',
                      getRankColorClass(index + 1)
                    ]"
                  >
                    {{ index + 1 }}
                  </span>
                  <span class="text-sm font-medium text-gray-900 truncate">{{ season.name }}</span>
                </div>
                <span :class="['text-lg font-bold', PRIMARY_COLORS.link]">{{ season.count }}</span>
              </div>
            </div>
            <div class="pt-3 border-t">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-700">Ø pro Saison</span>
                <span :class="['text-sm font-bold', PRIMARY_COLORS.link]">{{ stats.seasons.avg_per_season }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 9. Badges - Full Width -->
      <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-semibold mb-4 text-gray-800">Badges</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
          <div class="text-center p-4 rounded-lg hover:bg-gray-50 transition-colors">
            <div :class="['text-4xl font-bold mb-2', PRIMARY_COLORS.link]">{{ stats.badges.counts.basic }}</div>
            <div class="text-sm font-medium text-gray-700">Basic</div>
          </div>
          <div class="text-center p-4 rounded-lg hover:bg-gray-50 transition-colors">
            <div :class="['text-4xl font-bold mb-2', PRIMARY_COLORS.link]">{{ stats.badges.counts.bronze }}</div>
            <div class="text-sm font-medium text-gray-700">Bronze</div>
          </div>
          <div class="text-center p-4 rounded-lg hover:bg-gray-50 transition-colors">
            <div :class="['text-4xl font-bold mb-2', PRIMARY_COLORS.link]">{{ stats.badges.counts.silver }}</div>
            <div class="text-sm font-medium text-gray-700">Silber</div>
          </div>
          <div class="text-center p-4 rounded-lg hover:bg-gray-50 transition-colors">
            <div :class="['text-4xl font-bold mb-2', PRIMARY_COLORS.link]">{{ stats.badges.counts.gold }}</div>
            <div class="text-sm font-medium text-gray-700">Gold</div>
          </div>
        </div>
        <div v-if="stats.badges.top_5 && stats.badges.top_5.length > 0" class="mb-6">
          <div class="text-sm text-gray-600 font-medium mb-3">Top 5 vergeben</div>
          <div class="flex flex-wrap gap-3">
            <BadgeIcon
              v-for="badge in stats.badges.top_5"
              :key="`${badge.role_id}-${badge.level}`"
              :logo-path="badge.logo_path"
              :level="badge.level"
              :role-name="badge.role_name"
              :role-short-name="badge.role_short_name"
              :engagement-count="badge.count"
              size="md"
            />
          </div>
        </div>
        <div class="pt-4 border-t divide-y divide-gray-100">
          <div v-if="stats.badges.most_awarded" class="py-3">
            <div class="text-sm text-gray-600 mb-1">Am häufigsten vergeben</div>
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-900">
                {{ stats.badges.most_awarded.role_name }} {{ getLevelName(stats.badges.most_awarded.level) }}
              </span>
              <span :class="['text-lg font-bold', PRIMARY_COLORS.link]">{{ stats.badges.most_awarded.count }}</span>
            </div>
          </div>
          <div class="py-3 flex items-center justify-between">
            <span class="text-sm text-gray-700">Max pro Benutzer</span>
            <span :class="['text-lg font-bold', PRIMARY_COLORS.link]">{{ stats.badges.max_per_user }}</span>
          </div>
          <div class="py-3 flex items-center justify-between">
            <span class="text-sm text-gray-700">Ø pro Benutzer</span>
            <span :class="['text-lg font-bold', PRIMARY_COLORS.link]">{{ stats.badges.avg_per_user }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import apiClient from '@/api/client'
import { getRankColorClass, PRIMARY_COLORS } from '@/constants/uiColors'
import BadgeIcon from '@/components/awards/BadgeIcon.vue'

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
