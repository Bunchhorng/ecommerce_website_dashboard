<script setup lang="ts">
import type { Component } from 'vue'
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import {
  ArrowRight,
  BadgeCheck,
  Check,
  ChevronRight,
  Clock,
  Flower2,
  Footprints,
  Headphones,
  Home,
  RefreshCcw,
  Send,
  Shirt,
  ShieldCheck,
  Smartphone,
  Sparkles,
  Star,
  Tag,
  Truck,
  Watch
} from 'lucide-vue-next'
import StarRating from '@/components/StarRating.vue'
import ProductRail from '@/components/ProductRail.vue'
import ProductCard from '@/components/ProductCard.vue'
import { useCartStore } from '@/stores/cart'
import type { AddToCartInput } from '@/stores/cart'
import { useWishlistStore } from '@/stores/wishlist'
import { catalogApi, brandsApi, categoriesApi } from '@/api'
import type { CatalogProduct, ApiBrand, ApiCategory } from '@/api'

const cartStore = useCartStore()
const wishlistStore = useWishlistStore()

function handleAdd(payload: AddToCartInput): void {
  cartStore.addItem({ product: payload.product, variantId: payload.variantId, quantity: 1 })
}

function handleWishlist(productId: string): void {
  wishlistStore.toggle(productId)
}

const categoryIcons: Record<string, Component> = {
  Smartphone,
  Shirt,
  Footprints,
  Flower2,
  Watch,
  Home,
  Tag
}

const featured = ref<CatalogProduct[]>([])
const heroPrimary = ref<CatalogProduct | null>(null)
const heroAccent = ref<CatalogProduct | null>(null)
const dealOfDay = ref<CatalogProduct | null>(null)
const bestSellers = ref<CatalogProduct[]>([])
const newArrivals = ref<CatalogProduct[]>([])
const brandsList = ref<ApiBrand[]>([])
const categoriesList = ref<ApiCategory[]>([])

onMounted(async () => {
  try {
    const [featuredRes, bestRes, newRes, brandsRes, catsRes] = await Promise.all([
      catalogApi.getFeatured(8),
      catalogApi.getProducts({ sort: 'rating', perPage: 8 }),
      catalogApi.getProducts({ sort: 'newest', perPage: 8 }),
      brandsApi.getAll(),
      categoriesApi.getTree()
    ])
    featured.value = featuredRes.data.data.slice(0, 4)
    bestSellers.value = bestRes.data.data
    newArrivals.value = newRes.data.data.slice(0, 4)
    brandsList.value = brandsRes.data.data
    categoriesList.value = catsRes.data.data
    if (featuredRes.data.data.length > 0) heroPrimary.value = featuredRes.data.data[0]
    if (featuredRes.data.data.length > 1) heroAccent.value = featuredRes.data.data[1]
    if (bestRes.data.data.length > 0) dealOfDay.value = bestRes.data.data[0]
  } catch {
    /* API may not be available */
  }
})

const categoryImages: Record<string, string> = {
  electronics: 'https://picsum.photos/seed/cat-electronics/600/700',
  fashion: 'https://picsum.photos/seed/cat-fashion/600/700',
  shoes: 'https://picsum.photos/seed/cat-shoes/600/700',
  beauty: 'https://picsum.photos/seed/cat-beauty/600/700',
  accessories: 'https://picsum.photos/seed/cat-accessories/600/700',
  home: 'https://picsum.photos/seed/cat-home/600/700'
}

const dealCountdown = ref({ days: '02', hours: '14', mins: '32', secs: '08' })
let timer: ReturnType<typeof setInterval> | null = null

function tick() {
  const now = new Date()
  const end = new Date(now)
  end.setHours(23, 59, 59, 0)

  let diff = Math.floor((end.getTime() - now.getTime()) / 1000)
  if (diff < 0) diff = 0

  const pad = (n: number) => String(n).padStart(2, '0')
  dealCountdown.value = {
    days: pad(Math.floor(diff / 86400)),
    hours: pad(Math.floor((diff % 86400) / 3600)),
    mins: pad(Math.floor((diff % 3600) / 60)),
    secs: pad(diff % 60)
  }
}

onMounted(() => {
  tick()
  timer = setInterval(tick, 1000)
})

onBeforeUnmount(() => {
  if (timer) clearInterval(timer)
})

