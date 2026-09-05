import { defineStore } from 'pinia'

export type ThemeMode = 'light' | 'dark'

const STORAGE_KEY = 'ekhmer_theme'

function loadInitial(): ThemeMode {
  try {
    const saved = localStorage.getItem(STORAGE_KEY)
    if (saved === 'light' || saved === 'dark') return saved
  } catch {
    /* storage unavailable */
  }
  return 'light'
}

export const useThemeStore = defineStore('theme', {
  state: () => ({
    currentTheme: loadInitial() as ThemeMode
  }),

  getters: {
    resolvedTheme(state): ThemeMode {
      return state.currentTheme
    }
  },

  actions: {
    applyTheme(mode?: ThemeMode) {
      const resolved = mode ?? this.currentTheme
      const root = document.documentElement
      root.classList.toggle('dark', resolved === 'dark')
      root.style.colorScheme = resolved
    },
    setTheme(theme: ThemeMode) {
      this.currentTheme = theme
      try {
        localStorage.setItem(STORAGE_KEY, theme)
      } catch {
        /* storage unavailable */
      }
      this.applyTheme(theme)
    },
    toggleTheme() {
      this.setTheme(this.currentTheme === 'dark' ? 'light' : 'dark')
    },
    initialize() {
      this.applyTheme(this.currentTheme)
    }
  }
})
