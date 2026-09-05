<script setup lang="ts">
import { computed, ref } from 'vue'
import { Check, Moon, Monitor, Sun } from 'lucide-vue-next'
import { useThemeStore, type ThemeMode } from '@/stores/theme'

const themeStore = useThemeStore()

const open = ref(false)

const resolvedIcon = computed(() => (themeStore.resolvedTheme === 'dark' ? Moon : Sun))

const options: Array<{ value: ThemeMode; label: string; icon: typeof Sun }> = [
  { value: 'light', label: 'Light', icon: Sun },
  { value: 'dark', label: 'Dark', icon: Moon },
  { value: 'system', label: 'System', icon: Monitor }
]

function select(value: ThemeMode) {
  themeStore.setTheme(value)
  open.value = false
}

function toggle() {
  themeStore.toggleTheme()
}
</script>

<template>
  <div class="relative">
    <button
      type="button"
      :aria-label="`Switch theme, current: ${themeStore.currentTheme}`"
      :title="`Theme: ${themeStore.currentTheme}`"
      class="btn-icon relative overflow-hidden"
      @click="toggle"
      @mouseenter="open = true"
    >
      <Transition name="theme-icon" mode="out-in">
        <component :is="resolvedIcon" :key="themeStore.resolvedTheme" :size="19" />
      </Transition>
    </button>

    <Transition name="dropdown">
      <div
        v-if="open"
        class="card absolute right-0 top-12 z-50 w-44 overflow-hidden py-1.5 shadow-popover dark:border-border-gray dark:bg-surface"
        @mouseleave="open = false"
      >
        <p class="px-4 pb-1.5 pt-1 text-[11px] font-semibold uppercase tracking-wider text-gray-400">
          Theme
        </p>
        <button
          v-for="opt in options"
          :key="opt.value"
          type="button"
          class="flex w-full items-center gap-3 px-4 py-2 text-sm transition-colors"
          :class="themeStore.currentTheme === opt.value ? 'font-semibold text-primary' : 'text-gray-600 hover:bg-gray-100 hover:text-ink dark:text-muted dark:hover:bg-surface-hover dark:hover:text-ink'"
          @click="select(opt.value)"
        >
          <component :is="opt.icon" :size="16" />
          <span class="flex-1 text-left">{{ opt.label }}</span>
          <Check v-if="themeStore.currentTheme === opt.value" :size="15" />
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

.theme-icon-enter-active,
.theme-icon-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.theme-icon-enter-from {
  opacity: 0;
  transform: rotate(-90deg) scale(0.6);
}
.theme-icon-leave-to {
  opacity: 0;
  transform: rotate(90deg) scale(0.6);
}
</style>
