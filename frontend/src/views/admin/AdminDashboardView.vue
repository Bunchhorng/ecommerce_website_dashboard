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
  ArrowUpRight,
  Package,
  PackageCheck,
  Hourglass,
  RefreshCw
} from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import RevenueChart from '@/components/admin/charts/RevenueChart.vue'
import OrdersTrendChart from '@/components/admin/charts/OrdersTrendChart.vue'
import OrderStatusChart from '@/components/admin/charts/OrderStatusChart.vue'
import PaymentStatusChart from '@/components/admin/charts/PaymentStatusChart.vue'
import SalesCategoryChart from '@/components/admin/charts/SalesCategoryChart.vue'
import StatusTag from '@/components/StatusTag.vue'
import StarRating from '@/components/StarRating.vue'
import { adminApi } from '@/api/admin'
import type { AdminDashboard, AdminOrderItem } from '@/api/admin'
import { formatCompactNumber, formatDate, formatPrice } from '@/utils/format'

const MONTH_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']

interface Kpi {
  key: string
  label: string
  value: string
  delta: number | null
  icon: Component
  danger?: boolean
}

const { t } = useI18n()

const loading = ref(true)
const error = ref(false)
const range = ref('30')
const customFrom = ref('')
const customTo = ref('')

const ranges = [
  { key: 'today', label: 'Today' },
  { key: 'yesterday', label: 'Yesterday' },
  { key: '7', label: '7D' },
  { key: '30', label: '30D' },
  { key: 'this_month', label: 'This Month' },
  { key: 'last_month', label: 'Last Month' }
]

const dashboard = ref<AdminDashboard | null>(null)

const metrics = computed(() => dashboard.value?.metrics ?? null)

const kpis = computed<Kpi[]>(() => {
  const m = metrics.value
  if (m === null) return []
  return [
    { key: 'revenue', label: t('admin.dashboard.total_revenue'), value: formatPrice(m.total_revenue), delta: m.revenue_delta, icon: DollarSign },
    { key: 'orders', label: t('admin.dashboard.total_orders'), value: String(m.orders_count), delta: m.orders_delta, icon: ShoppingCart },
    { key: 'customers', label: t('admin.dashboard.total_customers'), value: formatCompactNumber(m.customers_count), delta: m.customers_delta, icon: Users },
    { key: 'pending', label: t('admin.dashboard.pending_orders'), value: String(m.pending_orders), delta: null, icon: Hourglass },
    { key: 'processing', label: t('admin.dashboard.processing_orders'), value: String(m.processing_orders), delta: null, icon: Package },
    { key: 'completed', label: t('admin.dashboard.completed_orders'), value: String(m.completed_orders), delta: null, icon: PackageCheck },
    { key: 'low_stock', label: t('admin.dashboard.low_stock_alerts'), value: String(m.low_stock_products), delta: null, icon: AlertTriangle, danger: true },
    { key: 'out_of_stock', label: t('admin.dashboard.out_of_stock'), value: String(m.out_of_stock_products), delta: null, icon: AlertTriangle, danger: true }
  ]
})

const revenueTrend = computed<{ label: string; revenue: number }[]>(() => {
  const raw = dashboard.value?.revenue_trend ?? []
  return raw.map((entry) => ({ label: trendLabel(entry.date), revenue: entry.revenue }))
})

const ordersTrend = computed<{ label: string; orders: number }[]>(() => {
  const raw = dashboard.value?.orders_trend ?? []
  return raw.map((entry) => ({ label: trendLabel(entry.date), orders: entry.orders }))
})

const statusDistribution = computed(() => dashboard.value?.status_distribution ?? [])
const paymentStatusDistribution = computed(() => dashboard.value?.payment_status_distribution ?? [])
const salesByCategory = computed(() =>
  (dashboard.value?.sales_by_category ?? []).map((c) => ({ category: c.name, sales: c.revenue }))
)
const topSelling = computed(() => dashboard.value?.top_selling_products ?? [])
const lowStock = computed(() => dashboard.value?.low_stock ?? [])
const recentCustomers = computed(() => dashboard.value?.recent_customers ?? [])
const recentReviews = computed(() => dashboard.value?.recent_reviews ?? [])
const recentPayments = computed(() => dashboard.value?.recent_payments ?? [])
const recentOrders = ref<AdminOrderItem[]>([])

function capitalize(s: string): string {
  return s.charAt(0).toUpperCase() + s.slice(1)
}

function trendLabel(date: string): string {
  if (range.value === 'this_month' || range.value === 'last_month') {
    const monthIndex = parseInt(date.slice(5, 7), 10) - 1
    return MONTH_SHORT[monthIndex] ?? date
  }
  const parsed = new Date(`${date}T00:00:00`)
  if (Number.isNaN(parsed.getTime())) return date
  return `${MONTH_SHORT[parsed.getMonth()]} ${parsed.getDate()}`
}

