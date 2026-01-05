<script setup>
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Filler,
  Legend
} from 'chart.js'
import { Line } from 'vue-chartjs'
import { computed } from 'vue'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Filler,
  Legend
)

const props = defineProps({
    labels: Array,
    data: Array
})

const chartData = computed(() => {
    return {
        labels: props.labels,
        datasets: [
            {
                label: 'Attendance',
                backgroundColor: (context) => {
                    const ctx = context.chart.ctx;
                    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                    gradient.addColorStop(0, 'rgba(220, 38, 38, 0.5)'); // Red-600
                    gradient.addColorStop(1, 'rgba(220, 38, 38, 0.05)'); // Transparent
                    return gradient;
                },
                borderColor: '#dc2626', // Red-600
                pointBackgroundColor: '#fff',
                pointBorderColor: '#dc2626',
                pointHoverBackgroundColor: '#dc2626',
                pointHoverBorderColor: '#fff',
                data: props.data,
                fill: true,
                tension: 0.4 // Smooth curve
            }
        ]
    }
})

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            backgroundColor: '#18181b',
            titleColor: '#fff',
            bodyColor: '#fff',
            borderColor: '#dc2626',
            borderWidth: 1,
            displayColors: false,
            padding: 10,
            cornerRadius: 8,
            titleFont: {
                family: 'inherit',
                size: 14
            },
            bodyFont: {
                family: 'inherit',
                size: 14,
                weight: 'bold'
            }
        }
    },
    scales: {
        x: {
            grid: {
                display: false,
                drawBorder: false
            },
            ticks: {
                color: '#9ca3af', // Gray-400
                font: {
                    family: 'inherit'
                }
            }
        },
        y: {
            grid: {
                color: 'rgba(255, 255, 255, 0.05)',
                drawBorder: false
            },
            ticks: {
                color: '#9ca3af',
                stepSize: 1,
                font: {
                    family: 'inherit'
                }
            },
            beginAtZero: true
        }
    }
}
</script>

<template>
  <div class="w-full h-full">
    <Line :data="chartData" :options="chartOptions" />
  </div>
</template>
