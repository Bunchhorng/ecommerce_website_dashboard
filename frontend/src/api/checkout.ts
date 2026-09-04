import apiClient from './client'

export interface CheckoutAddress {
  full_name: string
  phone?: string
  address_line1: string
  address_line2?: string
  city: string
  state: string
  postal_code: string
  country?: string
}

export interface CheckoutPayload {
  shipping_method_id: number
  payment_method: string
  coupon_code?: string
  email?: string
  note?: string
  address_id?: number
  address?: CheckoutAddress
}

export interface ApiOrder {
  order_number: string
  status: string
  payment_status: string
  subtotal: number
  discount_amount: number
  tax_amount: number
  shipping_amount: number
  total: number
  currency: string
  shipping_address: Record<string, string> | null
  billing_address: Record<string, string> | null
  email: string | null
  phone: string | null
  customer_name: string | null
  note: string | null
  coupon_code: string | null
  placed_at: string | null
  items: {
    id: number
    product_id: number
    product_variant_id: number
    product_name: string
    variant_label: string | null
    sku: string
    image_path: string | null
    unit_price: number
    quantity: number
    line_total: number
  }[]
  payment: {
    id: number
    method: string
    status: string
    transaction_id: string | null
    amount: number
    paid_at: string | null
  } | null
  shipment: {
    tracking_number: string | null
    carrier: string | null
    status: string
    shipped_at: string | null
    delivered_at: string | null
    address_snapshot: string | null
  } | null
}

export const checkoutApi = {
  begin(payload: CheckoutPayload) {
    return apiClient.post<{ data: ApiOrder; reservation_expires_at: string }>('/checkout', payload)
  },

  confirm(orderNumber: string, transactionId?: string) {
    const body: Record<string, string> = {}
    if (transactionId) body.transaction_id = transactionId
    return apiClient.post<{ data: ApiOrder }>(`/checkout/${orderNumber}/confirm`, body)
  },

  cancel(orderNumber: string) {
    return apiClient.post<{ data: ApiOrder }>(`/checkout/${orderNumber}/cancel`)
  }
}
