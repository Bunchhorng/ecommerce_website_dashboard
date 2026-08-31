<script setup lang="ts">
import { computed, ref, reactive, watch } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import {
  ZoomIn,
  Heart,
  ShoppingCart,
  Truck,
  RefreshCw,
  ShieldCheck,
  CreditCard,
  BadgeCheck,
  Zap
} from 'lucide-vue-next'
import StarRating from '@/components/StarRating.vue'
import QuantityCounter from '@/components/QuantityCounter.vue'
import BaseBadge from '@/components/BaseBadge.vue'
import StatusTag from '@/components/StatusTag.vue'
import EmptyState from '@/components/EmptyState.vue'
import { getProductBySlug, getReviewsForProduct } from '@/data/mock'
import type { ProductVariantAttribute } from '@/types'
import { useCartStore } from '@/stores/cart'
import { useWishlistStore } from '@/stores/wishlist'
import { useUiStore } from '@/stores/ui'
import { formatPrice, formatDate } from '@/utils/format'

const route = useRoute()
const router = useRouter()
const cart = useCartStore()
const wishlist = useWishlistStore()
const ui = useUiStore()

const slug = computed(() => String(route.params.slug ?? ''))
const product = computed(() => getProductBySlug(slug.value))

const selectedImage = ref(product.value?.images[0] ?? null)
const quantity = ref(1)
const selectedOptions = reactive<Record<string, string>>({})
const zoomActive = ref(false)
const zoomX = ref(50)
const zoomY = ref(50)
const lightboxOpen = ref(false)
const activeTab = ref('description')
const writeReviewNote = ref(false)

function openWriteReview() {
  writeReviewNote.value = true
}

const TABS = [
  { key: 'description', label: 'Description' },
  { key: 'specifications', label: 'Specifications' },
  { key: 'shipping', label: 'Shipping & Returns' },
  { key: 'reviews', label: 'Reviews' }
]

function resetProduct(p: ReturnType<typeof getProductBySlug>) {
  for (const key of Object.keys(selectedOptions)) {
    delete selectedOptions[key]
  }
  if (!p) return
  selectedImage.value = p.images[0] ?? null
  quantity.value = 1
  const first = p.variants.find((v) => v.isInStock) ?? p.variants[0]
  if (first) {
    for (const a of first.attributes) selectedOptions[a.name] = a.value
  }
}

watch(
  () => route.params.slug,
  () => {
    resetProduct(product.value)
  }
)

resetProduct(product.value)

const mainImage = computed(() => selectedImage.value ?? product.value?.images[0] ?? null)

const attributeGroups = computed<{ name: string; values: ProductVariantAttribute[] }[]>(() => {
  const p = product.value
  if (!p) return []
  const map = new Map<string, ProductVariantAttribute[]>()
  for (const v of p.variants) {
    for (const a of v.attributes) {
      const arr = map.get(a.name) ?? []
      if (!arr.some((x) => x.value === a.value)) arr.push({ name: a.name, value: a.value })
      map.set(a.name, arr)
    }
  }
  return Array.from(map.entries()).map(([name, values]) => ({ name, values }))
})

const selectedVariant = computed(() => {
  const p = product.value
  if (!p) return undefined
  return p.variants.find((v) => v.attributes.every((a) => selectedOptions[a.name] === a.value))
})

const hasCombination = computed(() => {
  const p = product.value
  if (!p) return true
  if (!p.variants.length) return true
  return Boolean(
    p.variants.some((v) => v.attributes.every((a) => selectedOptions[a.name] === a.value))
  )
})

function optionAvailable(name: string, value: string): boolean {
  const p = product.value
  if (!p) return true
  const candidate = { ...selectedOptions, [name]: value }
  return p.variants.some((v) => v.attributes.every((a) => candidate[a.name] === a.value))
}

function selectOption(name: string, value: string) {
  if (!optionAvailable(name, value)) return
  selectedOptions[name] = value
}

const groupColorValues = computed<string[]>(() => {
  const g = attributeGroups.value.find((x) => x.name === 'Color')
  return g ? g.values.map((v) => v.value) : []
})

