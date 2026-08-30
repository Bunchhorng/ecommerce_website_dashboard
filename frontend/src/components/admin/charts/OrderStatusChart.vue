<script setup lang="ts">
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip, Legend } from 'chart.js'
import type { ChartData, ChartOptions, TooltipItem } from 'chart.js'
import { Bar } from 'vue-chartjs'
import { ORDER_STATUS_DISTRIBUTION } from '@/data/mock'

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend)
ChartJS.defaults.font.family = 'Inter, sans-serif'
ChartJS.defaults.color = '#6B7280'

const statusColors: Record<string, string> = {
  Pending: '#FBBF24',
  Confirmed: '#60A5FA',
  Processing: '#2563EB',
  Shipped: '#8B5CF6',
  Delivered: '#10B981',
  Cancelled: '#EF4444'
}

const chartData: ChartData<'bar'> = {
  labels: ORDER_STATUS_DISTRIBUTION.map((d) => d.status),
  datasets: [
    {
      label: 'Orders',
      data: ORDER_STATUS_DISTRIBUTION.map((d) => d.count),
      backgroundColor: ORDER_STATUS_DISTRIBUTION.map((d) => statusColors[d.status]),
      borderRadius: 6,
      barPercentage: 0.6,
      maxBarThickness: 40
    }
  ]
}

const chartOptions: ChartOptions<'bar'> = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      callbacks: {
        label: (context: TooltipItem<'bar'>) => `${context.parsed.y} orders`
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