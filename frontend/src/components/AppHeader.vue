<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from "vue";
import { useRouter } from "vue-router";
import type { RouteLocationRaw } from "vue-router";
import { Heart, Menu, Search, ShoppingBag, User, X } from "lucide-vue-next";
import { CURRENT_USER, NAV_CATEGORIES } from "@/data/mock";
import { useCartStore } from "@/stores/cart";
import { useUiStore } from "@/stores/ui";
import { useWishlistStore } from "@/stores/wishlist";

const router = useRouter();
const cartStore = useCartStore();
const uiStore = useUiStore();
const wishlistStore = useWishlistStore();

const searchTerm = ref("");
const mobileOpen = ref(false);
const userMenuOpen = ref(false);
const userMenuRef = ref<HTMLElement | null>(null);

const accountLinks: Array<{ label: string; to: RouteLocationRaw }> = [
  { label: "Dashboard", to: { name: "account-dashboard" } },
  { label: "My Orders", to: { name: "account-orders" } },
  { label: "Wishlist", to: { name: "account-wishlist" } },
  { label: "Addresses", to: { name: "account-addresses" } },
  { label: "Profile", to: { name: "account-profile" } },
];

function submitSearch(): void {
  const q = searchTerm.value.trim();
  router.push({ name: "shop", query: q ? { q } : {} });
}

function openMobile(): void {
  mobileOpen.value = true;
}

function closeMobile(): void {
  mobileOpen.value = false;
}

function toggleUserMenu(): void {
  userMenuOpen.value = !userMenuOpen.value;
}

function closeUserMenu(): void {
  userMenuOpen.value = false;
}

function onClickOutside(event: MouseEvent): void {
  if (userMenuRef.value && !userMenuRef.value.contains(event.target as Node)) {
    closeUserMenu();
  }
}

watch(userMenuOpen, (open) => {
  if (open) document.addEventListener("click", onClickOutside);
  else document.removeEventListener("click", onClickOutside);
});

onBeforeUnmount(() => document.removeEventListener("click", onClickOutside));
</script>

<template>
  <header>
    <div
      class="bg-primary-dark px-4 py-1.5 text-center text-xs font-medium text-white"
    >
      Free shipping on orders over $100 · Easy 30-day returns
    </div>
    <div class="sticky-header">
      <div class="container-app flex h-16 items-center gap-4 lg:h-20">
        <button
          type="button"
          class="btn-icon lg:hidden"
          aria-label="Open menu"
          @click="openMobile"
        >
          <Menu :size="22" />
        </button>

        <RouterLink
          to="/"
          class="text-2xl font-extrabold tracking-tight text-primary"
        >
          Shop<span class="text-ink">Verse</span>
        </RouterLink>

        <form
          class="relative hidden flex-1 max-w-lg md:block"
          role="search"
          @submit.prevent="submitSearch"
        >
          <Search
            class="pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-gray-400"
            :size="18"
          />
          <input
            v-model="searchTerm"
            type="search"
            class="input py-2.5 pl-10 pr-14"
            placeholder="Search products, brands and more…"
          />
          <button
            type="submit"
            class="btn-icon absolute right-1.5 top-1/2 h-8 w-8 -translate-y-1/2 rounded-md bg-primary text-white transition-colors hover:bg-primary-dark hover:text-white"
            aria-label="Search"
          >
            <Search :size="15" />
          </button>
        </form>

        <div class="ml-auto flex items-center gap-1">
          <RouterLink
            to="/account/wishlist"
            class="btn-icon relative"
            aria-label="Wishlist"
          >
            <Heart :size="20" />
            <span
              v-if="wishlistStore.count > 0"
              class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-bold text-white"
            >
              {{ wishlistStore.count }}
            </span>
          </RouterLink>

          <button
            type="button"
            class="btn-icon relative"
            aria-label="Open cart"
            @click="uiStore.openCartDrawer()"
          >
            <ShoppingBag :size="20" />
            <span
              v-if="cartStore.totalItemCount > 0"
              class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-bold text-white"
            >
              {{ cartStore.totalItemCount }}
            </span>
          </button>

          <div ref="userMenuRef" class="relative">
            <button
              type="button"
              class="btn-icon"
              :aria-expanded="userMenuOpen"
              aria-label="Account menu"
              @click="toggleUserMenu"
            >
              <User :size="20" />
            </button>
            <div
              v-if="userMenuOpen"
              class="card absolute right-0 top-11 z-50 w-56 overflow-hidden py-2 shadow-popover"
            >
              <div class="border-b border-border-gray px-4 py-3">
                <p class="text-sm font-semibold text-ink">
                  {{ CURRENT_USER.name }}
                </p>
                <p class="truncate text-xs text-gray-500">
                  {{ CURRENT_USER.email }}
                </p>
              </div>
              <div class="py-1">
                <RouterLink
                  v-for="link in accountLinks"
                  :key="link.label"
                  :to="link.to"
                  class="block px-4 py-2 text-sm text-gray-600 transition-colors hover:bg-canvas hover:text-primary"
                  @click="closeUserMenu"
                >
                  {{ link.label }}
                </RouterLink>
              </div>
              <div class="border-t border-border-gray px-4 py-2">
                <button
                  type="button"
                  class="text-sm font-medium text-red-500 transition-colors hover:text-red-700"
                  @click="closeUserMenu"
                >
                  Sign out
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <Teleport to="body">
    <Transition name="drawer">
      <div v-if="mobileOpen" class="fixed inset-0 z-50">
        <div class="fixed inset-0 bg-black/40" @click="closeMobile"></div>
        <aside
          class="panel fixed inset-y-0 left-0 z-50 flex w-72 max-w-[85vw] flex-col bg-white shadow-xl"
        >
          <div
            class="flex items-center justify-between border-b border-border-gray px-4 py-4"
          >
            <RouterLink
              to="/"
              class="text-xl font-extrabold tracking-tight text-primary"
              @click="closeMobile"
            >
              Shop<span class="text-ink">Verse</span>
            </RouterLink>
            <button
              type="button"
              class="btn-icon"
              aria-label="Close menu"
              @click="closeMobile"
            >
              <X :size="20" />
            </button>
          </div>
          <p
            class="px-5 pt-4 text-xs font-bold uppercase tracking-widest text-gray-400"
          >
            Shop by category
          </p>
          <nav class="flex-1 space-y-1 overflow-y-auto p-4">
            <RouterLink
              v-for="item in NAV_CATEGORIES"
              :key="item.id"
              :to="{ name: 'shop', query: { category: item.slug } }"
              class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-canvas hover:text-primary"
              @click="closeMobile"
            >
              {{ item.name }}
            </RouterLink>
          </nav>
          <div class="space-y-1 border-t border-border-gray p-4">
            <RouterLink
              to="/cart"
              class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-canvas hover:text-primary"
              @click="closeMobile"
            >
              Cart
            </RouterLink>
            <RouterLink
              to="/account"
              class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-canvas hover:text-primary"
              @click="closeMobile"
            >
              Dashboard
            </RouterLink>
            <RouterLink
              to="/account/wishlist"
              class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-canvas hover:text-primary"
              @click="closeMobile"
            >
              Wishlist
            </RouterLink>
          </div>
        </aside>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.drawer-enter-active,
.drawer-leave-active {
  transition: opacity 0.2s ease;
}
.drawer-enter-active .panel,
.drawer-leave-active .panel {
  transition: transform 0.25s ease;
}
.drawer-enter-from,
.drawer-leave-to {
  opacity: 0;
}
.drawer-enter-from .panel,
.drawer-leave-to .panel {
  transform: translateX(-100%);
}
</style>
