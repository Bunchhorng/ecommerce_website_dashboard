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
import { CURRENT_USER, NOTIFICATIONS, ORDERS } from '@/data/mock'
import { useWishlistStore } from '@/stores/wishlist'
import { ref } from 'vue'
import type { Component } from 'vue'

interface NavItem {
  name: string
  route: string
  icon: Component
  count?: number
}

const route = useRoute()
const wishlist = useWishlistStore()
const mobileOpen = ref(false)

const initials = CURRENT_USER.name
  .split(' ')
  .map((word) => word[0])
  .join('')

const unreadCount = NOTIFICATIONS.filter((n) => !n.read).length

const navItems: NavItem[] = [
  { name: 'Dashboard', route: 'account-dashboard', icon: LayoutDashboard },
  { name: 'My Orders', route: 'account-orders', icon: ShoppingBag, count: ORDERS.length },
  { name: 'Wishlist', route: 'account-wishlist', icon: Heart, count: wishlist.count },
  { name: 'Addresses', route: 'account-addresses', icon: MapPin },
  { name: 'Profile', route: 'account-profile', icon: User },
  { name: 'Notifications', route: 'account-notifications', icon: Bell, count: unreadCount },
  { name: 'Reviews', route: 'account-reviews', icon: Star },
  { name: 'Change Password', route: 'account-password', icon: KeyRound }
]

function isActive(name: string): boolean {
  return route.name === name
}

function signOut() {
  return
}
</script>

<template>
  <div class="flex min-h-screen flex-col bg-canvas lg:flex-row">
    <header class="sticky top-0 z-40 border-b border-border-gray bg-white lg:hidden">
      <div class="flex items-center justify-between px-4 py-3">
        <button type="button" class="btn-icon" @click="mobileOpen = true">
          <Menu class="h-5 w-5" />
        </button>
        <h2 class="text-base font-bold text-ink">My Account</h2>
        <RouterLink to="/" class="btn-icon">
          <ShoppingBag class="h-5 w-5" />
        </RouterLink>
      </div>
    </header>

    <div v-if="mobileOpen" class="fixed inset-0 z-50 lg:hidden">
      <div class="absolute inset-0 bg-black/50" @click="mobileOpen = false"></div>
      <div class="absolute inset-y-0 left-0 flex w-72 max-w-[85vw] flex-col overflow-y-auto bg-white shadow-2xl">
        <div class="relative flex items-center gap-3 border-b border-border-gray p-6">
          <div
            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-bold text-white"
          >
            {{ initials }}
          </div>
          <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-ink">{{ CURRENT_USER.name }}</p>
            <p class="truncate text-xs text-gray-500">{{ CURRENT_USER.email }}</p>
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
            :class="isActive(item.route) ? 'bg-primary/10 font-semibold text-primary' : 'text-gray-600 hover:bg-gray-100'"
          >
            <component :is="item.icon" class="h-5 w-5 shrink-0" />
            <span>{{ item.name }}</span>
            <span
              v-if="item.count"
              class="ml-auto rounded-full bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary"
            >
              {{ item.count }}
            </span>
          </RouterLink>
        </nav>
        <div class="border-t border-border-gray p-4">
          <RouterLink
            to="/"
            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-600 hover:bg-gray-100 hover:text-ink"
          >
            <ShoppingBag class="h-5 w-5 shrink-0" />
            Back to store
          </RouterLink>
          <button
            type="button"
            class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-600 hover:bg-gray-100 hover:text-red-500"
            @click="signOut"
          >
            <LogOut class="h-5 w-5 shrink-0" />
            Sign out
          </button>
        </div>
      </div>
    </div>

    <aside class="hidden w-72 shrink-0 border-r border-border-gray bg-white lg:flex lg:flex-col">
      <div class="flex items-center gap-3 border-b border-border-gray p-6">
        <div
          class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-bold text-white"
        >
          {{ initials }}
        </div>
        <div class="min-w-0">
          <p class="truncate text-sm font-semibold text-ink">{{ CURRENT_USER.name }}</p>
          <p class="truncate text-xs text-gray-500">{{ CURRENT_USER.email }}</p>
        </div>
      </div>
      <nav class="flex-1 space-y-1 p-4">
        <RouterLink
          v-for="item in navItems"
          :key="item.route"
          :to="{ name: item.route }"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm"
          :class="isActive(item.route) ? 'bg-primary/10 font-semibold text-primary' : 'text-gray-600 hover:bg-gray-100'"
        >
          <component :is="item.icon" class="h-5 w-5 shrink-0" />
          <span>{{ item.name }}</span>
          <span
            v-if="item.count"
            class="ml-auto rounded-full bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary"
          >
            {{ item.count }}
          </span>
        </RouterLink>
      </nav>
      <div class="border-t border-border-gray p-4">
        <RouterLink
          to="/"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-600 hover:bg-gray-100 hover:text-ink"
        >
          <ShoppingBag class="h-5 w-5 shrink-0" />
          Back to store
        </RouterLink>
        <button
          type="button"
          class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-600 hover:bg-gray-100 hover:text-red-500"
          @click="signOut"
        >
          <LogOut class="h-5 w-5 shrink-0" />
          Sign out
        </button>
      </div>
    </aside>

    <main class="flex-1 p-4 sm:p-6 lg:p-8">
      <h1 class="mb-6 text-2xl font-bold text-ink">{{ route.meta.title ?? 'Dashboard' }}</h1>
      <router-view />
    </main>
  </div>
</template>