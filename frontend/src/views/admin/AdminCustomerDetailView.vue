<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ArrowLeft, ChevronRight, ShoppingBag, CreditCard, Calendar } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import { adminApi } from '@/api/admin'
import type { AdminCustomerDetail } from '@/api/admin'
import StatusTag from '@/components/StatusTag.vue'
import { formatDate, formatPrice } from '@/utils/format'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()

const detail = ref<AdminCustomerDetail | null>(null)
const loading = ref(true)

function initials(name: string): string {
  return name
    .split(/\s+/)
    .map((p) => p.charAt(0))
    .slice(0, 2)
    .join('')
    .toUpperCase()
}

function capitalize(s: string): string {
  return s.charAt(0).toUpperCase() + s.slice(1)
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

async function loadCustomer() {
  const id = Number(route.params.id)
  if (!id) {
    router.replace({ name: 'admin-customers' })
    return
  }
  loading.value = true
  try {
    const { data } = await adminApi.getCustomer(id)
    detail.value = data
  } catch {
    showToast(t('admin.customer_detail.toast_load_error'))
    router.replace({ name: 'admin-customers' })
  } finally {
    loading.value = false
  }
}

onMounted(loadCustomer)
</script>

<template>
  <div class="space-y-6">
    <div v-if="loading" class="card p-10 text-center text-sm text-gray-500">
      {{ $t('common.loading') }}
    </div>

    <template v-else-if="detail">
      <div class="flex items-center gap-3">
        <button class="btn-icon" :title="$t('admin.customer_detail.back')" @click="router.push({ name: 'admin-customers' })">
          <ArrowLeft class="h-5 w-5" />
        </button>
        <div class="flex items-center gap-4">
          <div
            v-if="detail.user.avatar"
            class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-full bg-gray-200"
          >
            <img :src="detail.user.avatar" :alt="detail.user.name" class="h-full w-full object-cover" />
          </div>
          <div v-else class="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 text-lg font-semibold text-primary">
            {{ initials(detail.user.name) }}
          </div>
          <div>
            <h1 class="text-2xl font-bold text-ink">{{ detail.user.name }}</h1>
            <p class="mt-0.5 text-sm text-gray-500">
              {{ detail.user.email }}
              <span v-if="detail.user.phone" class="text-gray-400">· {{ detail.user.phone }}</span>
            </p>
          </div>
        </div>
      </div>

      <div class="grid gap-4 sm:grid-cols-3">
        <div class="card flex items-center gap-4 p-5">
          <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
            <ShoppingBag class="h-5 w-5" />
          </div>
          <div>
            <p class="text-2xl font-bold text-ink">{{ detail.orders_count }}</p>
            <p class="text-sm text-gray-500">{{ $t('admin.customer_detail.orders') }}</p>
          </div>
        </div>
        <div class="card flex items-center gap-4 p-5">
          <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600">
            <CreditCard class="h-5 w-5" />
          </div>
          <div>
            <p class="text-2xl font-bold text-ink">{{ formatPrice(detail.lifetime_spend) }}</p>
            <p class="text-sm text-gray-500">{{ $t('admin.customer_detail.lifetime_spend') }}</p>
          </div>
        </div>
        <div class="card flex items-center gap-4 p-5">
          <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600">
            <Calendar class="h-5 w-5" />
          </div>
          <div>
            <p class="text-2xl font-bold text-ink">{{ formatDate(detail.user.created_at) }}</p>
            <p class="text-sm text-gray-500">{{ $t('admin.customer_detail.joined') }}</p>
          </div>
        </div>
      </div>

      <div class="card overflow-hidden">
        <div class="border-b border-border-gray p-4">
          <h2 class="text-base font-semibold text-ink">{{ $t('admin.customer_detail.recent_orders') }}</h2>
        </div>
        <div v-if="detail.recent_orders.length" class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-border-gray bg-canvas/60 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                <th class="px-4 py-3">{{ $t('order.order') }}</th>
                <th class="px-4 py-3">{{ $t('order.date') }}</th>
                <th class="px-4 py-3 text-right">{{ $t('order.total') }}</th>
                <th class="px-4 py-3">{{ $t('admin.orders.payment_method') }}</th>
                <th class="px-4 py-3">{{ $t('order.status') }}</th>
                <th class="px-4 py-3 text-right">{{ $t('order.action') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border-gray">
              <tr v-for="o in detail.recent_orders" :key="o.id" class="hover:bg-canvas/40">
                <td class="px-4 py-3 font-mono text-xs text-ink">{{ o.order_number }}</td>
                <td class="px-4 py-3 text-gray-600">{{ formatDate(o.placed_at) }}</td>
                <td class="px-4 py-3 text-right font-semibold text-ink">{{ formatPrice(o.total) }}</td>
                <td class="px-4 py-3 text-gray-600">{{ capitalize(o.payment_status) }}</td>
                <td class="px-4 py-3">
                  <StatusTag :status="o.status" />
                </td>
                <td class="px-4 py-3 text-right">
                  <button
                    class="btn-secondary btn-xs"
                    :title="$t('admin.customer_detail.view_order')"
                    @click="router.push({ name: 'admin-order-detail', params: { id: o.id } })"
                  >
                    {{ $t('admin.customer_detail.view') }}
                    <ChevronRight class="h-3.5 w-3.5" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-else class="p-6 text-center text-sm text-gray-500">
          {{ $t('admin.customer_detail.no_orders') }}
        </p>
      </div>
    </template>

    <transition name="fade">
      <div v-if="toast" class="fixed bottom-6 right-6 card px-5 py-3 text-sm shadow-popover">
        {{ toast }}
      </div>
    </transition>
  </div>
</template>