<script setup lang="ts">
import { RouterLink, useRouter } from 'vue-router'
import { ChevronRight, Clock, Heart, Package, PackageCheck, ShoppingBag } from 'lucide-vue-next'
import { accountApi } from '@/api/account'
import { ordersApi } from '@/api/orders'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import { useWishlistStore } from '@/stores/wishlist'
import EmptyState from '@/components/EmptyState.vue'
import ProductCard from '@/components/ProductCard.vue'
import StatusTag from '@/components/StatusTag.vue'
import { formatDate, formatPrice } from '@/utils/format'
import { computed, onMounted, ref } from 'vue'
import type { Component } from 'vue'
import type { Product } from '@/types'

interface AddToCartPayload {
  variantId?: string
}

interface SummaryCard {
  labelKey: string
  value: number
  icon: Component
  tint: string
  to: { name: string }
}

interface DashboardOrder {
  id: string
  number: string
  placedAt: string
  total: number
  status: string
  itemsCount: number
}

const router = useRouter()
const cartStore = useCartStore()
const wishlist = useWishlistStore()

const loading = ref(true)
const dashboardOrders = ref<DashboardOrder[]>([])
const ordersCount = ref(0)
const reviewsCount = ref(0)
const wishlistCount = ref(0)

const userName = computed(() => useAuthStore().user?.name ?? 'User')
const firstName = computed(() => userName.value.split(' ')[0])
const initials = computed(() =>
  userName.value
    .split(' ')
    .map((word) => word[0])
    .join('')
)

const pendingCount = computed(
  () => dashboardOrders.value.filter((o) => ['pending', 'confirmed', 'processing'].includes(o.status.toLowerCase())).length
)

const completedCount = computed(() => dashboardOrders.value.filter((o) => o.status.toLowerCase() === 'delivered').length)

const recentOrders = computed(() => dashboardOrders.value.slice(0, 4))

const cards = computed<SummaryCard[]>(() => [
  {
    labelKey: 'account.total_orders',
    value: ordersCount.value,
    icon: ShoppingBag,
    tint: 'bg-primary/10 text-primary',
    to: { name: 'account-orders' }
  },
  {
    labelKey: 'account.pending_orders',
    value: pendingCount.value,
    icon: Clock,
    tint: 'bg-accent/10 text-accent',
    to: { name: 'account-orders' }
  },
  {
    labelKey: 'account.completed_orders',
    value: completedCount.value,
    icon: PackageCheck,
    tint: 'bg-emerald-100 text-emerald-600',
    to: { name: 'account-orders' }
  },
  {
    labelKey: 'account.wishlist_items',
    value: wishlistCount.value,
    icon: Heart,
    tint: 'bg-red-100 text-red-500',
    to: { name: 'account-wishlist' }
  }
])

const wishlistProducts = computed(() => wishlist.products)

function capitalizeStatus(status: string): string {
  return status.charAt(0).toUpperCase() + status.slice(1)
}

function addFromWishlist(product: Product, payload: AddToCartPayload) {
  if (!payload.variantId) return
  cartStore.addItem({ variantId: payload.variantId, quantity: 1 })
  wishlist.remove(product.id)
}

