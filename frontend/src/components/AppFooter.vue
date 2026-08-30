<script setup lang="ts">
import { ref } from 'vue'
import { Facebook, Instagram, Send, Twitter, Youtube } from 'lucide-vue-next'

const email = ref('')
const subscribed = ref(false)

const socials = [
  { label: 'Facebook', href: '#', icon: Facebook },
  { label: 'Instagram', href: '#', icon: Instagram },
  { label: 'Twitter', href: '#', icon: Twitter },
  { label: 'Youtube', href: '#', icon: Youtube }
]

const shopLinks: Array<{ label: string; to?: string }> = [
  { label: 'New Arrivals', to: '/shop' },
  { label: 'Best Sellers', to: '/shop' },
  { label: 'Sale', to: '/shop' },
  { label: 'Men' },
  { label: 'Women' }
]

const helpLinks = ['About Us', 'Contact', 'FAQ', 'Shipping & Returns', 'Privacy Policy']

function subscribe(): void {
  if (email.value.includes('@')) subscribed.value = true
}
</script>

<template>
  <footer class="bg-ink text-gray-300">
    <div class="container-app py-14">
      <div class="grid gap-10 lg:grid-cols-4">
        <div>
          <RouterLink to="/" class="text-2xl font-extrabold tracking-tight text-white">
            Shop<span class="text-primary">Verse</span>
          </RouterLink>
          <p class="mt-4 max-w-xs text-sm text-gray-400">
            Your one-stop destination for electronics, fashion, beauty and more — delivered fast and backed by friendly support.
          </p>
          <div class="mt-6 flex items-center gap-2">
            <a
              v-for="social in socials"
              :key="social.label"
              :href="social.href"
              :aria-label="social.label"
              class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-gray-300 transition-colors hover:bg-primary hover:text-white"
            >
              <component :is="social.icon" :size="16" />
            </a>
          </div>
        </div>

        <div>
          <h3 class="text-sm font-semibold uppercase tracking-wide text-white">Shop</h3>
          <ul class="mt-4 space-y-2.5 text-sm">
            <li v-for="link in shopLinks" :key="link.label">
              <RouterLink v-if="link.to" :to="link.to" class="text-gray-400 transition-colors hover:text-white">
                {{ link.label }}
              </RouterLink>
              <a v-else href="#" class="text-gray-400 transition-colors hover:text-white">
                {{ link.label }}
              </a>
            </li>
          </ul>
        </div>

        <div>
          <h3 class="text-sm font-semibold uppercase tracking-wide text-white">Help</h3>
          <ul class="mt-4 space-y-2.5 text-sm">
            <li v-for="link in helpLinks" :key="link">
              <a href="#" class="text-gray-400 transition-colors hover:text-white">{{ link }}</a>
            </li>
          </ul>
        </div>

        <div>
          <h3 class="text-sm font-semibold uppercase tracking-wide text-white">Newsletter</h3>
          <p class="mt-4 text-sm text-gray-400">
            Subscribe for exclusive deals, new arrivals and insider tips.
          </p>
          <form class="mt-4 flex gap-2" @submit.prevent="subscribe">
            <input
              v-model="email"
              type="email"
              class="input !border-white/10 !bg-white/5 !text-white placeholder:!text-gray-500"
              placeholder="you@example.com"
              aria-label="Email address"
            />
            <button type="submit" class="btn-primary shrink-0 !px-4" aria-label="Subscribe">
              <Send :size="15" />
            </button>
          </form>
          <p v-if="subscribed" class="mt-3 text-sm font-medium text-emerald-400">
            Thanks for subscribing!
          </p>
        </div>
      </div>

      <div class="mt-12 flex flex-wrap items-center justify-between gap-4 border-t border-white/10 pt-6">
        <p class="text-xs text-gray-400">© 2026 ShopVerse. All rights reserved.</p>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="pay in ['VISA', 'Mastercard', 'PayPal', 'Apple Pay', 'AMEX']"
            :key="pay"
            class="rounded border border-white/10 bg-white/5 px-2 py-1 text-[11px] font-semibold text-gray-300"
          >
            {{ pay }}
          </span>
        </div>
      </div>
    </div>
  </footer>
</template>