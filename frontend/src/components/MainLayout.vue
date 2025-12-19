<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Main Content Area -->
    <main class="flex-1 overflow-y-auto pb-20">
      <RouterView />
    </main>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-[1000]">
      <div class="flex justify-around items-center h-16">
        <RouterLink
          v-for="item in navigationItems"
          :key="item.name"
          :to="item.path"
          class="flex items-center justify-center flex-1 h-full text-gray-600 hover:text-blue-600 transition-colors"
          :class="{ 'text-blue-600': isActive(item.name) }"
        >
          <component :is="isActive(item.name) ? item.iconSolid : item.iconOutline" class="w-7 h-7" />
        </RouterLink>
      </div>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { RouterLink, RouterView, useRoute } from 'vue-router'
import { TrophyIcon, GlobeAltIcon, FaceSmileIcon, Cog6ToothIcon } from '@heroicons/vue/24/outline'
import { TrophyIcon as TrophyIconSolid, GlobeAltIcon as GlobeAltIconSolid, FaceSmileIcon as FaceSmileIconSolid, Cog6ToothIcon as Cog6ToothIconSolid } from '@heroicons/vue/24/solid'

const route = useRoute()

const navigationItems = [
  {
    name: 'awards',
    path: '/awards',
    iconOutline: TrophyIcon,
    iconSolid: TrophyIconSolid,
  },
  {
    name: 'all',
    path: '/all',
    iconOutline: GlobeAltIcon,
    iconSolid: GlobeAltIconSolid,
  },
  {
    name: 'people',
    path: '/people',
    iconOutline: FaceSmileIcon,
    iconSolid: FaceSmileIconSolid,
  },
  {
    name: 'settings',
    path: '/settings',
    iconOutline: Cog6ToothIcon,
    iconSolid: Cog6ToothIconSolid,
  },
]

const isActive = (name: string) => route.name === name
</script>

