<script setup lang="ts">
import ThemeToggle from '@/components/ThemeToggle.vue'
import LanguageSwitcher from '@/components/LanguageSwitcher.vue'
</script>

<template>
  <div class="flex min-h-screen flex-col bg-canvas">
    <header class="flex items-center justify-between border-b border-border-gray bg-white px-4 py-3 sm:px-6 dark:bg-gray-900">
      <RouterLink to="/" class="text-xl font-extrabold tracking-tight text-primary">
        E<span class="text-ink dark:text-gray-100">-KHMER</span>
      </RouterLink>
      <div class="flex items-center gap-2">
        <LanguageSwitcher />
        <ThemeToggle />
      </div>
    </header>
    <main class="flex flex-1 items-center justify-center px-4 py-10">
      <router-view />
    </main>
    <footer class="border-t border-border-gray px-4 py-4 text-center text-xs text-gray-400">
      {{ $t('footer.copyright') }}
    </footer>
  </div>
</template>
