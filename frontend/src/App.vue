<script setup lang="ts">
import { onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'

const authStore = useAuthStore()
const cartStore = useCartStore()

onMounted(async () => {
  await cartStore.initialize()
})

watch(
  () => authStore.isAuthenticated,
  async () => {
    await cartStore.fetch()
  }
)
</script>

<template>
  <router-view />
</template>