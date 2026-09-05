<script setup lang="ts">
import { PackageOpen } from 'lucide-vue-next'

withDefaults(
  defineProps<{
    title: string
    description?: string
    ctaLabel?: string
  }>(),
  { description: '', ctaLabel: '' }
)

const emit = defineEmits<{ (e: 'cta'): void }>()
</script>

<template>
  <div class="rounded-2xl border-2 border-dashed border-border-gray dark:border-border-gray bg-white dark:bg-canvas px-6 py-14 text-center">
    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-canvas dark:bg-surface-hover text-primary">
      <slot name="icon">
        <PackageOpen :size="28" />
      </slot>
    </div>
    <h3 class="mt-4 text-lg font-semibold text-ink">{{ title }}</h3>
    <p v-if="description" class="mt-1 text-sm text-gray-500 dark:text-muted">{{ description }}</p>
    <button
      v-if="ctaLabel"
      type="button"
      class="btn-primary btn-sm mt-5"
      @click="emit('cta')"
    >
      {{ ctaLabel }}
    </button>
    <div v-if="$slots.actions" class="mt-5">
      <slot name="actions" />
    </div>
  </div>
</template>