function colorHex(value: string): string {
  const idx = groupColorValues.value.indexOf(value)
  return product.value?.colors[idx] ?? '#CBD5E1'
}

const currentPrice = computed(() => selectedVariant.value?.price ?? product.value?.price ?? 0)

const currentCompareAt = computed(() => {
  if (selectedVariant.value?.compareAtPrice) return selectedVariant.value.compareAtPrice
  return product.value?.compareAtPrice ?? null
})

const currentStock = computed(() => selectedVariant.value?.stockQuantity ?? product.value?.stockQuantity ?? 0)

const discountPercent = computed(() => {
  if (currentCompareAt.value && currentCompareAt.value > currentPrice.value) {
    return Math.round(((currentCompareAt.value - currentPrice.value) / currentCompareAt.value) * 100)
  }
  return product.value?.discountPercent ?? null
})

const saveAmount = computed(() =>
  currentCompareAt.value && currentCompareAt.value > currentPrice.value
    ? currentCompareAt.value - currentPrice.value
    : 0
)

const stockStatus = computed<'out' | 'low' | 'in'>(() => {
  const p = product.value
  if (!p) return 'out'
  const variantOut = selectedVariant.value && !selectedVariant.value.isInStock
  if (variantOut || !p.isInStock) return 'out'
  if (currentStock.value <= 5) return 'low'
  return 'in'
})

const stockLabel = computed(() => {
  switch (stockStatus.value) {
    case 'out':
      return 'Out of Stock'
    case 'low':
      return `Hurry, only ${currentStock.value} left`
    default:
      return 'In Stock'
  }
})

const zoomStyle = computed(() => ({
  transform: `scale(${zoomActive.value ? 1.75 : 1})`,
  transformOrigin: `${zoomX.value}% ${zoomY.value}%`
}))

function onZoomMove(e: MouseEvent) {
  const el = e.currentTarget as HTMLElement
  const rect = el.getBoundingClientRect()
  zoomX.value = ((e.clientX - rect.left) / rect.width) * 100
  zoomY.value = ((e.clientY - rect.top) / rect.height) * 100
}

function openLightbox() {
  if (mainImage.value) lightboxOpen.value = true
}

function addToCart() {
  const p = product.value
  if (!p) return
  cart.addItem({ product: p, variantId: selectedVariant.value?.id, quantity: quantity.value })
  ui.openCartDrawer()
}

function buyNow() {
  const p = product.value
  if (!p) return
  cart.addItem({ product: p, variantId: selectedVariant.value?.id, quantity: quantity.value })
  router.push('/checkout')
}

function toggleWishlist() {
  const p = product.value
  if (p) wishlist.toggle(p.id)
}

function goToTab(key: string) {
  activeTab.value = key
}

watch(selectedVariant, () => {
  if (quantity.value > currentStock.value) quantity.value = Math.max(1, currentStock.value)
})

const reviews = computed(() => (product.value ? getReviewsForProduct(product.value.id) : []))

const ratingBreakdown = computed(() => {
  const buckets = [5, 4, 3, 2, 1].map((star) => ({
    star,
    count: reviews.value.filter((r) => r.rating === star).length
  }))
  return buckets
})

const reviewTotal = computed(() => ratingBreakdown.value.reduce((sum, b) => sum + b.count, 0))

function bucketPercent(count: number): number {
  if (!reviewTotal.value) return 0
  return Math.round((count / reviewTotal.value) * 100)
}

const featureList = computed(() => {
  const p = product.value
  if (!p) return []
  return p.description
    .split('.')
    .map((s) => s.trim())
    .filter(Boolean)
    .slice(0, 4)
})
</script>

