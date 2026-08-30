<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { CheckCircle2 } from 'lucide-vue-next'
import { getOrderById, CURRENT_USER } from '@/data/mock'
import StatusTag from '@/components/StatusTag.vue'
import EmptyState from '@/components/EmptyState.vue'
import { formatPrice, formatDate } from '@/utils/format'

const route = useRoute()
const router = useRouter()

const orderId = route.params.orderId as string
const order = computed(() => getOrderById(orderId))

const paymentLabels: Record<string, string> = {
  cod: 'Cash on Delivery',
  card: 'Credit / Debit Card',
  bank: 'Bank Transfer',
  gateway: 'Online Gateway'
}

const lineRows = computed(() => {
  if (!order.value) return []
  return [
    { label: 'Subtotal', value: order.value.subtotal },
    { label: 'Discount', value: -order.value.discount },
    { label: 'Tax', value: order.value.tax },
    { label: 'Shipping', value: order.value.shipping },
    { label: 'Total', value: order.value.total }
  ]
})

function displayPrice(value: number): string {
  return value < 0 ? `−${formatPrice(Math.abs(value))}` : formatPrice(value)
}
</script>

<template>
  <div v-if="order" class="container-app mx-auto max-w-2xl py-16 text-center">
    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
      <CheckCircle2 :size="40" />
    </div>

    <h1 class="mt-6 text-3xl font-extrabold text-ink">Order Confirmed!</h1>

    <p class="mt-3 text-gray-600">
      Thank you {{ CURRENT_USER.name }}! Your order has been received and is being processed.
    </p>

    <div class="mt-8 grid gap-4 text-left sm:grid-cols-3">
      <div class="card p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Order number</p>
        <p class="mt-1 font-semibold text-ink">{{ order.number }}</p>
      </div>
      <div class="card p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Estimated delivery</p>
        <p class="mt-1 font-semibold text-ink">{{ formatDate(order.estimatedDelivery) }}</p>
      </div>
      <div class="card p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Payment method</p>
        <p class="mt-1 font-semibold text-ink">{{ paymentLabels[order.paymentMethod] ?? order.paymentMethod }}</p>
      </div>
    </div>

    <div class="card mx-auto mt-8 max-w-md p-6 text-left">
      <div class="mb-3 flex items-center justify-between">
        <h2 class="font-bold text-ink">Order Summary</h2>
        <StatusTag :status="order.status" />
      </div>

      <div class="space-y-2 text-sm">
        <div v-for="row in lineRows" :key="row.label" class="flex justify-between">
          <span class="text-gray-600">{{ row.label }}</span>
          <span
            :class="row.label === 'Total' ? 'text-base font-bold text-ink' : row.value < 0 ? 'font-medium text-red-500' : 'font-medium text-ink'"
          >
            {{ displayPrice(row.value) }}
          </span>
        </div>
      </div>

      <div class="my-4 border-t border-border-gray"></div>

      <div class="space-y-3">
        <div v-for="item in order.items" :key="item.id" class="flex items-center gap-3">
          <img :src="item.image" :alt="item.title" class="h-14 w-12 rounded-lg object-cover" />
          <div class="min-w-0 flex-1">
            <p class="truncate font-semibold text-ink">{{ item.title }}</p>
            <p class="text-xs text-gray-500">Qty {{ item.quantity }}</p>
          </div>
          <p class="shrink-0 text-sm font-medium text-ink">{{ formatPrice(item.unitPrice * item.quantity) }}</p>
        </div>
      </div>
    </div>

    <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
      <RouterLink :to="`/order/tracking/${order.id}`" class="btn-primary">
        Track My Order
      </RouterLink>
      <RouterLink to="/shop" class="btn-secondary">
        Continue Shopping
      </RouterLink>
    </div>

    <p class="mt-6 text-xs text-gray-500">
      A confirmation email was sent to {{ CURRENT_USER.email }}
    </p>
  </div>

  <div v-else class="container-app mx-auto max-w-md py-16">
    <EmptyState
      title="Order not found"
      description="We couldn't find that order, but you can keep browsing the shop."
      ctaLabel="Go to shop"
      @cta="router.push('/shop')"
    />
  </div>
</template>
