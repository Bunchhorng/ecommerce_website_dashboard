import { defineStore } from 'pinia'
import { getProductById } from '@/data/mock'
import type { Product } from '@/types'

const LOCAL_STORAGE_KEY = 'shopverse_wishlist'

function load(): string[] {
  try {
    const raw = localStorage.getItem(LOCAL_STORAGE_KEY)
    return raw ? (JSON.parse(raw) as string[]) : []
  } catch {
    return []
  }
}

function save(ids: string[]) {
  try {
    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(ids))
  } catch {
    /* ignore */
  }
}

export const useWishlistStore = defineStore('wishlist', {
  state: () => ({
    productIds: load() as string[]
  }),

  getters: {
    count(state): number {
      return state.productIds.length
    },
    products(): Product[] {
      return this.productIds
        .map((id) => getProductById(id))
        .filter((p): p is Product => Boolean(p))
    },
    isWishlisted: (state) => (productId: string): boolean => state.productIds.includes(productId)
  },

  actions: {
    toggle(productId: string) {
      const idx = this.productIds.indexOf(productId)
      if (idx >= 0) {
        this.productIds.splice(idx, 1)
      } else {
        this.productIds.push(productId)
      }
      save(this.productIds)
      return !this.isWishlisted(productId)
    },
    remove(productId: string) {
      this.productIds = this.productIds.filter((id) => id !== productId)
      save(this.productIds)
    },
    clear() {
      this.productIds = []
      save(this.productIds)
    }
  }
})