<script setup lang="ts">
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Legend, Filler } from 'chart.js'
import type { ChartData, ChartOptions, TooltipItem } from 'chart.js'
import { Line } from 'vue-chartjs'
import { REVENUE_TREND } from '@/data/mock'
import { formatPrice } from '@/utils/format'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Legend, Filler)
ChartJS.defaults.font.family = 'Inter, sans-serif'
ChartJS.defaults.color = '#6B7280'

const chartData: ChartData<'line'> = {
  labels: REVENUE_TREND.map((p) => p.label),
  datasets: [
    {
      label: 'Revenue',
      data: REVENUE_TREND.map((p) => p.revenue),
      borderColor: '#2563EB',
      backgroundColor: 'rgba(37, 99, 235, 0.08)',
      pointBackgroundColor: '#2563EB',
      tension: 0.35,
      fill: true,
      borderWidth: 2,
      pointRadius: 3,
      pointHoverRadius: 5
    }
  ]
}

const chartOptions: ChartOptions<'line'> = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: { mode: 'index', intersect: false },
  plugins: {
    legend: { display: false },
    tooltip: {
      callbacks: {
        label: (context: TooltipItem<'line'>) => formatPrice(context.parsed.y ?? 0)
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
      ticks: {
        color: '#6B7280',
        callback: (value: number | string) => `$${Number(value) / 1000}k`
      }
    }
  }
}
</script>

<template>
  <div class="h-72" style="position: relative;">
    <Line :data="chartData" :options="chartOptions" />
  </div>
</template>