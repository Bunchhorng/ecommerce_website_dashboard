<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { Plus } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import AdminDataTable from '@/components/admin/AdminDataTable.vue'
import type { TableColumn, TableRow } from '@/types'
import { adminApi } from '@/api/admin'
import type { AdminProduct } from '@/api/admin'

const { t } = useI18n()
const router = useRouter()

const loading = ref(true)
const products = ref<AdminProduct[]>([])
const totalCount = ref(0)

const columns = computed<TableColumn[]>(() => [
  { key: 'image', label: t('admin.products.column_image'), type: 'image', sortable: true },
  { key: 'title', label: '', sortable: true },
  { key: 'brand', label: t('admin.products.column_brand') },
  { key: 'sku', label: t('product.sku') },
  { key: 'price', label: t('admin.products.column_price'), type: 'currency', sortable: true },
  { key: 'stock', label: t('admin.products.column_stock'), type: 'number', sortable: true },
  { key: 'status', label: t('admin.products.column_status'), type: 'status' },
  { key: 'actions', label: '', type: 'actions' }
])

const rows = computed<TableRow[]>(() =>
  products.value.map((p) => ({
    id: p.id,
    image: p.cover_image ?? '',
    title: p.name,
    brand: p.brand?.name ?? '—',
    category: p.category?.name ?? '—',
    sku: p.sku,
    price: p.price,
    stock: p.variants?.reduce((sum, v) => sum + v.available_quantity, 0) ?? 0,
    status: p.in_stock ? (p.variants?.some((v) => v.available_quantity <= 5) ? 'Low Stock' : 'In Stock') : 'Out of Stock'
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

async function loadProducts() {
  loading.value = true
  try {
    const { data: resp } = await adminApi.listProducts()
    products.value = resp.data
    totalCount.value = resp.meta.total
  } catch {
    showToast(t('admin.products.toast_load_error'))
  } finally {
    loading.value = false
  }
}

function onRowAction(payload: { action: string; row: TableRow }) {
  if (payload.action === 'edit') {
    const id = Number(payload.row.id)
    if (id) {
      router.push({ name: 'admin-product-edit', params: { id } })
    }
  } else if (payload.action === 'duplicate') {
    showToast(t('admin.products.toast_duplicated', { name: String(payload.row.title) }))
  } else if (payload.action === 'delete') {
    const id = Number(payload.row.id)
    adminApi.deleteProduct(id).then(() => {
      products.value = products.value.filter((p) => p.id !== id)
      totalCount.value--
      showToast(t('admin.products.toast_deleted', { name: String(payload.row.title) }))
    }).catch(() => {
      showToast(t('admin.products.toast_delete_error'))
    })
  }
}

function onBulkAction(payload: { action: string; ids: string[] }) {
  if (payload.action === 'delete') {
    Promise.all(payload.ids.map((id) => adminApi.deleteProduct(Number(id))))
      .then(() => {
        products.value = products.value.filter((p) => !payload.ids.includes(String(p.id)))
        totalCount.value -= payload.ids.length
        showToast(t('admin.products.toast_deleted_count', { count: payload.ids.length }))
      })
      .catch(() => {
        showToast(t('admin.products.toast_delete_error'))
      })
  } else if (payload.action === 'export') {
    showToast(t('admin.products.toast_exported_csv', { count: payload.ids.length }))
  }
}

onMounted(loadProducts)
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold text-ink">{{ $t('admin.products.title') }}</h1>
        <span class="chip">{{ $t('admin.products.total_count', { count: totalCount }) }}</span>
      </div>
      <router-link :to="{ name: 'admin-product-create' }" class="btn-primary btn-sm">
        <Plus class="h-4 w-4" />
        {{ $t('admin.products.add_product') }}
      </router-link>
    </div>

    <AdminDataTable
      :columns="columns"
      :rows="rows"
      :loading="loading"
      :search-keys="['title', 'brand', 'sku']"
      :search-placeholder="$t('admin.products.search_placeholder')"
      :page-size="8"
      :bulk-actions="[{ label: $t('actions.delete'), value: 'delete' }, { label: $t('admin.products.export_csv'), value: 'export' }]"
      :row-actions="[{ label: $t('actions.edit'), value: 'edit' }, { label: $t('admin.products.duplicate'), value: 'duplicate' }, { label: $t('actions.delete'), value: 'delete' }]"
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
