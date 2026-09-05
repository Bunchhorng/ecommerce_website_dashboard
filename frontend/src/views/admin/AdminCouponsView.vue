<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { Plus, X } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import AdminDataTable from '@/components/admin/AdminDataTable.vue'
import type { TableColumn, TableRow } from '@/types'
import { adminApi } from '@/api/admin'
import type { AdminCoupon } from '@/api/admin'
import { formatPrice } from '@/utils/format'

const { t } = useI18n()

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

const showCreateForm = ref(false)
const editingId = ref<number | null>(null)
const form = ref<{
  code: string
  type: 'percentage' | 'fixed'
  value: string
  minOrderAmount: string
  usageLimit: string
  expiresAt: string
}>({
  code: '',
  type: 'percentage',
  value: '',
  minOrderAmount: '',
  usageLimit: '',
  expiresAt: ''
})

function resetForm() {
  form.value = { code: '', type: 'percentage', value: '', minOrderAmount: '', usageLimit: '', expiresAt: '' }
  editingId.value = null
}

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

async function saveCoupon() {
  const value = Number(form.value.value)
  if (!form.value.code || !Number.isFinite(value)) {
    showToast(t('admin.coupons.toast_fill_required'))
    return
  }

  const payload = {
    code: form.value.code.toUpperCase(),
    type: form.value.type,
    value,
    min_order_amount: Number(form.value.minOrderAmount) || null,
    usage_limit: Number(form.value.usageLimit) || null,
    expires_at: form.value.expiresAt || null
  }

  try {
    if (editingId.value != null) {
      await adminApi.updateCoupon(editingId.value, payload)
      showToast(t('admin.coupons.toast_updated', { code: form.value.code }))
    } else {
      await adminApi.createCoupon(payload)
      showToast(t('admin.coupons.toast_created'))
    }
    await loadCoupons()
    resetForm()
    showCreateForm.value = false
  } catch {
    showToast(t('admin.coupons.toast_update_error'))
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
  const id = String(payload.row.id)
  if (payload.action === 'edit') {
    const c = coupons.value.find((x) => x.id === Number(id))
    if (!c) return
    editingId.value = c.id
    form.value = {
      code: c.code,
      type: c.type,
      value: String(c.value),
      minOrderAmount: c.min_order_amount != null ? String(c.min_order_amount) : '',
      usageLimit: c.usage_limit != null ? String(c.usage_limit) : '',
      expiresAt: c.expires_at ? c.expires_at.slice(0, 10) : ''
    }
    showCreateForm.value = true
  } else if (payload.action === 'toggle') {
    toggleStatus(id)
  } else if (payload.action === 'delete') {
    remove(id)
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
      <button class="btn-primary btn-sm" @click="showCreateForm = !showCreateForm; editingId = null">
        <Plus class="h-4 w-4" />
        {{ $t('admin.coupons.new_coupon') }}
      </button>
    </div>

    <div v-if="showCreateForm" class="card p-6">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold">{{ editingId != null ? $t('admin.coupons.edit_coupon') : $t('admin.coupons.create_coupon') }}</h2>
        <button class="btn-icon" type="button" @click="showCreateForm = false; resetForm()">
          <X class="h-5 w-5" />
        </button>
      </div>
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
          <label class="label" for="cp-code">{{ $t('admin.coupons.code_label') }}</label>
          <input id="cp-code" v-model="form.code" class="input" placeholder="SUMMER30" />
        </div>
        <div>
          <label class="label" for="cp-type">{{ $t('admin.coupons.type_label') }}</label>
          <select id="cp-type" v-model="form.type" class="select">
            <option value="percentage">{{ $t('admin.coupons.type_percentage') }}</option>
            <option value="fixed">{{ $t('admin.coupons.type_fixed_option') }}</option>
          </select>
        </div>
        <div>
          <label class="label" for="cp-value">{{ $t('admin.coupons.value_label') }}</label>
          <input id="cp-value" v-model="form.value" class="input" type="number" min="0" placeholder="10" />
        </div>
        <div>
          <label class="label" for="cp-min">{{ $t('admin.coupons.min_order_label') }}</label>
          <input id="cp-min" v-model="form.minOrderAmount" class="input" type="number" min="0" placeholder="50" />
        </div>
        <div>
          <label class="label" for="cp-limit">{{ $t('admin.coupons.usage_limit_label') }}</label>
          <input id="cp-limit" v-model="form.usageLimit" class="input" type="number" min="1" placeholder="1000" />
        </div>
        <div>
          <label class="label" for="cp-expires">{{ $t('admin.coupons.expires_label') }}</label>
          <input id="cp-expires" v-model="form.expiresAt" class="input" type="date" />
        </div>
      </div>
      <div class="mt-5 flex justify-end gap-2">
        <button class="btn-secondary btn-sm" type="button" @click="showCreateForm = false; resetForm()">{{ $t('actions.cancel') }}</button>
        <button class="btn-primary btn-sm" type="button" @click="saveCoupon">{{ editingId != null ? $t('actions.save_changes') : $t('admin.coupons.save_coupon') }}</button>
      </div>
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
