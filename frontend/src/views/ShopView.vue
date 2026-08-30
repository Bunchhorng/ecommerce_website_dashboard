<script setup lang="ts">
import { computed, reactive, watch } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { SlidersHorizontal, X, SearchX } from 'lucide-vue-next'
import ProductCard from '@/components/ProductCard.vue'
import BasePagination from '@/components/BasePagination.vue'
import EmptyState from '@/components/EmptyState.vue'
import StarRating from '@/components/StarRating.vue'
import { PRODUCTS, NAV_CATEGORIES, BRANDS } from '@/data/mock'
import { useCartStore } from '@/stores/cart'
import { useWishlistStore } from '@/stores/wishlist'
import { useUiStore } from '@/stores/ui'

interface Filters {
  q?: string
  category?: string
  brand?: string
  colors: string[]
  sizes: string[]
  min?: number
  max?: number
  rating?: number
  inStockOnly: boolean
  sort: string
  page: number
}

const route = useRoute()
const router = useRouter()
const cart = useCartStore()
const wishlist = useWishlistStore()
const ui = useUiStore()

const pageSize = 8
const SIZES = ['S', 'M', 'L', 'US 9', 'US 10', 'US 11', '30 ml']
const PRICE_MAX = 450

const filters = reactive<Filters>({
  q: undefined,
  category: undefined,
  brand: undefined,
  colors: [],
  sizes: [],
  min: undefined,
  max: undefined,
  rating: undefined,
  inStockOnly: false,
  sort: 'featured',
  page: 1
})

let applying = false

function toArray(v: unknown): string[] {
  if (Array.isArray(v)) return v.filter((x): x is string => typeof x === 'string')
  if (typeof v === 'string') return v ? [v] : []
  return []
}

function toNum(v: unknown): number | undefined {
  if (typeof v === 'string' && v !== '' && !Number.isNaN(Number(v))) return Number(v)
  return undefined
}

function applyFromQuery(q: Record<string, unknown>) {
  applying = true
  filters.q = typeof q.q === 'string' ? q.q : undefined
  filters.category = typeof q.category === 'string' ? q.category : undefined
  filters.brand = typeof q.brand === 'string' ? q.brand : undefined
  filters.colors = toArray(q.colors)
  filters.sizes = toArray(q.sizes)
  filters.min = toNum(q.min)
  filters.max = toNum(q.max)
  filters.rating = toNum(q.rating)
  filters.inStockOnly = q.stock === '1'
  filters.sort = typeof q.sort === 'string' ? q.sort : 'featured'
  const page = toNum(q.page)
  filters.page = page !== undefined && page > 0 ? page : 1
  applying = false
}

function pushRoute() {
  if (applying) return
  const query: Record<string, string | string[]> = {}
  if (filters.q) query.q = filters.q
  if (filters.category) query.category = filters.category
  if (filters.brand) query.brand = filters.brand
  if (filters.colors.length) query.colors = filters.colors
  if (filters.sizes.length) query.sizes = filters.sizes
  if (filters.min !== undefined) query.min = String(filters.min)
  if (filters.max !== undefined) query.max = String(filters.max)
  if (filters.rating !== undefined) query.rating = String(filters.rating)
  if (filters.inStockOnly) query.stock = '1'
  if (filters.sort !== 'featured') query.sort = filters.sort
  if (filters.page > 1) query.page = String(filters.page)
  router.replace({ query })
}

watch(filters, pushRoute, { deep: true })
watch(() => route.query, (q) => applyFromQuery(q))
applyFromQuery(route.query)

const filteredProducts = computed(() => {
  let list = PRODUCTS.filter((p) => {
    if (filters.q) {
      const hay = `${p.title} ${p.brand.name} ${p.category.name}`.toLowerCase()
      if (!hay.includes(filters.q.toLowerCase())) return false
    }
    if (filters.category) {
      if (filters.category === 'sale') {
        if (!p.compareAtPrice) return false
      } else if (p.category.slug !== filters.category) {
        return false
      }
    }
    if (filters.brand && p.brand.name !== filters.brand) return false
    if (filters.colors.length && !filters.colors.some((c) => p.colors.includes(c))) return false
    if (filters.sizes.length && !filters.sizes.some((s) => p.sizes.includes(s))) return false
    if (filters.min !== undefined && p.price < filters.min) return false
    if (filters.max !== undefined && p.price > filters.max) return false
    if (filters.rating !== undefined && p.rating < filters.rating) return false
    if (filters.inStockOnly && !p.isInStock) return false
    return true
  })

  switch (filters.sort) {
    case 'price-asc':
      list.sort((a, b) => a.price - b.price)
      break
    case 'price-desc':
      list.sort((a, b) => b.price - a.price)
      break
    case 'rating':
      list.sort((a, b) => b.rating - a.rating || b.reviewCount - a.reviewCount)
      break
    default:
      list.sort((a, b) => Number(b.isFeatured) - Number(a.isFeatured) || b.rating - a.rating)
  }
  return list
})

