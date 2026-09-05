<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import AdminDataTable from '@/components/admin/AdminDataTable.vue'
import type { TableColumn, TableRow } from '@/types'
import { adminApi } from '@/api/admin'
import type { AdminCustomer } from '@/api/admin'

const { t } = useI18n()
const router = useRouter()

const loading = ref(true)
const customers = ref<AdminCustomer[]>([])
const totalCount = ref(0)

const columns = computed<TableColumn[]>(() => [
  { key: 'avatar', label: t('admin.customers.customer'), type: 'image' },
  { key: 'name', label: '', sortable: true },
  { key: 'email', label: t('admin.customers.email') },
  { key: 'status', label: t('order.status'), type: 'status' },
  { key: 'joinedAt', label: t('admin.customers.joined'), type: 'date', sortable: true },
  { key: 'actions', label: '', type: 'actions' }
])

const bulkActions = computed(() => [])

const rowActions = computed(() => [
  { label: t('admin.customers.row.view_profile'), value: 'view' },
  { label: t('admin.customers.row.send_email'), value: 'email' }
])

function capitalize(s: string): string {
  return s.charAt(0).toUpperCase() + s.slice(1)
}

const rows = computed<TableRow[]>(() =>
  customers.value.map((c) => ({
    id: c.id,
    avatar: c.avatar ?? '',
    name: c.name,
    email: c.email,
    status: capitalize(c.role),
    joinedAt: c.created_at
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

async function loadCustomers(params: { q?: string } = {}) {
  loading.value = true
  try {
    const { data: resp } = await adminApi.listCustomers(params)
    customers.value = resp.data
    totalCount.value = resp.meta.total
  } catch {
    showToast(t('admin.customers.toast.load_error'))
  } finally {
    loading.value = false
  }
}

function onRowAction(payload: { action: string; row: TableRow }) {
  if (payload.action === 'view') {
    const id = Number(payload.row.id)
    if (id) {
      router.push({ name: 'admin-customer-detail', params: { id } })
      return
    }
    showToast(t('admin.customers.toast.viewing_profile', { name: String(payload.row.name) }))
  } else if (payload.action === 'email') {
    const email = String(payload.row.email ?? '')
    if (email) window.location.href = `mailto:${email}`
  }
}

function onBulkAction(_payload: { action: string; ids: string[] }) {
  /* no customer bulk endpoints available */
}

onMounted(() => loadCustomers())
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold text-ink">{{ $t('admin.customers.title') }}</h1>
        <span class="chip">{{ totalCount }} {{ $t('admin.customers.total') }}</span>
      </div>
    </div>

    <AdminDataTable
      :columns="columns"
      :rows="rows"
      :loading="loading"
      :search-keys="['name', 'email']"
      :search-placeholder="t('admin.customers.search_placeholder')"
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