<template>
  <div class="container-app py-6">
    <template v-if="product">
      <nav class="flex items-center gap-1 text-xs text-gray-500">
        <RouterLink to="/" class="hover:text-primary">Home</RouterLink>
        <span>/</span>
        <span>{{ product.category.name }}</span>
        <span>/</span>
        <span class="truncate text-ink">{{ product.title }}</span>
      </nav>

      <div class="mt-6 grid gap-10 lg:grid-cols-2">
        <div>
          <div
            class="group relative overflow-hidden rounded-2xl border border-border-gray bg-white"
            @mouseenter="zoomActive = true"
            @mouseleave="zoomActive = false"
            @mousemove="onZoomMove"
          >
            <button class="absolute inset-0 z-10 cursor-zoom-in" aria-label="Zoom image" @click="openLightbox"></button>
            <img
              :src="mainImage?.url"
              :alt="mainImage?.alt ?? product.title"
              class="aspect-square w-full object-cover transition-transform duration-150"
              :style="zoomStyle"
            />
            <span v-if="zoomActive" class="absolute right-3 top-3 z-20 rounded-full bg-white/80 p-2 text-ink">
              <ZoomIn class="h-5 w-5" />
            </span>
            <span v-if="discountPercent" class="absolute left-4 top-4 z-20 rounded-lg bg-accent px-2.5 py-1 text-sm font-bold text-ink">-{{ discountPercent }}%</span>
            <span v-if="product.isNew" class="absolute left-4 top-14 z-20 rounded-lg bg-primary px-2.5 py-1 text-xs font-bold text-white">NEW</span>
          </div>

          <div class="mt-4 grid grid-cols-4 gap-3">
            <button
              v-for="img in product.images"
              :key="img.id"
              type="button"
              class="aspect-square overflow-hidden rounded-lg border-2 transition"
              :class="selectedImage?.id === img.id ? 'border-primary' : 'border-transparent hover:border-gray-300'"
              @click="selectedImage = img"
            >
              <img :src="img.url" :alt="img.alt ?? product.title" class="h-full w-full object-cover" />
            </button>
          </div>
        </div>

        <div>
          <div class="text-sm font-semibold uppercase tracking-wide text-primary">{{ product.brand.name }}</div>
          <h1 class="mt-1 text-2xl font-bold text-ink lg:text-3xl">{{ product.title }}</h1>

          <div class="mt-2 text-xs text-gray-500">SKU: {{ selectedVariant?.sku ?? product.sku }}</div>

          <div class="mt-3 flex items-center gap-2 text-sm">
            <StarRating :value="product.rating" :show-value="true" />
            <span class="text-xs text-gray-500">({{ product.reviewCount }} reviews)</span>
            <button class="text-xs font-medium text-primary hover:underline" @click="goToTab('reviews')">Write a review</button>
          </div>

          <div class="mt-4 flex flex-wrap items-center gap-3">
            <span class="text-3xl font-extrabold text-ink">{{ formatPrice(currentPrice) }}</span>
            <span v-if="currentCompareAt" class="text-lg text-gray-400 line-through">{{ formatPrice(currentCompareAt) }}</span>
            <span v-if="saveAmount" class="rounded-md bg-accent/20 px-2 py-0.5 text-sm font-semibold text-ink">Save {{ formatPrice(saveAmount) }}</span>
          </div>

          <div class="mt-3 flex items-center gap-2">
            <StatusTag :status="stockLabel" />
            <span class="text-xs text-gray-500">Available: {{ currentStock }}</span>
          </div>

          <template v-for="group in attributeGroups" :key="group.name">
            <div class="mt-6">
              <div class="mb-2 text-sm font-semibold text-ink">{{ group.name }}</div>

              <template v-if="group.name === 'Color'">
                <div class="flex flex-wrap gap-2">
                  <button
                    v-for="v in group.values"
                    :key="v.value"
                    type="button"
                    :disabled="!optionAvailable(group.name, v.value)"
                    :title="v.value"
                    class="h-9 w-9 rounded-full transition"
                    :class="[
                      !optionAvailable(group.name, v.value) && 'cursor-not-allowed opacity-40',
                      selectedOptions[group.name] === v.value
                        ? 'ring-2 ring-primary ring-offset-2'
                        : 'ring-1 ring-gray-300'
                    ]"
                    :style="{ backgroundColor: colorHex(v.value) }"
                    @click="selectOption(group.name, v.value)"
                  ></button>
                </div>
              </template>

              <template v-else>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-for="v in group.values"
                    :key="v.value"
                    type="button"
                    :disabled="!optionAvailable(group.name, v.value)"
                    class="rounded-lg border px-4 py-2 text-sm transition"
                    :class="[
                      !optionAvailable(group.name, v.value) && 'cursor-not-allowed opacity-40',
                      selectedOptions[group.name] === v.value
                        ? 'border-primary bg-primary/5 text-primary'
                        : 'border-border-gray text-ink hover:border-gray-300'
                    ]"
                    @click="selectOption(group.name, v.value)"
                  >
                    {{ v.value }}
                  </button>
                </div>
              </template>
            </div>
          </template>

          <div v-if="!hasCombination" class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-xs font-medium text-red-600">
            Combination unavailable. Please pick another option.
          </div>

          <div class="mt-6 flex flex-wrap items-center gap-3">
            <QuantityCounter v-model="quantity" :max="currentStock" />
            <button
              class="btn-primary btn-lg flex-1"
              :disabled="stockStatus === 'out' || !hasCombination"
              @click="addToCart"
            >
              <ShoppingCart class="h-5 w-5" />
              Add to Cart
            </button>
          </div>

          <div class="mt-3 flex flex-wrap items-center gap-3">
            <button
              class="btn-secondary btn-lg flex-1"
              :disabled="stockStatus === 'out' || !hasCombination"
              @click="buyNow"
            >
              Buy Now
            </button>
            <button
              type="button"
              class="btn-secondary btn-lg px-4"
              aria-label="Toggle wishlist"
              @click="toggleWishlist"
            >
              <Heart
                class="h-5 w-5"
                :class="wishlist.isWishlisted(product.id) ? 'fill-red-500 text-red-500' : ''"
              />
            </button>
          </div>

          <div class="mt-8 grid grid-cols-2 gap-3">
            <div class="flex items-center gap-3 rounded-lg bg-canvas p-3">
              <Truck class="h-5 w-5 text-primary" />
              <div class="text-xs text-gray-600">
                <div class="font-semibold text-ink">Free shipping</div>
                Over $100
              </div>
            </div>
            <div class="flex items-center gap-3 rounded-lg bg-canvas p-3">
              <RefreshCw class="h-5 w-5 text-primary" />
              <div class="text-xs text-gray-600">
                <div class="font-semibold text-ink">30-day returns</div>
                No questions asked
              </div>
            </div>
            <div class="flex items-center gap-3 rounded-lg bg-canvas p-3">
              <ShieldCheck class="h-5 w-5 text-primary" />
              <div class="text-xs text-gray-600">
                <div class="font-semibold text-ink">2-year warranty</div>
                Full coverage
              </div>
            </div>
            <div class="flex items-center gap-3 rounded-lg bg-canvas p-3">
              <CreditCard class="h-5 w-5 text-primary" />
              <div class="text-xs text-gray-600">
                <div class="font-semibold text-ink">Secure payment</div>
                SSL encrypted
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-12 border-b border-border-gray">
        <div class="flex flex-wrap gap-6">
          <button
            v-for="tab in TABS"
            :key="tab.key"
            type="button"
            class="border-b-2 pb-3 text-sm font-semibold transition"
            :class="activeTab === tab.key ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-ink'"
            @click="goToTab(tab.key)"
          >
            {{ tab.label }}
          </button>
        </div>
      </div>

      <div class="py-8">
        <div v-if="activeTab === 'description'">
          <p class="text-gray-600 leading-relaxed">{{ product.description }}</p>
          <div class="mt-6">
            <h3 class="text-lg font-bold text-ink">Key features</h3>
            <ul class="mt-3 space-y-2">
              <li v-for="(f, i) in featureList" :key="i" class="flex items-start gap-2 text-sm text-gray-600">
                <span class="mt-1.5 h-1.5 w-1.5 flex-none rounded-full bg-primary"></span>
                {{ f }}
              </li>
            </ul>
          </div>
        </div>

        <div v-else-if="activeTab === 'specifications'">
          <table class="w-full border-collapse overflow-hidden rounded-xl border border-border-gray text-sm">
            <tbody>
              <tr v-for="(spec, i) in product.specifications" :key="spec.label" :class="i % 2 ? 'bg-white' : 'bg-canvas'">
                <td class="border border-border-gray px-4 py-3 font-semibold text-ink">{{ spec.label }}</td>
                <td class="border border-border-gray px-4 py-3 text-gray-600">{{ spec.value }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else-if="activeTab === 'shipping'" class="grid gap-4 md:grid-cols-3">
          <div class="card p-5">
            <Truck class="h-6 w-6 text-primary" />
            <div class="mt-3 font-semibold text-ink">Standard</div>
            <p class="mt-1 text-sm text-gray-500">5–7 business days · Free over $100</p>
          </div>
          <div class="card p-5">
            <Zap class="h-6 w-6 text-accent" />
            <div class="mt-3 font-semibold text-ink">Express</div>
            <p class="mt-1 text-sm text-gray-500">2–3 business days · Tracked</p>
          </div>
          <div class="card p-5">
            <RefreshCw class="h-6 w-6 text-primary" />
            <div class="mt-3 font-semibold text-ink">Returns</div>
            <p class="mt-1 text-sm text-gray-500">Within 30 days for a full refund</p>
          </div>
        </div>

        <div v-else-if="activeTab === 'reviews'">
          <div class="grid gap-8 lg:grid-cols-3">
            <div class="card h-fit p-6">
              <div class="text-4xl font-extrabold text-ink">{{ product.rating }}</div>
              <div class="mt-2">
                <StarRating :value="product.rating" />
              </div>
              <div class="mt-1 text-sm text-gray-500">Based on {{ product.reviewCount }} reviews</div>
              <div class="mt-5 space-y-2">
                <div v-for="b in ratingBreakdown" :key="b.star" class="flex items-center gap-2">
                  <span class="w-3 text-xs text-gray-500">{{ b.star }}</span>
                  <div class="h-2 flex-1 overflow-hidden rounded-full bg-canvas">
                    <div class="h-full rounded-full bg-accent" :style="{ width: bucketPercent(b.count) + '%' }"></div>
                  </div>
                </div>
              </div>
              <button class="btn-outline mt-6 w-full" @click="openWriteReview">Write a review</button>
            </div>

            <div class="lg:col-span-2">
              <h3 class="text-lg font-bold text-ink">Verified Customer Reviews</h3>
              <p v-if="writeReviewNote" class="mt-2 rounded-lg bg-canvas px-3 py-2 text-xs text-gray-600">Sign in to review</p>

              <div v-if="reviews.length" class="mt-5 space-y-5">
                <div v-for="r in reviews" :key="r.id" class="card p-5">
                  <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                      <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-sm font-bold text-white">
                        {{ r.author.charAt(0).toUpperCase() }}
                      </div>
                      <div>
                        <div class="flex items-center gap-1.5 text-sm font-semibold text-ink">
                          {{ r.author }}
                          <BadgeCheck v-if="r.verified" class="h-4 w-4 text-primary" />
                        </div>
                        <div class="flex items-center gap-2">
                          <StarRating :value="r.rating" size="sm" />
                          <span class="text-xs text-gray-400">{{ formatDate(r.date) }}</span>
                        </div>
                      </div>
                    </div>
                    <BaseBadge v-if="!r.verified" variant="neutral">Unverified purchase</BaseBadge>
                  </div>
                  <div class="mt-3">
                    <div class="font-semibold text-ink">{{ r.title }}</div>
                    <p class="mt-1 text-sm text-gray-600">{{ r.body }}</p>
                  </div>
                </div>
              </div>

              <div v-else class="mt-5">
                <EmptyState
                  title="No reviews yet"
                  description="Be the first to share your experience."
                  cta-label="Write a review"
                  @cta="openWriteReview"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <template v-else>
      <EmptyState
        title="Product not found"
        description="The product you are looking for does not exist or has been removed."
        cta-label="Back to shop"
        @cta="router.push('/shop')"
      />
    </template>

    <Teleport to="body">
      <div v-if="lightboxOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-6" @click="lightboxOpen = false">
        <img :src="mainImage?.url" :alt="mainImage?.alt ?? ''" class="max-h-full max-w-full rounded-xl object-contain" />
      </div>
    </Teleport>
  </div>
</template>
