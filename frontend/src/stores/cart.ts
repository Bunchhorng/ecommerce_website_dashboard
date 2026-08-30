import { defineStore } from 'pinia'
import { COUPONS } from '@/data/mock'
import type { CartItem, Coupon, Product } from '@/types'

export const TAX_RATE = 0.1

export interface AddToCartInput {
  product: Product
  variantId?: string
  quantity?: number
}

function round2(value: number): number {
  return Math.round((value + Number.EPSILON) * 100) / 100
}

function toUSD(value: number): string {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value)
}

const LOCAL_STORAGE_KEY = 'shopverse_cart'

function persist(state: CartState) {
  try {
    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify({ items: state.items, appliedCoupon: state.appliedCoupon }))
  } catch {
    /* storage unavailable */
  }
}

type CartState = {
  items: CartItem[]
  appliedCoupon: Coupon | null
  couponError: string
  couponSuccess: string
}

export const useCartStore = defineStore('cart', {
  state: (): CartState => {
    let restored: { items: CartItem[]; appliedCoupon: Coupon | null } | null = null
    try {
      const raw = localStorage.getItem(LOCAL_STORAGE_KEY)
      if (raw) restored = JSON.parse(raw) as { items: CartItem[]; appliedCoupon: Coupon | null }
    } catch {
      restored = null
    }
    return {
      items: restored?.items ?? [],
      appliedCoupon: restored?.appliedCoupon ?? null,
      couponError: '',
      couponSuccess: ''
    }
  },

  getters: {
    subtotal(state): number {
      return round2(state.items.reduce((sum, item) => sum + item.unitPrice * item.quantity, 0))
    },
    discountAmount(state): number {
      if (!state.appliedCoupon) return 0
      if (state.appliedCoupon.type === 'percentage') {
        return round2((this.subtotal * state.appliedCoupon.value) / 100)
      }
      return round2(Math.min(state.appliedCoupon.value, this.subtotal))
    },
    taxAmount(): number {
      return round2((this.subtotal - this.discountAmount) * TAX_RATE)
    },
    totalAmount(): number {
      return round2(this.subtotal - this.discountAmount + this.taxAmount)
    },
    totalItemCount(): number {
      return this.items.reduce((sum, item) => sum + item.quantity, 0)
    },
    isEmpty(): boolean {
      return this.items.length === 0
    }
  },

  actions: {
    addItem({ product, variantId, quantity = 1 }: AddToCartInput) {
      const targetVariant = variantId ? product.variants.find((v) => v.id === variantId) : undefined
      const unitPrice = targetVariant?.price ?? product.price
      const maxQty = targetVariant?.stockQuantity ?? product.stockQuantity
      const safeQty = Math.max(1, Math.min(quantity, Math.max(maxQty, 1)))

      const existing = this.items.find(
        (item) => item.productId === product.id && item.variant?.variantId === (variantId ?? null)
      )

      if (existing) {
        existing.quantity = Math.min(existing.quantity + safeQty, Math.max(maxQty, 1))
      } else {
        this.items.push({
          id: `ci-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
          productId: product.id,
          slug: product.slug,
          title: product.title,
          brand: product.brand.name,
          image: product.images[0].url,
          unitPrice,
          quantity: safeQty,
          variant: targetVariant
            ? {
                variantId: targetVariant.id,
                attributes: targetVariant.attributes,
                sku: targetVariant.sku
              }
            : null
        })
      }
      this.clearCouponFeedback()
      persist(this.$state as unknown as CartState)
    },

    removeItem(itemId: string) {
      this.items = this.items.filter((item) => item.id !== itemId)
      persist(this.$state as unknown as CartState)
    },

    updateQuantity(itemId: string, quantity: number) {
      const item = this.items.find((i) => i.id === itemId)
      if (!item) return
      item.quantity = Math.max(1, quantity)
      persist(this.$state as unknown as CartState)
    },

    applyCoupon(code: string): boolean {
      const normalized = code.trim().toUpperCase()
      const coupon = COUPONS.find((c) => c.code === normalized)
      if (!coupon) {
        this.couponError = `Coupon "${normalized}" is not valid.`
        this.couponSuccess = ''
        return false
      }
      if (this.subtotal < coupon.minOrderAmount) {
        this.couponError = `Coupon requires a minimum order of ${toUSD(coupon.minOrderAmount)}.`
        this.couponSuccess = ''
        return false
      }
      this.appliedCoupon = coupon
      this.couponError = ''
      this.couponSuccess = `Coupon ${coupon.code} applied!`
      persist(this.$state as unknown as CartState)
      return true
    },

    removeCoupon() {
      this.appliedCoupon = null
      this.couponSuccess = ''
      this.couponError = ''
      persist(this.$state as unknown as CartState)
    },

    clearCouponFeedback() {
      this.couponSuccess = ''
      this.couponError = ''
    },

    clearCart() {
      this.items = []
      this.appliedCoupon = null
      this.couponSuccess = ''
      this.couponError = ''
      try {
        localStorage.removeItem(LOCAL_STORAGE_KEY)
      } catch {
        /* ignore */
      }
    }
  }
})