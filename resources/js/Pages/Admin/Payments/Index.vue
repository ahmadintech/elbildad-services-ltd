<template>
  <AdminLayout>
    <Head title="Transactions" />
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Transactions & Payments</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">Track all commitment fees and general platform transactions.</p>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 md:gap-6">
        <div v-for="(stat, index) in stats" :key="index" class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] shadow-sm">
          <div class="flex items-center gap-4">
            <div :class="[
              'flex h-12 w-12 items-center justify-center rounded-xl text-lg font-bold',
              stat.color === 'blue' ? 'bg-blue-50 text-blue-500 dark:bg-blue-500/10' :
              stat.color === 'orange' ? 'bg-orange-50 text-orange-500 dark:bg-orange-500/10' :
              stat.color === 'purple' ? 'bg-purple-50 text-purple-500 dark:bg-purple-500/10' :
              'bg-green-50 text-green-500 dark:bg-green-500/10'
            ]">
              <svg v-if="stat.color === 'green'" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              <svg v-else-if="stat.color === 'blue'" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              <svg v-else class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">{{ stat.title }}</p>
              <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ stat.value }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
            <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-900/50 dark:text-gray-400 border-b border-gray-200 dark:border-gray-800">
              <tr>
                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Date</th>
                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Customer</th>
                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Reference</th>
                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Payment For</th>
                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Amount</th>
                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="transactions.length === 0">
                <td colspan="6" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-base text-gray-500 font-medium">No transactions found</p>
                  </div>
                </td>
              </tr>
              <tr 
                v-for="transaction in transactions" 
                :key="transaction.id"
                class="border-b border-gray-100 bg-white hover:bg-gray-50 dark:border-gray-800 dark:bg-transparent dark:hover:bg-white/[0.02] transition-colors"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-gray-900 dark:text-white font-medium">{{ transaction.created_at_formatted.split(' ')[0] }} {{ transaction.created_at_formatted.split(' ')[1] }} {{ transaction.created_at_formatted.split(' ')[2] }}</div>
                  <div class="text-xs text-gray-500">{{ transaction.created_at_formatted.split(' ')[3] }} {{ transaction.created_at_formatted.split(' ')[4] }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ transaction.user.split('(')[0].trim() }}</div>
                  <div class="text-[11px] text-gray-500">{{ transaction.user.includes('(') ? transaction.user.split('(')[1].replace(')', '') : '' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="font-mono text-xs font-semibold text-brand-600 bg-brand-50 px-2 py-1 rounded dark:bg-brand-500/10 dark:text-brand-400 inline-block">
                    {{ transaction.reference }}
                  </div>
                  <div class="text-[10px] text-gray-400 mt-1">via {{ transaction.gateway }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ transaction.payment_for }}</div>
                  <Link v-if="transaction.rfq" :href="`/admin/rfqs/${transaction.rfq.id}`" class="text-[10px] text-brand-500 hover:underline uppercase tracking-wider font-semibold">View RFQ</Link>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-bold text-gray-900 dark:text-white">
                    {{ transaction.currency }} {{ formatNumber(transaction.amount) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    'px-2.5 py-1 text-xs font-bold rounded-full uppercase tracking-wider',
                    transaction.status === 'successful' ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' :
                    transaction.status === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400' :
                    'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400'
                  ]">
                    {{ transaction.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
  transactions: Array,
  stats: Array
})

const formatNumber = (num) => {
  return Number(num).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}
</script>
