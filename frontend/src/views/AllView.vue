<template>
  <div class="p-4 pb-32">
    <h1 class="text-2xl font-bold mb-4">Alle</h1>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-gray-200 mb-4">
      <button
        @click="activeTab = 'leaderboard'"
        :class="[
          'flex-1 py-3 text-center font-medium border-b-2 transition-colors',
          activeTab === 'leaderboard'
            ? 'border-blue-600 text-blue-600'
            : 'border-transparent text-gray-500 hover:text-gray-700'
        ]"
      >
        Bestenliste
      </button>
      <button
        @click="activeTab = 'map'"
        :class="[
          'flex-1 py-3 text-center font-medium border-b-2 transition-colors',
          activeTab === 'map'
            ? 'border-blue-600 text-blue-600'
            : 'border-transparent text-gray-500 hover:text-gray-700'
        ]"
      >
        Karte
      </button>
    </div>

    <!-- Tab Content -->
    <div v-if="activeTab === 'leaderboard'" class="bg-white rounded-lg shadow p-6">
      <h2 class="text-lg font-semibold mb-4">Bestenliste</h2>
      <p class="text-gray-600">Bestenliste wird hier angezeigt...</p>
    </div>

    <div v-if="activeTab === 'map'" class="bg-white rounded-lg shadow p-6">
      <h2 class="text-lg font-semibold mb-4">Karte</h2>
      <p class="text-gray-600">Karte wird hier angezeigt...</p>
    </div>

    <!-- Search Overlay (Placeholder) -->
    <div class="fixed bottom-24 left-0 right-0 px-4 pb-4">
      <div class="bg-white rounded-lg shadow-lg p-4">
        <input
          type="text"
          placeholder="Benutzer suchen..."
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const activeTab = ref<'leaderboard' | 'map'>('leaderboard')
</script>

