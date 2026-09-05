<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { Star } from 'lucide-vue-next'
import EmptyState from '@/components/EmptyState.vue'
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-ink dark:text-ink">{{ $t('nav.reviews') }}</h1>
      <p class="mt-1 text-sm text-gray-500 dark:text-muted">{{ $t('account.reviews_approved_note') }}</p>
    </div>

    <EmptyState
      :title="$t('account.no_reviews_title')"
      :description="$t('account.reviews_empty_description')"
      :cta-label="$t('account.browse_products')"
      @cta="$router.push('/shop')"
    >
      <template #icon>
        <Star class="h-10 w-10 text-gray-300" />
      </template>
    </EmptyState>
  </div>
</template>
