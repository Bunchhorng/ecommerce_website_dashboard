<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { MapPin, Save, User } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import { addressesApi, type AddressPayload } from '@/api/addresses'
import type { Address } from '@/types'

const { t } = useI18n()

const router = useRouter()
const route = useRoute()

const isEdit = computed(() => Boolean(route.params.id))
const addressId = computed(() => (isEdit.value ? Number(route.params.id) : null))

interface AddressForm {
  label: string
  fullName: string
  line1: string
  line2: string
  city: string
  state: string
  postalCode: string
  country: string
  phone: string
}

const emptyForm = (): AddressForm => ({
  label: '',
  fullName: '',
  line1: '',
  line2: '',
  city: '',
  state: '',
  postalCode: '',
  country: '',
  phone: ''
})

const form = reactive<AddressForm>(emptyForm())

function mapAddress(raw: { id: number; label: string | null; full_name: string; phone: string | null; address_line1: string; address_line2: string | null; city: string; state: string; postal_code: string; country: string; is_default: boolean }): Address {
  return {
    id: String(raw.id),
    label: raw.label ?? '',
    fullName: raw.full_name,
    line1: raw.address_line1,
    line2: raw.address_line2 ?? undefined,
    city: raw.city,
    state: raw.state,
    postalCode: raw.postal_code,
    country: raw.country,
    phone: raw.phone ?? '',
    isDefault: raw.is_default
  }
}

const formTitle = computed(() =>
  isEdit.value ? t('account.edit_address') : t('account.add_address')
)

const initializing = ref(true)
const saving = ref(false)
const toast = ref('')
let toastTimer: ReturnType<typeof setTimeout> | undefined

function showToast(msg: string) {
  toast.value = msg
  if (toastTimer) clearTimeout(toastTimer)
  toastTimer = setTimeout(() => {
    toast.value = ''
  }, 2500)
}

onMounted(async () => {
  try {
    if (isEdit.value && addressId.value) {
      const res = await addressesApi.list()
      const found = (res.data.data ?? []).find((a) => a.id === addressId.value)
      if (!found) {
        router.replace({ name: 'account-addresses' })
        return
      }
      const address = mapAddress(found)
      Object.assign(form, {
        label: address.label,
        fullName: address.fullName,
        line1: address.line1,
        line2: address.line2 ?? '',
        city: address.city,
        state: address.state,
        postalCode: address.postalCode,
        country: address.country,
        phone: address.phone
      })
    }
  } catch {
    if (isEdit.value) {
      showToast(t('account.toast_load_error'))
      router.replace({ name: 'account-addresses' })
    }
  } finally {
    initializing.value = false
  }
})

async function save() {
  if (saving.value) return
  saving.value = true
  try {
    const payload: AddressPayload = {
      label: form.label || undefined,
      full_name: form.fullName,
      phone: form.phone || undefined,
      address_line1: form.line1,
      address_line2: form.line2 || undefined,
      city: form.city,
      state: form.state,
      postal_code: form.postalCode,
      country: form.country || undefined
    }

    if (isEdit.value && addressId.value) {
      await addressesApi.update(addressId.value, payload)
    } else {
      await addressesApi.create(payload)
    }
    router.push({ name: 'account-addresses' })
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <form class="space-y-6 pb-16" @submit.prevent="save()">
    <div class="card flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <div class="section-eyebrow">{{ $t('nav.account') }}</div>
        <h1 class="text-xl font-bold text-ink">{{ formTitle }}</h1>
      </div>
      <div class="flex flex-wrap gap-2">
        <button class="btn-secondary btn-sm" type="button" @click="router.push({ name: 'account-addresses' })">{{ $t('actions.cancel') }}</button>
        <button class="btn-primary btn-sm" type="submit" :disabled="saving">
          <Save class="h-4 w-4" />
          {{ isEdit ? $t('account.update_address') : $t('account.add_address') }}
        </button>
      </div>
    </div>

    <div v-if="initializing" class="card p-10 text-center">
      <p class="text-sm text-gray-500 dark:text-muted">{{ $t('common.loading') }}</p>
    </div>

    <template v-else>
      <div class="card p-6">
        <div class="flex items-center gap-2.5">
          <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
            <User class="h-4 w-4" />
          </span>
          <div>
            <h2 class="text-lg font-semibold">{{ $t('account.recipient_information') }}</h2>
          </div>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2">
          <div class="sm:col-span-2">
            <label class="label" for="addr-full-name">{{ $t('checkout.full_name') }}</label>
            <input id="addr-full-name" v-model="form.fullName" type="text" class="input" />
          </div>
          <div>
            <label class="label" for="addr-label">{{ $t('account.label') }}</label>
            <input id="addr-label" v-model="form.label" type="text" class="input" :placeholder="$t('account.label_home')" />
          </div>
          <div>
            <label class="label" for="addr-phone">{{ $t('checkout.phone') }}</label>
            <input id="addr-phone" v-model="form.phone" type="tel" class="input" />
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center gap-2.5">
          <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
            <MapPin class="h-4 w-4" />
          </span>
          <div>
            <h2 class="text-lg font-semibold">{{ $t('account.address_details') }}</h2>
          </div>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2">
          <div class="sm:col-span-2">
            <label class="label" for="addr-line1">{{ $t('checkout.address_line_1') }}</label>
            <input id="addr-line1" v-model="form.line1" type="text" class="input" :placeholder="$t('checkout.street_placeholder')" />
          </div>
          <div>
            <label class="label" for="addr-line2">{{ $t('checkout.address_line_2') }}</label>
            <input id="addr-line2" v-model="form.line2" type="text" class="input" :placeholder="$t('checkout.apt_placeholder')" />
          </div>
          <div>
            <label class="label" for="addr-city">{{ $t('checkout.city') }}</label>
            <input id="addr-city" v-model="form.city" type="text" class="input" />
          </div>
          <div>
            <label class="label" for="addr-state">{{ $t('checkout.state') }}</label>
            <input id="addr-state" v-model="form.state" type="text" class="input" />
          </div>
          <div>
            <label class="label" for="addr-postal">{{ $t('checkout.postal_code') }}</label>
            <input id="addr-postal" v-model="form.postalCode" type="text" class="input" />
          </div>
          <div>
            <label class="label" for="addr-country">{{ $t('checkout.country') }}</label>
            <input id="addr-country" v-model="form.country" type="text" class="input" />
          </div>
        </div>
      </div>

      <div class="flex flex-wrap items-center justify-end gap-2">
        <button class="btn-secondary" type="button" @click="router.push({ name: 'account-addresses' })">{{ $t('actions.cancel') }}</button>
        <button class="btn-primary" type="submit" :disabled="saving">
          <Save class="h-4 w-4" />
          {{ isEdit ? $t('account.update_address') : $t('account.add_address') }}
        </button>
      </div>
    </template>

    <transition name="fade">
      <div
        v-if="toast"
        class="fixed bottom-6 right-6 card px-5 py-3 text-sm shadow-popover"
      >
        {{ toast }}
      </div>
    </transition>
  </form>
</template>