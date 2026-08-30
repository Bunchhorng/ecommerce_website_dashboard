<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    count?: number
    columns?: number
  }>(),
  { count: 8, columns: 4 }
)

const gridClass = computed(() => {
  if (props.columns <= 2) return 'grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3'
  if (props.columns === 3) return 'grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3'
  return 'grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4'
})
</script>

<template>
  <div class="grid gap-5" :class="gridClass">
    <div v-for="i in count" :key="i" class="animate-pulse">
      <div class="aspect-square rounded-xl bg-gray-200"></div>
      <div class="mt-3 h-3 w-20 rounded bg-gray-200"></div>
      <div class="mt-2 h-3 w-3/4 rounded bg-gray-200"></div>
      <div class="mt-2 h-3 w-24 rounded bg-gray-200"></div>
      <div class="mt-3 h-4 w-24 rounded bg-gray-200"></div>
    </div>
  </div>
</template>