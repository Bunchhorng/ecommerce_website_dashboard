<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { Plus, Pencil, Trash2, UploadCloud, ImagePlus } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import BaseModal from '@/components/BaseModal.vue'
import { adminApi } from '@/api/admin'
import { mediaApi } from '@/api/uploads'
import type { AdminBrand } from '@/api/admin'

const { t } = useI18n()

const loading = ref(true)
const brands = ref<AdminBrand[]>([])

const modalOpen = ref(false)
const editingId = ref<number | null>(null)
const form = reactive({ name: '', website: '', logoUrl: null as string | null })
const logoFile = ref<File | null>(null)
const logoInput = ref<HTMLInputElement | null>(null)

function resetLogo() {
  logoFile.value = null
  if (logoInput.value) logoInput.value.value = ''
}

function onLogoPicked(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file || !file.type.startsWith('image/')) return
  logoFile.value = file
  form.logoUrl = URL.createObjectURL(file)
}

function removeLogoPreview() {
  logoFile.value = null
  form.logoUrl = null
  if (logoInput.value) logoInput.value.value = ''
}

function openAdd() {
  editingId.value = null
  form.name = ''
  form.website = ''
  form.logoUrl = null
  resetLogo()
  modalOpen.value = true
}

function openEdit(b: AdminBrand) {
  editingId.value = b.id
  form.name = b.name
  form.website = b.description ?? ''
  form.logoUrl = b.logo
  resetLogo()
  modalOpen.value = true
}

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

async function saveBrand() {
  if (!form.name.trim()) {
    showToast(t('admin.brands.toast_enter_name'))
    return
  }
  const name = form.name.trim()
  const slug = name.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '')

  try {
    if (editingId.value != null) {
      await adminApi.updateBrand(editingId.value, { name, description: form.website.trim() || null })
      if (logoFile.value) {
        await mediaApi.uploadBrandLogo(editingId.value, logoFile.value)
      }
      await loadBrands()
      showToast(t('admin.brands.toast_updated', { name }))
    } else {
      const { data: resp } = await adminApi.createBrand({ name, slug, description: form.website.trim() || null })
      if (logoFile.value) {
        await mediaApi.uploadBrandLogo(resp.data.id, logoFile.value)
      }
      await loadBrands()
      showToast(t('admin.brands.toast_added', { name }))
    }
    modalOpen.value = false
  } catch {
    showToast(t('admin.brands.toast_save_error'))
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
      <button class="btn-primary btn-sm" @click="openAdd()">
        <Plus class="h-4 w-4" />
        {{ $t('admin.brands.add_brand') }}
      </button>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
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
            <button class="btn-icon h-9 w-9" type="button" :title="$t('actions.edit')" @click="openEdit(b)">
              <Pencil class="h-4 w-4" />
            </button>
            <button class="btn-icon h-9 w-9 hover:text-red-600" type="button" :title="$t('actions.delete')" @click="removeBrand(b.id)">
              <Trash2 class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <BaseModal v-model="modalOpen" :title="editingId ? $t('admin.brands.edit_brand') : $t('admin.brands.add_brand')" size="md">
      <div class="space-y-4">
        <div>
          <label class="label" for="brand-name">{{ $t('admin.brands.name_label') }}</label>
          <input id="brand-name" v-model="form.name" class="input" :placeholder="$t('admin.brands.name_placeholder')" />
        </div>
        <div>
          <label class="label" for="brand-website">{{ $t('admin.brands.website_label') }}</label>
          <input id="brand-website" v-model="form.website" class="input" :placeholder="$t('admin.brands.website_placeholder')" />
        </div>
        <div>
          <label class="label">{{ $t('admin.brands.logo_label') }}</label>
          <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full bg-canvas">
              <img v-if="form.logoUrl" :src="form.logoUrl" alt="Logo" class="h-full w-full object-cover" />
              <UploadCloud v-else class="h-6 w-6 text-gray-300" />
            </div>
            <div class="flex gap-2">
              <button type="button" class="btn-secondary btn-sm" @click="logoInput?.click()">
                <ImagePlus class="h-4 w-4" />
                {{ form.logoUrl ? $t('admin.brands.change_logo') : $t('admin.brands.upload_logo') }}
              </button>
              <button v-if="form.logoUrl" type="button" class="btn-ghost btn-sm" @click="removeLogoPreview()">
                {{ $t('admin.brands.remove_logo') }}
              </button>
            </div>
            <input ref="logoInput" type="file" accept="image/*" class="hidden" @change="onLogoPicked" />
          </div>
        </div>
      </div>
      <template #footer>
        <button class="btn-secondary btn-sm" type="button" @click="modalOpen = false">{{ $t('actions.cancel') }}</button>
        <button class="btn-primary btn-sm" type="button" @click="saveBrand()">{{ $t('admin.brands.save') }}</button>
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
