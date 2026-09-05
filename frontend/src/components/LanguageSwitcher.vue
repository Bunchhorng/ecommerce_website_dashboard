<script setup lang="ts">
import { ref } from 'vue'
import { Check, ChevronDown } from 'lucide-vue-next'
import { useLocaleStore } from '@/stores/locale'

const localeStore = useLocaleStore()

const open = ref(false)
const dropdownRef = ref<HTMLElement | null>(null)

const options: Array<{ value: 'km' | 'en'; label: string; flag: string }> = [
  { value: 'km', label: 'ខ្មែរ', flag: '🇰🇭' },
  { value: 'en', label: 'English', flag: '🇬🇧' }
]

const current = () => options.find((o) => o.value === localeStore.currentLocale) ?? options[0]

function select(value: 'km' | 'en') {
  localeStore.setLocale(value)
  open.value = false
}

function toggle() {
  open.value = !open.value
}

function onClickOutside(event: MouseEvent) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    open.value = false
  }
}

function onDocClick(event: MouseEvent) {
  onClickOutside(event)
}

function attach() {
  document.addEventListener('click', onDocClick)
}

function detach() {
  document.removeEventListener('click', onDocClick)
}
</script>

<template>
  <div ref="dropdownRef" class="relative">
    <button
      type="button"
      class="flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-600 transition-colors hover:bg-gray-100 hover:text-ink dark:text-muted dark:hover:bg-surface-hover dark:hover:text-ink"
      aria-haspopup="listbox"
      :aria-expanded="open"
      @click="toggle"
      @mouseenter="attach"
      @mouseleave="detach"
    >
      <span class="text-sm leading-none">{{ current().flag }}</span>
      <span class="leading-tight">{{ current().label }}</span>
      <ChevronDown class="h-3.5 w-3.5" />
    </button>

    <Transition name="dropdown">
      <div
        v-if="open"
        class="card absolute right-0 top-11 z-50 w-44 overflow-hidden py-1.5 shadow-popover dark:border-border-gray dark:bg-surface"
      >
        <p class="px-4 pb-1.5 pt-1 text-[11px] font-semibold uppercase tracking-wider text-gray-400">
          Language
        </p>
        <button
          v-for="opt in options"
          :key="opt.value"
          type="button"
          class="flex w-full items-center gap-3 px-4 py-2 text-sm transition-colors"
          :class="localeStore.currentLocale === opt.value ? 'font-semibold text-primary' : 'text-gray-600 hover:bg-gray-100 hover:text-ink dark:text-muted dark:hover:bg-surface-hover dark:hover:text-ink'"
          @click="select(opt.value)"
        >
          <span class="text-base leading-none">{{ opt.flag }}</span>
          <span class="flex-1 text-left leading-tight">{{ opt.label }}</span>
          <Check v-if="localeStore.currentLocale === opt.value" class="h-4 w-4" />
        </button>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>
