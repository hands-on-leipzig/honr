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
import { getBadgeBorderClass } from '@/constants/badgeColors'

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
  return getBadgeBorderClass(props.level)
})

function handleImageError(event: Event) {
  const img = event.target as HTMLImageElement
  img.style.display = 'none'
}
</script>

