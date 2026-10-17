<template>
  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800 flex items-center justify-between">
      <h3 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Financial Overview</h3>
      <Link
        href="/finance/invoices"
        class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-bold text-gray-700 hover:bg-gray-50 hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 shadow-xs transition"
      >
        <span>All Invoices</span>
        <i class="fa-solid fa-arrow-right text-[10px]"></i>
      </Link>
    </div>

    <div class="divide-y divide-gray-100 px-6 py-4 dark:divide-gray-800">
      <!-- Key Metrics -->
      <div class="pb-5">
        <div class="mb-3">
          <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Key Metrics</p>
        </div>
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">Total Invoices</span>
            <span class="text-base font-black text-gray-900 dark:text-white">{{ stats.totalInvoices }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">Invoice Amount</span>
            <span class="text-base font-black text-gray-900 dark:text-white">{{ stats.totalInvoiceAmount }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">Paid Amount</span>
            <span class="text-base font-black text-emerald-600 dark:text-emerald-400">{{ stats.paidAmount }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">Pending Payments</span>
            <span class="text-base font-black text-amber-600 dark:text-amber-400">{{ stats.pendingPayments }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">Estimates</span>
            <span class="text-base font-black text-gray-900 dark:text-white">{{ stats.totalEstimates }}</span>
          </div>
        </div>
      </div>

      <!-- Recent Invoices -->
      <div class="pt-5">
        <div class="mb-3">
          <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Recent Invoices</p>
        </div>
        <div class="space-y-2.5 max-h-64 overflow-y-auto custom-scrollbar pr-1">
          <div
            v-for="invoice in invoices"
            :key="invoice.id"
            class="rounded-xl border border-gray-200 bg-white p-3 shadow-xs dark:border-gray-800 dark:bg-gray-900/60 transition hover:border-gray-300"
          >
            <div class="mb-1.5 flex items-center justify-between">
              <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ invoice.product }}</p>
              <span
                :class="getInvoiceStatusClass(invoice.status)"
                class="text-xs font-bold uppercase tracking-wider rounded-md px-2.5 py-0.5"
              >
                {{ invoice.status }}
              </span>
            </div>
            <div class="flex items-center justify-between text-xs">
              <span class="font-medium text-gray-600 dark:text-gray-400">{{ invoice.customer }}</span>
              <span class="font-black text-gray-900 dark:text-white">{{ invoice.amount }}</span>
            </div>
            <p class="mt-1 text-[11px] font-medium text-gray-500 dark:text-gray-400">Due: {{ invoice.due_date }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
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
