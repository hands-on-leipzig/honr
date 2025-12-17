<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Zurück zur Benutzeransicht</h1>

    <div class="bg-white rounded-lg shadow p-4">
      <p class="text-gray-600 mb-4">
        Zurück zur normalen Benutzeroberfläche wechseln.
      </p>
      <button
        @click="goToUserView"
        class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors font-medium"
      >
        Zur Benutzeransicht wechseln
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'

const router = useRouter()

const goToUserView = () => {
  router.push('/me')
}
</script>


