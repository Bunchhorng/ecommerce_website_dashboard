import { defineStore } from 'pinia'
import i18n from '@/plugins/i18n'

export type AppLocale = 'km' | 'en'

const STORAGE_KEY = 'app_locale'
const FALLBACK: AppLocale = 'km'

function loadInitial(): AppLocale {
  try {
    const saved = localStorage.getItem(STORAGE_KEY)
    if (saved === 'km' || saved === 'en') return saved
  } catch {
    /* storage unavailable */
  }
  return FALLBACK
}

export const useLocaleStore = defineStore('locale', {
  state: () => ({
    currentLocale: loadInitial() as AppLocale
  }),

  actions: {
    applyLocale() {
      i18n.global.locale.value = this.currentLocale
      if (typeof document !== 'undefined') {
        document.documentElement.lang = this.currentLocale
        document.documentElement.classList.toggle('font-khmer-mode', this.currentLocale === 'km')
      }
    },

    setLocale(locale: AppLocale) {
      if (locale !== 'km' && locale !== 'en') return
      this.currentLocale = locale
      try {
        localStorage.setItem(STORAGE_KEY, locale)
      } catch {
        /* storage unavailable */
      }
      this.applyLocale()
    },

    toggle() {
      this.setLocale(this.currentLocale === 'km' ? 'en' : 'km')
    },

    initialize() {
      this.applyLocale()
    }
  }
})
