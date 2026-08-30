<script setup lang="ts">
import { Star } from 'lucide-vue-next'

withDefaults(
  defineProps<{
    value: number
    size?: number | 'sm' | 'md'
    max?: number
    showValue?: boolean
  }>(),
  { size: 16, max: 5, showValue: false }
)

function resolveSize(size: number | 'sm' | 'md'): number {
  if (size === 'sm') return 12
  if (size === 'md') return 16
  return size
}
</script>

<template>
  <span class="inline-flex items-center" :class="showValue ? 'gap-1' : 'gap-0.5'">
    <Star
      v-for="i in max"
      :key="i"
      :size="resolveSize(size)"
      :stroke-width="1.5"
      class="shrink-0"
      :class="i <= Math.min(Math.round(value), max) ? 'fill-current text-accent' : 'text-gray-300'"
    />
    <span v-if="showValue" class="ml-1 text-xs font-medium text-gray-600">{{ value.toFixed(1) }}/{{ max }}</span>
  </span>
</template>