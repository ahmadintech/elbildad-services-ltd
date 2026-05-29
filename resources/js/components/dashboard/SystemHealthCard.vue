<template>
  <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="mb-6 flex items-center justify-between">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">System Health</h3>
      <span :class="[
        'inline-flex rounded-full px-3 py-1 text-xs font-semibold',
        systemHealth.score >= 75 ? 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-400' :
        systemHealth.score >= 50 ? 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400' :
        'bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-400'
      ]">
        {{ systemHealth.status }}
      </span>
    </div>

    <!-- Main Health Score -->
    <div class="mb-6">
      <div class="mb-3 flex items-end justify-between">
        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Overall Score</span>
        <span class="text-2xl font-bold text-gray-900 dark:text-white/90">{{ systemHealth.score }}%</span>
      </div>
      <div class="h-3 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
        <div
          :style="{ width: systemHealth.score + '%' }"
          :class="[
            'h-full rounded-full transition-all duration-300',
            systemHealth.score >= 75 ? 'bg-green-500' :
            systemHealth.score >= 50 ? 'bg-blue-500' :
            'bg-orange-500'
          ]"
        ></div>
      </div>
    </div>

    <!-- Sub-metrics -->
    <div class="space-y-3 border-t border-gray-200 pt-4 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <svg class="h-4 w-4 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-sm text-gray-600 dark:text-gray-400">Completion Rate</span>
        </div>
        <span class="font-semibold text-gray-900 dark:text-white/90">{{ systemHealth.completionRate }}%</span>
      </div>

      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <svg class="h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-sm text-gray-600 dark:text-gray-400">Payment Rate</span>
        </div>
        <span class="font-semibold text-gray-900 dark:text-white/90">{{ systemHealth.paymentRate }}%</span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  systemHealth: {
    type: Object,
    required: true
  }
})
</script>
