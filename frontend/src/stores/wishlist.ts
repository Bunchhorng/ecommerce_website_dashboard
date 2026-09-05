import { defineStore } from 'pinia'
import { wishlistApi, catalogApi } from '@/api'
import type { CatalogProduct } from '@/api'

const LOCAL_STORAGE_KEY = 'ekhmer_wishlist'

function load(): number[] {
  try {
    const raw = localStorage.getItem(LOCAL_STORAGE_KEY)
    return raw ? (JSON.parse(raw) as number[]) : []
  } catch {
    return []
  }
}

function save(ids: number[]) {
  try {
    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(ids))
  } catch {
    /* ignore */
  }
}

export const useWishlistStore = defineStore('wishlist', {
  state: () => ({
    productIds: load() as number[],
    products: [] as CatalogProduct[],
    loading: false
  }),

  getters: {
    count(state): number {
      return state.productIds.length
    },
    isWishlisted: (state) => (productId: string): boolean => {
      const numId = Number(productId)
      return state.productIds.includes(numId)
    }
  },

  actions: {
    async fetchProducts() {
      this.loading = true
      try {
        const ids = this.productIds
        if (ids.length === 0) {
          this.products = []
          return
        }
        const { data } = await wishlistApi.list()
        this.products = data.data
      } catch {
        this.products = []
      } finally {
        this.loading = false
      }
    },

    async toggle(productId: string) {
      const numId = Number(productId)
      const idx = this.productIds.indexOf(numId)
      if (idx >= 0) {
        this.productIds.splice(idx, 1)
        try { await wishlistApi.remove(numId) } catch { /* ignore */ }
      } else {
        this.productIds.push(numId)
        try { await wishlistApi.add(numId) } catch { /* ignore */ }
      }
      save(this.productIds)
      await this.fetchProducts()
      return !this.isWishlisted(productId)
    },

    async remove(productId: string) {
      const numId = Number(productId)
      this.productIds = this.productIds.filter((id) => id !== numId)
      save(this.productIds)
      try { await wishlistApi.remove(numId) } catch { /* ignore */ }
      await this.fetchProducts()
    },

    clear() {
      this.productIds = []
      this.products = []
      save(this.productIds)
    }
  }
})
