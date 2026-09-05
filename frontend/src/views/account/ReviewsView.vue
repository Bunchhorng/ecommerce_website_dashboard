<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Link2, Loader2, Star } from 'lucide-vue-next'
import EmptyState from '@/components/EmptyState.vue'
import StarRating from '@/components/StarRating.vue'
import BaseBadge from '@/components/BaseBadge.vue'
import { reviewsApi } from '@/api/reviews'
import type { ApiReview } from '@/api/reviews'
import { formatDate } from '@/utils/format'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const reviews = ref<ApiReview[]>([])
const loading = ref(true)
const error = ref('')

async function loadReviews() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await reviewsApi.listMine()
    reviews.value = data.data
  } catch {
    error.value = t('account.reviews_load_error')
  } finally {
    loading.value = false
  }
}

onMounted(loadReviews)
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-ink dark:text-ink">{{ $t('nav.reviews') }}</h1>
      <p class="mt-1 text-sm text-gray-500 dark:text-muted">{{ $t('account.reviews_approved_note') }}</p>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
      <Loader2 class="h-6 w-6 animate-spin text-primary" />
    </div>

    <p v-else-if="error" class="text-sm text-red-500">{{ error }}</p>

    <div v-else-if="reviews.length" class="space-y-4">
      <div v-for="r in reviews" :key="r.id" class="card p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <div class="flex items-center gap-2">
              <StarRating :value="r.rating" size="sm" />
              <BaseBadge :variant="r.status === 'approved' ? 'success' : r.status === 'rejected' ? 'danger' : 'neutral'">
                {{ $t(`account.review_status_${r.status}`) }}
              </BaseBadge>
            </div>
            <div v-if="r.title?.trim()" class="mt-2 font-semibold text-ink dark:text-ink">{{ r.title }}</div>
            <p v-if="r.body?.trim()" class="mt-1 text-sm text-gray-600 dark:text-muted">{{ r.body }}</p>
            <div class="mt-2 text-xs text-gray-400 dark:text-gray-500">{{ formatDate(r.created_at) }}</div>
          </div>
          <RouterLink
            v-if="r.product"
            :to="`/product/${r.product.slug}`"
            class="inline-flex items-center gap-1.5 rounded-lg border border-border-gray px-3 py-1.5 text-xs font-medium text-primary hover:bg-canvas dark:border-border-gray dark:hover:bg-canvas"
          >
            <Link2 class="h-3.5 w-3.5" />
            <span class="max-w-[160px] truncate">{{ r.product.name }}</span>
          </RouterLink>
        </div>
      </div>
    </div>

    <EmptyState
      v-else
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