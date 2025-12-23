<template>
  <button
    @click="$emit('click')"
    :class="sizeClass"
    class="relative flex items-center justify-center cursor-pointer hover:opacity-80 hover:scale-105 transition-all duration-200"
  >
    <!-- Icon with level border -->
    <div v-if="logoPath" :class="[borderClass, borderWidthClass, borderStyleClass]" class="w-full h-full rounded-full overflow-hidden flex items-center justify-center bg-white">
      <img
        :src="logoUrl"
        :alt="roleName"
        :class="paddingClass"
        class="w-full h-full object-contain"
        @error="handleImageError"
      />
    </div>
    <!-- Chip fallback with border -->
    <div v-else :class="[borderClass, borderWidthClass, borderStyleClass]" class="w-full h-full rounded-full flex items-center justify-center bg-white">
      <span :class="textClass" class="px-2 py-1 text-gray-700 font-medium text-center truncate">
        {{ roleName }}
      </span>
    </div>
    <!-- Engagement count badge in bottom right corner - positioned outside to be in front -->
    <div
      v-if="engagementCount !== undefined && engagementCount !== null"
      :class="[smallCircleSize, borderClass, borderWidthClass, borderStyleClass]"
      class="absolute rounded-full bg-white flex items-center justify-center z-20"
      :style="smallCirclePosition"
    >
      <span :class="[smallCircleTextSize, borderTextColorClass]" class="font-bold leading-none">
        {{ engagementCount }}
      </span>
    </div>
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { getBadgeBorderClass } from '@/constants/uiColors'

interface Props {
  logoPath: string | null
  level: number
  roleName: string
  size?: 'md' | 'lg'
  engagementCount?: number | null
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md'
})

const logoUrl = computed(() => {
  if (!props.logoPath) return ''
  const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8001/api'
  const backendUrl = apiUrl.replace('/api', '')
  return `${backendUrl}/storage/${props.logoPath}`
})

const borderClass = computed(() => {
  return getBadgeBorderClass(props.level)
})

const sizeClass = computed(() => {
  return props.size === 'lg' ? 'w-24 h-24' : 'w-12 h-12'
})

const borderWidthClass = computed(() => {
  return props.size === 'lg' ? 'border-[4px]' : 'border-[2px]'
})

const borderStyleClass = computed(() => {
  // Level 1 (default) gets dashed border, others get solid
  return props.level === 1 ? 'border-dashed' : 'border-solid'
})

const paddingClass = computed(() => {
  return props.size === 'lg' ? 'p-2' : 'p-1'
})

const textClass = computed(() => {
  return props.size === 'lg' ? 'text-sm' : 'text-xs'
})

// Small circle is 1/3 of badge diameter
const smallCircleSize = computed(() => {
  return props.size === 'lg' ? 'w-8 h-8' : 'w-4 h-4'
})

const smallCircleTextSize = computed(() => {
  return props.size === 'lg' ? 'text-xs' : 'text-[8px]'
})

// Text color matches border color
const borderTextColorClass = computed(() => {
  switch (props.level) {
    case 1:
      return 'text-gray-400'
    case 2:
      return 'text-[#CD7F32]' // Bronze
    case 3:
      return 'text-[#C0C0C0]' // Silver
    case 4:
      return 'text-[#FFD700]' // Gold
    default:
      return 'text-gray-400'
  }
})

// Position small circle in bottom right corner, overlapping the badge
// Small circle diameter is 1/3 of badge, so offset by 1/6 to center it on the edge
const smallCirclePosition = computed(() => {
  const offset = props.size === 'lg' ? '-8px' : '-4px' // Negative offset to overlap
  return {
    bottom: offset,
    right: offset,
  }
})

function handleImageError(event: Event) {
  const img = event.target as HTMLImageElement
  img.style.display = 'none'
}
</script>

