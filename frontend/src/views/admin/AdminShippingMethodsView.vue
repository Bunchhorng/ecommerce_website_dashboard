<script setup lang="ts">
import { ref } from 'vue'
import { Truck, Pencil, Save } from 'lucide-vue-next'
import type { ShippingMethod } from '@/types'
import { SHIPPING_METHODS } from '@/data/mock'
import { formatPrice } from '@/utils/format'

type ShipMethod = ShippingMethod & { enabled: boolean }

const methods = ref<ShipMethod[]>([...SHIPPING_METHODS].map((m) => ({ ...m, enabled: true })))

const editingId = ref('')

function toggleEnabled(id: string) {
  const m = methods.value.find((x) => x.id === id)
  if (m) m.enabled = !m.enabled
}

function startEdit(id: string) {
  editingId.value = id
}

function saveEdit(_m: ShipMethod) {
  editingId.value = ''
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
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold text-ink">Shipping Methods</h1>
        <span class="chip">{{ methods.length }} total</span>
      </div>
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
            :aria-checked="m.enabled"
            aria-label="Toggle shipping method"
            class="relative h-6 w-11 shrink-0 rounded-full transition-colors"
            :class="m.enabled ? 'bg-emerald-500' : 'bg-gray-200'"
            @click="toggleEnabled(m.id)"
          >
            <span
              class="absolute top-0.5 h-5 w-5 rounded-full bg-white shadow transition-all"
              :class="m.enabled ? 'left-[22px]' : 'left-0.5'"
            ></span>
          </button>
        </div>

        <template v-if="editingId === m.id">
          <div class="mt-4 space-y-3">
            <div>
              <label class="label" :for="`sm-name-${m.id}`">Name</label>
              <input :id="`sm-name-${m.id}`" v-model="m.name" class="input" />
            </div>
            <div>
              <label class="label" :for="`sm-price-${m.id}`">Price</label>
              <input :id="`sm-price-${m.id}`" v-model.number="m.price" class="input" type="number" min="0" step="0.01" />
            </div>
            <div>
              <label class="label" :for="`sm-eta-${m.id}`">ETA (days)</label>
              <input :id="`sm-eta-${m.id}`" v-model.number="m.etaDays" class="input" type="number" min="1" />
            </div>
            <div>
              <label class="label" :for="`sm-desc-${m.id}`">Description</label>
              <input :id="`sm-desc-${m.id}`" v-model="m.description" class="input" />
            </div>
          </div>
          <div class="mt-4 flex justify-end gap-2">
            <button class="btn-secondary btn-sm" type="button" @click="editingId = ''">Cancel</button>
            <button class="btn-primary btn-sm" type="button" @click="saveEdit(m); showToast(`Updated ${m.name}`)">
              <Save class="h-4 w-4" />
              Save
            </button>
          </div>
        </template>

        <template v-else>
          <h3 class="mt-4 text-base font-semibold text-ink">{{ m.name }}</h3>
          <p class="mt-1 text-sm text-gray-500">{{ m.description }}</p>

          <div class="mt-4 flex flex-wrap items-center gap-2">
            <span class="chip">{{ m.etaDays }} days</span>
            <span
              v-if="m.price === 0"
              class="inline-flex items-center rounded-full bg-accent/20 px-3 py-1 text-xs font-semibold text-amber-700"
            >
              Free
            </span>
            <span v-else class="text-sm font-semibold text-ink">{{ formatPrice(m.price) }}</span>
          </div>

          <div class="mt-auto flex items-center justify-between border-t border-border-gray pt-4">
            <span class="text-xs font-medium" :class="m.enabled ? 'text-emerald-600' : 'text-gray-400'">
              {{ m.enabled ? 'Enabled' : 'Disabled' }}
            </span>
            <button class="btn-secondary btn-sm" type="button" @click="startEdit(m.id)">
              <Pencil class="h-4 w-4" />
              Edit
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