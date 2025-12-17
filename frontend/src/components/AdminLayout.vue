<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Main Content Area -->
    <main class="flex-1 overflow-y-auto pb-20">
      <RouterView />
    </main>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-50">
      <div class="flex justify-around items-center h-16">
        <RouterLink
          v-for="item in navigationItems"
          :key="item.name"
          :to="item.path"
          class="flex flex-col items-center justify-center flex-1 h-full text-gray-600 hover:text-blue-600 transition-colors"
          :class="{ 'text-blue-600': isActive(item.name) }"
        >
          <component :is="isActive(item.name) ? item.iconSolid : item.iconOutline" class="w-6 h-6 mb-1" />
          <span class="text-xs font-medium">{{ item.label }}</span>
        </RouterLink>
      </div>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { RouterLink, RouterView, useRoute } from 'vue-router'
import { TableCellsIcon, ChartBarIcon, UserIcon } from '@heroicons/vue/24/outline'
import { TableCellsIcon as TableCellsIconSolid, ChartBarIcon as ChartBarIconSolid, UserIcon as UserIconSolid } from '@heroicons/vue/24/solid'

const route = useRoute()

const navigationItems = [
  {
    name: 'admin-tables',
    path: '/admin/tables',
    label: 'Tabellen',
    iconOutline: TableCellsIcon,
    iconSolid: TableCellsIconSolid,
  },
  {
    name: 'admin-statistics',
    path: '/admin/statistics',
    label: 'Statistiken',
    iconOutline: ChartBarIcon,
    iconSolid: ChartBarIconSolid,
  },
  {
    name: 'admin-back',
    path: '/admin/back',
    label: 'Zurück',
    iconOutline: UserIcon,
    iconSolid: UserIconSolid,
  },
]

const isActive = (name: string) => route.name === name
</script>

