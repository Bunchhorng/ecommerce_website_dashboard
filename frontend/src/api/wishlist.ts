import apiClient from './client'
import type { CatalogProduct } from './catalog'

export const wishlistApi = {
  list() {
    return apiClient.get<{ data: CatalogProduct[] }>('/wishlist')
  },

  add(productId: number) {
    return apiClient.post<{ data: number[] }>('/wishlist', { product_id: productId })
  },

  remove(productId: number) {
    return apiClient.delete<{ data: number[] }>(`/wishlist/${productId}`)
  }
}
