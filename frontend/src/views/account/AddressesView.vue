<script setup lang="ts">
import { Pencil, Plus, Trash2 } from 'lucide-vue-next'
import BaseBadge from '@/components/BaseBadge.vue'
import BaseModal from '@/components/BaseModal.vue'
import { ADDRESSES } from '@/data/mock'
import { randomId } from '@/utils/format'
import { reactive, ref } from 'vue'
import type { Address } from '@/types'

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

const addresses = ref<Address[]>([...ADDRESSES])
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

function saveAddress() {
  const payload = { ...form }
  const editing = editingAddress.value
  if (editing) {
    const idx = addresses.value.findIndex((a) => a.id === editing.id)
    if (idx >= 0) {
      addresses.value[idx] = { ...addresses.value[idx], ...payload }
    }
  } else {
    addresses.value.push({ id: randomId('ad'), ...payload, isDefault: false })
  }
  closeModal()
}

function removeAddress(address: Address) {
  if (address.isDefault) {
    deleteError.value = 'You cannot delete your default address.'
    return
  }
  addresses.value = addresses.value.filter((a) => a.id !== address.id)
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <h1 class="text-2xl font-bold text-ink">My Addresses</h1>
      <button type="button" class="btn-primary btn-sm w-fit" @click="openNew">
        <Plus class="h-4 w-4" />
        Add New Address
      </button>
    </div>

    <p v-if="deleteError" class="text-sm text-red-500">{{ deleteError }}</p>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div v-for="a in addresses" :key="a.id" class="card p-5">
        <div class="flex items-center gap-2">
          <span class="chip">{{ a.label }}</span>
          <span v-if="a.isDefault">
            <BaseBadge variant="success" dot>Default</BaseBadge>
          </span>
        </div>
        <p class="mt-3 font-semibold text-ink">{{ a.fullName }}</p>
        <div class="mt-1 space-y-0.5 text-sm text-gray-600">
          <p>{{ a.line1 }}</p>
          <p v-if="a.line2">{{ a.line2 }}</p>
          <p>{{ a.city }}, {{ a.state }} {{ a.postalCode }}</p>
          <p>{{ a.country }}</p>
        </div>
        <p class="mt-2 text-sm text-gray-500">{{ a.phone }}</p>
        <div class="mt-4 flex justify-end gap-1 border-t border-border-gray pt-3">
          <button type="button" class="btn-ghost btn-sm" @click="openEdit(a)">
            <Pencil class="h-4 w-4" />
            Edit
          </button>
          <button
            type="button"
            class="btn-ghost btn-sm text-red-500 hover:text-red-600"
            @click="removeAddress(a)"
          >
            <Trash2 class="h-4 w-4" />
            Delete
          </button>
        </div>
      </div>
    </div>

    <BaseModal v-model="modalOpen" :title="editingAddress ? 'Edit Address' : 'Add Address'" size="sm">
      <form @submit.prevent="saveAddress">
        <div class="space-y-4">
          <div>
            <label class="label" for="addr-label">Label</label>
            <input id="addr-label" v-model="form.label" type="text" class="input" placeholder="e.g. Home" />
          </div>
          <div>
            <label class="label" for="addr-full-name">Full name</label>
            <input id="addr-full-name" v-model="form.fullName" type="text" class="input" placeholder="Jane Doe" />
          </div>
          <div>
            <label class="label" for="addr-line1">Address line 1</label>
            <input id="addr-line1" v-model="form.line1" type="text" class="input" placeholder="Street address" />
          </div>
          <div>
            <label class="label" for="addr-line2">
              Address line 2 <span class="text-xs text-gray-400">(optional)</span>
            </label>
            <input id="addr-line2" v-model="form.line2" type="text" class="input" placeholder="Apt, suite, etc." />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label" for="addr-city">City</label>
              <input id="addr-city" v-model="form.city" type="text" class="input" />
            </div>
            <div>
              <label class="label" for="addr-state">State</label>
              <input id="addr-state" v-model="form.state" type="text" class="input" />
            </div>
          </div>
          <div>
            <label class="label" for="addr-postal">Postal code</label>
            <input id="addr-postal" v-model="form.postalCode" type="text" class="input" />
          </div>
          <div>
            <label class="label" for="addr-country">Country</label>
            <input id="addr-country" v-model="form.country" type="text" class="input" />
          </div>
          <div>
            <label class="label" for="addr-phone">Phone</label>
            <input id="addr-phone" v-model="form.phone" type="tel" class="input" />
          </div>
          <button type="submit" class="btn-primary btn-sm w-full">
            {{ editingAddress ? 'Update Address' : 'Add Address' }}
          </button>
        </div>
      </form>
      <template #footer>
        <button type="button" class="btn-secondary btn-sm" @click="closeModal">Cancel</button>
      </template>
    </BaseModal>
  </div>
</template>