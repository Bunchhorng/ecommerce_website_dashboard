<script setup lang="ts">
import type { Component } from 'vue'
import { computed, onMounted, ref } from 'vue'
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
import { useI18n } from 'vue-i18n'
import RevenueChart from '@/components/admin/charts/RevenueChart.vue'
import OrderStatusChart from '@/components/admin/charts/OrderStatusChart.vue'
import SalesCategoryChart from '@/components/admin/charts/SalesCategoryChart.vue'
import StatusTag from '@/components/StatusTag.vue'
import { adminApi } from '@/api/admin'
import type { AdminOrderItem } from '@/api/admin'
import { formatCompactNumber, formatDate, formatPrice } from '@/utils/format'

interface Kpi {
  label: string
  value: string
  delta: number | null
  icon: Component
  danger?: boolean
}

const { t } = useI18n()

const loading = ref(true)

const metrics = ref({
  total_revenue: 0,
  orders_count: 0,
  customers_count: 0,
  pending_orders: 0,
  low_stock_products: 0
})

const revenueTrend = ref<{ label: string; revenue: number }[]>([])
const statusDistribution = ref<{ status: string; count: number }[]>([])
const salesByCategory = ref<{ category: string; sales: number }[]>([])
const recentOrders = ref<AdminOrderItem[]>([])

const totalOrders = computed(() => metrics.value.orders_count)

const kpis = computed<Kpi[]>(() => [
  { label: t('admin.dashboard.total_revenue'), value: formatPrice(metrics.value.total_revenue), delta: null, icon: DollarSign },
  { label: t('admin.dashboard.total_orders'), value: String(metrics.value.orders_count), delta: null, icon: ShoppingCart },
  { label: t('admin.dashboard.total_customers'), value: formatCompactNumber(metrics.value.customers_count), delta: null, icon: Users },
  { label: t('admin.dashboard.low_stock_alerts'), value: String(metrics.value.low_stock_products), delta: null, icon: AlertTriangle, danger: true }
])

function capitalize(s: string): string {
  return s.charAt(0).toUpperCase() + s.slice(1)
}

function aggregateByMonth(raw: { date: string; revenue: number }[]): { label: string; revenue: number }[] {
  const monthMap = new Map<string, number>()
  for (const entry of raw) {
    const monthKey = entry.date.slice(0, 7)
    monthMap.set(monthKey, (monthMap.get(monthKey) ?? 0) + entry.revenue)
  }
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
  return Array.from(monthMap.entries())
    .sort(([a], [b]) => a.localeCompare(b))
    .map(([key, revenue]) => {
      const monthIndex = parseInt(key.slice(5, 7), 10) - 1
      return { label: months[monthIndex] ?? key, revenue }
    })
}

async function loadDashboard() {
  loading.value = true
  try {
    const { data: resp } = await adminApi.getDashboard()
    const dashboard = resp.data
    metrics.value = dashboard.metrics
    revenueTrend.value = aggregateByMonth(dashboard.revenue_trend)
    statusDistribution.value = dashboard.status_distribution
    salesByCategory.value = dashboard.sales_by_category.map((c) => ({
      category: c.name,
      sales: c.revenue
    }))
  } catch {
    // Silently handle errors - UI shows defaults
  } finally {
    loading.value = false
  }
}

async function loadRecentOrders() {
  try {
    const { data: resp } = await adminApi.listOrders()
    recentOrders.value = resp.data.slice(0, 5)
  } catch {
    // Silently handle
  }
}

onMounted(() => {
  loadDashboard()
  loadRecentOrders()
})
</script>

