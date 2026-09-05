import { defineStore } from 'pinia'

export type ThemeMode = 'light' | 'dark' | 'system'

const STORAGE_KEY = 'ekhmer_theme'

function loadInitial(): ThemeMode {
  try {
    const saved = localStorage.getItem(STORAGE_KEY)
    if (saved === 'light' || saved === 'dark' || saved === 'system') return saved
  } catch {
    /* storage unavailable */
  }
  return 'system'
}

function isSystemDark(): boolean {
  return typeof window !== 'undefined' && window.matchMedia?.('(prefers-color-scheme: dark)').matches
}

function resolveTheme(mode: ThemeMode): 'light' | 'dark' {
  if (mode === 'system') return isSystemDark() ? 'dark' : 'light'
  return mode
}

export const useThemeStore = defineStore('theme', {
  state: () => ({
    currentTheme: loadInitial() as ThemeMode
  }),

  getters: {
    resolvedTheme(state): 'light' | 'dark' {
      return resolveTheme(state.currentTheme)
    }
  },

  actions: {
    applyTheme(mode?: ThemeMode) {
      const theme = mode ?? this.currentTheme
      const resolved = resolveTheme(theme)
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
      const next: ThemeMode = this.resolvedTheme === 'dark' ? 'light' : 'dark'
      this.setTheme(next)
    },
    initialize() {
      this.applyTheme(this.currentTheme)

      if (typeof window !== 'undefined' && window.matchMedia) {
        const mql = window.matchMedia('(prefers-color-scheme: dark)')
        const onChange = () => {
          if (this.currentTheme === 'system') this.applyTheme('system')
        }
        if (mql.addEventListener) mql.addEventListener('change', onChange)
        else mql.addListener(onChange)
      }
    }
  }
})
