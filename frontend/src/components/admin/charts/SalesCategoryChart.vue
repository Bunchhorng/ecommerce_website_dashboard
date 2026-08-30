<script setup lang="ts">
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'
import type { ChartData, ChartOptions, TooltipItem } from 'chart.js'
import { Doughnut } from 'vue-chartjs'
import { SALES_BY_CATEGORY } from '@/data/mock'
import { formatCompactNumber } from '@/utils/format'

ChartJS.register(ArcElement, Tooltip, Legend)
ChartJS.defaults.font.family = 'Inter, sans-serif'
ChartJS.defaults.color = '#6B7280'

const palette = ['#2563EB', '#10B981', '#FBBF24', '#8B5CF6', '#EC4899', '#64748B']

const chartData: ChartData<'doughnut'> = {
  labels: SALES_BY_CATEGORY.map((c) => c.category),
  datasets: [
    {
      data: SALES_BY_CATEGORY.map((c) => c.sales),
      backgroundColor: palette,
      borderColor: '#FFFFFF',
      borderWidth: 2
    }
  ]
}

const chartOptions: ChartOptions<'doughnut'> = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '62%',
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        boxWidth: 10,
        usePointStyle: true,
        pointStyle: 'circle',
        padding: 12,
        color: '#6B7280'
      }
    },
    tooltip: {
      callbacks: {
        label: (context: TooltipItem<'doughnut'>) => `${context.label || ''}: ${formatCompactNumber(context.parsed)}`
      }
    }
  }
}
</script>

<template>
  <div class="h-72" style="position: relative;">
    <Doughnut :data="chartData" :options="chartOptions" />
  </div>
</template>