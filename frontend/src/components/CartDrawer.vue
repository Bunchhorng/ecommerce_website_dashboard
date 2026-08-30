<script setup lang="ts">
import { useRouter } from 'vue-router'
import { ShoppingBag, Trash2, X } from 'lucide-vue-next'
import { useCartStore } from '@/stores/cart'
import { useUiStore } from '@/stores/ui'
import type { CartVariantSelection } from '@/types'
import { formatPrice } from '@/utils/format'
import EmptyState from './EmptyState.vue'
import QuantityCounter from './QuantityCounter.vue'

const router = useRouter()
const cartStore = useCartStore()
const uiStore = useUiStore()

function closeCartDrawer(): void {
  uiStore.closeCartDrawer()
}

function startShopping(): void {
  closeCartDrawer()
  router.push('/shop')
}

function updateItemQuantity(itemId: string, quantity: number): void {
  cartStore.updateQuantity(itemId, quantity)
}

function removeItem(itemId: string): void {
  cartStore.removeItem(itemId)
}

function variantLabel(variant: CartVariantSelection): string {
  return variant.attributes.map((a) => a.value).join(', ')
}
</script>

<template>
  <Teleport to="body">
    <Transition name="drawer">
      <div v-if="uiStore.cartDrawerOpen" class="fixed inset-0 z-50">
        <div class="fixed inset-0 bg-black/40" @click="closeCartDrawer"></div>
        <aside class="panel fixed inset-y-0 right-0 z-50 flex w-full max-w-sm flex-col bg-white shadow-xl">
          <header class="flex shrink-0 items-center justify-between border-b border-border-gray px-5 py-4">
            <h2 class="flex items-center gap-2 text-lg font-semibold text-ink">
              Your Cart
              <span
                v-if="cartStore.totalItemCount > 0"
                class="flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1.5 text-xs font-semibold text-white"
              >
                {{ cartStore.totalItemCount }}
              </span>
            </h2>
            <button type="button" class="btn-icon" aria-label="Close cart" @click="closeCartDrawer">
              <X :size="20" />
            </button>
          </header>

          <div class="flex-1 overflow-y-auto p-4">
            <EmptyState
              v-if="cartStore.items.length === 0"
              title="Your cart is empty"
              description="Explore our collections and find something you love."
              cta-label="Start Shopping"
              @cta="startShopping"
            >
              <template #icon>
                <ShoppingBag :size="28" />
              </template>
            </EmptyState>

            <ul v-else class="divide-y divide-border-gray">
              <li v-for="item in cartStore.items" :key="item.id" class="flex gap-3 py-4">
                <RouterLink :to="`/product/${item.slug}`" class="shrink-0">
                  <img
                    :src="item.image"
                    :alt="item.title"
                    loading="lazy"
                    class="h-20 w-16 rounded-lg object-cover"
                  />
                </RouterLink>
                <div class="min-w-0 flex-1">
                  <RouterLink
                    :to="`/product/${item.slug}`"
                    class="line-clamp-1 text-sm font-semibold text-ink transition-colors hover:text-primary"
                  >
                    {{ item.title }}
                  </RouterLink>
                  <p v-if="item.variant" class="mt-0.5 line-clamp-1 text-xs text-gray-500">
                    {{ variantLabel(item.variant) }}
                  </p>
                  <div class="mt-2 flex items-center justify-between gap-2">
                    <span class="text-xs text-gray-400">{{ formatPrice(item.unitPrice) }}</span>
                    <QuantityCounter
                      size="sm"
                      :model-value="item.quantity"
                      @update:model-value="(qty) => updateItemQuantity(item.id, qty)"
                    />
                    <span class="text-sm font-semibold text-ink">
                      {{ formatPrice(item.unitPrice * item.quantity) }}
                    </span>
                  </div>
                </div>
                <button
                  type="button"
                  class="shrink-0 self-start text-red-500 transition-colors hover:text-red-700"
                  aria-label="Remove item"
                  @click="removeItem(item.id)"
                >
                  <Trash2 :size="15" />
                </button>
              </li>
            </ul>
          </div>

          <footer v-if="cartStore.items.length > 0" class="shrink-0 space-y-3 border-t border-border-gray p-5">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-600">Subtotal</span>
              <span class="text-base font-bold text-ink">{{ formatPrice(cartStore.subtotal) }}</span>
            </div>
            <p class="text-xs text-gray-500">Shipping &amp; taxes calculated at checkout</p>
            <RouterLink to="/checkout" class="btn-primary w-full" @click="closeCartDrawer">
              Proceed to Checkout
            </RouterLink>
            <p class="text-center">
              <RouterLink
                to="/cart"
                class="text-sm font-semibold text-primary transition-colors hover:text-primary-dark"
                @click="closeCartDrawer"
              >
                View Cart
              </RouterLink>
            </p>
          </footer>
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
  transform: translateX(100%);
}
</style>