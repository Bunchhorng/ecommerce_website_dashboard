<script setup lang="ts">
import type { Component } from 'vue'
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import { Smartphone, Shirt, Footprints, Flower2, Watch, Home, Tag, Send } from 'lucide-vue-next'
import StarRating from '@/components/StarRating.vue'
import ProductRail from '@/components/ProductRail.vue'
import {
  CATEGORIES,
  FEATURED_PRODUCTS,
  BEST_SELLERS,
  NEW_ARRIVALS,
  TESTIMONIALS,
  PRODUCTS
} from '@/data/mock'

const categoryIcons: Record<string, Component> = {
  Smartphone,
  Shirt,
  Footprints,
  Flower2,
  Watch,
  Home,
  Tag
}

const heroPrimary = PRODUCTS[0]
const heroSecondary = PRODUCTS[3]

const email = ref('')
const subscribed = ref(false)

function subscribe() {
  if (email.value.trim()) subscribed.value = true
}
</script>

<template>
  <div>
    <section class="relative overflow-hidden bg-gradient-to-r from-blue-700 via-primary to-primary-dark text-white">
      <div class="pointer-events-none absolute -left-20 -top-20 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
      <div class="pointer-events-none absolute -bottom-24 right-10 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
      <div class="pointer-events-none absolute left-1/3 top-1/2 h-40 w-40 rounded-full bg-accent/20 blur-3xl"></div>

      <div class="container-app relative py-16 lg:py-24">
        <div class="grid items-center gap-12 lg:grid-cols-2">
          <div class="max-w-xl">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider ring-1 ring-white/20">
              Summer Sale · Up to 40% off
            </span>
            <h1 class="mt-5 text-4xl font-extrabold leading-tight lg:text-5xl">Everything you love, delivered fast.</h1>
            <p class="mt-4 text-lg text-blue-100">Discover hand-picked essentials across electronics, fashion, beauty and home — with free shipping on orders over $100.</p>
            <div class="mt-8 flex flex-wrap gap-4">
              <RouterLink to="/shop" class="btn-accent btn-lg">Shop Now</RouterLink>
              <RouterLink to="/shop?sort=featured" class="btn btn-lg border border-white/40 text-white hover:bg-white/10">Explore</RouterLink>
            </div>
          </div>

          <div class="relative hidden lg:block">
            <div class="relative h-[420px]">
              <img :src="heroPrimary.images[0].url" :alt="heroPrimary.images[0].alt" class="absolute right-0 top-0 h-72 w-72 rounded-3xl object-cover shadow-2xl" />
              <img :src="heroSecondary.images[0].url" :alt="heroSecondary.images[0].alt" class="absolute bottom-0 left-0 h-64 w-64 rounded-3xl object-cover shadow-2xl" />
              <div class="absolute -left-4 top-1/2 -translate-y-1/2 rounded-2xl bg-white p-4 text-ink shadow-2xl">
                <div class="text-2xl font-extrabold text-primary">-22%</div>
                <div class="text-xs text-gray-500">Today only</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="categories" class="container-app py-16">
      <div class="section-eyebrow">Browse</div>
      <h2 class="section-title">Shop by Category</h2>
      <p class="section-subtitle">Find exactly what you are looking for across our curated collections.</p>

      <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <RouterLink
          v-for="cat in CATEGORIES"
          :key="cat.id"
          :to="`/shop?category=${cat.slug}`"
          class="group rounded-xl border border-border-gray bg-white p-6 text-center transition hover:border-primary hover:shadow-card"
        >
          <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-canvas text-ink transition group-hover:bg-primary group-hover:text-white">
            <component :is="categoryIcons[cat.icon ?? 'Tag'] ?? Tag" class="h-7 w-7" />
          </div>
          <div class="mt-3 text-sm font-semibold text-ink">{{ cat.name }}</div>
        </RouterLink>
      </div>
    </section>

    <section class="container-app pb-4">
      <ProductRail
        title="Featured Products"
        subtitle="Hand-picked this week"
        :products="FEATURED_PRODUCTS"
        view-all-link="/shop"
      />
    </section>

    <section class="bg-primary-dark py-14 text-white">
      <div class="container-app flex flex-col items-center justify-between gap-6 lg:flex-row">
        <div class="text-center lg:text-left">
          <h2 class="text-2xl font-bold sm:text-3xl">The Mega Sale is On — don't miss out</h2>
          <p class="mt-2 text-blue-100">Grab up to 40% off your favorites while stock lasts.</p>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-3">
          <div class="rounded-xl bg-white/10 px-4 py-3 text-center ring-1 ring-white/20">
            <div class="text-2xl font-extrabold">02</div>
            <div class="text-xs text-blue-100">Days</div>
          </div>
          <div class="rounded-xl bg-white/10 px-4 py-3 text-center ring-1 ring-white/20">
            <div class="text-2xl font-extrabold">14</div>
            <div class="text-xs text-blue-100">Hours</div>
          </div>
          <div class="rounded-xl bg-white/10 px-4 py-3 text-center ring-1 ring-white/20">
            <div class="text-2xl font-extrabold">32</div>
            <div class="text-xs text-blue-100">Mins</div>
          </div>
          <div class="rounded-xl bg-white/10 px-4 py-3 text-center ring-1 ring-white/20">
            <div class="text-2xl font-extrabold">08</div>
            <div class="text-xs text-blue-100">Secs</div>
          </div>
        </div>
        <RouterLink to="/shop?category=sale" class="btn-accent btn-lg">Shop Sale</RouterLink>
      </div>
    </section>

    <section class="container-app py-12">
      <ProductRail
        title="Best Sellers"
        subtitle="Loved by thousands of shoppers"
        :products="BEST_SELLERS"
        view-all-link="/shop?sort=rating"
      />
    </section>

    <section class="container-app pb-16">
      <ProductRail
        title="New Arrivals"
        subtitle="Fresh drops, just in"
        :products="NEW_ARRIVALS"
        view-all-link="/shop?sort=featured"
      />
    </section>

    <section class="container-app py-16">
      <h2 class="section-title text-center">What our customers say</h2>
      <p class="section-subtitle text-center">Real stories from happy shoppers around the world.</p>

      <div class="mt-10 grid gap-6 lg:grid-cols-3">
        <div
          v-for="t in TESTIMONIALS"
          :key="t.id"
          class="card flex flex-col justify-between rounded-2xl p-6"
        >
          <div>
            <StarRating :value="t.rating" />
            <p class="mt-4 text-gray-600 italic">“{{ t.quote }}”</p>
          </div>
          <div class="mt-6 flex items-center gap-3">
            <img :src="t.avatar" :alt="t.name" class="h-10 w-10 rounded-full object-cover" />
            <div>
              <div class="text-sm font-semibold text-ink">{{ t.name }}</div>
              <div class="text-xs text-gray-500">{{ t.role }}</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="container-app pb-16">
      <div class="rounded-2xl bg-gradient-to-r from-primary to-primary-dark p-8 text-center text-white sm:p-12">
        <h2 class="text-2xl font-bold sm:text-3xl">Get 10% off your first order</h2>
        <p class="mx-auto mt-2 max-w-md text-blue-100">Join our newsletter for exclusive deals, new arrivals, and early access to sales.</p>

        <div v-if="!subscribed" class="mx-auto mt-6 flex max-w-md gap-2">
          <input
            v-model="email"
            type="email"
            placeholder="Enter your email"
            class="input w-full bg-white"
          />
          <button class="btn-accent !px-6" @click="subscribe">
            <Send class="h-4 w-4" />
            Subscribe
          </button>
        </div>
        <p v-else class="mt-6 rounded-lg bg-white/10 px-4 py-3 text-sm font-medium ring-1 ring-white/20">Thanks! Your 10% off code is on its way. 🎉</p>
      </div>
    </section>
  </div>
</template>
