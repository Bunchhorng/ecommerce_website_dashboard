<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import AdminDataTable from '@/components/admin/AdminDataTable.vue'
import type { TableColumn, TableRow } from '@/types'
import { adminApi } from '@/api/admin'
import type { AdminOrderItem } from '@/api/admin'

const { t } = useI18n()
const router = useRouter()

const loading = ref(true)
const orders = ref<AdminOrderItem[]>([])
const totalCount = ref(0)

const columns = computed<TableColumn[]>(() => [
  { key: 'number', label: t('order.order'), sortable: true },
  { key: 'customer', label: t('admin.orders.customer') },
  { key: 'items', label: t('order.items') },
  { key: 'total', label: t('order.total'), type: 'currency', sortable: true },
  { key: 'payment', label: t('admin.orders.payment_method'), type: 'badge' },
  { key: 'status', label: t('order.status'), type: 'status', sortable: true },
  { key: 'date', label: t('order.date'), type: 'date', sortable: true },
  { key: 'actions', label: '', type: 'actions' }
])

const bulkActions = computed(() => [
  { label: t('admin.orders.bulk.export_csv'), value: 'export' },
  { label: t('admin.orders.bulk.mark_delivered'), value: 'delivered' },
  { label: t('admin.orders.bulk.print_labels'), value: 'print' }
])

const rowActions = computed(() => [
  { label: t('admin.orders.row.view'), value: 'view' },
  { label: t('admin.orders.row.mark_processing'), value: 'process' },
  { label: t('actions.cancel'), value: 'cancel' }
])

function capitalize(s: string): string {
  return s.charAt(0).toUpperCase() + s.slice(1)
}

const rows = computed<TableRow[]>(() =>
  orders.value.map((o) => ({
    id: o.id,
    number: o.order_number,
    customer: o.user?.name ?? '—',
    items: t('admin.orders.items_count', { count: o.items_count }),
    total: o.total,
    payment: capitalize(o.payment_status),
    status: capitalize(o.status),
    date: o.placed_at
  }))
)

const toast = ref('')
let toastTimer: ReturnType<typeof setTimeout> | undefined
function showToast(msg: string) {
  toast.value = msg
  if (toastTimer) clearTimeout(toastTimer)
  toastTimer = setTimeout(() => {
    toast.value = ''
  }, 2500)
}

async function loadOrders(params: { status?: string; q?: string } = {}) {
  loading.value = true
  try {
    const { data: resp } = await adminApi.listOrders(params)
    orders.value = resp.data
    totalCount.value = resp.meta.total
  } catch {
    showToast(t('admin.orders.toast.load_error'))
  } finally {
    loading.value = false
  }
}

function onRowAction(payload: { action: string; row: TableRow }) {
  if (payload.action === 'view') {
    const id = Number(payload.row.id)
    if (id) {
      router.push({ name: 'admin-order-detail', params: { id } })
      return
    }
    showToast(t('admin.orders.toast.open_invoice', { number: String(payload.row.number) }))
  } else if (payload.action === 'process') {
    const orderId = orders.value.find((o) => o.order_number === payload.row.number)?.order_number
    if (orderId) {
      showToast(t('admin.orders.toast.marked_processing', { number: String(payload.row.number) }))
    }
  } else if (payload.action === 'cancel') {
    showToast(t('admin.orders.toast.cancelled', { number: String(payload.row.number) }))
  }
}

function onBulkAction(payload: { action: string; ids: string[] }) {
  if (payload.action === 'export') {
    showToast(t('admin.orders.toast.exported_csv', { count: payload.ids.length }))
  } else if (payload.action === 'delivered') {
    showToast(t('admin.orders.toast.marked_delivered', { count: payload.ids.length }))
  } else if (payload.action === 'print') {
    showToast(t('admin.orders.toast.printing_labels', { count: payload.ids.length }))
  }
}

onMounted(() => loadOrders())
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold text-ink">{{ $t('admin.orders.title') }}</h1>
        <span class="chip">{{ totalCount }} {{ $t('admin.orders.total') }}</span>
      </div>
    </div>

    <AdminDataTable
      :columns="columns"
      :rows="rows"
      :loading="loading"
      :search-keys="['number', 'customer']"
      :search-placeholder="t('admin.orders.search_placeholder')"
      :page-size="8"
      :bulk-actions="bulkActions"
      :row-actions="rowActions"
      @row-action="onRowAction"
      @bulk-action="onBulkAction"
    />

    <transition name="fade">
      <div
        v-if="toast"
        class="fixed bottom-6 right-6 card px-5 py-3 text-sm shadow-popover"
      >
        {{ toast }}
      </div>
    </transition>
  </div>
</template>
