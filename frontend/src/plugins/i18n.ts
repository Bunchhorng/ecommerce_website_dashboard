import { createI18n } from 'vue-i18n'
import en from '@/locales/en.json'
import km from '@/locales/km.json'

export type AppLocale = 'km' | 'en'

function loadInitialLocale(): AppLocale {
  try {
    const saved = localStorage.getItem('app_locale')
    if (saved === 'km' || saved === 'en') return saved
  } catch {
    /* storage unavailable */
  }
  return 'km'
}

const i18n = createI18n({
  legacy: false,
  locale: loadInitialLocale(),
  fallbackLocale: 'en',
  messages: { en, km }
})

export default i18n
