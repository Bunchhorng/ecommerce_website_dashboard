import apiClient from './client'

export interface CouponValidation {
  code: string
  type: 'percentage' | 'fixed'
  value: number
  min_order_amount: number | null
  max_discount_amount: number | null
  valid: boolean
  discount_amount: number
  message: string
}

export const couponsApi = {
  validate(code: string, subtotal: number) {
    return apiClient.post<{ data: CouponValidation; subtotal: number }>('/coupons/validate', {
      code,
      subtotal
    })
  }
}
