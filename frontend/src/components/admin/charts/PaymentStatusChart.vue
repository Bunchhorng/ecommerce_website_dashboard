<script setup lang="ts">
import { computed } from 'vue'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'
import type { ChartData, ChartOptions, TooltipItem } from 'chart.js'
import { Doughnut } from 'vue-chartjs'
import { useI18n } from 'vue-i18n'
import { useChartTheme } from '@/composables/useChartTheme'

ChartJS.register(ArcElement, Tooltip, Legend)
ChartJS.defaults.font.family = 'Inter, sans-serif'

const props = defineProps<{
  data: { status: string; count: number }[]
}>()

const { palette } = useChartTheme()
const { t } = useI18n()

const statusColors: Record<string, string> = {
  pending: '#FBBF24',
  completed: '#10B981',
  failed: '#EF4444',
  refunded: '#8B5CF6'
}

function capitalize(s: string): string {
  return s.charAt(0).toUpperCase() + s.slice(1)
}

const total = computed(() => props.data.reduce((sum, d) => sum + d.count, 0))

const chartData = computed<ChartData<'doughnut'>>(() => ({
  labels: props.data.map((d) => capitalize(d.status)),
  datasets: [
    {
      data: props.data.map((d) => d.count),
      backgroundColor: props.data.map((d) => statusColors[d.status] ?? '#6B728B'),
      borderWidth: 2,
      borderColor: palette.value.cardBg ?? '#ffffff'
    }
  ]
}))

const chartOptions = computed<ChartOptions<'doughnut'>>(() => ({
  responsive: true,
  maintainAspectRatio: false,
  cutout: '65%',
  plugins: {
    legend: { position: 'bottom', labels: { color: palette.value.text, usePointStyle: true, boxWidth: 8 } },
    tooltip: {
      callbacks: {
        label: (context: TooltipItem<'doughnut'>) => `${context.label}: ${context.parsed}`
      }
    }
  }
}))
</script>

<template>
  <div class="h-72" style="position: relative;">
    <Doughnut v-if="total > 0" :data="chartData" :options="chartOptions" />
    <div v-else class="flex h-full items-center justify-center text-sm text-gray-400 dark:text-gray-500">
      {{ t('admin.dashboard.no_data') }}
    </div>
  </div>
</template>