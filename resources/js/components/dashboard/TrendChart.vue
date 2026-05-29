<template>
  <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
    <h3 class="mb-6 text-lg font-semibold text-gray-900 dark:text-white/90">{{ title }}</h3>
    
    <div class="overflow-x-auto custom-scrollbar">
      <VueApexCharts type="area" height="320" :options="chartOptions" :series="series" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import VueApexCharts from 'vue3-apexcharts'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  labels: {
    type: Array,
    required: true
  },
  data: {
    type: Array,
    required: true
  },
  color: {
    type: String,
    default: '#3B82F6'
  }
})

const series = computed(() => [
  {
    name: props.title,
    data: props.data,
  }
])

const chartOptions = ref({
  legend: {
    show: false,
    position: 'top',
    horizontalAlign: 'left',
  },
  colors: ['#3B82F6'],
  chart: {
    fontFamily: 'Outfit, sans-serif',
    type: 'area',
    toolbar: {
      show: false,
    },
  },
  fill: {
    gradient: {
      enabled: true,
      opacityFrom: 0.55,
      opacityTo: 0,
    },
  },
  stroke: {
    curve: 'smooth',
    width: 2,
  },
  markers: {
    size: 5,
  },
  labels: {
    show: false,
    position: 'top',
  },
  grid: {
    xaxis: {
      lines: {
        show: false,
      },
    },
    yaxis: {
      lines: {
        show: true,
      },
    },
  },
  dataLabels: {
    enabled: false,
  },
  tooltip: {
    x: {
      format: 'mmm',
    },
  },
  xaxis: {
    type: 'category',
    categories: props.labels,
    axisBorder: {
      show: false,
    },
    axisTicks: {
      show: false,
    },
  },
  yaxis: {
    title: {
      style: {
        fontSize: '0px',
      },
    },
  },
})

watch(() => props.labels, () => {
  chartOptions.value.xaxis.categories = props.labels
}, { deep: true })

watch(() => props.color, (newColor) => {
  chartOptions.value.colors = [newColor]
})
</script>
