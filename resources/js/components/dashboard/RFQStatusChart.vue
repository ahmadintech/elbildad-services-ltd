<template>
  <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
    <h3 class="mb-6 text-lg font-semibold text-gray-900 dark:text-white/90">{{ title }}</h3>
    
    <div class="flex items-center justify-center">
      <VueApexCharts type="donut" height="300" :options="chartOptions" :series="series" />
    </div>

    <!-- Legend -->
    <div class="mt-6 space-y-2">
      <div
        v-for="(label, index) in labels"
        :key="index"
        class="flex items-center justify-between"
      >
        <div class="flex items-center gap-2">
          <div
            :style="{ backgroundColor: colors[index] }"
            class="h-3 w-3 rounded-full"
          ></div>
          <span class="text-sm text-gray-600 dark:text-gray-400">{{ label }}</span>
        </div>
        <span class="font-semibold text-gray-900 dark:text-white/90">{{ data[index] }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
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
  colors: {
    type: Array,
    required: true
  }
})

const series = computed(() => props.data)

const chartOptions = ref({
  labels: props.labels,
  colors: props.colors,
  chart: {
    fontFamily: 'Outfit, sans-serif',
    type: 'donut',
    toolbar: {
      show: false,
    },
  },
  stroke: {
    colors: ['#ffffff'],
    width: 2,
  },
  plotOptions: {
    pie: {
      donut: {
        size: '65%',
        background: 'transparent',
      },
    },
  },
  legend: {
    show: false,
  },
  tooltip: {
    enabled: true,
  },
  dataLabels: {
    enabled: false,
  },
})
</script>
