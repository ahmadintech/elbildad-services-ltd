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
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-400">RFQs Assigned</th>
            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-400">Performance (Completed)</th>
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
              <div 
                class="flex items-center gap-2 cursor-help group relative"
                :title="`${agent.completed_count} completed out of ${agent.rfq_count} assigned`"
              >
                <div class="h-2 w-32 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                  <div
                    :style="{ width: agent.percentage + '%' }"
                    class="h-full rounded-full bg-gradient-to-r from-blue-500 to-green-500 transition-all duration-500"
                  ></div>
                </div>
                <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ agent.percentage }}%</span>
                
                <!-- Custom Tooltip -->
                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block w-max rounded bg-gray-900 px-3 py-1.5 text-xs text-white shadow-lg z-10">
                  <div class="font-semibold text-gray-200 mb-0.5">Details:</div>
                  <div class="text-gray-300">
                    <span class="text-green-400">{{ agent.completed_count }}</span> completed / 
                    <span class="text-blue-400">{{ agent.rfq_count }}</span> assigned
                  </div>
                  <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                </div>
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
</script>
