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
import {
  BRANDS,
  CATEGORIES,
  FEATURED_PRODUCTS,
  BEST_SELLERS,
  NEW_ARRIVALS,
  TESTIMONIALS,
  PRODUCTS
} from '@/data/mock'

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

const featured = FEATURED_PRODUCTS.slice(0, 4)
const heroPrimary = PRODUCTS[0]
const heroAccent = PRODUCTS[7]

const dealOfDay = PRODUCTS[1]

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
  { icon: Truck, title: 'Free Shipping', text: 'On orders over $100' },
  { icon: RefreshCcw, title: 'Free Returns', text: '30-day money back' },
  { icon: ShieldCheck, title: 'Secure Checkout', text: '256-bit SSL encrypted' },
  { icon: Headphones, title: '24/7 Support', text: 'Real humans, any time' }
]

const email = ref('')
const subscribed = ref(false)

const newsletterPerks = [
  'Exclusive subscriber deals',
  'Early access to new drops',
  '10% off your very first order'
]

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
              Summer Sale · Up to 40% off
            </div>

            <h1 class="mt-6 text-4xl font-extrabold leading-[1.1] sm:text-5xl lg:text-[3.4rem]">
              Everything you love,<br />
              <span class="bg-gradient-to-r from-accent to-amber-300 bg-clip-text text-transparent">delivered fast.</span>
            </h1>

            <p class="mt-5 max-w-lg text-lg leading-relaxed text-blue-100">
              Discover hand-picked essentials across electronics, fashion, beauty and home, with free shipping on orders over $100.
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-4">
              <RouterLink to="/shop" class="btn-accent btn-lg gap-2 !px-8">
                Shop Now
                <ArrowRight :size="18" />
              </RouterLink>
              <RouterLink
                to="/shop?sort=featured"
                class="group inline-flex items-center gap-2 rounded-full border-2 border-white/30 px-6 py-3 text-sm font-semibold text-white transition hover:border-white hover:bg-white/10"
              >
                Explore offers
                <ChevronRight :size="16" class="transition-transform group-hover:translate-x-0.5" />
              </RouterLink>
            </div>

            <div class="mt-10 flex items-center gap-8 border-t border-white/15 pt-6">
              <div>
                <div class="flex items-center gap-1">
                  <Star v-for="i in 5" :key="i" :size="16" class="fill-current text-accent" />
                </div>
                <p class="mt-1 text-xs text-blue-100">4.9/5 from 12k+ reviews</p>
              </div>
              <div>
                <div class="text-xl font-extrabold">50k+</div>
                <p class="text-xs text-blue-100">Happy customers</p>
              </div>
              <div>
                <div class="text-xl font-extrabold">99%</div>
                <p class="text-xs text-blue-100">On-time delivery</p>
              </div>
            </div>
          </div>

          <div class="relative hidden lg:block">
            <div class="relative mx-auto h-[440px] max-w-md">
              <div class="absolute -inset-6 rounded-[2.5rem] bg-gradient-to-tr from-white/10 to-transparent"></div>

              <img
                :src="heroPrimary.images[0].url"
                :alt="heroPrimary.images[0].alt"
                class="absolute right-0 top-6 h-72 w-72 rotate-3 rounded-3xl object-cover shadow-2xl ring-1 ring-white/20"
              />
              <img
                :src="heroAccent.images[0].url"
                :alt="heroAccent.images[0].alt"
                class="absolute bottom-0 left-0 h-56 w-56 -rotate-6 rounded-3xl object-cover shadow-2xl ring-1 ring-white/20"
              />

              <div class="absolute left-10 top-0 flex items-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-ink shadow-2xl">
                <Truck :size="18" class="text-primary" />
                <div class="text-xs">
                  <div class="font-bold">Free shipping</div>
                  <div class="text-gray-500">Orders over $100</div>
                </div>
              </div>

              <div class="absolute bottom-14 right-2 rounded-2xl bg-white p-4 text-ink shadow-2xl">
                <div class="flex items-center gap-1">
                  <StarRating :value="5" :size="14" />
                </div>
                <div class="mt-1 text-sm font-bold">Aurora Headphones</div>
                <div class="text-xs text-gray-500">Loved by 1.2k shoppers</div>
              </div>

              <div class="absolute -left-2 top-1/2 flex -translate-y-1/2 items-center gap-2 rounded-2xl bg-accent px-3.5 py-2.5 text-ink shadow-2xl">
                <Tag :size="16" class="shrink-0" />
                <div>
                  <div class="text-base font-extrabold leading-none">-22%</div>
                  <div class="text-[10px] font-semibold uppercase tracking-wide">Today only</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== BENEFITS STRIP ===================== -->
    <section class="border-b border-border-gray bg-white">
      <div class="container-app grid grid-cols-2 gap-6 py-6 lg:grid-cols-4">
        <div v-for="b in benefits" :key="b.title" class="flex items-center gap-3">
          <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
            <component :is="b.icon" :size="20" />
          </div>
          <div>
            <div class="text-sm font-bold text-ink">{{ b.title }}</div>
            <div class="text-xs text-gray-500">{{ b.text }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== CATEGORIES ===================== -->
    <section id="categories" class="container-app py-16">
      <div class="flex items-end justify-between gap-4">
        <div>
          <div class="section-eyebrow">Browse</div>
          <h2 class="section-title">Shop by Category</h2>
          <p class="section-subtitle">Find exactly what you're looking for across our curated collections.</p>
        </div>
        <RouterLink
          to="/shop"
          class="hidden shrink-0 items-center gap-1 text-sm font-semibold text-primary transition-colors hover:text-primary-dark sm:flex"
        >
          All categories
          <ArrowRight :size="16" />
        </RouterLink>
      </div>

      <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <RouterLink
          v-for="cat in CATEGORIES"
          :key="cat.id"
          :to="`/shop?category=${cat.slug}`"
          class="group relative overflow-hidden rounded-2xl border border-border-gray bg-white shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
        >
          <div class="relative h-28 overflow-hidden bg-canvas sm:h-32">
            <img
              :src="categoryImages[cat.slug]"
              :alt="cat.name"
              loading="lazy"
              class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
          </div>
          <div class="flex items-center gap-3 px-4 py-3.5">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-white">
              <component :is="categoryIcons[cat.icon ?? 'Tag'] ?? Tag" class="h-4.5 w-4.5" />
            </div>
            <div class="min-w-0">
              <div class="truncate text-sm font-semibold text-ink">{{ cat.name }}</div>
              <div class="text-xs text-gray-500">Shop now</div>
            </div>
            <ChevronRight :size="16" class="ml-auto shrink-0 text-gray-300 transition-transform group-hover:translate-x-0.5 group-hover:text-primary" />
          </div>
        </RouterLink>
      </div>
    </section>

    <!-- ===================== FEATURED ===================== -->
    <section class="container-app pb-4">
      <ProductRail
        title="Featured Products"
        subtitle="Hand-picked this week"
        :products="featured"
        view-all-link="/shop"
      />
    </section>

    <!-- ===================== DEAL OF THE DAY ===================== -->
    <section class="container-app py-16">
      <div class="overflow-hidden rounded-3xl bg-gradient-to-r from-primary-dark via-primary to-primary text-white">
        <div class="grid items-center gap-8 lg:grid-cols-2">
          <div class="p-8 sm:p-12">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider ring-1 ring-white/20">
              <Clock :size="14" class="text-accent" />
              Deal of the Day
            </div>

            <h2 class="mt-5 text-3xl font-extrabold leading-tight sm:text-4xl">{{ dealOfDay.title }}</h2>
            <p class="mt-3 line-clamp-3 max-w-md text-blue-100">{{ dealOfDay.description }}</p>

            <div class="mt-6 flex items-end gap-3">
              <div class="text-4xl font-extrabold">{{ dealOfDay.price.toFixed(2) }}</div>
              <div v-if="dealOfDay.compareAtPrice" class="pb-1 text-lg text-blue-200 line-through">
                {{ dealOfDay.compareAtPrice.toFixed(2) }}
              </div>
              <span v-if="dealOfDay.discountPercent" class="mb-1.5 rounded-md bg-accent px-2 py-1 text-xs font-bold text-ink">
                -{{ dealOfDay.discountPercent }}%
              </span>
            </div>

            <div class="mt-7 flex flex-wrap gap-3">
              <div
                v-for="(value, key) in dealCountdown"
                :key="key"
                class="min-w-[64px] rounded-xl bg-white/10 px-3 py-2.5 text-center ring-1 ring-white/20 backdrop-blur"
              >
                <div class="text-2xl font-extrabold tabular-nums">{{ value }}</div>
                <div class="text-[10px] font-semibold uppercase tracking-wider text-blue-100">{{ key }}</div>
              </div>
            </div>

            <RouterLink to="/shop" class="btn-accent btn-lg gap-2 mt-8 !px-8">
              Shop the deal
              <ArrowRight :size="18" />
            </RouterLink>
          </div>

          <div class="relative hidden h-full min-h-[420px] lg:block">
            <div class="absolute inset-0 overflow-hidden">
              <img
                :src="dealOfDay.images[0].url"
                :alt="dealOfDay.images[0].alt"
                class="h-full w-full object-cover"
              />
              <div class="absolute inset-0 bg-gradient-to-r from-primary/60 to-transparent"></div>
            </div>
            <div class="absolute bottom-8 left-8 flex items-center gap-3 rounded-2xl bg-white/90 px-4 py-3 text-ink shadow-2xl backdrop-blur">
              <BadgeCheck :size="20" class="text-primary" />
              <div class="text-xs">
                <div class="font-bold">Ends tonight</div>
                <div class="text-gray-500">While stock lasts</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== BEST SELLERS ===================== -->
    <section class="container-app pb-12">
      <ProductRail
        title="Best Sellers"
        subtitle="Loved by thousands of shoppers"
        :products="BEST_SELLERS"
        view-all-link="/shop?sort=rating"
      />
    </section>

    <!-- ===================== BRAND STRIP ===================== -->
    <section class="border-y border-border-gray bg-white py-10">
      <div class="container-app">
        <div class="grid grid-cols-2 gap-6 sm:grid-cols-4 lg:grid-cols-7">
          <RouterLink
            v-for="brand in BRANDS"
            :key="brand.id"
            :to="`/shop?brand=${brand.slug}`"
            class="flex items-center justify-center rounded-xl border border-border-gray px-4 py-5 text-center transition-colors hover:border-primary hover:bg-primary/5"
          >
            <span class="text-sm font-extrabold uppercase tracking-wide text-gray-500 transition-colors hover:text-primary">{{ brand.name }}</span>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- ===================== NEW ARRIVALS ===================== -->
    <section class="container-app py-16">
      <div class="mb-5 flex items-end justify-between gap-4">
        <div>
          <div class="section-eyebrow">Just landed</div>
          <h2 class="section-title">New Arrivals</h2>
          <p class="section-subtitle">Fresh drops, just in. Be the first to grab them.</p>
        </div>
        <RouterLink
          to="/shop?sort=featured"
          class="hidden shrink-0 items-center gap-1 text-sm font-semibold text-primary transition-colors hover:text-primary-dark sm:flex"
        >
          View All
          <ArrowRight :size="16" />
        </RouterLink>
      </div>
      <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        <ProductCard
          v-for="product in NEW_ARRIVALS.slice(0, 4)"
          :key="product.id"
          :product="product"
          @add-to-cart="handleAdd"
          @wishlist-toggle="handleWishlist"
        />
      </div>
    </section>

    <!-- ===================== TESTIMONIALS ===================== -->
    <section class="bg-canvas py-16">
      <div class="container-app">
        <div class="mx-auto max-w-2xl text-center">
          <div class="section-eyebrow">Testimonials</div>
          <h2 class="section-title">What our customers say</h2>
          <p class="section-subtitle">Real stories from happy shoppers around the world.</p>
        </div>

        <div class="mt-10 grid gap-6 lg:grid-cols-3">
          <div
            v-for="t in TESTIMONIALS"
            :key="t.id"
            class="card relative flex flex-col justify-between rounded-2xl p-6 transition-shadow duration-300 hover:shadow-lg"
          >
            <div>
              <StarRating :value="t.rating" />
              <p class="mt-4 text-gray-600 italic">“{{ t.quote }}”</p>
            </div>
            <div class="mt-6 flex items-center gap-3">
              <img :src="t.avatar" :alt="t.name" class="h-10 w-10 rounded-full object-cover" />
              <div>
                <div class="text-sm font-semibold text-ink">{{ t.name }}</div>
                <div class="flex items-center gap-1 text-xs text-gray-500">
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

          <h2 class="mt-5 text-2xl font-bold sm:text-3xl">Get 10% off your first order</h2>
          <p class="mx-auto mt-2 max-w-md text-blue-100">
            Join our newsletter for exclusive deals, new arrivals, and early access to sales.
          </p>

          <ul class="mx-auto mt-5 flex max-w-md flex-col gap-2 text-left sm:grid sm:grid-cols-1">
            <li
              v-for="perk in newsletterPerks"
              :key="perk"
              class="flex items-center gap-2 text-sm text-blue-100"
            >
              <Check :size="16" class="shrink-0 text-accent" />
              {{ perk }}
            </li>
          </ul>

          <div v-if="!subscribed" class="mx-auto mt-7 flex max-w-md gap-2">
            <input
              v-model="email"
              type="email"
              placeholder="Enter your email"
              class="input w-full bg-white"
            />
            <button class="btn-accent !px-6" @click="subscribe">
              <Send :size="16" />
              Subscribe
            </button>
          </div>
          <p v-else class="mx-auto mt-6 max-w-md rounded-lg bg-white/10 px-4 py-3 text-sm font-medium ring-1 ring-white/20">
            Thanks! Your 10% off code is on its way. 🎉
          </p>
        </div>
      </div>
    </section>
  </div>
</template>
