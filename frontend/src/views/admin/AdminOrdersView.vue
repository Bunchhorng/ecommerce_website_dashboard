<script setup lang="ts">
import { computed, ref } from 'vue'
import AdminDataTable from '@/components/admin/AdminDataTable.vue'
import type { Order, TableColumn, TableRow } from '@/types'
import { ORDERS, PRODUCTS, ADDRESSES } from '@/data/mock'

const paymentLabels: Record<string, string> = {
  cod: 'COD',
  card: 'Card',
  bank: 'Bank Transfer',
  gateway: 'Online'
}

const syntheticOrders: Order[] = [
  {
    id: 's1',
    number: 'SV-2026-1101',
    items: [{ id: 's1-1', productId: 'p1', title: 'Aurora Wireless Headphones', brand: 'TechNova', image: PRODUCTS[0].images[0].url, unitPrice: 249.99, quantity: 1, variant: null }],
    subtotal: 249.99,
    discount: 0,
    shipping: 0,
    tax: 20,
    total: 269.99,
    status: 'Pending',
    placedAt: '2026-08-30T09:11:00',
    estimatedDelivery: '2026-09-06T17:00:00',
    trackingEvents: [{ status: 'Pending', at: '2026-08-30T09:11:00' }],
    shippingAddress: ADDRESSES[0],
    paymentMethod: 'card'
  },
  {
    id: 's2',
    number: 'SV-2026-1100',
    items: [{ id: 's2-1', productId: 'p4', title: 'AeroLite Running Shoes', brand: 'CloudStep', image: PRODUCTS[3].images[0].url, unitPrice: 139.99, quantity: 2, variant: null }],
    subtotal: 279.98,
    discount: 0,
    shipping: 9.99,
    tax: 23.2,
    total: 313.17,
    status: 'Confirmed',
    placedAt: '2026-08-29T16:40:00',
    estimatedDelivery: '2026-09-05T17:00:00',
    trackingEvents: [
      { status: 'Pending', at: '2026-08-29T16:40:00' },
      { status: 'Confirmed', at: '2026-08-29T16:52:00' }
    ],
    shippingAddress: ADDRESSES[1],
    paymentMethod: 'gateway'
  },
  {
    id: 's3',
    number: 'SV-2026-1098',
    items: [{ id: 's3-1', productId: 'p6', title: 'Meridian Slim-Fit Blazer', brand: 'Urban Threads', image: PRODUCTS[5].images[0].url, unitPrice: 189.99, quantity: 1, variant: null }],
    subtotal: 189.99,
    discount: 39.9,
    shipping: 0,
    tax: 12,
    total: 162.09,
    status: 'Processing',
    placedAt: '2026-08-28T11:25:00',
    estimatedDelivery: '2026-09-04T17:00:00',
    trackingEvents: [
      { status: 'Pending', at: '2026-08-28T11:25:00' },
      { status: 'Confirmed', at: '2026-08-28T11:33:00' },
      { status: 'Processing', at: '2026-08-28T15:00:00' }
    ],
    shippingAddress: ADDRESSES[2],
    paymentMethod: 'bank'
  },
  {
    id: 's4',
    number: 'SV-2026-1095',
    items: [{ id: 's4-1', productId: 'p8', title: 'Lumière Hydra Glow Serum', brand: 'Elara Beauty', image: PRODUCTS[7].images[0].url, unitPrice: 42, quantity: 3, variant: null }],
    subtotal: 126,
    discount: 0,
    shipping: 0,
    tax: 10.08,
    total: 136.08,
    status: 'Shipped',
    placedAt: '2026-08-27T09:02:00',
    estimatedDelivery: '2026-09-02T17:00:00',
    trackingEvents: [
      { status: 'Pending', at: '2026-08-27T09:02:00' },
      { status: 'Confirmed', at: '2026-08-27T09:15:00' },
      { status: 'Processing', at: '2026-08-27T13:00:00' },
      { status: 'Shipped', at: '2026-08-28T10:30:00' }
    ],
    shippingAddress: ADDRESSES[0],
    paymentMethod: 'cod'
  },
  {
    id: 's5',
    number: 'SV-2026-1090',
    items: [{ id: 's5-1', productId: 'p10', title: 'Orbit Everyday Backpack', brand: 'Northpeek', image: PRODUCTS[9].images[0].url, unitPrice: 119.99, quantity: 1, variant: null }],
    subtotal: 119.99,
    discount: 24,
    shipping: 0,
    tax: 7.68,
    total: 103.67,
    status: 'Delivered',
    placedAt: '2026-08-25T19:40:00',
    estimatedDelivery: '2026-09-01T17:00:00',
    trackingEvents: [
      { status: 'Pending', at: '2026-08-25T19:40:00' },
      { status: 'Confirmed', at: '2026-08-25T19:55:00' },
      { status: 'Processing', at: '2026-08-26T09:00:00' },
      { status: 'Shipped', at: '2026-08-27T11:00:00' },
      { status: 'Delivered', at: '2026-09-01T14:22:00' }
    ],
    shippingAddress: ADDRESSES[1],
    paymentMethod: 'card'
  },
  {
    id: 's6',
    number: 'SV-2026-1088',
    items: [{ id: 's6-1', productId: 'p12', title: 'Serene Wooden Desk Lamp', brand: 'Hearth & Home', image: PRODUCTS[11].images[0].url, unitPrice: 79.99, quantity: 1, variant: null }],
    subtotal: 79.99,
    discount: 16,
    shipping: 9.99,
    tax: 5.6,
    total: 79.58,
    status: 'Pending',
    placedAt: '2026-08-24T08:15:00',
    estimatedDelivery: '2026-09-03T17:00:00',
    trackingEvents: [{ status: 'Pending', at: '2026-08-24T08:15:00' }],
    shippingAddress: ADDRESSES[2],
    paymentMethod: 'bank'
  }
]

