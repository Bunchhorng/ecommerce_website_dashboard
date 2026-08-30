<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Package, Check } from 'lucide-vue-next'
import type { OrderStatus } from '@/types'
import { getOrderById } from '@/data/mock'
import StatusTag from '@/components/StatusTag.vue'
import EmptyState from '@/components/EmptyState.vue'
import { formatDate, formatDateTime, formatPrice } from '@/utils/format'

const route = useRoute()
const router = useRouter()

const orderId = route.params.orderId as string
const order = computed(() => getOrderById(orderId))

const FLOW: OrderStatus[] = ['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered']

const currentIndex = computed(() =>
  order.value ? FLOW.indexOf(order.value.status) : -1
)

function timestampFor(status: OrderStatus): string | null {
  if (!order.value) return null
  const event = order.value.trackingEvents.find((e) => e.status === status)
  return event ? formatDateTime(event.at) : null
}
</script>

<template>
  <div v-if="order" class="container-app mx-auto max-w-3xl py-10">
    <div class="card p-6">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-ink">Order {{ order.number }}</h1>
          <p class="mt-1 text-sm text-gray-500">
            Placed {{ formatDate(order.placedAt) }} · Arrives {{ formatDate(order.estimatedDelivery) }}
          </p>
        </div>
        <StatusTag :status="order.status" />
      </div>
    </div>

    <div v-if="order.status === 'Delivered'" class="mt-6 flex items-center gap-2 rounded-xl bg-emerald-100 p-4 text-sm font-medium text-emerald-700">
      <Check class="h-5 w-5" />
      Delivered on {{ formatDate(order.estimatedDelivery) }}
    </div>

    <div class="card mt-6 p-6">
      <h2 class="mb-6 text-lg font-bold text-ink">Order Progress</h2>

      <div class="hidden sm:flex sm:items-start">
        <template v-for="(status, i) in FLOW" :key="status">
          <div class="flex flex-col items-center">
            <div
              class="flex h-10 w-10 items-center justify-center rounded-full font-semibold"
              :class="
                i < currentIndex
                  ? 'bg-emerald-500 text-white'
                  : i === currentIndex
                    ? 'bg-primary text-white'
                    : 'bg-gray-200 text-gray-500'
              "
            >
              <Check v-if="i < currentIndex" class="h-5 w-5" />
              <span v-else>{{ i + 1 }}</span>
            </div>
            <p class="mt-2 text-xs font-semibold" :class="i === currentIndex ? 'text-primary' : i < currentIndex ? 'text-ink' : 'text-gray-400'">
              {{ status }}
            </p>
            <p class="mt-1 text-center text-[10px] text-gray-500">
              {{ timestampFor(status) ?? '—' }}
            </p>
          </div>
          <div
            v-if="i < FLOW.length - 1"
            class="mx-2 mt-5 h-0.5 flex-1"
            :class="i < currentIndex ? 'bg-emerald-500' : 'bg-gray-200'"
          ></div>
        </template>
      </div>

      <div class="sm:hidden">
        <div v-for="(status, i) in FLOW" :key="status" class="flex gap-4">
          <div class="flex flex-col items-center">
            <div
              class="flex h-10 w-10 items-center justify-center rounded-full font-semibold"
              :class="
                i < currentIndex
                  ? 'bg-emerald-500 text-white'
                  : i === currentIndex
                    ? 'bg-primary text-white'
                    : 'bg-gray-200 text-gray-500'
              "
            >
              <Check v-if="i < currentIndex" class="h-5 w-5" />
              <span v-else>{{ i + 1 }}</span>
            </div>
            <div
              v-if="i < FLOW.length - 1"
              class="mt-1 w-0.5 flex-1"
              :class="i < currentIndex ? 'bg-emerald-500' : 'bg-gray-200'"
            ></div>
          </div>
          <div class="pb-8">
            <p class="text-sm font-semibold" :class="i === currentIndex ? 'text-primary' : i < currentIndex ? 'text-ink' : 'text-gray-400'">
              {{ status }}
            </p>
            <p class="mt-0.5 text-xs text-gray-500">
              {{ timestampFor(status) ?? '—' }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="card mt-6 flex items-center gap-4 p-6">
      <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary">
        <Package class="h-6 w-6" />
      </div>
      <div>
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Estimated delivery</p>
        <p class="font-semibold text-ink">{{ formatDate(order.estimatedDelivery) }}</p>
      </div>
    </div>

    <div class="card mt-6 p-6">
      <h2 class="mb-4 text-lg font-bold text-ink">Items</h2>
      <div class="divide-y divide-border-gray">
        <div v-for="item in order.items" :key="item.id" class="flex items-center gap-4 py-4">
          <img :src="item.image" :alt="item.title" class="h-16 w-14 rounded-lg object-cover" />
          <div class="min-w-0 flex-1">
            <p class="font-semibold text-ink">{{ item.title }}</p>
            <p class="text-xs text-gray-500">
              {{ item.brand }} · {{ item.variant?.attributes ? item.variant.attributes.map((a) => a.value).join(', ') : '—' }}
            </p>
            <p class="text-xs text-gray-500">Qty {{ item.quantity }}</p>
          </div>
          <p class="shrink-0 font-medium text-ink">{{ formatPrice(item.unitPrice * item.quantity) }}</p>
        </div>
      </div>
    </div>
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
