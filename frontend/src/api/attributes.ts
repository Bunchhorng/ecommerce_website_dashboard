import apiClient from './client'

export interface ApiAttribute {
  id: number
  name: string
  slug: string
  type: string
  is_filterable: boolean
  values: { id: number; value: string; swatch_color: string | null }[]
}

export const attributesApi = {
  getAll() {
    return apiClient.get<{ data: ApiAttribute[] }>('/attributes')
  }
}
