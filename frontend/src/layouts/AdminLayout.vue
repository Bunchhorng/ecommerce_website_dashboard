<script setup lang="ts">
import type { Component } from 'vue'
import { computed, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import {
  LayoutDashboard,
  Boxes,
  PackagePlus,
  FolderTree,
  Tags,
  ShoppingCart,
  Truck,
  TicketPercent,
  Users,
  MessageSquare,
  Search,
  Bell,
  ChevronDown,
  Menu,
  PanelLeftClose,
  PanelLeftOpen,
  LogOut,
  Settings
} from 'lucide-vue-next'
import { useUiStore } from '@/stores/ui'
import { NOTIFICATIONS } from '@/data/mock'
import { formatDateTime } from '@/utils/format'

interface NavItem {
  label: string
  route: { name: string }
  icon: Component
}

interface NavGroup {
  title: string
  items: NavItem[]
}

const route = useRoute()
const router = useRouter()
const uiStore = useUiStore()

const expanded = computed(() => !uiStore.adminSidebarCollapsed)
const profileOpen = ref(false)
const unreadCount = computed(() => NOTIFICATIONS.filter((n) => !n.read).length)

const navGroups: NavGroup[] = [
  {
    title: 'Overview',
    items: [{ label: 'Dashboard', route: { name: 'admin-dashboard' }, icon: LayoutDashboard }]
  },
  {
    title: 'Catalog',
    items: [
      { label: 'Products', route: { name: 'admin-products' }, icon: Boxes },
      { label: 'Add Product', route: { name: 'admin-product-create' }, icon: PackagePlus },
      { label: 'Categories', route: { name: 'admin-categories' }, icon: FolderTree },
      { label: 'Brands', route: { name: 'admin-brands' }, icon: Tags }
    ]
  },
  {
    title: 'Fulfillment',
    items: [
      { label: 'Orders', route: { name: 'admin-orders' }, icon: ShoppingCart },
      { label: 'Shipping Methods', route: { name: 'admin-shipping' }, icon: Truck }
    ]
  },
  {
    title: 'Marketing',
    items: [{ label: 'Coupons', route: { name: 'admin-coupons' }, icon: TicketPercent }]
  },
  {
    title: 'Community',
    items: [
      { label: 'Customers', route: { name: 'admin-customers' }, icon: Users },
      { label: 'Reviews', route: { name: 'admin-reviews' }, icon: MessageSquare }
    ]
  }
]

function isActive(item: NavItem): boolean {
  return route.name === item.route.name
}

function signOut() {
  router.push('/')
}
</script>

<template>
  <div class="flex h-screen overflow-hidden bg-canvas">
    <aside
      class="flex shrink-0 flex-col border-r border-border-gray bg-white transition-all duration-200"
      :class="expanded ? 'w-64' : 'w-[72px]'"
    >
      <div class="flex h-16 items-center border-b border-border-gray p-4" :class="expanded ? '' : 'justify-center'">
        <RouterLink v-if="expanded" to="/admin" class="flex items-center gap-2.5">
          <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-sm font-extrabold text-white">SV</div>
          <div class="flex flex-col items-start leading-none">
            <span class="text-base font-extrabold tracking-tight text-ink">ShopVerse</span>
            <span class="chip mt-1 !px-2 !py-0 text-[10px] uppercase tracking-wide">Admin</span>
          </div>
        </RouterLink>
        <RouterLink v-else to="/admin" title="ShopVerse Admin">
          <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-sm font-extrabold text-white">SV</div>
        </RouterLink>
      </div>

      <nav class="flex-1 space-y-1 overflow-y-auto p-3">
        <template v-for="group in navGroups" :key="group.title">
          <div v-if="expanded" class="px-3 pb-1 pt-4 text-[11px] font-semibold uppercase tracking-wider text-gray-400">
            {{ group.title }}
          </div>
          <RouterLink
            v-for="item in group.items"
            :key="item.label"
            :to="item.route"
            :title="expanded ? undefined : item.label"
            class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
            :class="[isActive(item) ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-100', expanded ? '' : 'justify-center']"
          >
            <component :is="item.icon" :size="18" />
            <span v-if="expanded">{{ item.label }}</span>
          </RouterLink>
        </template>
      </nav>

      <div class="border-t border-border-gray p-3">
        <button
          class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition-colors hover:bg-gray-100 hover:text-primary"
          :class="expanded ? '' : 'justify-center'"
          @click="uiStore.toggleAdminSidebar()"
        >
          <component :is="expanded ? PanelLeftClose : PanelLeftOpen" :size="18" />
          <span v-if="expanded">Collapse</span>
        </button>
      </div>
    </aside>

    <div class="flex flex-1 flex-col overflow-hidden">
      <header class="flex h-16 shrink-0 items-center gap-3 border-b border-border-gray bg-white px-4 sm:px-6">
        <button class="btn-icon" title="Toggle sidebar" @click="uiStore.toggleAdminSidebar()">
          <Menu class="h-5 w-5" />
        </button>
        <h1 class="text-base font-semibold text-ink sm:text-lg">{{ route.meta.title }}</h1>
        <div class="flex-1"></div>

        <div class="relative hidden md:block">
          <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
          <input type="text" class="input h-9 w-64 pl-9" placeholder="Search…" />
        </div>

        <div class="relative">
          <button class="btn-icon relative" title="Notifications" @click="uiStore.toggleAdminNotifications()">
            <Bell class="h-5 w-5" />
            <span v-if="unreadCount > 0" class="absolute right-2.5 top-2.5 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
          </button>

          <template v-if="uiStore.adminNotificationsOpen">
            <div class="fixed inset-0 z-40" @click="uiStore.adminNotificationsOpen = false"></div>
            <div class="absolute right-0 top-12 z-50 w-80 overflow-hidden rounded-xl border border-border-gray bg-white shadow-popover">
              <div class="flex items-center justify-between border-b border-border-gray px-4 py-3">
                <span class="text-sm font-semibold text-ink">Notifications</span>
                <span class="chip !py-0.5 text-[11px]">{{ unreadCount }} new</span>
              </div>
              <div class="max-h-80 divide-y divide-border-gray overflow-y-auto">
                <div v-for="n in NOTIFICATIONS" :key="n.id" class="flex gap-3 px-4 py-3">
                  <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                      <span class="truncate text-sm font-medium text-ink">{{ n.title }}</span>
                      <span v-if="!n.read" class="h-1.5 w-1.5 shrink-0 rounded-full bg-primary"></span>
                    </div>
                    <p class="mt-0.5 truncate text-xs text-gray-500">{{ n.message }}</p>
                    <div class="mt-1 text-[11px] text-gray-400">{{ formatDateTime(n.date) }}</div>
                  </div>
                </div>
              </div>
              <RouterLink
                to="/account/notifications"
                class="block border-t border-border-gray px-4 py-2.5 text-center text-sm font-medium text-primary transition-colors hover:bg-canvas"
                @click="uiStore.adminNotificationsOpen = false"
              >
                View all
              </RouterLink>
            </div>
          </template>
        </div>

        <div class="relative">
          <button class="flex items-center gap-2 rounded-lg px-2 py-1.5 transition-colors hover:bg-gray-100" @click="profileOpen = !profileOpen">
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-sm font-bold text-white">A</div>
            <span class="hidden text-sm font-medium text-ink sm:block">Admin</span>
            <ChevronDown class="h-4 w-4 text-gray-400" />
          </button>

          <template v-if="profileOpen">
            <div class="fixed inset-0 z-40" @click="profileOpen = false"></div>
            <div class="absolute right-0 top-12 z-50 w-48 overflow-hidden rounded-xl border border-border-gray bg-white py-1 shadow-popover">
              <RouterLink to="/account/profile" class="flex items-center px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-50 hover:text-ink" @click="profileOpen = false">
                Profile
              </RouterLink>
              <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-50 hover:text-ink">
                <Settings class="h-4 w-4" />
                Settings
              </a>
              <div class="my-1 border-t border-border-gray"></div>
              <button class="flex w-full items-center gap-2 px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50" @click="signOut">
                <LogOut class="h-4 w-4" />
                Sign out
              </button>
            </div>
          </template>
        </div>
      </header>

      <main class="flex-1 overflow-y-auto p-4 sm:p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>