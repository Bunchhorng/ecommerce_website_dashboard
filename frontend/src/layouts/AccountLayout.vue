<script setup lang="ts">
import { RouterLink, useRoute } from 'vue-router'
import {
  Bell,
  Heart,
  KeyRound,
  LayoutDashboard,
  LogOut,
  MapPin,
  Menu,
  ShoppingBag,
  Star,
  User,
  X
} from 'lucide-vue-next'
import { useWishlistStore } from '@/stores/wishlist'
import { useAuthStore } from '@/stores/auth'
import ThemeToggle from '@/components/ThemeToggle.vue'
import { useRouter } from 'vue-router'
import { ref, computed, onMounted } from 'vue'
import { accountApi } from '@/api'
import type { Component } from 'vue'

interface NavItem {
  nameKey: string
  route: string
  icon: Component
  count?: number
}

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const wishlist = useWishlistStore()
const mobileOpen = ref(false)

const displayName = computed(() => auth.user?.name ?? '')
const displayEmail = computed(() => auth.user?.email ?? '')

const initials = computed(() => displayName.value
  .split(' ')
  .map((word) => word[0])
  .join('')
)

const unreadCount = ref(0)
const ordersCount = ref(0)

onMounted(async () => {
  try {
    const [notiRes, dashRes] = await Promise.all([
      accountApi.getNotifications(),
      accountApi.getDashboard()
    ])
    unreadCount.value = (notiRes.data.data ?? []).filter((n: { read_at: string | null }) => !n.read_at).length
    ordersCount.value = dashRes.data.orders_count ?? 0
  } catch {
    // ignore
  }
})

const navItems = computed<NavItem[]>(() => [
  { nameKey: 'nav.dashboard', route: 'account-dashboard', icon: LayoutDashboard },
  { nameKey: 'nav.my_orders', route: 'account-orders', icon: ShoppingBag, count: ordersCount.value || undefined },
  { nameKey: 'nav.wishlist', route: 'account-wishlist', icon: Heart, count: wishlist.count || undefined },
  { nameKey: 'nav.addresses', route: 'account-addresses', icon: MapPin },
  { nameKey: 'nav.profile', route: 'account-profile', icon: User },
  { nameKey: 'nav.notifications', route: 'account-notifications', icon: Bell, count: unreadCount.value || undefined },
  { nameKey: 'nav.reviews', route: 'account-reviews', icon: Star },
  { nameKey: 'nav.change_password', route: 'account-password', icon: KeyRound }
])

function isActive(name: string): boolean {
  return route.name === name
}

const resending = ref(false)
const resendSent = ref(false)
const resendError = ref(false)

const showVerifyBanner = computed(
  () => auth.isAuthenticated && !auth.user?.email_verified
)

async function resendVerification() {
  resending.value = true
  resendSent.value = false
  resendError.value = false
  try {
    await auth.sendVerificationEmail()
    resendSent.value = true
  } catch {
    resendError.value = true
  } finally {
    resending.value = false
  }
}

async function signOut() {
  await auth.logout()
  router.push('/auth/login')
}
</script>

