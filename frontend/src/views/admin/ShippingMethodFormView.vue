<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Save } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import { adminApi } from '@/api/admin'
import type { AdminShippingMethod } from '@/api/admin'

const { t } = useI18n()

const router = useRouter()
const route = useRoute()

const isEdit = computed(() => Boolean(route.params.id))
const methodId = computed(() => (isEdit.value ? Number(route.params.id) : null))

const form = reactive({
  name: '',
  code: '',
  description: '',
  price: 0,
  estimatedDaysMin: 1,
  estimatedDaysMax: 5,
  status: 'Active'
})

const errors = reactive<Record<string, string>>({})

function slugify(value: string): string {
  return value
    .trim()
    .toLowerCase()
    .replace(/\s+/g, '-')
    .replace(/[^a-z0-9-]/g, '')
}

const formTitle = computed(() =>
  isEdit.value
    ? t('admin.shipping.edit_method')
    : t('admin.shipping.add_method')
)

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
    const { data: resp } = await adminApi.listShippingMethods()
    if (isEdit.value && methodId.value) {
      const method = resp.data.find((m: AdminShippingMethod) => m.id === methodId.value)
      if (!method) {
        router.replace({ name: 'admin-shipping' })
        return
      }
      form.name = method.name
      form.code = method.code
      form.description = method.description ?? ''
      form.price = method.price
      form.estimatedDaysMin = method.estimated_days_min ?? 1
      form.estimatedDaysMax = method.estimated_days_max ?? 5
      form.status = method.is_active ? 'Active' : 'Draft'
    }
  } catch {
    if (isEdit.value) {
      showToast(t('admin.shipping.toast_load_error'))
      router.replace({ name: 'admin-shipping' })
    }
  }
})

function validate(): boolean {
  errors.name = form.name.trim() ? '' : t('admin.shipping.toast_enter_name')
  return Object.values(errors).every((v) => v === '')
}

async function save() {
  if (!validate()) {
    showToast(t('admin.shipping.toast_enter_name'))
    return
  }

  const name = form.name.trim()
  const code = form.code.trim() || slugify(name)

  const payload: Record<string, unknown> = {
    name,
    code,
    description: form.description.trim() || null,
    price: Number(form.price) || 0,
    estimated_days_min: form.estimatedDaysMin ? Number(form.estimatedDaysMin) : null,
    estimated_days_max: form.estimatedDaysMax ? Number(form.estimatedDaysMax) : null,
    is_active: form.status === 'Active'
  }

  try {
    saving.value = true
    const targetId = methodId.value

    if (isEdit.value && targetId) {
      await adminApi.updateShippingMethod(targetId, payload)
      showToast(t('admin.shipping.toast_updated', { name }))
    } else {
      await adminApi.createShippingMethod(payload)
      showToast(t('admin.shipping.toast_added', { name }))
    }
    router.push({ name: 'admin-shipping' })
  } catch {
    showToast(isEdit.value ? t('admin.shipping.toast_update_error') : t('admin.shipping.toast_add_error'))
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <form class="space-y-6 pb-16" @submit.prevent="save()">
    <div class="card flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <div class="section-eyebrow">{{ $t('admin.nav.group_fulfillment') }}</div>
        <h1 class="text-xl font-bold text-ink">{{ formTitle }}</h1>
      </div>
      <div class="flex flex-wrap gap-2">
        <button class="btn-secondary btn-sm" type="button" @click="router.push({ name: 'admin-shipping' })">{{ $t('actions.cancel') }}</button>
        <button class="btn-primary btn-sm" type="submit" :disabled="saving">
          <Save class="h-4 w-4" />
          {{ $t('admin.shipping.save_method') }}
        </button>
      </div>
    </div>

    <div class="card p-6">
      <div class="section-eyebrow">{{ $t('admin.products.step_1') }}</div>
      <h2 class="mt-2 text-lg font-semibold">{{ $t('admin.products.basic_information') }}</h2>

      <div class="mt-5 grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
          <label class="label" for="sm-name">{{ $t('admin.shipping.name_label') }}</label>
          <input
            id="sm-name"
            v-model="form.name"
            class="input"
            :class="{ 'input-error': errors.name }"
            placeholder="Standard"
          />
          <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name }}</p>
        </div>

        <div>
          <label class="label" for="sm-status">{{ $t('admin.products.status_label') }}</label>
          <select id="sm-status" v-model="form.status" class="select">
            <option value="Active">{{ $t('status.active') }}</option>
            <option value="Draft">{{ $t('status.draft') }}</option>
          </select>
        </div>

        <div>
          <label class="label" for="sm-code">{{ $t('admin.shipping.code_label') }}</label>
          <input id="sm-code" v-model="form.code" class="input" placeholder="standard" />
        </div>

        <div>
          <label class="label" for="sm-price">{{ $t('admin.shipping.price_label') }}</label>
          <input id="sm-price" v-model.number="form.price" class="input" type="number" min="0" step="0.01" placeholder="5.00" />
        </div>

        <div>
          <label class="label">{{ $t('admin.shipping.eta_label') }}</label>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <input id="sm-eta-min" v-model.number="form.estimatedDaysMin" class="input" type="number" min="0" :placeholder="$t('admin.shipping.min_days_label')" />
            </div>
            <div>
              <input id="sm-eta-max" v-model.number="form.estimatedDaysMax" class="input" type="number" min="1" :placeholder="$t('admin.shipping.max_days_label')" />
            </div>
          </div>
        </div>

        <div class="sm:col-span-2">
          <label class="label" for="sm-description">{{ $t('admin.shipping.description_label') }}</label>
          <textarea
            id="sm-description"
            v-model="form.description"
            class="textarea"
            rows="4"
            :placeholder="$t('admin.shipping.description_label')"
          ></textarea>
        </div>
      </div>
    </div>

    <div class="flex flex-wrap items-center justify-end gap-2">
      <button class="btn-secondary" type="button" @click="router.push({ name: 'admin-shipping' })">{{ $t('actions.cancel') }}</button>
      <button class="btn-primary" type="submit" :disabled="saving">
        <Save class="h-4 w-4" />
        {{ $t('admin.shipping.save_method') }}
      </button>
    </div>

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