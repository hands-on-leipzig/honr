<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        @click.self="handleClose"
      >
        <div
          :class="[
            'bg-white rounded-lg w-full',
            maxWidthClass
          ]"
          :style="maxHeightStyle"
        >
          <!-- Header -->
          <div v-if="title || showClose" class="p-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white rounded-t-lg">
            <h3 v-if="title" class="text-lg font-semibold">{{ title }}</h3>
            <div v-else></div>
            <button
              v-if="showClose"
              @click="handleClose"
              class="text-gray-500 hover:text-gray-700"
              type="button"
            >
              ✕
            </button>
          </div>
          
          <!-- Content -->
          <div :class="contentClass">
            <slot />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  show: boolean
  title?: string
  showClose?: boolean
  maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | 'full'
  maxHeight?: string
  contentPadding?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  showClose: true,
  maxWidth: 'md',
  contentPadding: true
})

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'update:show', value: boolean): void
}>()

const maxWidthClass = computed(() => {
  const classes = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
    full: 'max-w-full'
  }
  return classes[props.maxWidth]
})

const maxHeightStyle = computed(() => {
  if (props.maxHeight) {
    return { maxHeight: props.maxHeight }
  }
  return {}
})

const contentClass = computed(() => {
  return props.contentPadding ? 'p-4' : ''
})

function handleClose() {
  emit('close')
  emit('update:show', false)
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>


