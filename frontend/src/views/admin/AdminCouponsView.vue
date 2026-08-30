<script setup lang="ts">
import { computed, ref } from 'vue'
import { Plus, X } from 'lucide-vue-next'
import AdminDataTable from '@/components/admin/AdminDataTable.vue'
import type { AdminCoupon, TableColumn, TableRow } from '@/types'
import { ADMIN_COUPONS } from '@/data/mock'
import { formatPrice, randomId } from '@/utils/format'

const coupons = ref<AdminCoupon[]>([...ADMIN_COUPONS])

const columns: TableColumn[] = [
  { key: 'code', label: 'Code', sortable: true },
  { key: 'type', label: 'Type', type: 'badge' },
  { key: 'value', label: 'Value' },
  { key: 'minOrderAmount', label: 'Min Order', type: 'currency', sortable: true },
  { key: 'usage', label: 'Usage' },
  { key: 'expiresAt', label: 'Expires', type: 'date', sortable: true },
  { key: 'status', label: 'Status', type: 'status' },
  { key: 'actions', label: '', type: 'actions' }
]

const rows = computed<TableRow[]>(() =>
  coupons.value.map((c) => ({
    id: c.id,
    code: c.code,
    type: c.type === 'percentage' ? '%' : 'Fixed',
    value: c.type === 'percentage' ? `${c.value}%` : formatPrice(c.value),
    minOrderAmount: c.minOrderAmount,
    usage: `${c.usedCount} / ${c.usageLimit}`,
    expiresAt: c.expiresAt,
    status: c.status
  }))
)

const showCreateForm = ref(false)
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

function createCoupon() {
  const value = Number(form.value.value)
  if (!form.value.code || !Number.isFinite(value)) {
    showToast('Please fill in the coupon code and value')
    return
  }
  coupons.value.push({
    id: randomId('ac'),
    code: form.value.code.toUpperCase(),
    type: form.value.type,
    value,
    minOrderAmount: Number(form.value.minOrderAmount) || 0,
    usageLimit: Number(form.value.usageLimit) || 1,
    usedCount: 0,
    expiresAt: form.value.expiresAt || '2026-12-31',
    status: 'active'
  })
  form.value = { code: '', type: 'percentage', value: '', minOrderAmount: '', usageLimit: '', expiresAt: '' }
  showCreateForm.value = false
  showToast('Coupon created')
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

function toggleStatus(id: string) {
  const c = coupons.value.find((x) => x.id === id)
  if (!c) return
  c.status = c.status === 'active' ? 'draft' : 'active'
  showToast(`${c.code} is now ${c.status === 'active' ? 'active' : 'inactive'}`)
}

function duplicate(id: string) {
  const c = coupons.value.find((x) => x.id === id)
  if (!c) return
  coupons.value.push({
    ...c,
    id: randomId('ac'),
    code: `${c.code}-COPY`,
    status: 'draft',
    usedCount: 0
  })
  showToast(`Duplicated ${c.code}`)
}

function remove(id: string) {
  const c = coupons.value.find((x) => x.id === id)
  coupons.value = coupons.value.filter((x) => x.id !== id)
  showToast(`Deleted coupon ${c ? c.code : ''}`)
}

function onRowAction(payload: { action: string; row: TableRow }) {
  const id = String(payload.row.id)
  if (payload.action === 'edit') {
    showToast(`Editing ${String(payload.row.code)}`)
  } else if (payload.action === 'duplicate') {
    duplicate(id)
  } else if (payload.action === 'toggle') {
    toggleStatus(id)
  } else if (payload.action === 'delete') {
    remove(id)
  }
}

function onBulkAction(payload: { action: string; ids: string[] }) {
  if (payload.action === 'delete') {
    coupons.value = coupons.value.filter((c) => !payload.ids.includes(c.id))
    showToast(`Deleted ${payload.ids.length} coupons`)
  } else if (payload.action === 'export') {
    showToast(`Exported ${payload.ids.length} coupons to CSV`)
  }
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold text-ink">Coupons</h1>
        <span class="chip">{{ coupons.length }} total</span>
      </div>
      <button class="btn-primary btn-sm" @click="showCreateForm = !showCreateForm">
        <Plus class="h-4 w-4" />
        New Coupon
      </button>
    </div>

    <div v-if="showCreateForm" class="card p-6">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold">Create Coupon</h2>
        <button class="btn-icon" type="button" @click="showCreateForm = false">
          <X class="h-5 w-5" />
        </button>
      </div>
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
          <label class="label" for="cp-code">Code</label>
          <input id="cp-code" v-model="form.code" class="input" placeholder="SUMMER30" />
        </div>
        <div>
          <label class="label" for="cp-type">Type</label>
          <select id="cp-type" v-model="form.type" class="select">
            <option value="percentage">Percentage (%)</option>
            <option value="fixed">Fixed ($)</option>
          </select>
        </div>
        <div>
          <label class="label" for="cp-value">Value</label>
          <input id="cp-value" v-model="form.value" class="input" type="number" min="0" placeholder="10" />
        </div>
        <div>
          <label class="label" for="cp-min">Min order amount</label>
          <input id="cp-min" v-model="form.minOrderAmount" class="input" type="number" min="0" placeholder="50" />
        </div>
        <div>
          <label class="label" for="cp-limit">Usage limit</label>
          <input id="cp-limit" v-model="form.usageLimit" class="input" type="number" min="1" placeholder="1000" />
        </div>
        <div>
          <label class="label" for="cp-expires">Expires</label>
          <input id="cp-expires" v-model="form.expiresAt" class="input" type="date" />
        </div>
      </div>
      <div class="mt-5 flex justify-end gap-2">
        <button class="btn-secondary btn-sm" type="button" @click="showCreateForm = false">Cancel</button>
        <button class="btn-primary btn-sm" type="button" @click="createCoupon">Save Coupon</button>
      </div>
    </div>

    <AdminDataTable
      :columns="columns"
      :rows="rows"
      :search-keys="['code']"
      search-placeholder="Search coupons…"
      :page-size="8"
      :bulk-actions="[{ label: 'Delete', value: 'delete' }, { label: 'Export', value: 'export' }]"
      :row-actions="[
        { label: 'Edit', value: 'edit' },
        { label: 'Duplicate', value: 'duplicate' },
        { label: 'Activate/Deactivate', value: 'toggle' },
        { label: 'Delete', value: 'delete' }
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
