<template>
  <button
    @click="$emit('click')"
    class="relative w-12 h-12 flex items-center justify-center cursor-pointer hover:opacity-80 transition-opacity"
  >
    <!-- Icon with border -->
    <div v-if="logoPath" :class="borderClass" class="w-full h-full rounded-full overflow-hidden border-[6px] flex items-center justify-center bg-white">
      <img
        :src="logoUrl"
        :alt="roleName"
        class="w-full h-full object-contain p-1"
        @error="handleImageError"
      />
    </div>
    <!-- Chip fallback with border -->
    <div v-else :class="borderClass" class="w-full h-full rounded-full border-[6px] flex items-center justify-center bg-white">
      <span class="px-2 py-1 text-gray-700 text-xs font-medium text-center truncate">
        {{ roleName }}
      </span>
    </div>
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  logoPath: string | null
  level: number
  roleName: string
}

const props = defineProps<Props>()

const logoUrl = computed(() => {
  if (!props.logoPath) return ''
  const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8001/api'
  const backendUrl = apiUrl.replace('/api', '')
  return `${backendUrl}/storage/${props.logoPath}`
})

const borderClass = computed(() => {
  switch (props.level) {
    case 1:
      return 'border-transparent' // No border for level 1
    case 2:
      return 'border-[#CD7F32]' // Bronze
    case 3:
      return 'border-[#C0C0C0]' // Silver
    case 4:
      return 'border-[#FFD700]' // Gold
    default:
      return 'border-transparent'
  }
})

function handleImageError(event: Event) {
  const img = event.target as HTMLImageElement
  img.style.display = 'none'
}
</script>

