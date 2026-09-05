<script setup lang="ts">
import { computed } from 'vue'
import { Moon, Sun } from 'lucide-vue-next'
import { useThemeStore } from '@/stores/theme'

const themeStore = useThemeStore()

const resolvedIcon = computed(() => (themeStore.resolvedTheme === 'dark' ? Moon : Sun))

function toggle() {
  themeStore.toggleTheme()
}
</script>

<template>
  <button
    type="button"
    :aria-label="`Switch theme, current: ${themeStore.currentTheme}`"
    :title="`Theme: ${themeStore.currentTheme}`"
    class="btn-icon relative overflow-hidden"
    @click="toggle"
  >
    <Transition name="theme-icon" mode="out-in">
      <component :is="resolvedIcon" :key="themeStore.resolvedTheme" :size="19" />
    </Transition>
  </button>
</template>

<style scoped>
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
