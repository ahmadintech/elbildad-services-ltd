<template>
  <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Agent Activities</h3>
    </div>

    <div class="space-y-4">
      <div
        v-for="(agent, index) in activities"
        :key="index"
        class="flex items-center justify-between rounded-xl border border-gray-100 p-4 transition-colors hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800/30"
      >
        <div class="flex items-center gap-4">
          <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400 font-semibold">
            {{ agent.name.charAt(0) }}
          </div>
          <div>
            <h4 class="text-sm font-medium text-gray-900 dark:text-white/90">{{ agent.name }}</h4>
            <div class="mt-1 flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
              <span class="flex items-center gap-1">
                Last Login: {{ agent.last_login_at }}
              </span>
            </div>
          </div>
        </div>

        <div class="flex flex-col items-end gap-1">
          <span class="text-sm font-semibold text-gray-900 dark:text-white/90">
            Avg Response: <span class="text-brand-500">{{ agent.avg_response_time }}</span>
          </span>
          <span class="text-xs text-gray-500 dark:text-gray-400">
            Completed RFQs: {{ agent.completed_rfqs }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  activities: {
    type: Array,
    required: true
  }
})
</script>