const pageCount = computed(() => Math.max(1, Math.ceil(filteredProducts.value.length / pageSize)))

const pagedProducts = computed(() => {
  const start = (filters.page - 1) * pageSize
  return filteredProducts.value.slice(start, start + pageSize)
})

const categoryCounts = computed(() => {
  const map = new Map<string, number>()
  for (const c of NAV_CATEGORIES) {
    if (c.slug === 'sale') {
      map.set('sale', PRODUCTS.filter((p) => p.compareAtPrice).length)
    } else {
      map.set(c.slug, PRODUCTS.filter((p) => p.category.slug === c.slug).length)
    }
  }
  return map
})

const brandOptions = computed(() =>
  BRANDS.map((b) => ({
    name: b.name,
    count: PRODUCTS.filter((p) => p.brand.name === b.name).length
  }))
)

const colorOptions = computed(() => Array.from(new Set(PRODUCTS.flatMap((p) => p.colors))))

const activeCategoryName = computed(() => {
  if (!filters.category) return ''
  const c = NAV_CATEGORIES.find((x) => x.slug === filters.category)
  return c ? c.name : ''
})

const summaryChips = computed(() => {
  const chips: { key: string; label: string }[] = []
  if (filters.q) chips.push({ key: 'q', label: `“${filters.q}”` })
  if (filters.category) {
    const c = NAV_CATEGORIES.find((x) => x.slug === filters.category)
    chips.push({ key: 'category', label: c ? c.name : filters.category })
  }
  if (filters.brand) chips.push({ key: 'brand', label: filters.brand })
  return chips
})

function setSort(v: string) {
  filters.page = 1
  filters.sort = v
}

function removeChip(key: string) {
  filters.page = 1
  if (key === 'q') filters.q = undefined
  if (key === 'category') filters.category = undefined
  if (key === 'brand') filters.brand = undefined
}

function toggleColor(hex: string) {
  filters.page = 1
  const idx = filters.colors.indexOf(hex)
  if (idx >= 0) filters.colors.splice(idx, 1)
  else filters.colors.push(hex)
}

function toggleSize(size: string) {
  filters.page = 1
  const idx = filters.sizes.indexOf(size)
  if (idx >= 0) filters.sizes.splice(idx, 1)
  else filters.sizes.push(size)
}

function setMin(value: number) {
  filters.page = 1
  filters.min = value
  if (filters.max !== undefined && filters.min !== undefined && filters.min > filters.max) {
    filters.max = filters.min
  }
}

function setMax(value: number) {
  filters.page = 1
  filters.max = value
  if (filters.min !== undefined && filters.max !== undefined && filters.max < filters.min) {
    filters.min = filters.max
  }
}

function setRating(value: number | undefined) {
  filters.page = 1
  filters.rating = value
}

function setCategory(slug: string | undefined) {
  filters.page = 1
  filters.category = slug
}

function setBrand(name: string | undefined) {
  filters.page = 1
  filters.brand = name
}

function setInStock(value: boolean) {
  filters.page = 1
  filters.inStockOnly = value
}

function resetFilters() {
  applying = true
  filters.q = undefined
  filters.category = undefined
  filters.brand = undefined
  filters.colors = []
  filters.sizes = []
  filters.min = undefined
  filters.max = undefined
  filters.rating = undefined
  filters.inStockOnly = false
  filters.sort = 'featured'
  filters.page = 1
  applying = false
  router.replace({ query: {} })
}

function clearAll() {
  resetFilters()
}

function setPage(p: number) {
  filters.page = p
}
</script>

