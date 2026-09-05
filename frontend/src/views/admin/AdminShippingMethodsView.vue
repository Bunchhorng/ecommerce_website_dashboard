<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { Truck, Pencil, Save } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import { adminApi } from '@/api/admin'
import type { AdminShippingMethod } from '@/api/admin'
import { formatPrice } from '@/utils/format'

const { t } = useI18n()

const loading = ref(true)
const methods = ref<AdminShippingMethod[]>([])

const editingId = ref<number | null>(null)

async function loadMethods() {
  loading.value = true
  try {
    const { data: resp } = await adminApi.listShippingMethods()
    methods.value = resp.data
  } catch {
    showToast(t('admin.shipping.toast_load_error'))
  } finally {
    loading.value = false
  }
}

function startEdit(id: number) {
  editingId.value = id
}

function cancelEdit() {
  editingId.value = null
}

async function saveEdit(m: AdminShippingMethod) {
  try {
    await adminApi.updateShippingMethod(m.id, {
      name: m.name,
      description: m.description,
      price: m.price,
      estimated_days_min: m.estimated_days_min,
      estimated_days_max: m.estimated_days_max,
      is_active: m.is_active
    })
    editingId.value = null
    showToast(t('admin.shipping.toast_updated', { name: m.name }))
  } catch {
    showToast(t('admin.shipping.toast_update_error'))
  }
}

async function toggleEnabled(m: AdminShippingMethod) {
  const newState = !m.is_active
  try {
    await adminApi.updateShippingMethod(m.id, { is_active: newState })
    m.is_active = newState
  } catch {
    showToast(t('admin.shipping.toast_update_error'))
  }
}

async function addMethod() {
  try {
    await adminApi.createShippingMethod({
      name: 'New Method',
      code: 'new-method',
      description: '',
      price: 0,
      estimated_days_min: 1,
      estimated_days_max: 5,
      is_active: true
    })
    await loadMethods()
    showToast(t('admin.shipping.toast_added'))
  } catch {
    showToast(t('admin.shipping.toast_add_error'))
  }
}

async function removeMethod(m: AdminShippingMethod) {
  try {
    await adminApi.deleteShippingMethod(m.id)
    methods.value = methods.value.filter((x) => x.id !== m.id)
    showToast(t('admin.shipping.toast_deleted', { name: m.name }))
  } catch {
    showToast(t('admin.shipping.toast_delete_error'))
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

onMounted(loadMethods)
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold text-ink">{{ $t('admin.shipping.title') }}</h1>
        <span class="chip">{{ $t('admin.shipping.total_count', { count: methods.length }) }}</span>
      </div>
      <button class="btn-primary btn-sm" @click="addMethod()">
        <Truck class="h-4 w-4" />
        {{ $t('admin.shipping.add_method') }}
      </button>
    </div>

    <div class="grid gap-5 lg:grid-cols-3">
      <div v-for="m in methods" :key="m.id" class="card flex flex-col p-5">
        <div class="flex items-start justify-between gap-3">
          <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-primary/10 text-primary">
            <Truck class="h-5 w-5" />
          </div>
          <button
            type="button"
            role="switch"
            :aria-checked="m.is_active"
            :aria-label="$t('admin.shipping.toggle_aria_label')"
            class="relative h-6 w-11 shrink-0 rounded-full transition-colors"
            :class="m.is_active ? 'bg-success' : 'bg-gray-200 dark:bg-surface-hover'"
            @click="toggleEnabled(m)"
          >
            <span
              class="absolute top-0.5 h-5 w-5 rounded-full bg-white shadow transition-all"
              :class="m.is_active ? 'left-[22px]' : 'left-0.5'"
            ></span>
          </button>
        </div>

        <template v-if="editingId === m.id">
          <div class="mt-4 space-y-3">
            <div>
              <label class="label" :for="`sm-name-${m.id}`">{{ $t('admin.shipping.name_label') }}</label>
              <input :id="`sm-name-${m.id}`" v-model="m.name" class="input" />
            </div>
            <div>
              <label class="label" :for="`sm-price-${m.id}`">{{ $t('admin.shipping.price_label') }}</label>
              <input :id="`sm-price-${m.id}`" v-model.number="m.price" class="input" type="number" min="0" step="0.01" />
            </div>
            <div>
              <label class="label" :for="`sm-eta-${m.id}`">{{ $t('admin.shipping.eta_label') }}</label>
              <input :id="`sm-eta-${m.id}`" v-model.number="m.estimated_days_max" class="input" type="number" min="1" />
            </div>
            <div>
              <label class="label" :for="`sm-desc-${m.id}`">{{ $t('admin.shipping.description_label') }}</label>
              <input :id="`sm-desc-${m.id}`" v-model="m.description" class="input" />
            </div>
          </div>
          <div class="mt-4 flex justify-end gap-2">
            <button class="btn-secondary btn-sm" type="button" @click="cancelEdit()">{{ $t('actions.cancel') }}</button>
            <button class="btn-primary btn-sm" type="button" @click="saveEdit(m)">
              <Save class="h-4 w-4" />
              {{ $t('admin.shipping.save') }}
            </button>
          </div>
        </template>

        <template v-else>
          <h3 class="mt-4 text-base font-semibold text-ink">{{ m.name }}</h3>
          <p class="mt-1 text-sm text-gray-500">{{ m.description }}</p>

          <div class="mt-4 flex flex-wrap items-center gap-2">
            <span class="chip">{{ $t('admin.shipping.days', { count: m.estimated_days_max ?? 0 }) }}</span>
            <span
              v-if="m.price === 0"
              class="inline-flex items-center rounded-full bg-accent/20 px-3 py-1 text-xs font-semibold text-amber-700"
            >
              {{ $t('admin.shipping.free') }}
            </span>
            <span v-else class="text-sm font-semibold text-ink">{{ formatPrice(m.price) }}</span>
          </div>

          <div class="mt-auto flex items-center justify-between border-t border-border-gray pt-4">
            <span class="text-xs font-medium" :class="m.is_active ? 'text-emerald-600' : 'text-gray-400'">
              {{ m.is_active ? $t('admin.shipping.enabled') : $t('admin.shipping.disabled') }}
            </span>
            <button class="btn-secondary btn-sm" type="button" @click="startEdit(m.id)">
              <Pencil class="h-4 w-4" />
              {{ $t('actions.edit') }}
            </button>
          </div>
        </template>
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
