<script setup lang="ts">
import { onBeforeUnmount, watch } from 'vue'
import { X } from 'lucide-vue-next'

type ModalSize = 'sm' | 'md' | 'lg'

const props = withDefaults(
  defineProps<{
    modelValue: boolean
    title?: string
    size?: ModalSize
    closeOnBackdrop?: boolean
  }>(),
  { title: '', size: 'md', closeOnBackdrop: true }
)

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'close'): void
}>()

const sizeClasses: Record<ModalSize, string> = {
  sm: 'sm:max-w-sm',
  md: 'lg:max-w-lg',
  lg: 'xl:max-w-xl'
}

function close(): void {
  emit('update:modelValue', false)
  emit('close')
}

function handleBackdropClick(): void {
  if (props.closeOnBackdrop) close()
}

function onKeydown(event: KeyboardEvent): void {
  if (event.key === 'Escape') close()
}

watch(
  () => props.modelValue,
  (open) => {
    if (open) window.addEventListener('keydown', onKeydown)
    else window.removeEventListener('keydown', onKeydown)
  },
  { immediate: true }
)

onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown))
</script>

<template>
  <Teleport to="body">
    <div v-if="modelValue" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="fixed inset-0 animate-fade-in bg-black/40" @click="handleBackdropClick"></div>
      <div class="relative flex min-h-full items-center justify-center p-4">
        <div
          class="relative mx-4 my-8 flex max-h-[85vh] w-full flex-col rounded-xl bg-white shadow-popover"
          :class="sizeClasses[size]"
        >
          <header class="flex shrink-0 items-center justify-between border-b border-border-gray px-6 py-4">
            <h3 class="text-lg font-semibold text-ink">{{ title }}</h3>
            <button type="button" class="btn-icon" aria-label="Close" @click="close">
              <X :size="20" />
            </button>
          </header>
          <div class="overflow-y-auto px-6 py-5">
            <slot />
          </div>
          <footer v-if="$slots.footer" class="shrink-0 border-t border-border-gray px-6 py-4">
            <slot name="footer" />
          </footer>
        </div>
      </div>
    </div>
  </Teleport>
</template>