<template>
  <div class="container-app py-6">
    <nav class="text-xs text-gray-500">
      <RouterLink to="/" class="hover:text-primary">Home</RouterLink>
      <span class="mx-1">/</span>
      <RouterLink to="/shop" class="hover:text-primary">Shop</RouterLink>
      <span v-if="filters.category" class="mx-1">/</span>
      <span v-if="filters.category" class="font-medium text-ink">{{ activeCategoryName }}</span>
      <template v-else-if="filters.q">
        <span class="mx-1">/</span>
        <span class="font-medium text-ink">Search results</span>
      </template>
    </nav>

    <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-ink sm:text-3xl">Shop Products</h1>
        <p class="mt-1 text-sm text-gray-500">
          <template v-if="filters.q || filters.category || filters.brand || filters.colors.length || filters.sizes.length || filters.min !== undefined || filters.max !== undefined || filters.rating !== undefined || filters.inStockOnly">
            Showing {{ pagedProducts.length }} of {{ filteredProducts.length }} products
          </template>
          <template v-else>
            {{ filteredProducts.length }} products
          </template>
        </p>
      </div>
    </div>

    <div class="card mt-5 flex items-center justify-between gap-3 p-3">
      <div class="flex flex-wrap items-center gap-2">
        <button class="btn-secondary btn-sm lg:hidden" @click="ui.openMobileFilters()">
          <SlidersHorizontal class="h-4 w-4" />
          Filters
        </button>
        <div v-if="summaryChips.length" class="flex flex-wrap items-center gap-2">
          <span
            v-for="chip in summaryChips"
            :key="chip.key"
            class="chip"
          >
            {{ chip.label }}
            <button class="ml-1 text-gray-400 hover:text-gray-700" @click="removeChip(chip.key)">
              <X class="h-3 w-3" />
            </button>
          </span>
        </div>
      </div>
      <select class="select w-auto" :value="filters.sort" @change="setSort(($event.target as HTMLSelectElement).value)">
        <option value="featured">Featured</option>
        <option value="price-asc">Price: Low to High</option>
        <option value="price-desc">Price: High to Low</option>
        <option value="rating">Highest Rated</option>
      </select>
    </div>

    <div class="mt-6 lg:grid lg:grid-cols-[260px_1fr] lg:gap-8">
      <aside class="hidden lg:block">
        <div class="card space-y-6 p-5">
          <div>
            <div class="label">Category</div>
            <div class="space-y-2">
              <label v-for="c in NAV_CATEGORIES" :key="c.id" class="flex cursor-pointer items-center justify-between text-sm text-gray-700 hover:text-primary">
                <span class="flex items-center gap-2">
                  <input
                    type="radio"
                    name="category"
                    :checked="filters.category === c.slug"
                    class="h-4 w-4 accent-primary"
                    @change="setCategory(filters.category === c.slug ? undefined : c.slug)"
                  />
                  {{ c.name }}
                </span>
                <span class="text-xs text-gray-400">{{ categoryCounts.get(c.slug) ?? 0 }}</span>
              </label>
            </div>
          </div>

          <div>
            <div class="label">Brand</div>
            <div class="space-y-2">
              <label v-for="b in brandOptions" :key="b.name" class="flex cursor-pointer items-center justify-between text-sm text-gray-700 hover:text-primary">
                <span class="flex items-center gap-2">
                  <input
                    type="radio"
                    name="brand"
                    :checked="filters.brand === b.name"
                    class="h-4 w-4 accent-primary"
                    @change="setBrand(filters.brand === b.name ? undefined : b.name)"
                  />
                  {{ b.name }}
                </span>
                <span class="text-xs text-gray-400">{{ b.count }}</span>
              </label>
            </div>
          </div>

          <div>
            <div class="label">Price Range</div>
            <div class="px-1">
              <input type="range" min="0" :max="PRICE_MAX" step="5" :value="filters.min ?? 0" class="w-full accent-primary" @input="setMin(Number(($event.target as HTMLInputElement).value))" />
              <input type="range" min="0" :max="PRICE_MAX" step="5" :value="filters.max ?? PRICE_MAX" class="w-full accent-primary" @input="setMax(Number(($event.target as HTMLInputElement).value))" />
            </div>
            <div class="mt-2 flex items-center justify-between text-xs font-medium text-gray-600">
              <span class="chip">${{ filters.min ?? 0 }}</span>
              <span>–</span>
              <span class="chip">${{ filters.max ?? PRICE_MAX }}</span>
            </div>
          </div>

          <div>
            <div class="label">Rating</div>
            <div class="space-y-2">
              <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
                <input type="radio" name="rating" :checked="filters.rating === 4.5" class="h-4 w-4 accent-primary" @change="setRating(filters.rating === 4.5 ? undefined : 4.5)" />
                <StarRating :value="4.5" size="sm" />
                <span class="text-xs text-gray-400">&amp; up</span>
              </label>
              <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
                <input type="radio" name="rating" :checked="filters.rating === 4.0" class="h-4 w-4 accent-primary" @change="setRating(filters.rating === 4.0 ? undefined : 4.0)" />
                <StarRating :value="4.0" size="sm" />
                <span class="text-xs text-gray-400">&amp; up</span>
              </label>
              <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
                <input type="radio" name="rating" :checked="filters.rating === 3.5" class="h-4 w-4 accent-primary" @change="setRating(filters.rating === 3.5 ? undefined : 3.5)" />
                <StarRating :value="3.5" size="sm" />
                <span class="text-xs text-gray-400">&amp; up</span>
              </label>
              <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
                <input type="radio" name="rating" :checked="filters.rating === undefined" class="h-4 w-4 accent-primary" @change="setRating(undefined)" />
                <span>Any</span>
              </label>
            </div>
          </div>

          <div>
            <div class="label">Color</div>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="hex in colorOptions"
                :key="hex"
                type="button"
                class="h-8 w-8 rounded-full border-2 transition"
                :class="filters.colors.includes(hex) ? 'border-primary ring-2 ring-primary/30' : 'border-gray-300 hover:border-gray-400'"
                :style="{ backgroundColor: hex }"
                @click="toggleColor(hex)"
              ></button>
            </div>
          </div>

          <div>
            <div class="label">Size</div>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="s in SIZES"
                :key="s"
                type="button"
                class="rounded-lg border px-3 py-1.5 text-sm transition"
                :class="filters.sizes.includes(s) ? 'border-primary bg-primary/5 text-primary' : 'border-border-gray text-gray-600 hover:border-gray-300'"
                @click="toggleSize(s)"
              >
                {{ s }}
              </button>
            </div>
          </div>

          <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" :checked="filters.inStockOnly" class="h-4 w-4 accent-primary" @change="setInStock(($event.target as HTMLInputElement).checked)" />
            In stock only
          </label>

          <button class="btn-outline w-full" @click="clearAll">Clear all</button>
        </div>
      </aside>

      <div class="mt-6 lg:mt-0">
        <div v-if="pagedProducts.length" class="grid grid-cols-2 gap-5 md:grid-cols-3 xl:grid-cols-4">
          <ProductCard
            v-for="p in pagedProducts"
            :key="p.id"
            :product="p"
            @add-to-cart="cart.addItem($event)"
            @wishlist-toggle="wishlist.toggle($event)"
          />
        </div>

        <EmptyState
          v-else
          title="No products match your filters"
          description="Try adjusting or clearing filters."
          cta-label="Clear filters"
          @cta="clearAll()"
        >
          <template #icon>
            <SearchX class="h-10 w-10 text-gray-300" />
          </template>
        </EmptyState>

        <BasePagination
          :page="filters.page"
          :page-count="pageCount"
          :total-items="filteredProducts.length"
          :page-size="pageSize"
          @update:page="setPage"
        />
      </div>
    </div>

    <Teleport to="body">
      <div
        v-if="ui.mobileFiltersOpen"
        class="fixed inset-0 z-50 lg:hidden"
      >
        <div class="absolute inset-0 bg-black/50" @click="ui.closeMobileFilters()"></div>
        <div class="absolute inset-y-0 right-0 w-80 max-w-[85vw] overflow-y-auto bg-white p-5 shadow-2xl">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-bold text-ink">Filters</h3>
            <button class="btn-icon" @click="ui.closeMobileFilters()">
              <X class="h-5 w-5" />
            </button>
          </div>

          <div class="space-y-6">
            <div>
              <div class="label">Category</div>
              <div class="space-y-2">
                <label v-for="c in NAV_CATEGORIES" :key="c.id" class="flex cursor-pointer items-center justify-between text-sm text-gray-700">
                  <span class="flex items-center gap-2">
                    <input type="radio" name="m-category" :checked="filters.category === c.slug" class="h-4 w-4 accent-primary" @change="setCategory(filters.category === c.slug ? undefined : c.slug)" />
                    {{ c.name }}
                  </span>
                  <span class="text-xs text-gray-400">{{ categoryCounts.get(c.slug) ?? 0 }}</span>
                </label>
              </div>
            </div>

            <div>
              <div class="label">Brand</div>
              <div class="space-y-2">
                <label v-for="b in brandOptions" :key="b.name" class="flex cursor-pointer items-center justify-between text-sm text-gray-700">
                  <span class="flex items-center gap-2">
                    <input type="radio" name="m-brand" :checked="filters.brand === b.name" class="h-4 w-4 accent-primary" @change="setBrand(filters.brand === b.name ? undefined : b.name)" />
                    {{ b.name }}
                  </span>
                  <span class="text-xs text-gray-400">{{ b.count }}</span>
                </label>
              </div>
            </div>

            <div>
              <div class="label">Price Range</div>
              <input type="range" min="0" :max="PRICE_MAX" step="5" :value="filters.min ?? 0" class="w-full accent-primary" @input="setMin(Number(($event.target as HTMLInputElement).value))" />
              <input type="range" min="0" :max="PRICE_MAX" step="5" :value="filters.max ?? PRICE_MAX" class="w-full accent-primary" @input="setMax(Number(($event.target as HTMLInputElement).value))" />
              <div class="mt-2 flex items-center justify-between text-xs font-medium text-gray-600">
                <span class="chip">${{ filters.min ?? 0 }}</span>
                <span>–</span>
                <span class="chip">${{ filters.max ?? PRICE_MAX }}</span>
              </div>
            </div>

            <div>
              <div class="label">Rating</div>
              <div class="space-y-2">
                <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
                  <input type="radio" name="m-rating" :checked="filters.rating === 4.5" class="h-4 w-4 accent-primary" @change="setRating(filters.rating === 4.5 ? undefined : 4.5)" />
                  <StarRating :value="4.5" size="sm" /> <span class="text-xs text-gray-400">&amp; up</span>
                </label>
                <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
                  <input type="radio" name="m-rating" :checked="filters.rating === 4.0" class="h-4 w-4 accent-primary" @change="setRating(filters.rating === 4.0 ? undefined : 4.0)" />
                  <StarRating :value="4.0" size="sm" /> <span class="text-xs text-gray-400">&amp; up</span>
                </label>
                <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
                  <input type="radio" name="m-rating" :checked="filters.rating === 3.5" class="h-4 w-4 accent-primary" @change="setRating(filters.rating === 3.5 ? undefined : 3.5)" />
                  <StarRating :value="3.5" size="sm" /> <span class="text-xs text-gray-400">&amp; up</span>
                </label>
                <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
                  <input type="radio" name="m-rating" :checked="filters.rating === undefined" class="h-4 w-4 accent-primary" @change="setRating(undefined)" />
                  <span>Any</span>
                </label>
              </div>
            </div>

            <div>
              <div class="label">Color</div>
              <div class="flex flex-wrap gap-2">
                <button v-for="hex in colorOptions" :key="hex" type="button" class="h-8 w-8 rounded-full border-2 transition" :class="filters.colors.includes(hex) ? 'border-primary ring-2 ring-primary/30' : 'border-gray-300' " :style="{ backgroundColor: hex }" @click="toggleColor(hex)"></button>
              </div>
            </div>

            <div>
              <div class="label">Size</div>
              <div class="flex flex-wrap gap-2">
                <button v-for="s in SIZES" :key="s" type="button" class="rounded-lg border px-3 py-1.5 text-sm" :class="filters.sizes.includes(s) ? 'border-primary bg-primary/5 text-primary' : 'border-border-gray text-gray-600'" @click="toggleSize(s)">{{ s }}</button>
              </div>
            </div>

            <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
              <input type="checkbox" :checked="filters.inStockOnly" class="h-4 w-4 accent-primary" @change="setInStock(($event.target as HTMLInputElement).checked)" />
              In stock only
            </label>

            <div class="flex gap-2">
              <button class="btn-outline flex-1" @click="clearAll">Clear all</button>
              <button class="btn-primary flex-1 lg:hidden" @click="ui.closeMobileFilters()">Apply</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
