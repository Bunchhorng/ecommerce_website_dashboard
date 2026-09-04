import apiClient from './client'

export interface CatalogProduct {
  id: number
  slug: string
  name: string
  short_description: string | null
  price: number
  compare_at_price: number | null
  rating_avg: number
  rating_count: number
  is_featured: boolean
  is_active: boolean
  in_stock: boolean
  cover_image: string | null
  brand: { slug: string; name: string } | null
  category: { slug: string; name: string } | null
}

export interface ProductDetail extends CatalogProduct {
  description: string | null
  sku: string
  weight: number | null
  meta_title: string | null
  meta_description: string | null
  gallery: { id: number; image_path: string; alt_text: string | null }[]
  attributes: {
    id: number
    name: string
    slug: string
    type: string
    values: { id: number; value: string; swatch_color: string | null }[]
  }[]
  variants: {
    id: number
    sku: string
    name: string
    price: number
    compare_at_price: number | null
    is_default: boolean
    is_active: boolean
    available_quantity: number
    in_stock: boolean
    attributes: { attribute_slug: string; name: string; value: string }[]
  }[]
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export interface Facets {
  brands: { slug: string; name: string; count: number }[]
  categories: { slug: string; name: string; count: number }[]
  colors: { slug: string; name: string; count: number }[]
  sizes: { slug: string; name: string; count: number }[]
}

export interface CatalogFilters {
  q?: string
  category?: string
  brand?: string
  colors?: string[]
  sizes?: string[]
  min?: number
  max?: number
  rating?: number
  stock?: number
  sort?: string
  page?: number
  perPage?: number
}

export const catalogApi = {
  getProducts(filters: CatalogFilters = {}) {
    const params = new URLSearchParams()
    if (filters.q) params.set('q', filters.q)
    if (filters.category) params.set('category', filters.category)
    if (filters.brand) params.set('brand', filters.brand)
    if (filters.colors?.length) filters.colors.forEach((c) => params.append('colors[]', c))
    if (filters.sizes?.length) filters.sizes.forEach((s) => params.append('sizes[]', s))
    if (filters.min !== undefined) params.set('min', String(filters.min))
    if (filters.max !== undefined) params.set('max', String(filters.max))
    if (filters.rating !== undefined) params.set('rating', String(filters.rating))
    if (filters.stock !== undefined) params.set('stock', String(filters.stock))
    if (filters.sort) params.set('sort', filters.sort)
    if (filters.page) params.set('page', String(filters.page))
    if (filters.perPage) params.set('perPage', String(filters.perPage))
    return apiClient.get<PaginatedResponse<CatalogProduct>>('/catalog/products', { params })
  },

  getProduct(slug: string) {
    return apiClient.get<{ data: ProductDetail }>(`/catalog/products/${slug}`)
  },

  getFeatured(limit = 8) {
    return apiClient.get<{ data: CatalogProduct[] }>('/catalog/featured', { params: { limit } })
  },

  getFacets() {
    return apiClient.get<{ data: Facets }>('/catalog/facets')
  }
}
