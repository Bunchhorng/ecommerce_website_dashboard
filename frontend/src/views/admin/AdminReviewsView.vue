<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import AdminDataTable from '@/components/admin/AdminDataTable.vue'
import type { TableColumn, TableRow } from '@/types'
import { adminApi } from '@/api/admin'
import type { AdminReview } from '@/api/admin'

const { t } = useI18n()

type Tab = 'pending' | 'approved'

const loading = ref(true)
const activeTab = ref<Tab>('pending')
const pending = ref<AdminReview[]>([])
const approved = ref<AdminReview[]>([])

const columns = computed<TableColumn[]>(() => [
  { key: 'author', label: t('admin.reviews.customer') },
  { key: 'product', label: t('admin.reviews.product') },
  { key: 'rating', label: t('admin.reviews.rating'), type: 'number' },
  { key: 'title', label: t('admin.reviews.review') },
  { key: 'status', label: t('order.status'), type: 'status' },
  { key: 'date', label: t('order.date'), type: 'date' },
  { key: 'actions', label: '', type: 'actions' }
])

const bulkActions = computed(() =>
  activeTab.value === 'pending'
    ? [{ label: t('admin.reviews.bulk.approve_selected'), value: 'approve' }, { label: t('actions.delete'), value: 'delete' }]
    : []
)

const rowActions = computed(() =>
  activeTab.value === 'pending'
    ? [{ label: t('status.approved'), value: 'approve' }, { label: t('admin.reviews.row.reject'), value: 'reject' }]
    : []
)

function capitalize(s: string): string {
  return s.charAt(0).toUpperCase() + s.slice(1)
}

function toRow(r: AdminReview): TableRow {
  return {
    id: r.id,
    author: r.user.name,
    product: r.product.name ?? t('admin.reviews.unknown_product'),
    rating: r.rating,
    title: r.title ?? '',
    status: capitalize(r.status),
    date: r.created_at
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

async function loadReviews(status?: string) {
  loading.value = true
  try {
    const params = status ? { status } : {}
    const { data: resp } = await adminApi.listReviews(params)
    return resp.data
  } catch {
    showToast(t('admin.reviews.toast.load_error'))
    return []
  } finally {
    loading.value = false
  }
}

async function loadAllReviews() {
  const [pendingData, approvedData] = await Promise.all([
    loadReviews('pending'),
    loadReviews('approved')
  ])
  pending.value = pendingData
  approved.value = approvedData
}

async function approveReview(id: number) {
  try {
    const { data: resp } = await adminApi.approveReview(id)
    const review = resp.data
    pending.value = pending.value.filter((r) => r.id !== id)
    approved.value.unshift(review)
    showToast(t('admin.reviews.toast.approved_review', { author: review.user.name }))
  } catch {
    showToast(t('admin.reviews.toast.approve_error'))
  }
}

async function rejectReview(id: number) {
  try {
    await adminApi.rejectReview(id)
    const review = pending.value.find((r) => r.id === id)
    pending.value = pending.value.filter((r) => r.id !== id)
    if (review) {
      showToast(t('admin.reviews.toast.rejected_review', { author: review.user.name }))
    }
  } catch {
    showToast(t('admin.reviews.toast.reject_error'))
  }
}

function onRowAction(payload: { action: string; row: TableRow }) {
  const id = Number(payload.row.id)
  if (payload.action === 'approve') approveReview(id)
  else if (payload.action === 'reject') rejectReview(id)
}

function onBulkAction(payload: { action: string; ids: string[] }) {
  if (payload.action === 'approve') {
    Promise.all(payload.ids.map((id) => approveReview(Number(id))))
      .then(() => {
        showToast(t('admin.reviews.toast.approved_count', { count: payload.ids.length }))
      })
  } else if (payload.action === 'delete') {
    showToast(t('admin.reviews.toast.deleted_count', { count: payload.ids.length }))
  }
}

onMounted(loadAllReviews)
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-ink">{{ $t('admin.reviews.title') }}</h1>
      <p class="mt-1 text-sm text-gray-500">
        {{ t('admin.reviews.awaiting_moderation', pendingCount) }}
      </p>
    </div>

    <div class="card flex w-fit items-center gap-1 p-1">
      <button
        class="rounded-lg px-4 py-2 text-sm font-semibold transition"
        :class="activeTab === 'pending' ? 'bg-primary text-white' : 'text-gray-600 hover:text-ink'"
        @click="activeTab = 'pending'"
      >
        {{ $t('status.pending') }} ({{ pendingCount }})
      </button>
      <button
        class="rounded-lg px-4 py-2 text-sm font-semibold transition"
        :class="activeTab === 'approved' ? 'bg-primary text-white' : 'text-gray-600 hover:text-ink'"
        @click="activeTab = 'approved'"
      >
        {{ $t('status.approved') }} ({{ approved.length }})
      </button>
    </div>

    <AdminDataTable
      :columns="columns"
      :rows="activeRows"
      :loading="loading"
      :search-keys="['author', 'product', 'title']"
      :search-placeholder="t('admin.reviews.search_placeholder')"
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