const benefits = [
  { icon: Truck, titleKey: 'home.benefit_shipping_title', textKey: 'home.benefit_shipping_text' },
  { icon: RefreshCcw, titleKey: 'home.benefit_returns_title', textKey: 'home.benefit_returns_text' },
  { icon: ShieldCheck, titleKey: 'home.benefit_secure_title', textKey: 'home.benefit_secure_text' },
  { icon: Headphones, titleKey: 'home.benefit_support_title', textKey: 'home.benefit_support_text' }
]

const email = ref('')
const subscribed = ref(false)

const newsletterPerks = ['home.perk_exclusive', 'home.perk_early_access', 'home.perk_first_order']

function subscribe() {
  if (email.value.trim()) subscribed.value = true
}
</script>

<template>
  <div>
    <!-- ===================== HERO ===================== -->
    <section class="relative overflow-hidden bg-[radial-gradient(1200px_600px_at_80%_-10%,rgba(37,99,235,0.45),transparent),linear-gradient(135deg,#0B1F4B_0%,#1E40AF_55%,#2563EB_100%)] text-white">
      <div class="pointer-events-none absolute -left-24 -top-24 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
      <div class="pointer-events-none absolute -bottom-28 right-4 h-96 w-96 rounded-full bg-accent/20 blur-3xl"></div>
      <div class="pointer-events-none absolute right-1/3 top-10 h-40 w-40 rounded-full bg-primary/40 blur-2xl"></div>

      <div class="container-app relative py-14 lg:py-24">
        <div class="grid items-center gap-14 lg:grid-cols-2">
          <div class="max-w-xl">
            <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider backdrop-blur">
              <Sparkles :size="14" class="text-accent" />
              {{ $t('home.hero_badge') }}
            </div>

            <h1 class="mt-6 text-4xl font-extrabold leading-[1.1] sm:text-5xl lg:text-[3.4rem]">
              {{ $t('home.hero_title_1') }}<br />
              <span class="bg-gradient-to-r from-accent to-amber-300 bg-clip-text text-transparent">{{ $t('home.hero_title_2') }}</span>
            </h1>

            <p class="mt-5 max-w-lg text-lg leading-relaxed text-blue-100">
              {{ $t('home.hero_subtitle') }}
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-4">
              <RouterLink to="/shop" class="btn-accent btn-lg gap-2 !px-8">
                {{ $t('home.shop_now') }}
                <ArrowRight :size="18" />
              </RouterLink>
              <RouterLink
                to="/shop?sort=featured"
                class="group inline-flex items-center gap-2 rounded-full border-2 border-white/30 px-6 py-3 text-sm font-semibold text-white transition hover:border-white hover:bg-white/10"
              >
                {{ $t('home.explore_offers') }}
                <ChevronRight :size="16" class="transition-transform group-hover:translate-x-0.5" />
              </RouterLink>
            </div>

            <div class="mt-10 flex items-center gap-8 border-t border-white/15 pt-6">
              <div>
                <div class="flex items-center gap-1">
                  <Star v-for="i in 5" :key="i" :size="16" class="fill-current text-accent" />
                </div>
                <p class="mt-1 text-xs text-blue-100">{{ $t('home.hero_rating') }}</p>
              </div>
              <div>
                <div class="text-xl font-extrabold">50k+</div>
                <p class="text-xs text-blue-100">{{ $t('home.hero_happy_customers') }}</p>
              </div>
              <div>
                <div class="text-xl font-extrabold">99%</div>
                <p class="text-xs text-blue-100">{{ $t('home.hero_ontime_delivery') }}</p>
              </div>
            </div>
          </div>

          <div class="relative hidden lg:block">
            <div class="relative mx-auto h-[440px] max-w-md">
              <div class="absolute -inset-6 rounded-[2.5rem] bg-gradient-to-tr from-white/10 to-transparent"></div>

              <img
                v-if="heroPrimary?.cover_image"
                :src="heroPrimary.cover_image"
                :alt="heroPrimary.name"
                class="absolute right-0 top-6 h-72 w-72 rotate-3 rounded-3xl object-cover shadow-2xl ring-1 ring-white/20"
              />
              <img
                v-if="heroAccent?.cover_image"
                :src="heroAccent.cover_image"
                :alt="heroAccent.name"
                class="absolute bottom-0 left-0 h-56 w-56 -rotate-6 rounded-3xl object-cover shadow-2xl ring-1 ring-white/20"
              />

              <div class="absolute left-10 top-0 flex items-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-ink shadow-2xl dark:bg-gray-800 dark:text-gray-100">
                <Truck :size="18" class="text-primary" />
                  <div class="text-xs">
                  <div class="font-bold">{{ $t('home.benefit_shipping_title') }}</div>
                  <div class="text-gray-500 dark:text-gray-400">{{ $t('home.benefit_shipping_text') }}</div>
                </div>
              </div>

              <div class="absolute bottom-14 right-2 rounded-2xl bg-white p-4 text-ink shadow-2xl dark:bg-gray-800 dark:text-gray-100">
                <div class="flex items-center gap-1">
                  <StarRating :value="5" :size="14" />
                </div>
                <div class="mt-1 text-sm font-bold">{{ heroPrimary?.name ?? 'Product' }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $t('home.loved_by_shoppers') }}</div>
              </div>

              <div class="absolute -left-2 top-1/2 flex -translate-y-1/2 items-center gap-2 rounded-2xl bg-accent px-3.5 py-2.5 text-ink shadow-2xl dark:text-gray-100">
                <Tag :size="16" class="shrink-0" />
                <div>
                  <div class="text-base font-extrabold leading-none">-22%</div>
                  <div class="text-[10px] font-semibold uppercase tracking-wide">{{ $t('home.today_only') }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== BENEFITS STRIP ===================== -->
    <section class="border-b border-border-gray bg-white dark:border-gray-700 dark:bg-gray-900">
      <div class="container-app grid grid-cols-2 gap-6 py-6 lg:grid-cols-4">
        <div v-for="b in benefits" :key="b.titleKey" class="flex items-center gap-3">
          <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
            <component :is="b.icon" :size="20" />
          </div>
          <div>
            <div class="text-sm font-bold text-ink dark:text-gray-100">{{ $t(b.titleKey) }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $t(b.textKey) }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== CATEGORIES ===================== -->
    <section id="categories" class="container-app py-16">
      <div class="flex items-end justify-between gap-4">
        <div>
          <div class="section-eyebrow">{{ $t('home.browse') }}</div>
          <h2 class="section-title">{{ $t('home.shop_by_category') }}</h2>
          <p class="section-subtitle">{{ $t('home.category_subtitle') }}</p>
        </div>
        <RouterLink
          to="/shop"
          class="hidden shrink-0 items-center gap-1 text-sm font-semibold text-primary transition-colors hover:text-primary-dark sm:flex"
        >
          {{ $t('home.all_categories') }}
          <ArrowRight :size="16" />
        </RouterLink>
      </div>

      <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <RouterLink
          v-for="cat in categoriesList"
          :key="cat.id"
          :to="`/shop?category=${cat.slug}`"
          class="group relative overflow-hidden rounded-2xl border border-border-gray bg-white shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800"
        >
          <div class="relative h-28 overflow-hidden bg-canvas sm:h-32">
            <img
              :src="categoryImages[cat.slug] ?? `https://picsum.photos/seed/${cat.slug}/600/700`"
              :alt="cat.name"
              loading="lazy"
              class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
          </div>
          <div class="flex items-center gap-3 px-4 py-3.5">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-white">
              <component :is="categoryIcons['Tag'] ?? Tag" class="h-4.5 w-4.5" />
            </div>
            <div class="min-w-0">
              <div class="truncate text-sm font-semibold text-ink dark:text-gray-100">{{ cat.name }}</div>
              <div class="text-xs text-gray-500 dark:text-gray-400">{{ $t('home.shop_now') }}</div>
            </div>
            <ChevronRight :size="16" class="ml-auto shrink-0 text-gray-300 transition-transform group-hover:translate-x-0.5 group-hover:text-primary dark:text-gray-500" />
          </div>
        </RouterLink>
      </div>
    </section>

    <!-- ===================== FEATURED ===================== -->
    <section class="container-app pb-4">
      <ProductRail
        :title="$t('home.featured_title')"
        :subtitle="$t('home.featured_subtitle')"
        :products="featured"
        view-all-link="/shop"
      />
    </section>

    <!-- ===================== DEAL OF THE DAY ===================== -->
    <section v-if="dealOfDay" class="container-app py-16">
      <div class="overflow-hidden rounded-3xl bg-gradient-to-r from-primary-dark via-primary to-primary text-white">
        <div class="grid items-center gap-8 lg:grid-cols-2">
          <div class="p-8 sm:p-12">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider ring-1 ring-white/20">
              <Clock :size="14" class="text-accent" />
              {{ $t('home.deal_of_day') }}
            </div>

            <h2 class="mt-5 text-3xl font-extrabold leading-tight sm:text-4xl">{{ dealOfDay.name }}</h2>
            <p class="mt-3 line-clamp-3 max-w-md text-blue-100">{{ dealOfDay.short_description }}</p>

            <div class="mt-6 flex items-end gap-3">
              <div class="text-4xl font-extrabold">{{ dealOfDay.price.toFixed(2) }}</div>
              <div v-if="dealOfDay.compare_at_price" class="pb-1 text-lg text-blue-200 line-through">
                {{ dealOfDay.compare_at_price.toFixed(2) }}
              </div>
              <span v-if="dealOfDay.compare_at_price && dealOfDay.compare_at_price > dealOfDay.price" class="mb-1.5 rounded-md bg-accent px-2 py-1 text-xs font-bold text-ink">
                -{{ Math.round(((dealOfDay.compare_at_price - dealOfDay.price) / dealOfDay.compare_at_price) * 100) }}%
              </span>
            </div>

            <div class="mt-7 flex flex-wrap gap-3">
              <div
                v-for="(value, key) in dealCountdown"
                :key="key"
                class="min-w-[64px] rounded-xl bg-white/10 px-3 py-2.5 text-center ring-1 ring-white/20 backdrop-blur"
              >
                <div class="text-2xl font-extrabold tabular-nums">{{ value }}</div>
                <div class="text-[10px] font-semibold uppercase tracking-wider text-blue-100">{{ $t('home.time_' + key) }}</div>
              </div>
            </div>

            <RouterLink to="/shop" class="btn-accent btn-lg gap-2 mt-8 !px-8">
              {{ $t('home.shop_the_deal') }}
              <ArrowRight :size="18" />
            </RouterLink>
          </div>

          <div class="relative hidden h-full min-h-[420px] lg:block">
            <div class="absolute inset-0 overflow-hidden">
              <img
                v-if="dealOfDay.cover_image"
                :src="dealOfDay.cover_image"
                :alt="dealOfDay.name"
                class="h-full w-full object-cover"
              />
              <div class="absolute inset-0 bg-gradient-to-r from-primary/60 to-transparent"></div>
            </div>
            <div class="absolute bottom-8 left-8 flex items-center gap-3 rounded-2xl bg-white/90 px-4 py-3 text-ink shadow-2xl backdrop-blur dark:bg-gray-800/90 dark:text-gray-100">
              <BadgeCheck :size="20" class="text-primary" />
              <div class="text-xs">
                <div class="font-bold">{{ $t('home.ends_tonight') }}</div>
                <div class="text-gray-500 dark:text-gray-400">{{ $t('home.while_stock_lasts') }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== BEST SELLERS ===================== -->
    <section class="container-app pb-12">
      <ProductRail
        :title="$t('home.best_sellers_title')"
        :subtitle="$t('home.best_sellers_subtitle')"
        :products="bestSellers"
        view-all-link="/shop?sort=rating"
      />
    </section>

    <!-- ===================== BRAND STRIP ===================== -->
    <section class="border-y border-border-gray bg-white py-10 dark:border-gray-700 dark:bg-gray-900">
      <div class="container-app">
        <div class="grid grid-cols-2 gap-6 sm:grid-cols-4 lg:grid-cols-7">
          <RouterLink
            v-for="brand in brandsList"
            :key="brand.id"
            :to="`/shop?brand=${brand.slug}`"
            class="flex items-center justify-center rounded-xl border border-border-gray px-4 py-5 text-center transition-colors hover:border-primary hover:bg-primary/5 dark:border-gray-700"
          >
            <span class="text-sm font-extrabold uppercase tracking-wide text-gray-500 transition-colors hover:text-primary dark:text-gray-400">{{ brand.name }}</span>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- ===================== NEW ARRIVALS ===================== -->
    <section class="container-app py-16">
      <div class="mb-5 flex items-end justify-between gap-4">
        <div>
          <div class="section-eyebrow">{{ $t('home.just_landed') }}</div>
          <h2 class="section-title">{{ $t('home.new_arrivals') }}</h2>
          <p class="section-subtitle">{{ $t('home.new_arrivals_subtitle') }}</p>
        </div>
        <RouterLink
          to="/shop?sort=featured"
          class="hidden shrink-0 items-center gap-1 text-sm font-semibold text-primary transition-colors hover:text-primary-dark sm:flex"
        >
          {{ $t('actions.view_all') }}
          <ArrowRight :size="16" />
        </RouterLink>
      </div>
      <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        <ProductCard
          v-for="product in newArrivals"
          :key="product.id"
          :product="product"
          @add-to-cart="handleAdd"
          @wishlist-toggle="handleWishlist"
        />
      </div>
    </section>

    <!-- ===================== TESTIMONIALS ===================== -->
    <section class="bg-canvas py-16 dark:bg-gray-900">
      <div class="container-app">
        <div class="mx-auto max-w-2xl text-center">
          <div class="section-eyebrow">{{ $t('home.testimonials') }}</div>
          <h2 class="section-title">{{ $t('home.testimonials_title') }}</h2>
          <p class="section-subtitle">{{ $t('home.testimonials_subtitle') }}</p>
        </div>

        <div class="mt-10 grid gap-6 lg:grid-cols-3">
          <div
            v-for="(t, i) in [
              { name: 'Olivia Bennett', role: 'Verified Buyer', quote: 'The checkout was buttery smooth and my order arrived two days early. This is now my go-to store.', rating: 5 },
              { name: 'James Carter', role: 'Frequent Customer', quote: 'Easily the best online shopping experience I have had. Real-time tracking made the wait enjoyable.', rating: 5 },
              { name: 'Mei-Lin Chen', role: 'Verified Buyer', quote: 'Quality products, honest prices, and the wishlist saved me when my size restocked.', rating: 4 }
            ]"
            :key="i"
            class="card relative flex flex-col justify-between rounded-2xl p-6 transition-shadow duration-300 hover:shadow-lg"
          >
            <div>
              <StarRating :value="t.rating" />
              <p class="mt-4 text-gray-600 italic dark:text-gray-300">"{{ t.quote }}"</p>
            </div>
            <div class="mt-6 flex items-center gap-3">
              <img :src="`https://picsum.photos/seed/avatar-${i}/100/100`" :alt="t.name" class="h-10 w-10 rounded-full object-cover" />
              <div>
                <div class="text-sm font-semibold text-ink dark:text-gray-100">{{ t.name }}</div>
                <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                  <BadgeCheck :size="13" class="text-primary" />
                  {{ t.role }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== NEWSLETTER ===================== -->
    <section class="container-app pb-16">
      <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-primary to-primary-dark p-8 text-center text-white sm:p-12">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-20 -left-10 h-64 w-64 rounded-full bg-accent/20 blur-3xl"></div>

        <div class="relative">
          <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/20">
            <Send :size="24" class="text-accent" />
          </div>

          <h2 class="mt-5 text-2xl font-bold sm:text-3xl">{{ $t('home.newsletter_title') }}</h2>
          <p class="mx-auto mt-2 max-w-md text-blue-100">
            {{ $t('home.newsletter_subtitle') }}
          </p>

          <ul class="mx-auto mt-5 flex max-w-md flex-col gap-2 text-left sm:grid sm:grid-cols-1">
            <li
              v-for="perk in newsletterPerks"
              :key="perk"
              class="flex items-center gap-2 text-sm text-blue-100"
            >
              <Check :size="16" class="shrink-0 text-accent" />
              {{ $t(perk) }}
            </li>
          </ul>

          <div v-if="!subscribed" class="mx-auto mt-7 flex max-w-md gap-2">
            <input
              v-model="email"
              type="email"
              :placeholder="$t('home.email_placeholder')"
              class="input w-full bg-white dark:bg-gray-800"
            />
            <button class="btn-accent !px-6" @click="subscribe">
              <Send :size="16" />
              {{ $t('footer.subscribe') }}
            </button>
          </div>
          <p v-else class="mx-auto mt-6 max-w-md rounded-lg bg-white/10 px-4 py-3 text-sm font-medium ring-1 ring-white/20">
            {{ $t('home.subscribe_thanks') }}
          </p>
        </div>
      </div>
    </section>
  </div>
</template>
