<template>
  <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="mb-6 flex items-center justify-between">
      <h3 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">System Health</h3>
      <span :class="[
        'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wider',
        systemHealth.score >= 75 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' :
        systemHealth.score >= 50 ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300' :
        'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300'
      ]">
        <span class="h-1.5 w-1.5 rounded-full bg-current animate-pulse"></span>
        {{ systemHealth.status }}
      </span>
    </div>

    <!-- Main Health Score -->
    <div class="mb-6">
      <div class="mb-3 flex items-end justify-between">
        <span class="text-sm font-bold text-gray-700 dark:text-gray-300">Overall Pipeline Score</span>
        <span class="text-3xl font-black text-gray-900 dark:text-white">{{ systemHealth.score }}%</span>
      </div>
      <div class="h-3 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
        <div
          :style="{ width: systemHealth.score + '%' }"
          :class="[
            'h-full rounded-full transition-all duration-300',
            systemHealth.score >= 75 ? 'bg-emerald-500' :
            systemHealth.score >= 50 ? 'bg-blue-500' :
            'bg-amber-500'
          ]"
        ></div>
      </div>
    </div>

    <!-- Sub-metrics -->
    <div class="space-y-3.5 border-t border-gray-100 pt-5 dark:border-gray-800">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-base"></i>
          <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Completion Rate</span>
        </div>
        <span class="text-base font-black text-gray-900 dark:text-white">{{ systemHealth.completionRate }}%</span>
      </div>

      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <i class="fa-solid fa-credit-card text-blue-600 dark:text-blue-400 text-base"></i>
          <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Payment Collection Rate</span>
        </div>
        <span class="text-base font-black text-gray-900 dark:text-white">{{ systemHealth.paymentRate }}%</span>
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
