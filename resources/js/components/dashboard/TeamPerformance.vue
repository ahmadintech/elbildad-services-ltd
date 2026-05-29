<template>
  <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Top Performing Agents</h3>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="border-b border-gray-200 dark:border-gray-800">
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-400">Agent Name</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-400">Company</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-400">RFQs Handled</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-400">Performance</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(agent, index) in agents"
            :key="index"
            class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800/30 transition-colors"
          >
            <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white/90">{{ agent.name }}</td>
            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ agent.company }}</td>
            <td class="px-6 py-4">
              <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-sm font-semibold text-blue-700 dark:bg-blue-500/15 dark:text-blue-400">
                {{ agent.rfq_count }}
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-2">
                <div class="h-2 w-32 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                  <div
                    :style="{ width: getPerformancePercentage(index) + '%' }"
                    class="h-full rounded-full bg-gradient-to-r from-blue-500 to-green-500"
                  ></div>
                </div>
                <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ getPerformancePercentage(index) }}%</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="agents.length === 0" class="px-6 py-12 text-center">
      <p class="text-gray-500 dark:text-gray-400">No agents data available</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  agents: {
    type: Array,
    required: true
  }
})

const getPerformancePercentage = (index) => {
  // Scores based on ranking: 1st gets 100%, 2nd gets 80%, etc.
  const scores = [100, 80, 60, 40, 20]
  return scores[index] || 10
}
</script>
