<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { Save, Store, BellRing, Globe } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const SETTINGS_KEY = 'shopverse-admin-settings'

interface AdminSettings {
  storeName: string
  supportEmail: string
  supportPhone: string
  currency: string
  locale: string
  lowStockThreshold: number
  emailOrderNotifications: boolean
  emailLowStockAlerts: boolean
}

const defaults: AdminSettings = {
  storeName: 'ShopVerse',
  supportEmail: 'support@shopverse.com',
  supportPhone: '',
  currency: 'USD',
  locale: 'en',
  lowStockThreshold: 5,
  emailOrderNotifications: true,
  emailLowStockAlerts: true
}

const form = reactive<AdminSettings>({ ...defaults })
const loaded = ref(false)
const saving = ref(false)
const saved = ref(false)

onMounted(() => {
  try {
    const raw = localStorage.getItem(SETTINGS_KEY)
    if (raw) {
      Object.assign(form, JSON.parse(raw))
    }
  } catch {
    // Fall back to defaults
  }
  loaded.value = true
})

const toast = ref('')
let toastTimer: ReturnType<typeof setTimeout> | undefined
function showToast(msg: string) {
  toast.value = msg
  if (toastTimer) clearTimeout(toastTimer)
  toastTimer = setTimeout(() => {
    toast.value = ''
  }, 2500)
}

function saveSettings() {
  saved.value = true
  try {
    localStorage.setItem(SETTINGS_KEY, JSON.stringify(form))
    showToast(t('admin.settings.toast_saved'))
  } catch {
    showToast(t('admin.settings.toast_error'))
  } finally {
    setTimeout(() => {
      saved.value = false
    }, 1500)
  }
}

function resetSettings() {
  Object.assign(form, defaults)
  localStorage.removeItem(SETTINGS_KEY)
  showToast(t('admin.settings.toast_reset'))
}
</script>

<template>
  <div v-if="!loaded" class="card p-10 text-center text-sm text-gray-500">
    {{ $t('common.loading') }}
  </div>

  <div v-else class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-ink">{{ $t('admin.settings.title') }}</h1>
      <p class="mt-0.5 text-sm text-gray-500">{{ $t('admin.settings.subtitle') }}</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
      <div class="card p-6">
        <div class="flex items-center gap-2">
          <Store class="h-5 w-5 text-primary" />
          <h2 class="text-base font-semibold text-ink">{{ $t('admin.settings.store_profile') }}</h2>
        </div>
        <p class="mt-1 text-sm text-gray-500">{{ $t('admin.settings.store_profile_description') }}</p>

        <div class="mt-5 space-y-4">
          <div>
            <label class="label" for="store-name">{{ $t('admin.settings.store_name') }}</label>
            <input id="store-name" v-model="form.storeName" class="input" type="text" />
          </div>
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="label" for="support-email">{{ $t('admin.settings.support_email') }}</label>
              <input id="support-email" v-model="form.supportEmail" class="input" type="email" />
            </div>
            <div>
              <label class="label" for="support-phone">{{ $t('admin.settings.support_phone') }}</label>
              <input id="support-phone" v-model="form.supportPhone" class="input" type="tel" />
            </div>
          </div>
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="label" for="currency">{{ $t('admin.settings.currency') }}</label>
              <select id="currency" v-model="form.currency" class="input">
                <option value="USD">USD — US Dollar</option>
                <option value="EUR">EUR — Euro</option>
                <option value="KHR">KHR — Cambodian Riel</option>
              </select>
            </div>
            <div>
              <label class="label" for="locale">{{ $t('admin.settings.locale') }}</label>
              <select id="locale" v-model="form.locale" class="input">
                <option value="en">English</option>
                <option value="km">ខ្មែរ (Khmer)</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div class="card p-6">
          <div class="flex items-center gap-2">
            <BellRing class="h-5 w-5 text-primary" />
            <h2 class="text-base font-semibold text-ink">{{ $t('admin.settings.notifications') }}</h2>
          </div>
          <p class="mt-1 text-sm text-gray-500">{{ $t('admin.settings.notifications_description') }}</p>

          <div class="mt-5 space-y-4">
            <div>
              <label class="label" for="low-stock-threshold">{{ $t('admin.settings.low_stock_threshold') }}</label>
              <input
                id="low-stock-threshold"
                v-model.number="form.lowStockThreshold"
                class="input"
                type="number"
                min="0"
                step="1"
              />
            </div>
            <label class="flex items-start justify-between gap-4 rounded-lg border border-border-gray p-4">
              <span>
                <span class="block text-sm font-medium text-ink">{{ $t('admin.settings.email_order_notifications') }}</span>
                <span class="text-xs text-gray-500">{{ $t('admin.settings.email_order_notifications_desc') }}</span>
              </span>
              <button
                type="button"
                class="relative h-6 w-11 shrink-0 rounded-full transition-colors"
                :class="form.emailOrderNotifications ? 'bg-emerald-500' : 'bg-gray-200'"
                :aria-checked="form.emailOrderNotifications"
                role="switch"
                @click="form.emailOrderNotifications = !form.emailOrderNotifications"
              >
                <span
                  class="absolute top-0.5 h-5 w-5 rounded-full bg-white shadow transition-all"
                  :class="form.emailOrderNotifications ? 'left-[22px]' : 'left-0.5'"
                ></span>
              </button>
            </label>
            <label class="flex items-start justify-between gap-4 rounded-lg border border-border-gray p-4">
              <span>
                <span class="block text-sm font-medium text-ink">{{ $t('admin.settings.email_low_stock_alerts') }}</span>
                <span class="text-xs text-gray-500">{{ $t('admin.settings.email_low_stock_alerts_desc') }}</span>
              </span>
              <button
                type="button"
                class="relative h-6 w-11 shrink-0 rounded-full transition-colors"
                :class="form.emailLowStockAlerts ? 'bg-emerald-500' : 'bg-gray-200'"
                :aria-checked="form.emailLowStockAlerts"
                role="switch"
                @click="form.emailLowStockAlerts = !form.emailLowStockAlerts"
              >
                <span
                  class="absolute top-0.5 h-5 w-5 rounded-full bg-white shadow transition-all"
                  :class="form.emailLowStockAlerts ? 'left-[22px]' : 'left-0.5'"
                ></span>
              </button>
            </label>
          </div>
        </div>

        <div class="card p-6">
          <div class="flex items-center gap-2">
            <Globe class="h-5 w-5 text-primary" />
            <h2 class="text-base font-semibold text-ink">{{ $t('admin.settings.local_storage') }}</h2>
          </div>
          <p class="mt-1 text-sm text-gray-500">{{ $t('admin.settings.local_storage_description') }}</p>
        </div>
      </div>
    </div>

    <div class="flex items-center gap-3">
      <button type="button" class="btn-primary" :disabled="saving" @click="saveSettings">
        <Save v-if="!saved" class="h-4 w-4" />
        {{ saved ? $t('admin.settings.saved') : $t('admin.settings.save_changes') }}
      </button>
      <button type="button" class="btn-secondary" @click="resetSettings">
        {{ $t('admin.settings.reset') }}
      </button>
    </div>

    <transition name="fade">
      <div v-if="toast" class="fixed bottom-6 right-6 card px-5 py-3 text-sm shadow-popover">
        {{ toast }}
      </div>
    </transition>
  </div>
</template>