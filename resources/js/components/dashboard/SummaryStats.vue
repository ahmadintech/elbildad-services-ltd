<template>
  <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
    <h3 class="mb-6 text-xl font-bold tracking-tight text-gray-900 dark:text-white">{{ title }}</h3>
    
    <div class="space-y-4">
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="pb-4 last:pb-0"
      >
        <!-- Label and Value -->
        <div class="mb-2 flex items-center justify-between">
          <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ stat.label }}</span>
          <div class="flex items-center gap-2.5">
            <span class="text-xl font-black text-gray-900 dark:text-white">{{ stat.value }}</span>
            <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
              {{ stat.percentage }}%
            </span>
          </div>
        </div>

        <!-- Progress Bar -->
        <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
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