<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
      <div v-for="kpi in kpis" :key="kpi.label" class="card feature-glow p-5">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="text-sm text-gray-500 dark:text-muted">{{ kpi.label }}</div>
            <div class="mt-2 truncate text-2xl font-extrabold text-ink sm:text-3xl dark:text-ink">{{ kpi.value }}</div>
            <div v-if="kpi.delta !== null" class="mt-3 flex items-center gap-2">
              <span
                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold"
                :class="kpi.delta >= 0
                  ? 'bg-emerald-50 text-emerald-600 dark:bg-success/15 dark:text-success'
                  : 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-400'"
              >
                <component :is="kpi.delta >= 0 ? TrendingUp : TrendingDown" class="h-3.5 w-3.5" />
                {{ kpi.delta >= 0 ? '+' : '' }}{{ kpi.delta }}%
              </span>
              <span class="text-xs text-gray-400 dark:text-gray-500">{{ $t('admin.dashboard.vs_last_month') }}</span>
            </div>
          </div>
          <div
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
            :class="kpi.danger
              ? 'bg-red-50 text-red-500 dark:bg-red-500/15 dark:text-red-400'
              : 'bg-primary/10 text-primary'"
          >
            <component :is="kpi.icon" class="h-5 w-5" />
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
      <div class="card p-5 xl:col-span-2">
        <div class="mb-5 flex items-center justify-between">
          <h2 class="text-base font-semibold text-ink">{{ $t('admin.chart.revenue_trend') }}</h2>
          <span class="chip">{{ $t('admin.dashboard.last_12_months') }}</span>
        </div>
        <RevenueChart :data="revenueTrend" />
      </div>

      <div class="card p-5">
        <div class="mb-5 flex items-center justify-between">
          <h2 class="text-base font-semibold text-ink">{{ $t('admin.chart.orders_by_status') }}</h2>
          <span class="chip">{{ $t('admin.dashboard.total_count', { count: totalOrders }) }}</span>
        </div>
        <OrderStatusChart :data="statusDistribution" />
      </div>

      <div class="grid gap-5 xl:col-span-3 xl:grid-cols-3">
        <div class="card p-5 xl:col-span-1">
          <div class="mb-5 flex items-center justify-between">
            <h2 class="text-base font-semibold text-ink">{{ $t('admin.chart.sales_by_category') }}</h2>
            <span class="chip">{{ $t('admin.dashboard.this_year') }}</span>
          </div>
          <SalesCategoryChart :data="salesByCategory" />
        </div>

        <div class="card overflow-hidden xl:col-span-2">
          <div class="flex flex-wrap items-center justify-between gap-2 px-5 pb-4 pt-5">
            <h2 class="text-base font-semibold text-ink">{{ $t('admin.dashboard.recent_orders') }}</h2>
            <RouterLink to="/admin/orders" class="inline-flex items-center gap-1 text-sm font-medium text-primary transition-colors hover:text-primary-dark">
              {{ $t('actions.view_all') }}
              <ArrowUpRight class="h-4 w-4" />
            </RouterLink>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-sm">
              <thead>
                <tr class="border-y border-border-gray bg-gray-50 dark:bg-surface-hover/40">
                  <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-muted">{{ $t('admin.table.col_order') }}</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-muted">{{ $t('admin.table.col_customer') }}</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-muted">{{ $t('admin.table.col_total') }}</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-muted">{{ $t('admin.table.col_status') }}</th>
                  <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-muted">{{ $t('admin.table.col_date') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border-gray">
                <tr v-for="order in recentOrders" :key="order.order_number" class="transition-colors hover:bg-canvas/50">
                  <td class="px-5 py-3.5 font-semibold text-ink dark:text-ink">{{ order.order_number }}</td>
                  <td class="px-5 py-3.5 text-gray-600 dark:text-muted">{{ order.user?.name ?? '—' }}</td>
                  <td class="px-5 py-3.5 font-medium text-gray-700 dark:text-gray-200">{{ formatPrice(order.total) }}</td>
                  <td class="px-5 py-3.5">
                    <StatusTag :status="capitalize(order.status)" />
                  </td>
                  <td class="px-5 py-3.5 text-right text-gray-500 dark:text-muted">{{ formatDate(order.placed_at) }}</td>
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
          <h2 class="text-base font-semibold text-ink">{{ $t('admin.dashboard.low_stock_alerts') }}</h2>
          <p class="mt-0.5 text-sm text-gray-500 dark:text-muted">{{ $t('admin.dashboard.low_stock_description') }}</p>
        </div>
        <RouterLink to="/admin/products" class="inline-flex items-center gap-1 text-sm font-medium text-primary transition-colors hover:text-primary-dark">
          {{ $t('admin.dashboard.view_all_products') }}
          <ArrowUpRight class="h-4 w-4" />
        </RouterLink>
      </div>
      <ul class="divide-y divide-border-gray border-t border-border-gray">
        <li v-if="metrics.low_stock_products === 0" class="px-5 py-6 text-center text-sm text-gray-400 dark:text-gray-500">
          {{ $t('admin.dashboard.no_low_stock') }}
        </li>
        <li v-else class="px-5 py-3.5 text-center text-sm text-gray-500 dark:text-muted">
          {{ $t('admin.dashboard.low_stock_count', { count: metrics.low_stock_products }) }}
        </li>
      </ul>
    </div>
  </div>
</template>
