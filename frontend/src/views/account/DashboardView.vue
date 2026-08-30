<script setup lang="ts">
import { RouterLink, useRouter } from 'vue-router'
import { ChevronRight, Clock, Heart, Package, PackageCheck, ShoppingBag } from 'lucide-vue-next'
import { CURRENT_USER, ORDERS } from '@/data/mock'
import { useCartStore } from '@/stores/cart'
import { useWishlistStore } from '@/stores/wishlist'
import EmptyState from '@/components/EmptyState.vue'
import ProductCard from '@/components/ProductCard.vue'
import StatusTag from '@/components/StatusTag.vue'
import { formatDate, formatPrice } from '@/utils/format'
import { computed } from 'vue'
import type { Component } from 'vue'
import type { Order, Product } from '@/types'

interface AddToCartPayload {
  variantId?: string
}

interface SummaryCard {
  label: string
  value: number
  icon: Component
  tint: string
  to: { name: string }
}

const router = useRouter()
const cartStore = useCartStore()
const wishlist = useWishlistStore()

const firstName = CURRENT_USER.name.split(' ')[0]

const initials = CURRENT_USER.name
  .split(' ')
  .map((word) => word[0])
  .join('')

const pendingCount = computed(
  () => ORDERS.filter((o) => ['Pending', 'Confirmed', 'Processing'].includes(o.status)).length
)

const completedCount = computed(() => ORDERS.filter((o) => o.status === 'Delivered').length)

const recentOrders = computed(() => ORDERS.slice(0, 4))

const cards = computed<SummaryCard[]>(() => [
  {
    label: 'Total Orders',
    value: ORDERS.length,
    icon: ShoppingBag,
    tint: 'bg-primary/10 text-primary',
    to: { name: 'account-orders' }
  },
  {
    label: 'Pending Orders',
    value: pendingCount.value,
    icon: Clock,
    tint: 'bg-accent/10 text-accent',
    to: { name: 'account-orders' }
  },
  {
    label: 'Completed Orders',
    value: completedCount.value,
    icon: PackageCheck,
    tint: 'bg-emerald-100 text-emerald-600',
    to: { name: 'account-orders' }
  },
  {
    label: 'Wishlist Items',
    value: wishlist.count,
    icon: Heart,
    tint: 'bg-red-100 text-red-500',
    to: { name: 'account-wishlist' }
  }
])

const wishlistProducts = computed(() => wishlist.products)

function itemsSummary(order: Order): string {
  const first = order.items[0]
  if (order.items.length === 1) return first.title
  return `${first.title} +${order.items.length - 1} more`
}

function addFromWishlist(product: Product, payload: AddToCartPayload) {
  cartStore.addItem({ product, variantId: payload.variantId, quantity: 1 })
  wishlist.remove(product.id)
}
</script>

<template>
  <div class="space-y-8">
    <div class="flex items-center justify-between rounded-xl bg-gradient-to-r from-primary to-primary-dark p-6 text-white">
      <div>
        <h2 class="text-xl font-bold sm:text-2xl">Welcome back, {{ firstName }}</h2>
        <p class="mt-1 text-sm text-blue-100">Here's what's happening with your orders today.</p>
      </div>
      <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white/20 font-bold">
        {{ initials }}
      </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <div v-for="card in cards" :key="card.label" class="card p-5">
        <div :class="['flex h-10 w-10 items-center justify-center rounded-full', card.tint]">
          <component :is="card.icon" class="h-5 w-5" />
        </div>
        <p class="mt-3 text-sm text-gray-500">{{ card.label }}</p>
        <p class="mt-1 text-3xl font-extrabold text-ink">{{ card.value }}</p>
        <RouterLink
          :to="card.to"
          class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline"
        >
          View all
          <ChevronRight class="h-3.5 w-3.5" />
        </RouterLink>
      </div>
    </div>

    <section class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-ink">Recent Orders</h3>
        <RouterLink :to="{ name: 'account-orders' }" class="text-sm font-medium text-primary hover:underline">
          View all
        </RouterLink>
      </div>

      <div v-if="recentOrders.length" class="card overflow-x-auto">
        <table class="w-full min-w-[640px] text-sm">
          <thead>
            <tr class="border-b border-border-gray text-left text-xs uppercase tracking-wide text-gray-500">
              <th class="px-4 py-3 font-semibold">Order</th>
              <th class="px-4 py-3 font-semibold">Date</th>
              <th class="px-4 py-3 font-semibold">Items</th>
              <th class="px-4 py-3 font-semibold">Total</th>
              <th class="px-4 py-3 font-semibold">Status</th>
              <th class="px-4 py-3 font-semibold"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border-gray">
            <tr v-for="o in recentOrders" :key="o.id">
              <td class="px-4 py-3">
                <RouterLink
                  :to="`/order/tracking/${o.id}`"
                  class="font-semibold text-primary hover:underline"
                >
                  {{ o.number }}
                </RouterLink>
              </td>
              <td class="px-4 py-3 text-gray-600">{{ formatDate(o.placedAt) }}</td>
              <td class="px-4 py-3 text-gray-600">{{ itemsSummary(o) }}</td>
              <td class="px-4 py-3 font-medium text-ink">{{ formatPrice(o.total) }}</td>
              <td class="px-4 py-3">
                <StatusTag :status="o.status" />
              </td>
              <td class="px-4 py-3 text-right">
                <RouterLink
                  :to="`/order/tracking/${o.id}`"
                  class="inline-flex items-center rounded-lg p-1 text-primary hover:bg-primary/10"
                  aria-label="View order"
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
        title="No orders yet"
        description="Your recent orders will appear here."
        cta-label="Start shopping"
        @cta="router.push('/shop')"
      >
        <template #icon>
          <Package class="h-10 w-10 text-gray-300" />
        </template>
      </EmptyState>
    </section>

    <section class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-ink">Your Wishlist</h3>
        <RouterLink :to="{ name: 'account-wishlist' }" class="text-sm font-medium text-primary hover:underline">
          View all
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
        title="Your wishlist is empty"
        description="Save the items you love and find them here."
        cta-label="Explore products"
        @cta="router.push('/shop')"
      >
        <template #icon>
          <Heart class="h-10 w-10 text-gray-300" />
        </template>
      </EmptyState>
    </section>
  </div>
</template>