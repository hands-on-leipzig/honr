<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Einstellungen</h1>

    <!-- Account Settings -->
    <div class="bg-white rounded-lg shadow mb-4">
      <div class="p-4 border-b border-gray-200">
        <h2 class="font-semibold">Benutzer-Einstellungen</h2>
      </div>
      <div class="divide-y divide-gray-200">
        <button class="w-full px-4 py-3 text-left hover:bg-gray-50">
          E-Mail ändern
        </button>
        <button class="w-full px-4 py-3 text-left hover:bg-gray-50">
          Passwort ändern
        </button>
        <button class="w-full px-4 py-3 text-left hover:bg-gray-50">
          Name ändern
        </button>
        <button class="w-full px-4 py-3 text-left hover:bg-gray-50">
          Über mich ändern
        </button>
        <button class="w-full px-4 py-3 text-left hover:bg-gray-50">
          Newsletter-Einwilligung
        </button>
        <button class="w-full px-4 py-3 text-left hover:bg-gray-50">
          Regionalpartner Name ändern
        </button>
        <button class="w-full px-4 py-3 text-left text-red-600 hover:bg-red-50">
          Benutzer löschen
        </button>
      </div>
    </div>

    <!-- Support -->
    <div class="bg-white rounded-lg shadow mb-4">
      <div class="p-4 border-b border-gray-200">
        <h2 class="font-semibold">Support</h2>
      </div>
      <a
        href="mailto:support@example.com?subject=HOTR%20-%20Hilfe/Feedback/Ideen"
        class="block w-full px-4 py-3 text-left hover:bg-gray-50"
      >
        Kontakt / Support
      </a>
    </div>

    <!-- Administration (Admin Only) -->
    <div v-if="isAdmin" class="bg-white rounded-lg shadow mb-4">
      <RouterLink
        to="/admin/tables"
        class="block w-full px-4 py-3 text-left hover:bg-gray-50"
      >
        <span class="font-semibold text-blue-600">Administration</span>
      </RouterLink>
    </div>

  </div>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { ref } from 'vue'

// TODO: Replace with actual auth check when authentication is implemented
// For now, hardcoded to true for testing admin UI
const isAdmin = ref(true)
</script>

