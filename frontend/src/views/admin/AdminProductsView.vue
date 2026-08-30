<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { Plus } from 'lucide-vue-next'
import AdminDataTable from '@/components/admin/AdminDataTable.vue'
import type { TableColumn, TableRow } from '@/types'
import { PRODUCTS } from '@/data/mock'

const router = useRouter()

const columns: TableColumn[] = [
  { key: 'image', label: 'Product', type: 'image', sortable: true },
  { key: 'title', label: '', sortable: true },
  { key: 'brand', label: 'Brand' },
  { key: 'sku', label: 'SKU' },
  { key: 'price', label: 'Price', type: 'currency', sortable: true },
  { key: 'stock', label: 'Stock', type: 'number', sortable: true },
  { key: 'status', label: 'Status', type: 'status' },
  { key: 'actions', label: '', type: 'actions' }
]

const rows = computed<TableRow[]>(() =>
  PRODUCTS.map((p) => ({
    id: p.id,
    image: p.images[0].url,
    title: p.title,
    brand: p.brand.name,
    category: p.category.name,
    sku: p.sku,
    price: p.price,
    stock: p.stockQuantity,
    status: p.isInStock ? (p.stockQuantity <= 5 ? 'Low Stock' : 'In Stock') : 'Out of Stock'
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
  if (payload.action === 'edit') {
    router.push({ name: 'admin-product-create' })
  } else if (payload.action === 'duplicate') {
    showToast(`Duplicated "${String(payload.row.title)}"`)
  } else if (payload.action === 'delete') {
    showToast(`Deleted "${String(payload.row.title)}"`)
  }
}

function onBulkAction(payload: { action: string; ids: string[] }) {
  if (payload.action === 'delete') {
    showToast(`Deleted ${payload.ids.length} products`)
  } else if (payload.action === 'export') {
    showToast(`Exported ${payload.ids.length} products to CSV`)
  }
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold text-ink">Products</h1>
        <span class="chip">{{ PRODUCTS.length }} total</span>
      </div>
      <router-link :to="{ name: 'admin-product-create' }" class="btn-primary btn-sm">
        <Plus class="h-4 w-4" />
        Add Product
      </router-link>
    </div>

    <AdminDataTable
      :columns="columns"
      :rows="rows"
      :search-keys="['title', 'brand', 'sku']"
      search-placeholder="Search products…"
      :page-size="8"
      :bulk-actions="[{ label: 'Delete', value: 'delete' }, { label: 'Export CSV', value: 'export' }]"
      :row-actions="[{ label: 'Edit', value: 'edit' }, { label: 'Duplicate', value: 'duplicate' }, { label: 'Delete', value: 'delete' }]"
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
