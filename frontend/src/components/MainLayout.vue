<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Top Header Bar -->
    <header class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-[999]">
      <div class="flex items-center justify-between px-4 py-3">
        <!-- HONR Logo -->
        <img 
          src="@/assets/logos/honr-logo.png" 
          alt="HONR" 
          class="h-8 object-contain"
        />
        
        <!-- Hands On Technology Logo -->
        <a 
          href="https://www.hands-on-technology.org/de/engagement" 
          target="_blank" 
          rel="noopener noreferrer"
          class="inline-block hover:opacity-80 transition-opacity"
        >
          <img 
            src="@/assets/logos/hands-on-logo.png" 
            alt="Hands On Technology" 
            class="h-8 object-contain"
          />
        </a>
      </div>
    </header>

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
import { TrophyIcon, GlobeAltIcon, UserGroupIcon, Cog6ToothIcon } from '@heroicons/vue/24/outline'
import { TrophyIcon as TrophyIconSolid, GlobeAltIcon as GlobeAltIconSolid, UserGroupIcon as UserGroupIconSolid, Cog6ToothIcon as Cog6ToothIconSolid } from '@heroicons/vue/24/solid'

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
    iconOutline: UserGroupIcon,
    iconSolid: UserGroupIconSolid,
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

