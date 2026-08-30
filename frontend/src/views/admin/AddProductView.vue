<script setup lang="ts">
import { computed, onBeforeUnmount, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import {
  Plus,
  X,
  UploadCloud,
  ImagePlus,
  Star,
  RefreshCw,
  Save,
  Check,
  AlertTriangle
} from 'lucide-vue-next'
import { BRANDS, CATEGORIES } from '@/data/mock'
import { formatPrice } from '@/utils/format'

interface GeneratedVariant {
  id: string
  attributes: { name: string; value: string }[]
  sku: string
  price: number | null
  stock: number
  enabled: boolean
}

interface AttrRow {
  name: string
  values: string[]
}

interface ImageItem {
  id: number
  url: string
  name: string
  isMain: boolean
}

const router = useRouter()

const form = reactive({
  title: '',
  brand: '',
  category: '',
  sku: '',
  status: 'Active',
  description: '',
  price: 0,
  compareAt: 0,
  baseStock: 0,
  trackInventory: true
})

const errors = reactive<Record<string, string>>({})

const attributes = ref<AttrRow[]>([{ name: 'Color', values: ['Black', 'White'] }])
const variants = ref<GeneratedVariant[]>([])
const images = ref<ImageItem[]>([])

function requiredBrandNames(): string[] {
  return BRANDS.map((b) => b.name)
}

const categoryNames = computed(() => CATEGORIES.map((c) => c.name))

const discountPct = computed(() => {
  const price = Number(form.price)
  const compare = Number(form.compareAt)
  if (compare > price && price > 0) return -Math.round((1 - price / compare) * 100)
  return 0
})

function setAttrValues(attr: AttrRow, value: string) {
  attr.values = value
    .split(',')
    .map((v) => v.trim())
    .filter(Boolean)
}

function addAttribute() {
  attributes.value.push({ name: '', values: [] })
}

function removeAttribute(index: number) {
  attributes.value.splice(index, 1)
}

function cartesianProduct<T>(arrays: T[][]): T[][] {
  return arrays.reduce<T[][]>(
    (acc, arr) => acc.flatMap((combo) => arr.map((item) => [...combo, item])),
    [[]]
  )
}

function generateVariants() {
  const usable = attributes.value.filter((a) => a.name.trim() !== '' && a.values.length > 0)
  const incomplete = attributes.value.length > 0 && usable.length !== attributes.value.length
  if (incomplete) {
    showToast('Define names and values for each attribute')
    return
  }
  if (attributes.value.length === 0) {
    variants.value = []
    return
  }

  const valueSets = usable.map((a) => ({
    name: a.name.trim(),
    values: Array.from(new Set(a.values.map((v) => v.trim()).filter(Boolean)))
  }))

  const names = valueSets.map((v) => v.name)
  const matrix = cartesianProduct(valueSets.map((v) => v.values))

  variants.value = matrix.map((combo, i) => ({
    id: `v-${Date.now()}-${i}`,
    attributes: combo.map((value, j) => ({ name: names[j], value })),
    sku: `SKU-${i + 1}`,
    price: null,
    stock: 0,
    enabled: true
  }))
  showToast(`${variants.value.length} variants generated`)
}

function removeVariant(index: number) {
  variants.value.splice(index, 1)
}

const dragOver = ref(false)
const objectUrls: string[] = []

function addFile(file: File) {
  if (!file.type.startsWith('image/')) return
  const url = URL.createObjectURL(file)
  objectUrls.push(url)
  images.value.push({ id: Date.now() + Math.random(), url, name: file.name, isMain: images.value.length === 0 })
}

function onFiles(e: Event) {
  const input = e.target as HTMLInputElement
  if (!input.files) return
  for (const file of Array.from(input.files)) addFile(file)
  input.value = ''
}

function onDrop(e: DragEvent) {
  dragOver.value = false
  const files = e.dataTransfer?.files
  if (!files) return
  for (const file of Array.from(files)) addFile(file)
}

function addSamples() {
  const base = images.value.length
  for (let i = 0; i < 3; i++) {
    images.value.push({
      id: Date.now() + Math.random(),
      url: `https://picsum.photos/seed/addp${base + i}/400/400`,
      name: `Sample ${base + i + 1}`,
      isMain: images.value.length === 0
    })
  }
}

function pickFiles() {
  document.getElementById('image-input')?.click()
}

function setMain(id: number) {
  images.value.forEach((img) => (img.isMain = img.id === id))
}

function removeImage(id: number) {
  images.value = images.value.filter((img) => img.id !== id)
}

onBeforeUnmount(() => {
  for (const url of objectUrls) URL.revokeObjectURL(url)
})

function scrollToId(id: string) {
  document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'center' })
}

