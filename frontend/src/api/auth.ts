import apiClient from './client'

export interface ResetPasswordPayload {
  email: string
  token: string
  password: string
  password_confirmation: string
}

export const authApi = {
  forgotPassword(email: string) {
    return apiClient.post<{ data: { message: string } }>('/auth/forgot-password', { email })
  },

  resetPassword(payload: ResetPasswordPayload) {
    return apiClient.post<{ data: { message: string } }>('/auth/reset-password', payload)
  }
}