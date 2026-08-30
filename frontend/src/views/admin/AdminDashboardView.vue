<script setup lang="ts">
import type { Component } from 'vue'
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import {
  DollarSign,
  ShoppingCart,
  Users,
  AlertTriangle,
  TrendingUp,
  TrendingDown,
  ArrowUpRight
} from 'lucide-vue-next'
import RevenueChart from '@/components/admin/charts/RevenueChart.vue'
import OrderStatusChart from '@/components/admin/charts/OrderStatusChart.vue'
import SalesCategoryChart from '@/components/admin/charts/SalesCategoryChart.vue'
import StatusTag from '@/components/StatusTag.vue'
import { ADMIN_METRICS, LOW_STOCK_PRODUCTS, ORDERS } from '@/data/mock'
import type { Product } from '@/types'
import { formatCompactNumber, formatDate, formatPrice } from '@/utils/format'

interface Kpi {
  label: string
  value: string
  delta: number | null
  icon: Component
  danger?: boolean
}

const recentOrders = computed(() => ORDERS.slice(0, 5))

const kpis = computed<Kpi[]>(() => [
  { label: 'Total Revenue', value: formatPrice(ADMIN_METRICS.totalRevenue), delta: ADMIN_METRICS.revenueDelta, icon: DollarSign },
  { label: 'Total Orders', value: String(ADMIN_METRICS.totalOrders), delta: ADMIN_METRICS.ordersDelta, icon: ShoppingCart },
  { label: 'Total Customers', value: formatCompactNumber(ADMIN_METRICS.totalCustomers), delta: ADMIN_METRICS.customersDelta, icon: Users },
  { label: 'Low Stock Alerts', value: String(ADMIN_METRICS.lowStockAlert), delta: null, icon: AlertTriangle, danger: true }
])

function deltaLabel(delta: number): string {
  return `${delta >= 0 ? '+' : ''}${delta}%`
}

function lowStockTone(p: Product): string {
  if (p.stockQuantity <= 5) return 'bg-red-50 text-red-600'
  if (p.stockQuantity <= 10) return 'bg-amber-50 text-amber-600'
  return 'bg-gray-100 text-gray-600'
}
</script>

<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
      <div v-for="kpi in kpis" :key="kpi.label" class="card p-5">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="text-sm text-gray-500">{{ kpi.label }}</div>
            <div class="mt-2 truncate text-2xl font-extrabold text-ink sm:text-3xl">{{ kpi.value }}</div>
            <div v-if="kpi.delta !== null" class="mt-3 flex items-center gap-2">
              <span
                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold"
                :class="kpi.delta >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600'"
              >
                <component :is="kpi.delta >= 0 ? TrendingUp : TrendingDown" class="h-3.5 w-3.5" />
                {{ deltaLabel(kpi.delta) }}
              </span>
              <span class="text-xs text-gray-400">vs last month</span>
            </div>
          </div>
          <div
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
            :class="kpi.danger ? 'bg-red-50 text-red-500' : 'bg-primary/10 text-primary'"
          >
            <component :is="kpi.icon" class="h-5 w-5" />
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
      <div class="card p-5 xl:col-span-2">
        <div class="mb-5 flex items-center justify-between">
          <h2 class="text-base font-semibold text-ink">Revenue Trend</h2>
          <span class="chip">Last 12 months</span>
        </div>
        <RevenueChart />
      </div>

      <div class="card p-5">
        <div class="mb-5 flex items-center justify-between">
          <h2 class="text-base font-semibold text-ink">Orders by Status</h2>
          <span class="chip">Total {{ ADMIN_METRICS.totalOrders }}</span>
        </div>
        <OrderStatusChart />
      </div>

      <div class="grid gap-5 xl:col-span-3 xl:grid-cols-3">
        <div class="card p-5 xl:col-span-1">
          <div class="mb-5 flex items-center justify-between">
            <h2 class="text-base font-semibold text-ink">Sales by Category</h2>
            <span class="chip">This year</span>
          </div>
          <SalesCategoryChart />
        </div>

        <div class="card overflow-hidden xl:col-span-2">
          <div class="flex flex-wrap items-center justify-between gap-2 px-5 pb-4 pt-5">
            <h2 class="text-base font-semibold text-ink">Recent Orders</h2>
            <RouterLink to="/admin/orders" class="inline-flex items-center gap-1 text-sm font-medium text-primary transition-colors hover:text-primary-dark">
              View all
              <ArrowUpRight class="h-4 w-4" />
            </RouterLink>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-sm">
              <thead>
                <tr class="border-y border-border-gray bg-gray-50">
                  <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Order</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Customer</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Total</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                  <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Date</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border-gray">
                <tr v-for="order in recentOrders" :key="order.id" class="transition-colors hover:bg-canvas/50">
                  <td class="px-5 py-3.5 font-semibold text-ink">{{ order.number }}</td>
                  <td class="px-5 py-3.5 text-gray-600">{{ order.shippingAddress.fullName }}</td>
                  <td class="px-5 py-3.5 font-medium text-gray-700">{{ formatPrice(order.total) }}</td>
                  <td class="px-5 py-3.5">
                    <StatusTag :status="order.status" />
                  </td>
                  <td class="px-5 py-3.5 text-right text-gray-500">{{ formatDate(order.placedAt) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="card overflow-hidden">
      <div class="flex flex-wrap items-center justify-between gap-2 p-5 pb-4">
        <div>
          <h2 class="text-base font-semibold text-ink">Low Stock Alerts</h2>
          <p class="mt-0.5 text-sm text-gray-500">Products that need restocking soon.</p>
        </div>
        <RouterLink to="/admin/products" class="inline-flex items-center gap-1 text-sm font-medium text-primary transition-colors hover:text-primary-dark">
          View all products
          <ArrowUpRight class="h-4 w-4" />
        </RouterLink>
      </div>
      <ul class="divide-y divide-border-gray border-t border-border-gray">
        <li v-for="p in LOW_STOCK_PRODUCTS" :key="p.id" class="flex items-center justify-between gap-3 px-5 py-3 transition-colors hover:bg-canvas/50">
          <div class="flex min-w-0 items-center gap-3">
            <img :src="p.images[0]?.url" :alt="p.title" class="h-9 w-9 shrink-0 rounded-lg object-cover" />
            <div class="min-w-0">
              <div class="truncate text-sm font-medium text-ink">{{ p.title }}</div>
              <div class="text-xs text-gray-500">{{ p.sku }}</div>
            </div>
          </div>
          <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold" :class="lowStockTone(p)">
            {{ p.stockQuantity }} left
          </span>
        </li>
      </ul>
    </div>
  </div>
</template>