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

const shopLinks: Array<{ labelKey: string; to?: string }> = [
  { labelKey: 'footer.new_arrivals', to: '/shop' },
  { labelKey: 'footer.best_sellers', to: '/shop' },
  { labelKey: 'footer.sale', to: '/shop' },
  { labelKey: 'footer.help', to: undefined }
]

const helpLinks = [
  { labelKey: 'footer.about_us' },
  { labelKey: 'footer.contact' },
  { labelKey: 'footer.faq' },
  { labelKey: 'footer.shipping_returns' },
  { labelKey: 'footer.privacy_policy' }
]

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
            E-<span class="text-primary">KHMER</span>
          </RouterLink>
          <p class="mt-4 max-w-xs text-sm text-gray-400">
            {{ $t('footer.tagline') }}
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
          <h3 class="text-sm font-semibold uppercase tracking-wide text-white">{{ $t('footer.shop') }}</h3>
          <ul class="mt-4 space-y-2.5 text-sm">
            <li v-for="link in shopLinks" :key="link.labelKey">
              <RouterLink v-if="link.to" :to="link.to" class="text-gray-400 transition-colors hover:text-white">
                {{ $t(link.labelKey) }}
              </RouterLink>
              <a v-else href="#" class="text-gray-400 transition-colors hover:text-white">
                {{ $t(link.labelKey) }}
              </a>
            </li>
          </ul>
        </div>

        <div>
          <h3 class="text-sm font-semibold uppercase tracking-wide text-white">{{ $t('footer.help') }}</h3>
          <ul class="mt-4 space-y-2.5 text-sm">
            <li v-for="link in helpLinks" :key="link.labelKey">
              <a href="#" class="text-gray-400 transition-colors hover:text-white">{{ $t(link.labelKey) }}</a>
            </li>
          </ul>
        </div>

        <div>
          <h3 class="text-sm font-semibold uppercase tracking-wide text-white">{{ $t('footer.newsletter') }}</h3>
          <p class="mt-4 text-sm text-gray-400">
            {{ $t('footer.newsletter_text') }}
          </p>
          <form class="mt-4 flex gap-2" @submit.prevent="subscribe">
            <input
              v-model="email"
              type="email"
              class="input !border-white/10 !bg-white/5 !text-white placeholder:!text-gray-500"
              :placeholder="$t('footer.email_placeholder')"
              :aria-label="$t('footer.email_placeholder')"
            />
            <button type="submit" class="btn-primary shrink-0 !px-4" :aria-label="$t('footer.subscribe')">
              <Send :size="15" />
            </button>
          </form>
          <p v-if="subscribed" class="mt-3 text-sm font-medium text-emerald-400">
            {{ $t('footer.newsletter_success') }}
          </p>
        </div>
      </div>

      <div class="mt-12 flex flex-wrap items-center justify-between gap-4 border-t border-white/10 pt-6">
        <p class="text-xs text-gray-400">{{ $t('footer.copyright') }}</p>
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