import { createI18n } from 'vue-i18n';
import en from '../locales/en.json';
import km from '../locales/km.json';

const i18n = createI18n({
  legacy: false, // Use Vue 3 Composition API mode
  locale: localStorage.getItem('user_locale') || 'km', // Default to Khmer
  fallbackLocale: 'en',
  messages: { en, km }
});

export default i18n;