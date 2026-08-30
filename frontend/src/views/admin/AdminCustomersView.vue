<script setup lang="ts">
import { computed, ref } from 'vue'
import AdminDataTable from '@/components/admin/AdminDataTable.vue'
import type { TableColumn, TableRow } from '@/types'
import { CUSTOMERS } from '@/data/mock'

const columns: TableColumn[] = [
  { key: 'avatar', label: 'Customer', type: 'image' },
  { key: 'name', label: '', sortable: true },
  { key: 'email', label: 'Email' },
  { key: 'orders', label: 'Orders', type: 'number', sortable: true },
  { key: 'totalSpent', label: 'Total Spent', type: 'currency', sortable: true },
  { key: 'status', label: 'Status', type: 'status' },
  { key: 'joinedAt', label: 'Joined', type: 'date', sortable: true },
  { key: 'actions', label: '', type: 'actions' }
]

const rows = computed<TableRow[]>(() =>
  CUSTOMERS.map((c) => ({
    id: c.id,
    avatar: c.avatar ?? '',
    name: c.name,
    email: c.email,
    orders: c.orders,
    totalSpent: c.totalSpent,
    status: c.status === 'active' ? 'Active' : 'Inactive',
    joinedAt: c.joinedAt
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
    showToast(`Viewing profile for ${String(payload.row.name)}`)
  } else if (payload.action === 'email') {
    showToast(`Email drafted to ${String(payload.row.name)}`)
  } else if (payload.action === 'block') {
    showToast(`${String(payload.row.name)} has been blocked`)
  }
}

function onBulkAction(payload: { action: string; ids: string[] }) {
  if (payload.action === 'export') {
    showToast(`Exported ${payload.ids.length} customers to CSV`)
  } else if (payload.action === 'segment') {
    showToast(`Added ${payload.ids.length} customers to segment`)
  }
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold text-ink">Customers</h1>
        <span class="chip">{{ CUSTOMERS.length }} total</span>
      </div>
    </div>

    <AdminDataTable
      :columns="columns"
      :rows="rows"
      :search-keys="['name', 'email']"
      search-placeholder="Search customers…"
      :page-size="8"
      :bulk-actions="[{ label: 'Export CSV', value: 'export' }, { label: 'Add to segment', value: 'segment' }]"
      :row-actions="[{ label: 'View profile', value: 'view' }, { label: 'Send email', value: 'email' }, { label: 'Block', value: 'block' }]"
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
