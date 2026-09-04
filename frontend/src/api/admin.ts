import apiClient from './client'
import type { ApiOrder } from './checkout'
import type { CatalogProduct, PaginatedResponse } from './catalog'

export interface AdminDashboard {
  metrics: {
    total_revenue: number
    orders_count: number
    customers_count: number
    pending_orders: number
    low_stock_products: number
  }
  revenue_trend: { date: string; revenue: number }[]
  status_distribution: { status: string; count: number }[]
  sales_by_category: { id: number; name: string; slug: string; revenue: number; order_count: number }[]
}

export interface AdminProduct extends CatalogProduct {
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

export interface AdminCategory {
  id: number
  name: string
  slug: string
  description: string | null
  image: string | null
  sort_order: number
  is_active: boolean
  parent_id: number | null
  products_count?: number
  children?: AdminCategory[]
}

export interface AdminBrand {
  id: number
  name: string
  slug: string
  description: string | null
  logo: string | null
  is_active: boolean
  products_count?: number
}

export interface AdminShippingMethod {
  id: number
  name: string
  code: string
  description: string | null
  price: number
  estimated_days_min: number | null
  estimated_days_max: number | null
  is_active: boolean
}

export interface AdminOrderItem {
  id: number
  order_number: string
  status: string
  payment_status: string
  total: number
  placed_at: string
  items_count: number
  user?: { id: number; name: string; email: string }
}

export interface AdminCoupon {
  id: number
  code: string
  type: 'percentage' | 'fixed'
  value: number
  min_order_amount: number | null
  max_discount_amount: number | null
  usage_limit: number | null
  per_user_limit: number | null
  starts_at: string | null
  expires_at: string | null
  is_active: boolean
}

export interface AdminReview {
  id: number
  rating: number
  title: string | null
  body: string | null
  verified: boolean
  status: string
  helpful_count: number
  created_at: string
  user: { id: number; name: string }
  product: { id: number; name: string; slug: string }
}

export interface AdminInventoryItem {
  id: number
  product_variant_id: number
  product: { id: number; name: string; slug: string }
  variant: { id: number; name: string; sku: string; is_active: boolean }
  variant_label: string
  quantity: number
  reserved_quantity: number
  available_quantity: number
  sold_count: number
  low_stock_threshold: number
  is_low_stock: boolean
  low_stock_notified_at: string | null
  updated_at: string
}

export interface InventoryTransaction {
  id: number
  inventory_id: number
  type: 'reserve' | 'release' | 'deduct' | 'adjust'
  quantity: number
  balance_after: number
  reference: string | null
  note: string | null
  created_by: { id: number; name: string } | null
  created_at: string
}

export interface AdminCustomer {
  id: number
  name: string
  email: string
  phone: string | null
  role: string
  avatar: string | null
  created_at: string
}

export interface AdminCustomerDetail {
  user: AdminCustomer
  orders_count: number
  lifetime_spend: number
  recent_orders: AdminOrderItem[]
}

export const adminApi = {
  getDashboard(days?: number) {
    const params: Record<string, string> = {}
    if (days) params.days = String(days)
    return apiClient.get<{ data: AdminDashboard }>('/admin/dashboard/overview', { params })
  },

  getOrdersCsv(status?: string, from?: string, to?: string) {
    const params: Record<string, string> = {}
    if (status) params.status = status
    if (from) params.from = from
    if (to) params.to = to
    return apiClient.get<Blob>('/admin/reports/orders.csv', { params, responseType: 'blob' })
  },

  getOrdersPdf(status?: string, from?: string, to?: string) {
    const params: Record<string, string> = {}
    if (status) params.status = status
    if (from) params.from = from
    if (to) params.to = to
    return apiClient.get<Blob>('/admin/reports/orders.pdf', { params, responseType: 'blob' })
  },

  listProducts(params: { q?: string; category_id?: number; brand_id?: number; stock_status?: string } = {}) {
    return apiClient.get<PaginatedResponse<AdminProduct>>('/admin/products', { params })
  },

  getProduct(id: number) {
    return apiClient.get<{ data: AdminProduct }>(`/admin/products/${id}`)
  },

  createProduct(payload: Record<string, unknown>) {
    return apiClient.post<{ data: AdminProduct }>('/admin/products', payload)
  },

  updateProduct(id: number, payload: Record<string, unknown>) {
    return apiClient.put<{ data: AdminProduct }>(`/admin/products/${id}`, payload)
  },

  deleteProduct(id: number) {
    return apiClient.delete<{ data: { message: string } }>(`/admin/products/${id}`)
  },

  listCategories() {
    return apiClient.get<{ data: AdminCategory[] }>('/admin/categories')
  },

  createCategory(payload: Record<string, unknown>) {
    return apiClient.post<{ data: AdminCategory }>('/admin/categories', payload)
  },

  updateCategory(id: number, payload: Record<string, unknown>) {
    return apiClient.put<{ data: AdminCategory }>(`/admin/categories/${id}`, payload)
  },

  deleteCategory(id: number) {
    return apiClient.delete<{ data: { message: string } }>(`/admin/categories/${id}`)
  },

  listBrands() {
    return apiClient.get<{ data: AdminBrand[] }>('/admin/brands')
  },

  createBrand(payload: Record<string, unknown>) {
    return apiClient.post<{ data: AdminBrand }>('/admin/brands', payload)
  },

  updateBrand(id: number, payload: Record<string, unknown>) {
    return apiClient.put<{ data: AdminBrand }>(`/admin/brands/${id}`, payload)
  },

  deleteBrand(id: number) {
    return apiClient.delete<{ data: { message: string } }>(`/admin/brands/${id}`)
  },

  listShippingMethods() {
    return apiClient.get<{ data: AdminShippingMethod[] }>('/admin/shipping-methods')
  },

  createShippingMethod(payload: Record<string, unknown>) {
    return apiClient.post<{ data: AdminShippingMethod }>('/admin/shipping-methods', payload)
  },

  updateShippingMethod(id: number, payload: Record<string, unknown>) {
    return apiClient.put<{ data: AdminShippingMethod }>(`/admin/shipping-methods/${id}`, payload)
  },

  deleteShippingMethod(id: number) {
    return apiClient.delete<{ data: { message: string } }>(`/admin/shipping-methods/${id}`)
  },

  listOrders(params: { status?: string; q?: string } = {}) {
    return apiClient.get<PaginatedResponse<AdminOrderItem>>('/admin/orders', { params })
  },

  getOrder(id: number) {
    return apiClient.get<{ data: ApiOrder }>(`/admin/orders/${id}`)
  },

  transitionOrder(id: number, status: string) {
    return apiClient.put<{ data: ApiOrder }>(`/admin/orders/${id}/transition`, { status })
  },

  getOrderReceipt(id: number) {
    return apiClient.get<Blob>(`/admin/orders/${id}/receipt`, { responseType: 'blob' })
  },

  listCoupons() {
    return apiClient.get<PaginatedResponse<AdminCoupon>>('/admin/coupons')
  },

  createCoupon(payload: Record<string, unknown>) {
    return apiClient.post<{ data: AdminCoupon }>('/admin/coupons', payload)
  },

  updateCoupon(id: number, payload: Record<string, unknown>) {
    return apiClient.put<{ data: AdminCoupon }>(`/admin/coupons/${id}`, payload)
  },

  deleteCoupon(id: number) {
    return apiClient.delete<{ data: { message: string } }>(`/admin/coupons/${id}`)
  },

  listReviews(params: { status?: string } = {}) {
    return apiClient.get<PaginatedResponse<AdminReview>>('/admin/reviews', { params })
  },

  approveReview(id: number) {
    return apiClient.post<{ data: AdminReview }>(`/admin/reviews/${id}/approve`)
  },

  rejectReview(id: number) {
    return apiClient.post<{ data: AdminReview }>(`/admin/reviews/${id}/reject`)
  },

  listCustomers(params: { q?: string } = {}) {
    return apiClient.get<PaginatedResponse<AdminCustomer>>('/admin/customers', { params })
  },

  getCustomer(id: number) {
    return apiClient.get<AdminCustomerDetail>(`/admin/customers/${id}`)
  },

  listInventory(params: { q?: string; stock_status?: string; page?: number } = {}) {
    return apiClient.get<PaginatedResponse<AdminInventoryItem>>('/admin/inventory', { params })
  },

  listInventoryTransactions(inventoryId: number, params: { type?: string; page?: number } = {}) {
    return apiClient.get<PaginatedResponse<InventoryTransaction>>(`/admin/inventory/${inventoryId}/transactions`, { params })
  }
}
