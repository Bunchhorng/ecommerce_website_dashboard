<script setup lang="ts">
import { computed, ref } from 'vue'
import AdminDataTable from '@/components/admin/AdminDataTable.vue'
import type { Review, TableColumn, TableRow } from '@/types'
import { REVIEWS, PENDING_REVIEWS, getProductById } from '@/data/mock'

type Tab = 'pending' | 'approved'

const activeTab = ref<Tab>('pending')
const pending = ref<Review[]>([...PENDING_REVIEWS])
const approved = ref<Review[]>([...REVIEWS.filter((r) => r.status === 'approved')])

const columns: TableColumn[] = [
  { key: 'author', label: 'Customer' },
  { key: 'product', label: 'Product' },
  { key: 'rating', label: 'Rating', type: 'number' },
  { key: 'title', label: 'Review' },
  { key: 'status', label: 'Status', type: 'status' },
  { key: 'date', label: 'Date', type: 'date' },
  { key: 'actions', label: '', type: 'actions' }
]

function toRow(r: Review): TableRow {
  return {
    id: r.id,
    author: r.author,
    product: getProductById(r.productId)?.title ?? 'Unknown product',
    rating: r.rating,
    title: r.title,
    status: r.status === 'approved' ? 'Approved' : r.status === 'pending' ? 'Pending' : 'Rejected',
    date: r.date
  }
}

const activeRows = computed<TableRow[]>(() =>
  activeTab.value === 'pending' ? pending.value.map(toRow) : approved.value.map(toRow)
)

const pendingCount = computed(() => pending.value.length)

const toast = ref('')
let toastTimer: ReturnType<typeof setTimeout> | undefined
function showToast(msg: string) {
  toast.value = msg
  if (toastTimer) clearTimeout(toastTimer)
  toastTimer = setTimeout(() => {
    toast.value = ''
  }, 2500)
}

function approve(r: Review) {
  r.status = 'approved'
  pending.value = pending.value.filter((x) => x.id !== r.id)
  approved.value.unshift({ ...r })
  showToast(`Approved review by ${r.author}`)
}

function reject(r: Review) {
  r.status = 'rejected'
  pending.value = pending.value.filter((x) => x.id !== r.id)
  showToast(`Rejected review by ${r.author}`)
}

function onRowAction(payload: { action: string; row: TableRow }) {
  const id = String(payload.row.id)
  const r = pending.value.find((x) => x.id === id)
  if (!r) return
  if (payload.action === 'approve') approve(r)
  else if (payload.action === 'reject') reject(r)
}

function onBulkAction(payload: { action: string; ids: string[] }) {
  if (payload.action === 'approve') {
    pending.value.forEach((r) => {
      if (payload.ids.includes(r.id)) {
        r.status = 'approved'
        approved.value.unshift({ ...r })
      }
    })
    pending.value = pending.value.filter((r) => !payload.ids.includes(r.id))
    showToast(`Approved ${payload.ids.length} reviews`)
  } else if (payload.action === 'delete') {
    pending.value = pending.value.filter((r) => !payload.ids.includes(r.id))
    showToast(`Deleted ${payload.ids.length} reviews`)
  }
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-ink">Review Moderation</h1>
      <p class="mt-1 text-sm text-gray-500">
        {{ pendingCount }} review{{ pendingCount === 1 ? '' : 's' }} awaiting moderation
      </p>
    </div>

    <div class="card flex w-fit items-center gap-1 p-1">
      <button
        class="rounded-lg px-4 py-2 text-sm font-semibold transition"
        :class="activeTab === 'pending' ? 'bg-primary text-white' : 'text-gray-600 hover:text-ink'"
        @click="activeTab = 'pending'"
      >
        Pending ({{ pendingCount }})
      </button>
      <button
        class="rounded-lg px-4 py-2 text-sm font-semibold transition"
        :class="activeTab === 'approved' ? 'bg-primary text-white' : 'text-gray-600 hover:text-ink'"
        @click="activeTab = 'approved'"
      >
        Approved ({{ approved.length }})
      </button>
    </div>

    <AdminDataTable
      :columns="columns"
      :rows="activeRows"
      :search-keys="['author', 'product', 'title']"
      search-placeholder="Search reviews…"
      :page-size="8"
      :bulk-actions="activeTab === 'pending' ? [{ label: 'Approve selected', value: 'approve' }, { label: 'Delete', value: 'delete' }] : []"
      :row-actions="activeTab === 'pending' ? [{ label: 'Approve', value: 'approve' }, { label: 'Reject', value: 'reject' }] : []"
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
