<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Einstellungen</h1>

    <!-- Account Settings -->
    <div class="bg-white rounded-lg shadow mb-4">
      <div class="p-4 border-b border-gray-200">
        <h2 class="font-semibold">Konto</h2>
      </div>
      <div class="divide-y divide-gray-200">
        <button class="w-full px-4 py-3 text-left hover:bg-gray-50">
          E-Mail ändern
        </button>
        <button class="w-full px-4 py-3 text-left hover:bg-gray-50">
          Passwort ändern
        </button>
        <button class="w-full px-4 py-3 text-left hover:bg-gray-50">
          Spitzname ändern
        </button>
        <button class="w-full px-4 py-3 text-left hover:bg-gray-50">
          Kurze Biografie ändern
        </button>
        <button class="w-full px-4 py-3 text-left hover:bg-gray-50">
          Newsletter-Einwilligung
        </button>
        <button class="w-full px-4 py-3 text-left hover:bg-gray-50">
          Regionalpartner Name ändern
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

    <!-- Danger Zone -->
    <div class="bg-white rounded-lg shadow">
      <div class="p-4 border-b border-gray-200">
        <h2 class="font-semibold text-red-600">Gefahrenzone</h2>
      </div>
      <button class="w-full px-4 py-3 text-left text-red-600 hover:bg-red-50">
        Konto löschen
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
</script>

