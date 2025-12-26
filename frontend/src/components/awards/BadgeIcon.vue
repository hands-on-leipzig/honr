<template>
  <button
    @click="$emit('click')"
    class="flex flex-col items-center justify-center cursor-pointer hover:opacity-80 hover:scale-105 transition-all duration-200"
  >
    <!-- Badge container -->
    <div :class="sizeClass" class="relative">
      <!-- Icon with level border -->
      <div 
        v-if="logoPath" 
        :class="[borderClass, borderWidthClass, borderStyleClass]" 
        class="w-full h-full rounded-full overflow-hidden flex items-center justify-center"
        :style="{ ...badgeBackgroundStyle, ...badgeGlowStyle }"
      >
        <img
          :src="logoUrl"
          :alt="roleName"
          :class="paddingClass"
          class="w-full h-full object-contain"
          @error="handleImageError"
        />
      </div>
      <!-- Chip fallback with border -->
      <div 
        v-else 
        :class="[borderClass, borderWidthClass, borderStyleClass]" 
        class="w-full h-full rounded-full flex items-center justify-center"
        :style="{ ...badgeBackgroundStyle, ...badgeGlowStyle }"
      >
        <span :class="textClass" class="px-2 py-1 text-gray-700 font-medium text-center truncate">
          {{ roleName }}
        </span>
      </div>
      <!-- Engagement count badge in bottom right corner - positioned outside to be in front -->
      <div
        v-if="engagementCount !== undefined && engagementCount !== null"
        :class="[smallCircleSize, smallCircleBorderClass, borderWidthClass, smallCircleBorderStyleClass, smallCircleFillClass]"
        class="absolute rounded-full flex items-center justify-center z-20"
        :style="smallCirclePosition"
      >
        <span :class="[smallCircleTextSize, smallCircleTextColorClass]" class="font-bold leading-none">
          {{ engagementCount }}
        </span>
      </div>
    </div>
    <!-- Short name in tiny font below badge -->
    <span v-if="roleShortName" :class="shortNameTextClass" class="mt-1 text-gray-600 text-center truncate" :style="shortNameWidthStyle">
      {{ roleShortName }}
    </span>
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { getBadgeBorderClass, getBadgeBackgroundTint, getBadgeGlow } from '@/constants/uiColors'

interface Props {
  logoPath: string | null
  level: number
  roleName: string
  roleShortName?: string | null
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
  // Progressive border width: Level 1-2: 2px, Level 3: 3px, Level 4: 4px (md)
  // Progressive border width: Level 1-2: 4px, Level 3: 5px, Level 4: 6px (lg)
  if (props.size === 'lg') {
    if (props.level === 1 || props.level === 2) return 'border-[4px]'
    if (props.level === 3) return 'border-[5px]'
    return 'border-[6px]' // Level 4
  } else {
    if (props.level === 1 || props.level === 2) return 'border-[2px]'
    if (props.level === 3) return 'border-[3px]'
    return 'border-[4px]' // Level 4
  }
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

const shortNameTextClass = computed(() => {
  return props.size === 'lg' ? 'text-[10px]' : 'text-[8px]'
})

const shortNameWidthStyle = computed(() => {
  return {
    width: props.size === 'lg' ? '96px' : '48px' // Match badge width
  }
})

// Background tint for badge
const badgeBackgroundStyle = computed(() => {
  return {
    backgroundColor: getBadgeBackgroundTint(props.level)
  }
})

// Box-shadow glow for badge
const badgeGlowStyle = computed(() => {
  return {
    boxShadow: getBadgeGlow(props.level, props.size)
  }
})

// Small circle is 1/2 of badge diameter
const smallCircleSize = computed(() => {
  return props.size === 'lg' ? 'w-12 h-12' : 'w-6 h-6'
})

const smallCircleTextSize = computed(() => {
  return props.size === 'lg' ? 'text-sm' : 'text-[10px]'
})

// Small circle border: white for levels 2-4, gray for level 1
const smallCircleBorderClass = computed(() => {
  if (props.level === 1) {
    return 'border-gray-300'
  }
  return 'border-white'
})

// Small circle border style: dashed for level 1, solid for 2-4
const smallCircleBorderStyleClass = computed(() => {
  return props.level === 1 ? 'border-dashed' : 'border-solid'
})

// Small circle fill: badge level color for 2-4, white for level 1
const smallCircleFillClass = computed(() => {
  switch (props.level) {
    case 1:
      return 'bg-white'
    case 2:
      return 'bg-[#CD7F32]' // Bronze
    case 3:
      return 'bg-[#C0C0C0]' // Silver
    case 4:
      return 'bg-[#FFD700]' // Gold
    default:
      return 'bg-white'
  }
})

// Small circle text color: white for levels 2-4, gray for level 1
const smallCircleTextColorClass = computed(() => {
  if (props.level === 1) {
    return 'text-gray-400'
  }
  return 'text-white'
})

// Position small circle in bottom right corner, overlapping the badge
// Small circle diameter is 1/2 of badge, so offset by 1/4 to center it on the edge
const smallCirclePosition = computed(() => {
  const offset = props.size === 'lg' ? '-12px' : '-6px' // Negative offset to overlap
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

