import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import i18n from '@/plugins/i18n'
import { useThemeStore } from './stores/theme'
import { useLocaleStore } from './stores/locale'
import './style.css'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.use(i18n)

useThemeStore(pinia).initialize()
useLocaleStore(pinia).initialize()

app.mount('#app')
