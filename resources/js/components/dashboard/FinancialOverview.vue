<template>
  <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Financial Overview</h3>
    </div>

    <div class="divide-y divide-gray-200 px-6 py-4 dark:divide-gray-800">
      <!-- Key Metrics -->
      <div class="pb-4">
        <div class="mb-4">
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Key Metrics</p>
        </div>
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-400">Total Invoices</span>
            <span class="font-bold text-gray-900 dark:text-white/90">{{ stats.totalInvoices }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-400">Invoice Amount</span>
            <span class="font-bold text-gray-900 dark:text-white/90">{{ stats.totalInvoiceAmount }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-400">Paid Amount</span>
            <span class="font-bold text-green-600 dark:text-green-400">{{ stats.paidAmount }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-400">Pending Payments</span>
            <span class="font-bold text-orange-600 dark:text-orange-400">{{ stats.pendingPayments }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-400">Estimates</span>
            <span class="font-bold text-gray-900 dark:text-white/90">{{ stats.totalEstimates }}</span>
          </div>
        </div>
      </div>

      <!-- Recent Invoices -->
      <div class="pt-4">
        <div class="mb-4">
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Recent Invoices</p>
        </div>
        <div class="space-y-3 max-h-64 overflow-y-auto">
          <div
            v-for="invoice in invoices"
            :key="invoice.id"
            class="rounded-lg border border-gray-200 p-3 dark:border-gray-700"
          >
            <div class="mb-2 flex items-center justify-between">
              <p class="text-sm font-semibold text-gray-900 dark:text-white/90">{{ invoice.product }}</p>
              <span
                :class="getInvoiceStatusClass(invoice.status)"
                class="text-xs font-semibold rounded px-2 py-1"
              >
                {{ invoice.status }}
              </span>
            </div>
            <div class="flex items-center justify-between text-xs">
              <span class="text-gray-600 dark:text-gray-400">{{ invoice.customer }}</span>
              <span class="font-bold text-gray-900 dark:text-white/90">{{ invoice.amount }}</span>
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">Due: {{ invoice.due_date }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  stats: {
    type: Object,
    required: true
  },
  invoices: {
    type: Array,
    required: true
  }
})

const getInvoiceStatusClass = (status) => {
  const statusClasses = {
    pending: 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-400',
    sent: 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
    paid: 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-400',
    overdue: 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400',
  }
  return statusClasses[status] || statusClasses.pending
}
</script>
