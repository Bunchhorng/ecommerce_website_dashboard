<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { Star } from 'lucide-vue-next'
import StarRating from '@/components/StarRating.vue'
import StatusTag from '@/components/StatusTag.vue'
import { REVIEWS, getProductById } from '@/data/mock'
import { formatDate } from '@/utils/format'
import { computed, ref } from 'vue'
import type { Product, Review } from '@/types'

interface ReviewItem {
  review: Review
  product: Product | undefined
  image: string
  slug: string
  title: string
}

const myReviews = ref<Review[]>([...REVIEWS].slice(0, 6))

const items = computed<ReviewItem[]>(() =>
  myReviews.value.map((review) => {
    const product = getProductById(review.productId)
    return {
      review,
      product,
      image: product?.images[0]?.url ?? '',
      slug: product?.slug ?? '',
      title: product?.title ?? ''
    }
  })
)
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-ink">My Reviews</h1>
      <p class="mt-1 text-sm text-gray-500">Reviews appear once approved by moderation.</p>
    </div>

    <div class="card divide-y divide-border-gray">
      <div v-for="item in items" :key="item.review.id" class="flex flex-col gap-4 p-4 sm:flex-row sm:items-start">
        <img
          v-if="item.image"
          :src="item.image"
          :alt="item.title"
          class="h-20 w-20 shrink-0 rounded-lg object-cover"
        />
        <div
          v-else
          class="flex h-20 w-20 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-primary to-accent"
        >
          <Star class="h-6 w-6 text-white" />
        </div>

        <div class="min-w-0 flex-1">
          <div class="flex flex-wrap items-center gap-2">
            <RouterLink
              v-if="item.slug"
              :to="`/product/${item.slug}`"
              class="font-semibold text-primary hover:underline"
            >
              {{ item.title }}
            </RouterLink>
            <StatusTag :status="item.review.status" />
          </div>
          <div class="mt-1 flex items-center gap-2">
            <StarRating :value="item.review.rating" />
            <span class="text-xs text-gray-400">{{ formatDate(item.review.date) }}</span>
          </div>
          <p class="mt-2 text-sm font-semibold text-ink">{{ item.review.title }}</p>
          <p class="mt-1 text-sm text-gray-600">{{ item.review.body }}</p>
        </div>
      </div>
    </div>
  </div>
</template>