function isMonthRange(): boolean {
  return range.value === 'this_month' || range.value === 'last_month'
}

function applyRange(value: string) {
  range.value = value
  loadDashboard()
}

function applyCustomRange() {
  if (!customFrom.value || !customTo.value) return
  range.value = 'custom'
  loadDashboard()
}

async function loadDashboard() {
  loading.value = true
  error.value = false
  try {
    const { data: resp } = await adminApi.getDashboard(
      range.value === 'custom' ? 'custom' : range.value,
      range.value === 'custom' ? customFrom.value : undefined,
      range.value === 'custom' ? customTo.value : undefined
    )
    dashboard.value = resp.data
    if (dashboard.value?.range) range.value = dashboard.value.range
  } catch {
    error.value = true
  } finally {
    loading.value = false
  }
}

async function loadRecentOrders() {
  const { data: resp } = await adminApi.listOrders()
  recentOrders.value = resp.data.slice(0, 5)
}

onMounted(() => {
  void loadDashboard()
  void loadRecentOrders().catch(() => {
    // Ignore secondary failure
  })
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <h1 class="text-lg font-bold text-ink dark:text-ink">{{ $t('admin.dashboard.title') }}</h1>
      <div class="flex flex-wrap items-center gap-2">
        <button
          v-for="r in ranges"
          :key="r.key"
          type="button"
          :class="range === r.key
            ? 'bg-primary text-white shadow-sm'
            : 'bg-surface text-gray-600 hover:bg-surface-hover dark:text-muted'"
          class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors"
          @click="applyRange(r.key)"
        >
          {{ $t(`admin.dashboard.range.${r.key}`) }}
        </button>
        <div class="flex items-center gap-1 rounded-lg bg-surface px-2 py-1.5">
          <input v-model="customFrom" type="date" class="bg-transparent text-xs text-gray-600 outline-none dark:text-muted" />
          <span class="text-gray-400">→</span>
          <input v-model="customTo" type="date" class="bg-transparent text-xs text-gray-600 outline-none dark:text-muted" />
          <button type="button" class="rounded-md bg-primary px-2 py-1 text-xs font-semibold text-white" @click="applyCustomRange">
            Go
          </button>
        </div>
        <button
          v-if="error"
          type="button"
          class="inline-flex items-center gap-1 rounded-lg bg-surface px-3 py-1.5 text-xs font-semibold text-primary"
          @click="loadDashboard"
        >
          <RefreshCw class="h-3.5 w-3.5" />
          {{ $t('actions.retry') }}
        </button>
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
      <div v-for="i in 8" :key="i" class="card animate-pulse p-5">
        <div class="h-3 w-2/3 rounded bg-gray-200 dark:bg-surface-hover"></div>
        <div class="mt-4 h-8 w-1/2 rounded bg-gray-200 dark:bg-surface-hover"></div>
      </div>
    </div>

    <div v-else-if="error" class="card p-10 text-center">
      <p class="text-sm text-gray-500 dark:text-muted">{{ $t('admin.dashboard.load_failed') }}</p>
      <button
        type="button"
        class="mt-4 inline-flex items-center gap-1 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white"
        @click="loadDashboard"
      >
        <RefreshCw class="h-4 w-4" />
        {{ $t('actions.retry') }}
      </button>
    </div>

    <template v-else-if="metrics">
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div v-for="kpi in kpis" :key="kpi.key" class="card feature-glow p-5">
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

      <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        <div class="card p-5">
          <div class="mb-5 flex items-center justify-between">
            <h2 class="text-base font-semibold text-ink">{{ $t('admin.chart.revenue_trend') }}</h2>
            <span class="chip">{{ $t('admin.dashboard.range_label_' + (isMonthRange() ? 'months' : 'days')) }}</span>
          </div>
          <RevenueChart :data="revenueTrend" />
        </div>

        <div class="card p-5">
          <div class="mb-5 flex items-center justify-between">
            <h2 class="text-base font-semibold text-ink">{{ $t('admin.chart.orders_over_time') }}</h2>
            <span class="chip">{{ $t('admin.dashboard.total_count', { count: metrics.orders_count }) }}</span>
          </div>
          <OrdersTrendChart :data="ordersTrend" />
        </div>
      </div>

      <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="card p-5">
          <div class="mb-5">
            <h2 class="text-base font-semibold text-ink">{{ $t('admin.chart.orders_by_status') }}</h2>
          </div>
          <OrderStatusChart :data="statusDistribution" />
        </div>

        <div class="card p-5">
          <div class="mb-5">
            <h2 class="text-base font-semibold text-ink">{{ $t('admin.chart.payments_by_status') }}</h2>
          </div>
          <PaymentStatusChart :data="paymentStatusDistribution" />
        </div>

        <div class="card p-5">
          <div class="mb-5">
            <h2 class="text-base font-semibold text-ink">{{ $t('admin.chart.sales_by_category') }}</h2>
          </div>
          <SalesCategoryChart :data="salesByCategory" />
        </div>
      </div>

      <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="card overflow-hidden xl:col-span-2">
          <div class="flex flex-wrap items-center justify-between gap-2 px-5 pb-4 pt-5">
            <h2 class="text-base font-semibold text-ink">{{ $t('admin.dashboard.top_selling') }}</h2>
            <RouterLink to="/admin/products" class="inline-flex items-center gap-1 text-sm font-medium text-primary transition-colors hover:text-primary-dark">
              {{ $t('admin.dashboard.view_all_products') }}
              <ArrowUpRight class="h-4 w-4" />
            </RouterLink>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full min-w-[560px] text-sm">
              <thead>
                <tr class="border-y border-border-gray bg-gray-50 dark:bg-surface-hover/40">
                  <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-muted">#</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-muted">{{ $t('admin.table.col_product') }}</th>
                  <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-muted">{{ $t('admin.dashboard.units_sold') }}</th>
                  <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-muted">{{ $t('admin.table.col_total') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border-gray">
                <tr v-if="topSelling.length === 0">
                  <td colspan="4" class="px-5 py-6 text-center text-sm text-gray-400 dark:text-gray-500">
                    {{ $t('admin.dashboard.no_data') }}
                  </td>
                </tr>
                <tr v-for="(p, index) in topSelling" :key="p.product_id" class="transition-colors hover:bg-canvas/50">
                  <td class="px-5 py-3.5 font-semibold text-gray-400 dark:text-gray-500">{{ index + 1 }}</td>
                  <td class="px-5 py-3.5 font-medium text-ink dark:text-ink">{{ p.product_name }}</td>
                  <td class="px-5 py-3.5 text-right text-gray-600 dark:text-muted">{{ p.total_qty }}</td>
                  <td class="px-5 py-3.5 text-right font-medium text-gray-700 dark:text-gray-200">{{ formatPrice(p.revenue) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card overflow-hidden">
          <div class="flex items-center justify-between gap-2 p-5 pb-4">
            <h2 class="text-base font-semibold text-ink">{{ $t('admin.dashboard.recent_orders') }}</h2>
            <RouterLink to="/admin/orders" class="inline-flex items-center gap-1 text-sm font-medium text-primary transition-colors hover:text-primary-dark">
              {{ $t('actions.view_all') }}
            </RouterLink>
          </div>
          <ul class="divide-y divide-border-gray border-t border-border-gray">
            <li v-if="recentOrders.length === 0" class="px-5 py-6 text-center text-sm text-gray-400 dark:text-gray-500">
              {{ $t('admin.dashboard.no_data') }}
            </li>
            <li v-for="order in recentOrders" :key="order.order_number" class="px-5 py-3">
              <RouterLink :to="`/admin/orders/${order.id}`" class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                  <div class="truncate font-semibold text-ink dark:text-ink">{{ order.order_number }}</div>
                  <div class="truncate text-xs text-gray-500 dark:text-muted">{{ order.user?.name }}</div>
                </div>
                <div class="flex shrink-0 items-center gap-3">
                  <span class="font-medium text-gray-700 dark:text-gray-200">{{ formatPrice(order.total) }}</span>
                  <StatusTag :status="capitalize(order.status)" />
                </div>
              </RouterLink>
            </li>
          </ul>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="card overflow-hidden">
          <div class="p-5 pb-4">
            <h2 class="text-base font-semibold text-ink">{{ $t('admin.dashboard.recent_customers') }}</h2>
          </div>
          <ul class="divide-y divide-border-gray border-t border-border-gray">
            <li v-if="recentCustomers.length === 0" class="px-5 py-6 text-center text-sm text-gray-400 dark:text-gray-500">
              {{ $t('admin.dashboard.no_data') }}
            </li>
            <li v-for="customer in recentCustomers" :key="customer.id" class="px-5 py-3">
              <RouterLink :to="`/admin/customers/${customer.id}`" class="flex items-center gap-3">
                <img v-if="customer.avatar" :src="customer.avatar" alt="" class="h-9 w-9 rounded-full object-cover" />
                <div v-else class="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">
                  {{ customer.name.charAt(0).toUpperCase() }}
                </div>
                <div class="min-w-0">
                  <div class="truncate text-sm font-semibold text-ink dark:text-ink">{{ customer.name }}</div>
                  <div class="truncate text-xs text-gray-500 dark:text-muted">{{ customer.email }}</div>
                </div>
                <div class="ml-auto text-xs text-gray-400 dark:text-gray-500">{{ formatDate(customer.created_at) }}</div>
              </RouterLink>
            </li>
          </ul>
        </div>

        <div class="card overflow-hidden">
          <div class="p-5 pb-4">
            <h2 class="text-base font-semibold text-ink">{{ $t('admin.dashboard.recent_reviews') }}</h2>
          </div>
          <ul class="divide-y divide-border-gray border-t border-border-gray">
            <li v-if="recentReviews.length === 0" class="px-5 py-6 text-center text-sm text-gray-400 dark:text-gray-500">
              {{ $t('admin.dashboard.no_data') }}
            </li>
            <li v-for="review in recentReviews" :key="review.id" class="px-5 py-3">
              <div class="flex items-center justify-between gap-2">
                <StarRating :value="review.rating" :size="14" />
                <StatusTag :status="capitalize(review.status)" />
              </div>
              <div class="mt-1.5 truncate text-sm font-medium text-ink dark:text-ink">{{ review.title ?? review.body ?? '…' }}</div>
              <div class="mt-0.5 truncate text-xs text-gray-500 dark:text-muted">
                {{ review.user_name }} · {{ review.product_name }}
              </div>
            </li>
          </ul>
        </div>

        <div class="card overflow-hidden">
          <div class="p-5 pb-4">
            <h2 class="text-base font-semibold text-ink">{{ $t('admin.dashboard.recent_payments') }}</h2>
          </div>
          <ul class="divide-y divide-border-gray border-t border-border-gray">
            <li v-if="recentPayments.length === 0" class="px-5 py-6 text-center text-sm text-gray-400 dark:text-gray-500">
              {{ $t('admin.dashboard.no_data') }}
            </li>
            <li v-for="payment in recentPayments" :key="payment.id" class="px-5 py-3">
              <div class="flex items-center justify-between gap-2">
                <span class="text-xs font-semibold text-gray-500 dark:text-muted">{{ payment.order_number ?? '—' }}</span>
                <StatusTag :status="capitalize(payment.status)" />
              </div>
              <div class="mt-1 flex items-center justify-between">
                <span class="truncate text-sm text-gray-600 dark:text-muted">{{ payment.method }}</span>
                <span class="font-semibold text-ink dark:text-ink">{{ formatPrice(payment.amount) }}</span>
              </div>
            </li>
          </ul>
        </div>
      </div>

      <div class="card overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-2 p-5 pb-4">
          <div>
            <h2 class="text-base font-semibold text-ink">{{ $t('admin.dashboard.low_stock_alerts') }}</h2>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-muted">{{ $t('admin.dashboard.low_stock_description') }}</p>
          </div>
          <RouterLink to="/admin/inventory" class="inline-flex items-center gap-1 text-sm font-medium text-primary transition-colors hover:text-primary-dark">
            {{ $t('admin.dashboard.view_all_products') }}
            <ArrowUpRight class="h-4 w-4" />
          </RouterLink>
        </div>
        <ul class="divide-y divide-border-gray border-t border-border-gray">
          <li v-if="lowStock.length === 0" class="px-5 py-6 text-center text-sm text-gray-400 dark:text-gray-500">
            {{ $t('admin.dashboard.no_low_stock') }}
          </li>
          <li v-for="item in lowStock" :key="item.id" class="flex flex-wrap items-center justify-between gap-3 px-5 py-3">
            <RouterLink :to="item.product_id ? `/admin/products/${item.product_id}` : '/admin/inventory'" class="min-w-0">
              <div class="truncate text-sm font-semibold text-ink hover:text-primary dark:text-ink dark:hover:text-primary">
                {{ item.product_name }}
                <span v-if="item.variant_name" class="font-normal text-gray-500 dark:text-muted">· {{ item.variant_name }}</span>
              </div>
              <div class="truncate text-xs text-gray-500 dark:text-muted">{{ item.sku }}</div>
            </RouterLink>
            <div class="flex shrink-0 items-center gap-2">
              <span
                class="rounded-full px-2 py-0.5 text-xs font-semibold"
                :class="item.is_out_of_stock
                  ? 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-400'
                  : 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400'"
              >
                {{ $t('admin.dashboard.count_left', { count: item.available_quantity }) }}
              </span>
              <span class="text-xs text-gray-400 dark:text-gray-500">
                {{ $t('admin.dashboard.threshold', { count: item.low_stock_threshold }) }}
              </span>
            </div>
          </li>
        </ul>
      </div>
    </template>
  </div>
</template>