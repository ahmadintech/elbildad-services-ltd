<template>
  <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
    <h3 class="mb-6 text-lg font-semibold text-gray-900 dark:text-white/90">{{ title }}</h3>
    
    <div class="space-y-4">
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="pb-4 last:pb-0"
      >
        <!-- Label and Value -->
        <div class="mb-2 flex items-center justify-between">
          <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ stat.label }}</span>
          <div class="flex items-center gap-2">
            <span class="text-lg font-bold text-gray-900 dark:text-white/90">{{ stat.value }}</span>
            <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-semibold text-green-600 dark:bg-green-500/15 dark:text-green-400">
              {{ stat.percentage }}%
            </span>
          </div>
        </div>

        <!-- Progress Bar -->
        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
          <div
            :style="{ width: stat.percentage + '%' }"
            :class="getProgressColor(stat.percentage)"
            class="h-full rounded-full transition-all duration-300"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: 'Summary'
  },
  stats: {
    type: Array,
    required: true
  }
})

const getProgressColor = (percentage) => {
  if (percentage >= 75) return 'bg-success-500'
  if (percentage >= 50) return 'bg-blue-500'
  if (percentage >= 25) return 'bg-orange-500'
  return 'bg-danger-500'
}
</script>
