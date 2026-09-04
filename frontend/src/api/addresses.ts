import apiClient from './client'

export interface ApiAddress {
  id: number
  label: string | null
  full_name: string
  phone: string | null
  address_line1: string
  address_line2: string | null
  city: string
  state: string
  postal_code: string
  country: string
  is_default: boolean
}

export interface AddressPayload {
  label?: string
  full_name: string
  phone?: string
  address_line1: string
  address_line2?: string
  city: string
  state: string
  postal_code: string
  country?: string
  is_default?: boolean
}

export const addressesApi = {
  list() {
    return apiClient.get<{ data: ApiAddress[] }>('/addresses')
  },

  create(payload: AddressPayload) {
    return apiClient.post<{ data: ApiAddress }>('/addresses', payload)
  },

  update(id: number, payload: AddressPayload) {
    return apiClient.put<{ data: ApiAddress }>(`/addresses/${id}`, payload)
  },

  remove(id: number) {
    return apiClient.delete<{ data: { message: string } }>(`/addresses/${id}`)
  },

  setDefault(id: number) {
    return apiClient.post<{ data: ApiAddress }>(`/addresses/${id}/default`)
  }
}
