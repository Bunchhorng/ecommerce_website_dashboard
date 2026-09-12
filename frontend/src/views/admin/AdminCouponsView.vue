<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { Plus } from 'lucide-vue-next'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import AdminDataTable from '@/components/admin/AdminDataTable.vue'
import type { TableColumn, TableRow } from '@/types'
import { adminApi } from '@/api/admin'
import type { AdminCoupon } from '@/api/admin'
import { formatPrice } from '@/utils/format'

const { t } = useI18n()
const router = useRouter()

const loading = ref(true)
const coupons = ref<AdminCoupon[]>([])
const totalCount = ref(0)

const columns = computed<TableColumn[]>(() => [
  { key: 'code', label: t('admin.coupons.column_code'), sortable: true },
  { key: 'type', label: t('admin.coupons.column_type'), type: 'badge' },
  { key: 'value', label: t('admin.coupons.column_value') },
  { key: 'minOrderAmount', label: t('admin.coupons.column_min_order'), type: 'currency', sortable: true },
  { key: 'usage', label: t('admin.coupons.column_usage') },
  { key: 'expiresAt', label: t('admin.coupons.column_expires'), type: 'date', sortable: true },
  { key: 'status', label: t('admin.coupons.column_status'), type: 'status' },
  { key: 'actions', label: '', type: 'actions' }
])

const rows = computed<TableRow[]>(() =>
  coupons.value.map((c) => ({
    id: c.id,
    code: c.code,
    type: c.type === 'percentage' ? '%' : t('admin.coupons.type_fixed'),
    value: c.type === 'percentage' ? `${c.value}%` : formatPrice(c.value),
    minOrderAmount: c.min_order_amount ?? 0,
    usage: c.usage_limit != null ? `${c.usage_limit}` : '∞',
    expiresAt: c.expires_at ?? '',
    status: c.is_active ? 'active' : 'draft'
  }))
)

async function loadCoupons() {
  loading.value = true
  try {
    const { data: resp } = await adminApi.listCoupons()
    coupons.value = resp.data
    totalCount.value = resp.meta.total
  } catch {
    showToast(t('admin.coupons.toast_load_error'))
  } finally {
    loading.value = false
  }
}

const toast = ref('')
let toastTimer: ReturnType<typeof setTimeout> | undefined
function showToast(msg: string) {
  toast.value = msg
  if (toastTimer) clearTimeout(toastTimer)
  toastTimer = setTimeout(() => {
    toast.value = ''
  }, 2500)
}

async function toggleStatus(id: string) {
  const c = coupons.value.find((x) => x.id === Number(id))
  if (!c) return
  try {
    await adminApi.updateCoupon(c.id, { is_active: !c.is_active })
    c.is_active = !c.is_active
    showToast(t('admin.coupons.toast_status_changed', { code: c.code, status: c.is_active ? t('status.active') : t('status.inactive') }))
  } catch {
    showToast(t('admin.coupons.toast_update_error'))
  }
}

function onRowAction(payload: { action: string; row: TableRow }) {
  const id = Number(payload.row.id)
  if (payload.action === 'edit') {
    router.push({ name: 'admin-coupon-edit', params: { id } })
  } else if (payload.action === 'toggle') {
    toggleStatus(String(id))
  } else if (payload.action === 'delete') {
    remove(String(id))
  }
}

async function remove(id: string) {
  const c = coupons.value.find((x) => x.id === Number(id))
  try {
    await adminApi.deleteCoupon(Number(id))
    coupons.value = coupons.value.filter((x) => x.id !== Number(id))
    totalCount.value--
    showToast(t('admin.coupons.toast_deleted', { code: c ? c.code : '' }))
  } catch {
    showToast(t('admin.coupons.toast_delete_error'))
  }
}

function onBulkAction(payload: { action: string; ids: string[] }) {
  if (payload.action === 'delete') {
    Promise.all(payload.ids.map((id) => adminApi.deleteCoupon(Number(id))))
      .then(() => {
        coupons.value = coupons.value.filter((c) => !payload.ids.includes(String(c.id)))
        totalCount.value -= payload.ids.length
        showToast(t('admin.coupons.toast_deleted_count', { count: payload.ids.length }))
      })
      .catch(() => {
        showToast(t('admin.coupons.toast_delete_error'))
      })
  }
}

onMounted(loadCoupons)
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold text-ink">{{ $t('admin.coupons.title') }}</h1>
        <span class="chip">{{ $t('admin.coupons.total_count', { count: totalCount }) }}</span>
      </div>
      <router-link :to="{ name: 'admin-coupon-create' }" class="btn-primary btn-sm w-fit">
        <Plus class="h-4 w-4" />
        {{ $t('admin.coupons.new_coupon') }}
      </router-link>
    </div>

    <AdminDataTable
      :columns="columns"
      :rows="rows"
      :loading="loading"
      :search-keys="['code']"
      :search-placeholder="$t('admin.coupons.search_placeholder')"
      :page-size="8"
      :bulk-actions="[{ label: $t('actions.delete'), value: 'delete' }]"
      :row-actions="[
        { label: $t('actions.edit'), value: 'edit' },
        { label: $t('admin.coupons.activate_deactivate'), value: 'toggle' },
        { label: $t('actions.delete'), value: 'delete' }
      ]"
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