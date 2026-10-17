<template>
  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800 flex items-center justify-between">
      <h3 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Recent RFQs</h3>
      <Link
        href="/admin/rfqs"
        class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-bold text-gray-700 hover:bg-gray-50 hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 shadow-xs transition"
      >
        <span>View All RFQs</span>
        <i class="fa-solid fa-arrow-right text-[10px]"></i>
      </Link>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="border-b border-gray-200 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/20">
            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Product</th>
            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Customer</th>
            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Agent</th>
            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Time Assigned</th>
            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Price</th>
            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Date</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="rfq in rfqs"
            :key="rfq.id"
            class="border-b border-gray-100 hover:bg-gray-50/80 dark:border-gray-800 dark:hover:bg-gray-800/30 transition-colors"
          >
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white font-bold">{{ rfq.product_name }}</td>
            <td class="px-6 py-4 text-sm font-medium text-gray-600 dark:text-gray-300">{{ rfq.customer }}</td>
            <td class="px-6 py-4">
              <span
                :class="getStatusClass(rfq.status)"
                class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold uppercase tracking-wider"
              >
                {{ rfq.status.replace('_', ' ') }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ rfq.assigned_agent.name }}</td>
            <td class="px-6 py-4 text-xs font-medium text-gray-500 dark:text-gray-400">{{ rfq.assigned_at }}</td>
            <td class="px-6 py-4 text-sm font-black text-gray-900 dark:text-white">{{ rfq.target_price }}</td>
            <td class="px-6 py-4 text-xs font-medium text-gray-500 dark:text-gray-400">{{ rfq.created_at_formatted }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="rfqs.length === 0" class="px-6 py-12 text-center">
      <p class="text-sm font-medium text-gray-500 dark:text-gray-400">No RFQs found</p>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
defineProps({
  rfqs: {
    type: Array,
    required: true
  }
})

const getStatusClass = (status) => {
  const statusClasses = {
    pending: 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-400',
    assigned: 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
    sourcing: 'bg-purple-50 text-purple-700 dark:bg-purple-500/15 dark:text-purple-400',
    purchased: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-400',
    shipped: 'bg-cyan-50 text-cyan-700 dark:bg-cyan-500/15 dark:text-cyan-400',
    completed: 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-400',
    queued: 'bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-400',
    not_found: 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400',
  }
  return statusClasses[status] || 'bg-gray-50 text-gray-700 dark:bg-gray-500/15 dark:text-gray-400'
}
</script>
