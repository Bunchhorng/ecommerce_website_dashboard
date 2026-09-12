<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { Plus, Pencil, Trash2 } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import { adminApi } from '@/api/admin'
import type { AdminBrand } from '@/api/admin'

const { t } = useI18n()

const loading = ref(true)
const brands = ref<AdminBrand[]>([])

async function loadBrands() {
  loading.value = true
  try {
    const { data: resp } = await adminApi.listBrands()
    brands.value = resp.data
  } catch {
    showToast(t('admin.brands.toast_load_error'))
  } finally {
    loading.value = false
  }
}

async function removeBrand(id: number) {
  const b = brands.value.find((x) => x.id === id)
  try {
    await adminApi.deleteBrand(id)
    brands.value = brands.value.filter((x) => x.id !== id)
    showToast(t('admin.brands.toast_deleted', { name: b ? b.name : '' }))
  } catch {
    showToast(t('admin.brands.toast_delete_error'))
  }
}

function initials(name: string): string {
  return name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((w) => w[0])
    .join('')
    .toUpperCase()
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

onMounted(loadBrands)
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold text-ink">{{ $t('admin.brands.title') }}</h1>
        <span class="chip">{{ $t('admin.brands.total_count', { count: brands.length }) }}</span>
      </div>
      <router-link :to="{ name: 'admin-brand-create' }" class="btn-primary btn-sm w-fit">
        <Plus class="h-4 w-4" />
        {{ $t('admin.brands.add_brand') }}
      </router-link>
    </div>

    <div v-if="loading" class="card p-10 text-center">
      <p class="text-sm text-gray-500 dark:text-muted">{{ $t('common.loading') }}</p>
    </div>

    <div v-else-if="brands.length" class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
      <div v-for="b in brands" :key="b.id" class="card p-5">
        <div class="flex items-start gap-4">
          <div v-if="b.logo" class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-canvas">
            <img :src="b.logo" :alt="b.name" class="h-full w-full object-cover" />
          </div>
          <div v-else class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-bold text-primary">
            {{ initials(b.name) }}
          </div>
          <div class="min-w-0 flex-1">
            <div class="truncate text-base font-semibold text-ink">{{ b.name }}</div>
            <div class="mt-1 flex flex-wrap items-center gap-2">
              <span class="chip">/{{ b.slug }}</span>
              <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-600">
                {{ b.is_active ? $t('status.active') : $t('status.inactive') }}
              </span>
            </div>
          </div>
        </div>
        <div class="mt-4 flex items-center justify-between border-t border-border-gray pt-4">
          <span class="text-sm text-gray-500">
            {{ $t('admin.brands.products_count', { count: b.products_count ?? 0 }) }}
          </span>
          <div class="flex gap-1">
            <router-link class="btn-icon h-9 w-9" type="button" :title="$t('actions.edit')" :to="{ name: 'admin-brand-edit', params: { id: b.id } }">
              <Pencil class="h-4 w-4" />
            </router-link>
            <button class="btn-icon h-9 w-9 hover:text-red-600" type="button" :title="$t('actions.delete')" @click="removeBrand(b.id)">
              <Trash2 class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

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