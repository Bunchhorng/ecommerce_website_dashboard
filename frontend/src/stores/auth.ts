import { defineStore } from 'pinia'
import apiClient from '@/api/client'

const TOKEN_KEY = 'access_token'
const USER_KEY = 'shopverse_user'

export interface AuthUser {
  id: number
  name: string
  email: string
  phone?: string | null
  role: 'customer' | 'admin'
  avatar?: string | null
  newsletter?: boolean
}

function loadUser(): AuthUser | null {
  try {
    const raw = localStorage.getItem(USER_KEY)
    if (!raw) return null
    const parsed = JSON.parse(raw) as Partial<AuthUser>
    if (!parsed?.id || !parsed?.email) return null
    return parsed as AuthUser
  } catch {
    return null
  }
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: loadUser() as AuthUser | null,
    loading: false
  }),

  getters: {
    isAuthenticated: (state): boolean => Boolean(state.user),
    isAdmin: (state): boolean => state.user?.role === 'admin',
    token: (): string | null => localStorage.getItem(TOKEN_KEY)
  },

  actions: {
    async login(email: string, password: string) {
      this.loading = true
      try {
        const { data } = await apiClient.post('/auth/login', { email, password })
        const responseData = data.data ?? data
        localStorage.setItem(TOKEN_KEY, responseData.token ?? responseData.access_token)
        this.user = responseData.user as AuthUser
        localStorage.setItem(USER_KEY, JSON.stringify(this.user))
        return this.user
      } finally {
        this.loading = false
      }
    },

    async register(payload: { name: string; email: string; password: string; password_confirmation?: string; newsletter?: boolean }) {
      this.loading = true
      try {
        const { data } = await apiClient.post('/auth/register', payload)
        const responseData = data.data ?? data
        localStorage.setItem(TOKEN_KEY, responseData.token ?? responseData.access_token)
        this.user = responseData.user as AuthUser
        localStorage.setItem(USER_KEY, JSON.stringify(this.user))
        return this.user
      } finally {
        this.loading = false
      }
    },

    async logout() {
      try {
        await apiClient.post('/auth/logout')
      } catch {
        /* ignore network errors on logout */
      }
      this.user = null
      localStorage.removeItem(TOKEN_KEY)
      localStorage.removeItem(USER_KEY)
    }
  }
})
