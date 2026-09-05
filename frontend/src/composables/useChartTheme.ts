import { computed, watch, type ComputedRef } from 'vue'
import { Chart as ChartJS } from 'chart.js'
import { useThemeStore } from '@/stores/theme'

export interface ChartPalette {
  text: string
  mutedText: string
  grid: string
  border: string
}

export function useChartTheme(): { isDark: ComputedRef<boolean>; palette: ComputedRef<ChartPalette> } {
  const theme = useThemeStore()

  const isDark = computed(() => theme.resolvedTheme === 'dark')

  const palette = computed<ChartPalette>(() => {
    const dark = isDark.value
    return {
      text: dark ? '#9CA3AF' : '#6B7280',
      mutedText: dark ? '#6B7280' : '#9CA3AF',
      grid: dark ? '#374151' : '#F3F4F6',
      border: dark ? '#1F2937' : '#FFFFFF'
    }
  })

  watch(
    palette,
    () => {
      ChartJS.defaults.color = palette.value.text
    },
    { immediate: true }
  )

  return { isDark, palette }
}