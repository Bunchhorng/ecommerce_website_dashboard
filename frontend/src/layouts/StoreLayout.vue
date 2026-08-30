<script setup lang="ts">
import AppHeader from '@/components/AppHeader.vue'
import CategoryNavBar from '@/components/CategoryNavBar.vue'
import AppFooter from '@/components/AppFooter.vue'
import CartDrawer from '@/components/CartDrawer.vue'
</script>

<template>
  <div class="flex min-h-screen flex-col">
    <AppHeader />
    <CategoryNavBar />
    <main class="flex-1">
      <router-view />
    </main>
    <AppFooter />
    <CartDrawer />
  </div>
</template>