const allOrders: Order[] = [...ORDERS, ...syntheticOrders]

const columns: TableColumn[] = [
  { key: 'number', label: 'Order', sortable: true },
  { key: 'customer', label: 'Customer' },
  { key: 'items', label: 'Items' },
  { key: 'total', label: 'Total', type: 'currency', sortable: true },
  { key: 'payment', label: 'Payment', type: 'badge' },
  { key: 'status', label: 'Status', type: 'status', sortable: true },
  { key: 'date', label: 'Date', type: 'date', sortable: true },
  { key: 'actions', label: '', type: 'actions' }
]

const rows = computed<TableRow[]>(() =>
  allOrders.map((o) => ({
    id: o.id,
    number: o.number,
    customer: o.shippingAddress.fullName,
    items: o.items.length === 1 ? o.items[0].title : `${o.items.length} items`,
    total: o.total,
    payment: paymentLabels[o.paymentMethod] ?? o.paymentMethod,
    status: o.status,
    date: o.placedAt
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

function onRowAction(payload: { action: string; row: TableRow }) {
  if (payload.action === 'view') {
    showToast(`Opening invoice ${String(payload.row.number)}`)
  } else if (payload.action === 'process') {
    showToast(`Marked ${String(payload.row.number)} as Processing`)
  } else if (payload.action === 'cancel') {
    showToast(`Cancelled ${String(payload.row.number)}`)
  }
}

function onBulkAction(payload: { action: string; ids: string[] }) {
  if (payload.action === 'export') {
    showToast(`Exported ${payload.ids.length} orders to CSV`)
  } else if (payload.action === 'delivered') {
    showToast(`Marked ${payload.ids.length} orders as Delivered`)
  } else if (payload.action === 'print') {
    showToast(`Printing labels for ${payload.ids.length} orders`)
  }
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold text-ink">Orders</h1>
        <span class="chip">{{ allOrders.length }} total</span>
      </div>
    </div>

    <AdminDataTable
      :columns="columns"
      :rows="rows"
      :search-keys="['number', 'customer']"
      search-placeholder="Search orders…"
      :page-size="8"
      :bulk-actions="[{ label: 'Export CSV', value: 'export' }, { label: 'Mark delivered', value: 'delivered' }, { label: 'Print labels', value: 'print' }]"
      :row-actions="[{ label: 'View', value: 'view' }, { label: 'Mark as Processing', value: 'process' }, { label: 'Cancel', value: 'cancel' }]"
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