<template>
  <div class="flex min-h-screen flex-col bg-canvas dark:bg-canvas lg:flex-row">
    <header class="sticky top-0 z-40 border-b border-border-gray dark:border-border-gray bg-white dark:bg-surface lg:hidden">
      <div class="flex items-center justify-between px-4 py-3">
        <button type="button" class="btn-icon" @click="mobileOpen = true">
          <Menu class="h-5 w-5" />
        </button>
        <h2 class="text-base font-bold text-ink dark:text-ink">{{ $t('account.my_account') }}</h2>
        <div class="flex items-center gap-1">
          <ThemeToggle />
          <RouterLink to="/" class="btn-icon">
            <ShoppingBag class="h-5 w-5" />
          </RouterLink>
        </div>
      </div>
    </header>

    <div v-if="mobileOpen" class="fixed inset-0 z-50 lg:hidden">
      <div class="absolute inset-0 bg-black/50" @click="mobileOpen = false"></div>
      <div class="absolute inset-y-0 left-0 flex w-72 max-w-[85vw] flex-col overflow-y-auto bg-white dark:bg-surface shadow-2xl dark:shadow-black/40">
        <div class="relative flex items-center gap-3 border-b border-border-gray dark:border-border-gray p-6">
          <div
            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-bold text-white"
          >
            {{ initials }}
          </div>
          <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-ink dark:text-ink">{{ displayName }}</p>
            <p class="truncate text-xs text-gray-500 dark:text-muted">{{ displayEmail }}</p>
          </div>
          <button type="button" class="btn-icon absolute right-2 top-2" @click="mobileOpen = false">
            <X class="h-5 w-5" />
          </button>
        </div>
        <nav class="flex-1 space-y-1 p-4">
          <RouterLink
            v-for="item in navItems"
            :key="item.route"
            :to="{ name: item.route }"
            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm"
            :class="isActive(item.route) ? 'bg-primary/10 font-semibold text-primary' : 'text-gray-600 dark:text-muted hover:bg-gray-100 dark:hover:bg-surface-hover'"
          >
            <component :is="item.icon" class="h-5 w-5 shrink-0" />
            <span>{{ $t(item.nameKey) }}</span>
            <span
              v-if="item.count"
              class="ml-auto rounded-full bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary"
            >
              {{ item.count }}
            </span>
          </RouterLink>
        </nav>
        <div class="border-t border-border-gray dark:border-border-gray p-4">
          <RouterLink
            to="/"
            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-600 dark:text-muted hover:bg-gray-100 dark:hover:bg-surface-hover hover:text-ink dark:hover:text-ink"
          >
            <ShoppingBag class="h-5 w-5 shrink-0" />
            {{ $t('nav.back_to_store') }}
          </RouterLink>
          <button
            type="button"
            class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-600 dark:text-muted hover:bg-gray-100 dark:hover:bg-surface-hover hover:text-red-500"
            @click="signOut"
          >
            <LogOut class="h-5 w-5 shrink-0" />
            {{ $t('nav.sign_out') }}
          </button>
        </div>
      </div>
    </div>

    <aside class="hidden w-72 shrink-0 border-r border-border-gray dark:border-border-gray bg-white dark:bg-surface lg:flex lg:flex-col">
      <div class="flex items-center gap-3 border-b border-border-gray dark:border-border-gray p-6">
        <div
          class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-bold text-white"
        >
          {{ initials }}
        </div>
        <div class="min-w-0">
          <p class="truncate text-sm font-semibold text-ink dark:text-ink">{{ displayName }}</p>
          <p class="truncate text-xs text-gray-500 dark:text-muted">{{ displayEmail }}</p>
        </div>
      </div>
      <nav class="flex-1 space-y-1 p-4">
        <RouterLink
          v-for="item in navItems"
          :key="item.route"
          :to="{ name: item.route }"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm"
          :class="isActive(item.route) ? 'bg-primary/10 font-semibold text-primary' : 'text-gray-600 dark:text-muted hover:bg-gray-100 dark:hover:bg-surface-hover'"
        >
          <component :is="item.icon" class="h-5 w-5 shrink-0" />
          <span>{{ $t(item.nameKey) }}</span>
          <span
            v-if="item.count"
            class="ml-auto rounded-full bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary"
          >
            {{ item.count }}
          </span>
        </RouterLink>
      </nav>
      <div class="border-t border-border-gray dark:border-border-gray p-4">
        <RouterLink
          to="/"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-600 dark:text-muted hover:bg-gray-100 dark:hover:bg-surface-hover hover:text-ink dark:hover:text-ink"
        >
          <ShoppingBag class="h-5 w-5 shrink-0" />
          {{ $t('nav.back_to_store') }}
        </RouterLink>
        <button
          type="button"
          class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-600 dark:text-muted hover:bg-gray-100 dark:hover:bg-surface-hover hover:text-red-500"
          @click="signOut"
        >
          <LogOut class="h-5 w-5 shrink-0" />
          {{ $t('nav.sign_out') }}
        </button>
      </div>
    </aside>

    <main class="flex-1 p-4 sm:p-6 lg:p-8">
      <div class="mb-6 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-ink dark:text-ink">{{ route.meta.title ?? 'Dashboard' }}</h1>
      </div>
      <div
        v-if="showVerifyBanner"
        class="mb-6 flex flex-col gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm sm:flex-row sm:items-center dark:border-amber-900/50 dark:bg-amber-900/20"
      >
        <div class="flex-1">
          <p class="font-semibold text-amber-800 dark:text-amber-300">{{ $t('verify.banner_message') }}</p>
          <p class="mt-0.5 text-amber-700 dark:text-amber-400">{{ $t('verify.banner_hint') }}</p>
          <p v-if="resendSent" class="mt-1 font-medium text-emerald-600 dark:text-success">
            {{ $t('verify.email_sent') }}
          </p>
          <p v-else-if="resendError" class="mt-1 font-medium text-red-500">{{ $t('verify.resend_failed') }}</p>
        </div>
        <button
          type="button"
          class="btn-outline shrink-0"
          :disabled="resending"
          @click="resendVerification"
        >
          {{ resending ? $t('verify.sending') : $t('verify.banner_resend') }}
        </button>
      </div>
      <router-view />
    </main>
  </div>
</template>