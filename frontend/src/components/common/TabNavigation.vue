<template>
  <div class="flex border-b border-gray-200 mb-4">
    <button
      v-for="tab in tabs"
      :key="tab.id"
      @click="selectTab(tab.id)"
      :class="[
        'flex-1 py-3 text-center font-medium border-b-2 transition-colors',
        modelValue === tab.id
          ? 'border-blue-600 text-blue-600'
          : 'border-transparent text-gray-500 hover:text-gray-700'
      ]"
      type="button"
    >
      {{ tab.label }}
    </button>
  </div>
</template>

<script setup lang="ts">
interface Tab {
  id: string
  label: string
}

interface Props {
  tabs: Tab[]
  modelValue: string
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

function selectTab(tabId: string) {
  emit('update:modelValue', tabId)
}
</script>

