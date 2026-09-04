<script setup lang="ts">
import { ref } from 'vue'
import { FileText, FileSpreadsheet, FileBarChart2 } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import { adminApi } from '@/api/admin'
import { downloadResponse } from '@/utils/download'

const { t } = useI18n()

const status = ref('all')
const from = ref('')
const to = ref('')
const downloading = ref('')

const statusOptions = [
  { value: 'all', labelKey: 'admin.reports.all_statuses' },
  { value: 'pending', labelKey: 'status.pending' },
  { value: 'confirmed', labelKey: 'status.confirmed' },
  { value: 'processing', labelKey: 'status.processing' },
  { value: 'shipped', labelKey: 'status.shipped' },
  { value: 'delivered', labelKey: 'status.delivered' },
  { value: 'cancelled', labelKey: 'status.cancelled' },
  { value: 'refunded', labelKey: 'status.refunded' }
]

const toast = ref('')
let toastTimer: ReturnType<typeof setTimeout> | undefined
function showToast(msg: string) {
  toast.value = msg
  if (toastTimer) clearTimeout(toastTimer)
  toastTimer = setTimeout(() => {
    toast.value = ''
  }, 2500)
}

function selectedStatusLabel(): string {
  const opt = statusOptions.find((o) => o.value === status.value)
  return opt ? t(opt.labelKey) : t('admin.reports.all_statuses')
}

async function exportReport(format: 'csv' | 'pdf') {
  if (downloading.value) return
  downloading.value = format
  try {
    const response =
      format === 'csv'
        ? await adminApi.getOrdersCsv(status.value, from.value || undefined, to.value || undefined)
        : await adminApi.getOrdersPdf(status.value, from.value || undefined, to.value || undefined)

    const isPdf = format === 'pdf'
    downloadResponse(response, `orders-${new Date().toISOString().slice(0, 10)}.${isPdf ? 'pdf' : 'csv'}`)

    showToast(
      t(format === 'pdf' ? 'admin.reports.toast_pdf' : 'admin.reports.toast_csv', {
        status: selectedStatusLabel()
      })
    )
  } catch {
    showToast(t('admin.reports.toast_error'))
  } finally {
    downloading.value = ''
  }
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-ink">{{ $t('admin.reports.title') }}</h1>
      <p class="mt-0.5 text-sm text-gray-500">{{ $t('admin.reports.subtitle') }}</p>
    </div>

    <div class="card p-6">
      <h2 class="text-base font-semibold text-ink">{{ $t('admin.reports.orders_report') }}</h2>

      <div class="mt-5 grid gap-4 sm:grid-cols-3">
        <div>
          <label class="label" for="report-status">{{ $t('order.status') }}</label>
          <select id="report-status" v-model="status" class="input">
            <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
              {{ $t(opt.labelKey) }}
            </option>
          </select>
        </div>
        <div>
          <label class="label" for="report-from">{{ $t('admin.reports.from') }}</label>
          <input id="report-from" v-model="from" type="date" class="input" />
        </div>
        <div>
          <label class="label" for="report-to">{{ $t('admin.reports.to') }}</label>
          <input id="report-to" v-model="to" type="date" class="input" />
        </div>
      </div>

      <div class="mt-6 flex flex-col gap-3 sm:flex-row">
        <button
          type="button"
          class="btn-primary"
          :disabled="downloading === 'pdf'"
          @click="exportReport('pdf')"
        >
          <FileText v-if="downloading !== 'pdf'" class="h-4 w-4" />
          {{ downloading === 'pdf'
            ? $t('admin.reports.downloading')
            : $t('admin.reports.download_pdf') }}
        </button>
        <button
          type="button"
          class="btn-secondary"
          :disabled="downloading === 'csv'"
          @click="exportReport('csv')"
        >
          <FileSpreadsheet v-if="downloading !== 'csv'" class="h-4 w-4" />
          {{ downloading === 'csv'
            ? $t('admin.reports.downloading')
            : $t('admin.reports.download_csv') }}
        </button>
      </div>
    </div>

    <div class="card flex items-start gap-4 p-6">
      <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
        <FileBarChart2 class="h-6 w-6" />
      </div>
      <div>
        <p class="font-semibold text-ink">{{ $t('admin.reports.summary_title') }}</p>
        <p class="mt-1 text-sm text-gray-500">{{ $t('admin.reports.summary_description') }}</p>
      </div>
    </div>

    <transition name="fade">
      <div v-if="toast" class="fixed bottom-6 right-6 card px-5 py-3 text-sm shadow-popover">
        {{ toast }}
      </div>
    </transition>
  </div>
</template>