<script setup lang="ts">
import { RouterLink, useRouter } from 'vue-router'
import { ShoppingBag } from 'lucide-vue-next'
import { ORDERS } from '@/data/mock'
import EmptyState from '@/components/EmptyState.vue'
import StatusTag from '@/components/StatusTag.vue'
import { formatDate, formatPrice } from '@/utils/format'
import type { Order } from '@/types'

const router = useRouter()

function itemsSummary(order: Order): string {
  const first = order.items[0]
  if (order.items.length === 1) return first.title
  return `${first.title} +${order.items.length - 1} more`
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <h1 class="text-2xl font-bold text-ink">My Orders</h1>
      <span class="chip w-fit">{{ ORDERS.length }} orders</span>
    </div>

    <div v-if="ORDERS.length" class="card overflow-x-auto p-0">
      <table class="w-full min-w-[720px] text-sm">
        <thead>
          <tr class="border-b border-border-gray text-left text-xs uppercase tracking-wide text-gray-500">
            <th class="px-4 py-3 font-semibold">Order</th>
            <th class="px-4 py-3 font-semibold">Placed</th>
            <th class="px-4 py-3 font-semibold">Items</th>
            <th class="px-4 py-3 font-semibold">Total</th>
            <th class="px-4 py-3 font-semibold">Status</th>
            <th class="px-4 py-3 font-semibold">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-border-gray">
          <tr v-for="o in ORDERS" :key="o.id">
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
            <td class="px-4 py-3">
              <RouterLink
                :to="`/order/tracking/${o.id}`"
                class="btn-secondary btn-sm"
              >
                Track
              </RouterLink>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <EmptyState
      v-else
      title="No orders yet"
      description="Your placed orders will be listed here."
      cta-label="Start shopping"
      @cta="router.push('/shop')"
    >
      <template #icon>
        <ShoppingBag class="h-10 w-10 text-gray-300" />
      </template>
    </EmptyState>
  </div>
</template>