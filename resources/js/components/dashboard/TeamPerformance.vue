<template>
  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800 flex items-center justify-between">
      <h3 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Top Performing Agents</h3>
      <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Pipeline Success Rate</span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="border-b border-gray-200 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/20">
            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Agent Name</th>
            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</th>
            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">RFQs Assigned</th>
            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Performance (Completed)</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(agent, index) in agents"
            :key="index"
            class="border-b border-gray-100 hover:bg-gray-50/80 dark:border-gray-800 dark:hover:bg-gray-800/30 transition-colors"
          >
            <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">{{ agent.name }}</td>
            <td class="px-6 py-4 text-sm font-medium text-gray-600 dark:text-gray-300">{{ agent.company }}</td>
            <td class="px-6 py-4">
              <span class="inline-flex rounded-lg bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                {{ agent.rfq_count }} Assigned
              </span>
            </td>
            <td class="px-6 py-4">
              <div 
                class="flex items-center gap-3 cursor-help group relative"
                :title="`${agent.completed_count} completed out of ${agent.rfq_count} assigned`"
              >
                <div class="h-2.5 w-36 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                  <div
                    :style="{ width: agent.percentage + '%' }"
                    class="h-full rounded-full bg-gradient-to-r from-blue-500 via-indigo-500 to-emerald-500 transition-all duration-500"
                  ></div>
                </div>
                <span class="text-xs font-black text-gray-900 dark:text-white">{{ agent.percentage }}%</span>
                
                <!-- Custom Tooltip -->
                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block w-max rounded-lg bg-gray-900 px-3 py-1.5 text-xs text-white shadow-xl z-10 border border-gray-700">
                  <div class="font-bold text-gray-200 mb-0.5">Performance Breakdown:</div>
                  <div class="text-gray-300">
                    <span class="text-emerald-400 font-bold">{{ agent.completed_count }}</span> completed / 
                    <span class="text-blue-400 font-bold">{{ agent.rfq_count }}</span> assigned
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
      <p class="text-sm font-medium text-gray-500 dark:text-gray-400">No agents data available</p>
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
