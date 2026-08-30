<script setup lang="ts">
import { useRoute } from 'vue-router'
import { NAV_CATEGORIES } from '@/data/mock'

const route = useRoute()

function isActive(slug: string): boolean {
  return route.query.category === slug
}
</script>

<template>
  <nav class="border-b border-border-gray bg-white">
    <div class="container-app">
      <ul
        class="flex items-center gap-1 overflow-x-auto whitespace-nowrap py-2.5 text-sm font-medium [scrollbar-width:none]"
      >
        <li>
          <RouterLink to="/shop" class="nav-link" exact-active-class="!bg-primary/10 !text-primary">
            All Products
          </RouterLink>
        </li>
        <li v-for="category in NAV_CATEGORIES" :key="category.id">
          <RouterLink
            :to="{ name: 'shop', query: { category: category.slug } }"
            class="nav-link"
            :class="isActive(category.slug) ? '!bg-primary/10 !text-primary' : ''"
          >
            {{ category.name }}
          </RouterLink>
        </li>
      </ul>
    </div>
  </nav>
</template>