function validate(): boolean {
  errors.title = form.title.trim() ? '' : 'Title is required'
  errors.price = form.price > 0 ? '' : 'Price must be greater than 0'
  errors.brand = form.brand ? '' : 'Select a brand'
  errors.category = form.category ? '' : 'Select a category'
  errors.images = images.value.length ? '' : 'Add at least one image'

  if (variants.value.length) {
    const bad = variants.value.filter((v) => v.enabled && (v.sku.trim() === '' || v.stock < 0))
    errors.variants = bad.length
      ? `${bad.length} ${bad.length === 1 ? 'enabled variant needs' : 'enabled variants need'} a SKU and stock >= 0`
      : ''
  } else {
    errors.variants = ''
  }

  return Object.values(errors).every((v) => v === '')
}

function save() {
  const ok = validate()
  if (!ok) {
    if (errors.images) scrollToId('images')
    else if (errors.variants) scrollToId('variants')
    else scrollToId('basic')
    showToast('Please fix the highlighted fields')
    return
  }
  const variantLabel = variants.value.length ? `${variants.value.length} variants · ` : ''
  showToast(`Product "${form.title}" saved (${variantLabel}${formatPrice(form.price)})`)
  router.push({ name: 'admin-products' })
}

function saveDraft() {
  showToast('Draft saved locally')
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
  <form class="mx-auto max-w-5xl space-y-6 pb-16" @submit.prevent="save()">
    <div class="card flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <div class="section-eyebrow">Catalog</div>
        <h1 class="text-xl font-bold text-ink">Add New Product</h1>
      </div>
      <div class="flex flex-wrap gap-2">
        <button class="btn-secondary btn-sm" type="button" @click="router.push({ name: 'admin-products' })">Cancel</button>
        <button class="btn-ghost btn-sm" type="button" @click="saveDraft()">Save Draft</button>
        <button class="btn-primary btn-sm" type="submit">
          <Save class="h-4 w-4" />
          Save Product
        </button>
      </div>
    </div>

    <div id="basic" class="card p-6">
      <div class="section-eyebrow">Step 1</div>
      <h2 class="mt-2 text-lg font-semibold">Basic Information</h2>
      <div class="mt-5 grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
          <label class="label" for="p-title">Title</label>
          <input
            id="p-title"
            v-model="form.title"
            class="input"
            :class="{ 'input-error': errors.title }"
            placeholder="Product name"
          />
          <p v-if="errors.title" class="mt-1 text-xs text-red-600">{{ errors.title }}</p>
        </div>

        <div>
          <label class="label" for="p-brand">Brand</label>
          <select id="p-brand" v-model="form.brand" class="select" :class="{ 'input-error': errors.brand }">
            <option value="" disabled>Select brand</option>
            <option v-for="b in requiredBrandNames()" :key="b" :value="b">{{ b }}</option>
          </select>
          <p v-if="errors.brand" class="mt-1 text-xs text-red-600">{{ errors.brand }}</p>
        </div>

        <div>
          <label class="label" for="p-category">Category</label>
          <select id="p-category" v-model="form.category" class="select" :class="{ 'input-error': errors.category }">
            <option value="" disabled>Select category</option>
            <option v-for="c in categoryNames" :key="c" :value="c">{{ c }}</option>
          </select>
          <p v-if="errors.category" class="mt-1 text-xs text-red-600">{{ errors.category }}</p>
        </div>

        <div>
          <label class="label" for="p-sku">SKU</label>
          <input id="p-sku" v-model="form.sku" class="input" placeholder="AUTOGEN-001" />
        </div>

        <div>
          <label class="label" for="p-status">Status</label>
          <select id="p-status" v-model="form.status" class="select">
            <option>Active</option>
            <option>Draft</option>
            <option>Archived</option>
          </select>
        </div>

        <div class="sm:col-span-2">
          <label class="label" for="p-description">Description</label>
          <textarea
            id="p-description"
            v-model="form.description"
            class="textarea"
            rows="4"
            placeholder="Describe the product…"
          ></textarea>
        </div>
      </div>
    </div>

    <div id="pricing" class="card p-6">
      <div class="section-eyebrow">Step 2</div>
      <h2 class="mt-2 text-lg font-semibold">Pricing &amp; Discount</h2>
      <div class="mt-5 grid gap-4 sm:grid-cols-3">
        <div>
          <label class="label" for="p-price">Price</label>
          <input id="p-price" v-model.number="form.price" class="input" type="number" min="0" step="0.01" placeholder="0.00" />
          <p v-if="errors.price" class="mt-1 text-xs text-red-600">{{ errors.price }}</p>
        </div>
        <div>
          <label class="label" for="p-compare">Compare-at Price</label>
          <input id="p-compare" v-model.number="form.compareAt" class="input" type="number" min="0" step="0.01" placeholder="0.00" />
        </div>
        <div>
          <label class="label">Discount</label>
          <div class="flex h-[42px] items-center">
            <span
              v-if="discountPct < 0"
              class="inline-flex items-center gap-1 rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-600"
            >
              {{ discountPct }}%
            </span>
            <span v-else class="text-sm text-gray-400">—</span>
          </div>
        </div>
      </div>

      <div v-if="form.price > 0" class="mt-5 rounded-xl bg-canvas p-4">
        <div class="flex items-baseline gap-3">
          <span class="text-2xl font-bold text-ink">{{ formatPrice(form.price) }}</span>
          <span v-if="discountPct < 0" class="text-sm text-gray-400 line-through">{{ formatPrice(Number(form.compareAt)) }}</span>
          <span v-if="discountPct < 0" class="text-sm font-semibold text-red-600">{{ discountPct }}% off</span>
        </div>
      </div>
    </div>

    <div id="variants" class="card p-6">
      <div class="section-eyebrow">Step 3</div>
      <h2 class="mt-2 text-lg font-semibold">Variant Attributes</h2>
      <p class="mt-1 text-sm text-gray-500">
        Define attributes like Color and Size to auto-generate SKU combinations.
      </p>

      <div class="mt-5 space-y-4">
        <div v-for="(attr, i) in attributes" :key="i" class="grid gap-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
          <div>
            <label class="label" :for="`attr-name-${i}`">Attribute name</label>
            <input :id="`attr-name-${i}`" v-model="attr.name" class="input" placeholder="e.g. Color" />
          </div>
          <div>
            <label class="label" :for="`attr-values-${i}`">Values (comma-separated)</label>
            <input
              :id="`attr-values-${i}`"
              class="input"
              placeholder="e.g. Black, White"
              :value="attr.values.join(', ')"
              @input="setAttrValues(attr, ($event.target as HTMLInputElement).value)"
            />
          </div>
          <button class="btn-icon h-10 w-10 hover:text-red-600" type="button" title="Remove attribute" @click="removeAttribute(i)">
            <X class="h-4 w-4" />
          </button>
        </div>

        <button type="button" class="btn-secondary btn-sm mt-3" @click="addAttribute()">
          <Plus class="h-4 w-4" />
          Add attribute
        </button>
      </div>

      <div class="mt-6">
        <button type="button" class="btn-primary" @click="generateVariants()">
          <RefreshCw class="h-4 w-4" />
          Generate Variants
        </button>
      </div>

      <div v-if="variants.length" class="mt-6">
        <div class="mb-3 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-ink">{{ variants.length }} variants generated</h3>
        </div>
        <div class="overflow-x-auto rounded-lg border border-border-gray">
          <table class="w-full text-sm">
            <thead class="bg-canvas text-left text-xs uppercase tracking-wide text-gray-500">
              <tr>
                <th class="px-3 py-2"></th>
                <th class="px-3 py-2">Attribute combination</th>
                <th class="px-3 py-2">SKU</th>
                <th class="px-3 py-2">Price</th>
                <th class="px-3 py-2">Stock</th>
                <th class="px-3 py-2"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border-gray">
              <tr v-for="(v, i) in variants" :key="v.id">
                <td class="px-3 py-2">
                  <input type="checkbox" v-model="v.enabled" class="h-4 w-4 accent-primary" />
                </td>
                <td class="px-3 py-2 font-medium text-ink">
                  {{ v.attributes.map((a) => a.value).join(' · ') }}
                </td>
                <td class="px-3 py-2">
                  <input v-model="v.sku" class="input px-2 py-1 text-xs" />
                </td>
                <td class="px-3 py-2">
                  <input v-model.number="v.price" class="input px-2 py-1 text-xs" type="number" min="0" step="0.01" :placeholder="form.price ? String(form.price) : '0.00'" />
                </td>
                <td class="px-3 py-2">
                  <input v-model.number="v.stock" class="input px-2 py-1 text-xs" type="number" min="0" />
                </td>
                <td class="px-3 py-2">
                  <button class="btn-icon h-8 w-8 hover:text-red-600" type="button" title="Remove variant" @click="removeVariant(i)">
                    <X class="h-4 w-4" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-if="errors.variants" class="mt-2 flex items-center gap-1 text-xs text-red-600">
          <AlertTriangle class="h-3.5 w-3.5" />
          {{ errors.variants }}
        </p>
      </div>
    </div>

    <div id="inventory" class="card p-6">
      <div class="section-eyebrow">Step 4</div>
      <h2 class="mt-2 text-lg font-semibold">Inventory &amp; Stock</h2>
      <div class="mt-5 grid gap-4 sm:grid-cols-2">
        <div>
          <label class="label" for="p-stock">Base stock (products without variants)</label>
          <input id="p-stock" v-model.number="form.baseStock" class="input" type="number" min="0" placeholder="0" />
        </div>
        <div>
          <label class="label">Track inventory</label>
          <button
            type="button"
            role="switch"
            :aria-checked="form.trackInventory"
            class="relative h-6 w-11 rounded-full transition-colors"
            :class="form.trackInventory ? 'bg-emerald-500' : 'bg-gray-200'"
            @click="form.trackInventory = !form.trackInventory"
          >
            <span
              class="absolute top-0.5 h-5 w-5 rounded-full bg-white shadow transition-all"
              :class="form.trackInventory ? 'left-[22px]' : 'left-0.5'"
            ></span>
          </button>
        </div>
      </div>
      <p class="mt-3 flex items-center gap-1 text-xs text-gray-500">
        <Check class="h-3.5 w-3.5 text-emerald-500" />
        Per-variant stock is managed in the variant matrix above.
      </p>
    </div>

    <div id="images" class="card p-6">
      <div class="section-eyebrow">Step 5</div>
      <h2 class="mt-2 text-lg font-semibold">Images</h2>

      <div
        class="mt-5 rounded-xl border-2 border-dashed p-10 text-center transition"
        :class="dragOver ? 'border-primary bg-primary/5' : 'border-gray-300'"
        @dragover.prevent="dragOver = true"
        @dragleave.prevent="dragOver = false"
        @drop.prevent="onDrop"
      >
        <UploadCloud class="mx-auto h-10 w-10 text-gray-400" />
        <p class="mt-3 text-sm font-medium text-ink">Drag &amp; drop images here, or click to browse</p>
        <p class="mt-1 text-xs text-gray-400">PNG, JPG, WEBP up to 5MB</p>
        <input id="image-input" type="file" accept="image/*" multiple class="hidden" @change="onFiles" />
        <div class="mt-4 flex flex-wrap justify-center gap-2">
          <button class="btn-secondary btn-sm" type="button" @click="pickFiles()">
            <ImagePlus class="h-4 w-4" />
            Browse files
          </button>
          <button class="btn-ghost btn-sm" type="button" @click="addSamples()">Add sample images</button>
        </div>
      </div>
      <p v-if="errors.images" class="mt-2 text-xs text-red-600">{{ errors.images }}</p>

      <div v-if="images.length" class="mt-4 grid grid-cols-4 gap-3">
        <div v-for="img in images" :key="img.id" class="group relative">
          <img :src="img.url" :alt="img.name" class="aspect-square w-full rounded-lg object-cover" />
          <button
            class="absolute right-1 top-1 flex h-6 w-6 items-center justify-center rounded-full bg-white shadow hover:text-red-600"
            type="button"
            title="Remove"
            @click="removeImage(img.id)"
          >
            <X class="h-3.5 w-3.5" />
          </button>
          <button
            class="absolute bottom-1 left-1 flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold"
            :class="img.isMain ? 'bg-accent text-ink' : 'bg-white/90 text-gray-500 hover:text-ink'"
            type="button"
            @click="setMain(img.id)"
          >
            <Star v-if="img.isMain" class="h-3 w-3 fill-current" />
            <Star v-else class="h-3 w-3" />
            {{ img.isMain ? 'Main' : 'Set main' }}
          </button>
        </div>
      </div>
    </div>

    <div class="flex flex-wrap items-center justify-end gap-2">
      <button class="btn-secondary" type="button" @click="router.push({ name: 'admin-products' })">Cancel</button>
      <button class="btn-primary" type="submit">
        <Save class="h-4 w-4" />
        Save Product
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
