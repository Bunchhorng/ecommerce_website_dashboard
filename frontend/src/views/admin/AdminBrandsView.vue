<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import { Plus, Pencil, Trash2 } from 'lucide-vue-next'
import BaseModal from '@/components/BaseModal.vue'
import type { Brand } from '@/types'
import { BRANDS, PRODUCTS } from '@/data/mock'
import { randomId } from '@/utils/format'

type LocalBrand = Brand & { website?: string }

const brands = ref<LocalBrand[]>([...BRANDS])

const productsPerBrand = computed(() => {
  const map = new Map<string, number>()
  for (const b of brands.value) {
    map.set(b.name, PRODUCTS.filter((p) => p.brand.name === b.name).length)
  }
  return map
})

const modalOpen = ref(false)
const editingId = ref('')
const form = reactive({ name: '', website: '' })

function openAdd() {
  editingId.value = ''
  form.name = ''
  form.website = ''
  modalOpen.value = true
}

function openEdit(b: LocalBrand) {
  editingId.value = b.id
  form.name = b.name
  form.website = b.website ?? ''
  modalOpen.value = true
}

function saveBrand() {
  if (!form.name.trim()) {
    showToast('Please enter a brand name')
    return
  }
  const name = form.name.trim()
  if (editingId.value) {
    const b = brands.value.find((x) => x.id === editingId.value)
    if (b) {
      b.name = name
      b.website = form.website.trim() || undefined
      showToast(`Updated brand "${name}"`)
    }
  } else {
    const slug = name.toLowerCase().replace(/\s+/g, '-')
    brands.value.push({ id: randomId('br'), name, slug, website: form.website.trim() || undefined })
    showToast(`Added brand "${name}"`)
  }
  modalOpen.value = false
}

function removeBrand(id: string) {
  const b = brands.value.find((x) => x.id === id)
  brands.value = brands.value.filter((x) => x.id !== id)
  showToast(`Deleted brand "${b ? b.name : ''}"`)
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
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold text-ink">Brands</h1>
        <span class="chip">{{ brands.length }} total</span>
      </div>
      <button class="btn-primary btn-sm" @click="openAdd()">
        <Plus class="h-4 w-4" />
        Add Brand
      </button>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
      <div v-for="b in brands" :key="b.id" class="card p-5">
        <div class="flex items-start gap-4">
          <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-bold text-primary">
            {{ initials(b.name) }}
          </div>
          <div class="min-w-0 flex-1">
            <div class="truncate text-base font-semibold text-ink">{{ b.name }}</div>
            <div class="mt-1 flex flex-wrap items-center gap-2">
              <span class="chip">/{{ b.slug ?? b.name.toLowerCase().replace(/\s+/g, '-') }}</span>
              <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-600">
                Active
              </span>
            </div>
          </div>
        </div>
        <div class="mt-4 flex items-center justify-between border-t border-border-gray pt-4">
          <span class="text-sm text-gray-500">
            {{ productsPerBrand.get(b.name) ?? 0 }} products
          </span>
          <div class="flex gap-1">
            <button class="btn-icon h-9 w-9" type="button" title="Edit" @click="openEdit(b)">
              <Pencil class="h-4 w-4" />
            </button>
            <button class="btn-icon h-9 w-9 hover:text-red-600" type="button" title="Delete" @click="removeBrand(b.id)">
              <Trash2 class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <BaseModal v-model="modalOpen" :title="editingId ? 'Edit Brand' : 'Add Brand'" size="md">
      <div class="space-y-4">
        <div>
          <label class="label" for="brand-name">Name</label>
          <input id="brand-name" v-model="form.name" class="input" placeholder="Brand name" />
        </div>
        <div>
          <label class="label" for="brand-website">Website</label>
          <input id="brand-website" v-model="form.website" class="input" placeholder="https://example.com" />
        </div>
      </div>
      <template #footer>
        <button class="btn-secondary btn-sm" type="button" @click="modalOpen = false">Cancel</button>
        <button class="btn-primary btn-sm" type="button" @click="saveBrand()">Save</button>
      </template>
    </BaseModal>

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