onMounted(async () => {
  loading.value = true
  try {
    const [dashRes, ordersRes] = await Promise.all([
      accountApi.getDashboard(),
      ordersApi.list()
    ])
    ordersCount.value = dashRes.data.orders_count
    reviewsCount.value = dashRes.data.reviews_count
    wishlistCount.value = dashRes.data.wishlist_count

    dashboardOrders.value = (ordersRes.data.data ?? []).map((o) => ({
      id: o.order_number,
      number: o.order_number,
      placedAt: o.placed_at,
      total: o.total,
      status: capitalizeStatus(o.status),
      itemsCount: o.items_count
    }))
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="space-y-8">
    <div class="flex items-center justify-between rounded-xl bg-gradient-to-r from-primary to-primary-dark p-6 text-white">
      <div>
        <h2 class="text-xl font-bold sm:text-2xl">{{ $t('account.welcome_back_name', { name: firstName }) }}</h2>
        <p class="mt-1 text-sm text-blue-100">{{ $t('account.today_summary') }}</p>
      </div>
      <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white/20 font-bold">
        {{ initials }}
      </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <div v-for="card in cards" :key="card.labelKey" class="card p-5">
        <div :class="['flex h-10 w-10 items-center justify-center rounded-full', card.tint]">
          <component :is="card.icon" class="h-5 w-5" />
        </div>
        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">{{ $t(card.labelKey) }}</p>
        <p class="mt-1 text-3xl font-extrabold text-ink dark:text-gray-100">{{ card.value }}</p>
        <RouterLink
          :to="card.to"
          class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline"
        >
          {{ $t('actions.view_all') }}
          <ChevronRight class="h-3.5 w-3.5" />
        </RouterLink>
      </div>
    </div>

    <section class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-ink dark:text-gray-100">{{ $t('account.recent_orders') }}</h3>
        <RouterLink :to="{ name: 'account-orders' }" class="text-sm font-medium text-primary hover:underline">
          {{ $t('actions.view_all') }}
        </RouterLink>
      </div>

      <div v-if="loading" class="card p-10 text-center">
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $t('common.loading') }}</p>
      </div>

      <div v-else-if="recentOrders.length" class="card overflow-x-auto">
        <table class="w-full min-w-[640px] text-sm">
          <thead>
            <tr class="border-b border-border-gray dark:border-gray-700 text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
              <th class="px-4 py-3 font-semibold">{{ $t('order.order') }}</th>
              <th class="px-4 py-3 font-semibold">{{ $t('order.date') }}</th>
              <th class="px-4 py-3 font-semibold">{{ $t('order.items') }}</th>
              <th class="px-4 py-3 font-semibold">{{ $t('order.total') }}</th>
              <th class="px-4 py-3 font-semibold">{{ $t('order.status') }}</th>
              <th class="px-4 py-3 font-semibold"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border-gray dark:divide-gray-700">
            <tr v-for="o in recentOrders" :key="o.id">
              <td class="px-4 py-3">
                <RouterLink
                  :to="`/order/tracking/${o.number}`"
                  class="font-semibold text-primary hover:underline"
                >
                  {{ o.number }}
                </RouterLink>
              </td>
              <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ formatDate(o.placedAt) }}</td>
              <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ o.itemsCount }} {{ $t('order.items') }}</td>
              <td class="px-4 py-3 font-medium text-ink dark:text-gray-100">{{ formatPrice(o.total) }}</td>
              <td class="px-4 py-3">
                <StatusTag :status="o.status" />
              </td>
              <td class="px-4 py-3 text-right">
                <RouterLink
                  :to="`/order/tracking/${o.number}`"
                  class="inline-flex items-center rounded-lg p-1 text-primary hover:bg-primary/10"
                  :aria-label="$t('order.track_my_order')"
                >
                  <ChevronRight class="h-4 w-4" />
                </RouterLink>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <EmptyState
        v-else
        :title="$t('account.no_orders_title')"
        :description="$t('account.no_orders_description')"
        :cta-label="$t('account.start_shopping')"
        @cta="router.push('/shop')"
      >
        <template #icon>
          <Package class="h-10 w-10 text-gray-300" />
        </template>
      </EmptyState>
    </section>

    <section class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-ink dark:text-gray-100">{{ $t('account.your_wishlist') }}</h3>
        <RouterLink :to="{ name: 'account-wishlist' }" class="text-sm font-medium text-primary hover:underline">
          {{ $t('actions.view_all') }}
        </RouterLink>
      </div>

      <div v-if="wishlistProducts.length" class="grid grid-cols-2 gap-5 sm:grid-cols-3 xl:grid-cols-4">
        <ProductCard
          v-for="p in wishlistProducts"
          :key="p.id"
          :product="p"
          @add-to-cart="(e) => addFromWishlist(p, e)"
          @wishlist-toggle="wishlist.toggle($event)"
        />
      </div>

      <EmptyState
        v-else
        :title="$t('account.wishlist_empty_title')"
        :description="$t('account.wishlist_empty_description')"
        :cta-label="$t('account.explore_products')"
        @cta="router.push('/shop')"
      >
        <template #icon>
          <Heart class="h-10 w-10 text-gray-300" />
        </template>
      </EmptyState>
    </section>
  </div>
</template>
