<script setup lang="ts">
import { computed } from 'vue'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip, Legend } from 'chart.js'
import type { ChartData, ChartOptions, TooltipItem } from 'chart.js'
import { Bar } from 'vue-chartjs'
import { useI18n } from 'vue-i18n'

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend)
ChartJS.defaults.font.family = 'Inter, sans-serif'
ChartJS.defaults.color = '#6B7280'

const props = defineProps<{
  data: { status: string; count: number }[]
}>()

const { t } = useI18n()

const statusColors: Record<string, string> = {
  pending: '#FBBF24',
  confirmed: '#60A5FA',
  processing: '#2563EB',
  shipped: '#8B5CF6',
  delivered: '#10B981',
  cancelled: '#EF4444'
}

function capitalize(s: string): string {
  return s.charAt(0).toUpperCase() + s.slice(1)
}

const chartData = computed<ChartData<'bar'>>(() => ({
  labels: props.data.map((d) => capitalize(d.status)),
  datasets: [
    {
      label: t('admin.chart.orders'),
      data: props.data.map((d) => d.count),
      backgroundColor: props.data.map((d) => statusColors[d.status] ?? '#6B728B'),
      borderRadius: 6,
      barPercentage: 0.6,
      maxBarThickness: 40
    }
  ]
}))

const chartOptions: ChartOptions<'bar'> = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      callbacks: {
        label: (context: TooltipItem<'bar'>) => `${context.parsed.y} ${t('admin.chart.orders').toLowerCase()}`
      }
    }
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { color: '#6B7280' }
    },
    y: {
      beginAtZero: true,
      border: { display: false },
      grid: { color: '#F3F4F6' },
      ticks: { color: '#6B7280' }
    }
  }
}
</script>

<template>
  <div class="h-72" style="position: relative;">
    <Bar :data="chartData" :options="chartOptions" />
  </div>
</template>
