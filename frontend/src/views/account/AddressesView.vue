<script setup lang="ts">
import { Pencil, Plus, Trash2 } from 'lucide-vue-next'
import { addressesApi, type AddressPayload } from '@/api/addresses'
import BaseBadge from '@/components/BaseBadge.vue'
import BaseModal from '@/components/BaseModal.vue'
import { useI18n } from 'vue-i18n'
import { onMounted, reactive, ref } from 'vue'
import type { Address } from '@/types'

const { t } = useI18n()

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

const addresses = ref<Address[]>([])
const loading = ref(true)
const modalOpen = ref(false)
const editingAddress = ref<Address | null>(null)
const deleteError = ref('')

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

async function fetchAddresses() {
  loading.value = true
  try {
    const res = await addressesApi.list()
    addresses.value = (res.data.data ?? []).map(mapAddress)
  } finally {
    loading.value = false
  }
}

onMounted(fetchAddresses)

function openNew() {
  editingAddress.value = null
  Object.assign(form, emptyForm())
  deleteError.value = ''
  modalOpen.value = true
}

function openEdit(address: Address) {
  editingAddress.value = address
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
  deleteError.value = ''
  modalOpen.value = true
}

function closeModal() {
  modalOpen.value = false
  editingAddress.value = null
}

async function saveAddress() {
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

  if (editingAddress.value) {
    await addressesApi.update(Number(editingAddress.value.id), payload)
  } else {
    await addressesApi.create(payload)
  }
  await fetchAddresses()
  closeModal()
}

async function removeAddress(address: Address) {
  if (address.isDefault) {
    deleteError.value = t('account.cannot_delete_default_address')
    return
  }
  await addressesApi.remove(Number(address.id))
  await fetchAddresses()
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <h1 class="text-2xl font-bold text-ink dark:text-ink">{{ $t('account.my_addresses') }}</h1>
      <button type="button" class="btn-primary btn-sm w-fit" @click="openNew">
        <Plus class="h-4 w-4" />
        {{ $t('checkout.add_new_address') }}
      </button>
    </div>

    <p v-if="deleteError" class="text-sm text-red-500">{{ deleteError }}</p>

    <div v-if="loading" class="card p-10 text-center">
      <p class="text-sm text-gray-500 dark:text-muted">{{ $t('common.loading') }}</p>
    </div>

    <div v-else-if="addresses.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div v-for="a in addresses" :key="a.id" class="card p-5">
        <div class="flex items-center gap-2">
          <span class="chip">{{ a.label }}</span>
          <span v-if="a.isDefault">
            <BaseBadge variant="success" dot>{{ $t('account.default') }}</BaseBadge>
          </span>
        </div>
        <p class="mt-3 font-semibold text-ink dark:text-ink">{{ a.fullName }}</p>
        <div class="mt-1 space-y-0.5 text-sm text-gray-600 dark:text-muted">
          <p>{{ a.line1 }}</p>
          <p v-if="a.line2">{{ a.line2 }}</p>
          <p>{{ a.city }}, {{ a.state }} {{ a.postalCode }}</p>
          <p>{{ a.country }}</p>
        </div>
        <p class="mt-2 text-sm text-gray-500 dark:text-muted">{{ a.phone }}</p>
        <div class="mt-4 flex justify-end gap-1 border-t border-border-gray dark:border-border-gray pt-3">
          <button type="button" class="btn-ghost btn-sm" @click="openEdit(a)">
            <Pencil class="h-4 w-4" />
            {{ $t('actions.edit') }}
          </button>
          <button
            type="button"
            class="btn-ghost btn-sm text-red-500 hover:text-red-600"
            @click="removeAddress(a)"
          >
            <Trash2 class="h-4 w-4" />
            {{ $t('actions.delete') }}
          </button>
        </div>
      </div>
    </div>

    <EmptyState
      v-else
      :title="$t('account.no_addresses_title')"
      :description="$t('account.no_addresses_description')"
      :cta-label="$t('checkout.add_new_address')"
      @cta="openNew"
    >
      <template #icon>
        <Plus class="h-10 w-10 text-gray-300" />
      </template>
    </EmptyState>

    <BaseModal v-model="modalOpen" :title="editingAddress ? $t('account.edit_address') : $t('account.add_address')" size="sm">
      <form @submit.prevent="saveAddress">
        <div class="space-y-4">
          <div>
            <label class="label" for="addr-label">{{ $t('account.label') }}</label>
            <input id="addr-label" v-model="form.label" type="text" class="input" :placeholder="$t('account.label_home')" />
          </div>
          <div>
            <label class="label" for="addr-full-name">{{ $t('checkout.full_name') }}</label>
            <input id="addr-full-name" v-model="form.fullName" type="text" class="input" />
          </div>
          <div>
            <label class="label" for="addr-line1">{{ $t('checkout.address_line_1') }}</label>
            <input id="addr-line1" v-model="form.line1" type="text" class="input" :placeholder="$t('checkout.street_placeholder')" />
          </div>
          <div>
            <label class="label" for="addr-line2">
              {{ $t('checkout.address_line_2') }}
            </label>
            <input id="addr-line2" v-model="form.line2" type="text" class="input" :placeholder="$t('checkout.apt_placeholder')" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label" for="addr-city">{{ $t('checkout.city') }}</label>
              <input id="addr-city" v-model="form.city" type="text" class="input" />
            </div>
            <div>
              <label class="label" for="addr-state">{{ $t('checkout.state') }}</label>
              <input id="addr-state" v-model="form.state" type="text" class="input" />
            </div>
          </div>
          <div>
            <label class="label" for="addr-postal">{{ $t('checkout.postal_code') }}</label>
            <input id="addr-postal" v-model="form.postalCode" type="text" class="input" />
          </div>
          <div>
            <label class="label" for="addr-country">{{ $t('checkout.country') }}</label>
            <input id="addr-country" v-model="form.country" type="text" class="input" />
          </div>
          <div>
            <label class="label" for="addr-phone">{{ $t('checkout.phone') }}</label>
            <input id="addr-phone" v-model="form.phone" type="tel" class="input" />
          </div>
          <button type="submit" class="btn-primary btn-sm w-full">
            {{ editingAddress ? $t('account.update_address') : $t('account.add_address') }}
          </button>
        </div>
      </form>
      <template #footer>
        <button type="button" class="btn-secondary btn-sm" @click="closeModal">{{ $t('actions.cancel') }}</button>
      </template>
    </BaseModal>
  </div>
</template>
