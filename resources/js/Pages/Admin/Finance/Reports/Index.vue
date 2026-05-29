<template>
  <AdminLayout>
    <Head title="Financial Reports" />
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Financial Reports</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">Real-time metrics, payment tracking, and aging receivables synced from Zoho Invoice.</p>
        </div>
      </div>

      <!-- Tab Buttons -->
      <div class="border-b border-gray-200 dark:border-gray-800">
        <div class="flex gap-6">
          <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="[activeTab === tab.id ? 'border-brand-500 text-brand-500 dark:text-brand-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200', 'pb-4 text-sm font-semibold border-b-2 transition-all']">
            {{ tab.name }}
          </button>
        </div>
      </div>

      <!-- Report views -->
      <div class="grid grid-cols-1 gap-6">
        <!-- Loading State -->
        <div v-if="loading" class="rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.03] flex flex-col items-center justify-center min-h-[300px]">
          <div class="h-8 w-8 animate-spin rounded-full border-4 border-brand-500 border-t-transparent"></div>
          <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Fetching report data...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.03] text-center space-y-3">
          <p class="text-error-500 font-medium">Unable to load report.</p>
          <p class="text-xs text-gray-400 max-w-md mx-auto">{{ error }}</p>
          <button @click="fetchActiveReport" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-600">
            Retry Fetch
          </button>
        </div>

        <!-- Report Data Blocks -->
        <div v-else class="space-y-6">
          <!-- 1. Invoice Summary Tab -->
          <div v-if="activeTab === 'summary'" class="space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
              <div v-for="stat in summaryStats" :key="stat.label" class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">{{ stat.label }}</p>
                <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white">{{ stat.value }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ stat.sub }}</p>
              </div>
            </div>

            <!-- List of Invoices Summary -->
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
              <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-800 dark:text-white">Invoiced Breakdown</h3>
                <span class="text-xs text-gray-400">Total: {{ summaryData.length }} records</span>
              </div>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                  <thead class="bg-gray-50 dark:bg-gray-800/20">
                    <tr>
                      <th class="px-6 py-3.5 text-left text-xs font-bold uppercase text-gray-400">Status</th>
                      <th class="px-6 py-3.5 text-right text-xs font-bold uppercase text-gray-400">Count</th>
                      <th class="px-6 py-3.5 text-right text-xs font-bold uppercase text-gray-400">Invoiced Amount</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                    <tr v-for="row in summaryData" :key="row.status">
                      <td class="px-6 py-4 font-semibold text-gray-700 dark:text-gray-300 capitalize">{{ row.status }}</td>
                      <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-400">{{ row.count }}</td>
                      <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">{{ formatCurrency(row.amount) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- 2. Payments Received Tab -->
          <div v-if="activeTab === 'payments'" class="space-y-6">
            <!-- Mode breakdown stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div v-for="mode in paymentModes" :key="mode.payment_mode" class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                  <span class="inline-flex items-center rounded bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-400">{{ mode.payment_mode || 'Online' }}</span>
                  <span class="text-xs text-gray-400">{{ mode.count }} payments</span>
                </div>
                <p class="mt-4 text-2xl font-extrabold text-gray-800 dark:text-white">{{ formatCurrency(mode.total) }}</p>
              </div>
            </div>

            <!-- List of recent payments -->
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
              <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-base font-bold text-gray-800 dark:text-white">Recent Payment Receipts</h3>
              </div>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                  <thead class="bg-gray-50 dark:bg-gray-800/20">
                    <tr>
                      <th class="px-6 py-3.5 text-left text-xs font-bold uppercase text-gray-400">Date</th>
                      <th class="px-6 py-3.5 text-left text-xs font-bold uppercase text-gray-400">Customer</th>
                      <th class="px-6 py-3.5 text-left text-xs font-bold uppercase text-gray-400">Invoice No.</th>
                      <th class="px-6 py-3.5 text-left text-xs font-bold uppercase text-gray-400">Mode / Reference</th>
                      <th class="px-6 py-3.5 text-right text-xs font-bold uppercase text-gray-400">Amount Received</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                    <tr v-for="pay in paymentsData" :key="pay.payment_id">
                      <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ formatDate(pay.date) }}</td>
                      <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">{{ pay.customer_name }}</td>
                      <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ pay.invoice_number }}</td>
                      <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                        <span class="font-medium">{{ pay.payment_mode }}</span>
                        <span v-if="pay.reference_number" class="ml-1.5 text-xs text-gray-400">#{{ pay.reference_number }}</span>
                      </td>
                      <td class="px-6 py-4 text-right font-bold text-success-600 dark:text-success-400">{{ formatCurrency(pay.amount) }}</td>
                    </tr>
                    <tr v-if="paymentsData.length === 0">
                      <td colspan="5" class="px-6 py-8 text-center text-gray-400">No payment receipts logged in Zoho.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- 3. Outstanding Receivables Tab -->
          <div v-if="activeTab === 'receivables'" class="space-y-6">
            <!-- Summary Aging Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Unpaid Balances</p>
                <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white">{{ formatCurrency(receivablesSummary.total_outstanding) }}</p>
                <p class="mt-1 text-xs text-gray-500">Across {{ receivablesData.length }} customers</p>
              </div>
              <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Overdue Balance</p>
                <p class="mt-2 text-2xl font-bold text-error-600 dark:text-error-400">{{ formatCurrency(receivablesSummary.overdue_amount) }}</p>
                <p class="mt-1 text-xs text-gray-500">Passed payment due date</p>
              </div>
              <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Draft Outstanding</p>
                <p class="mt-2 text-2xl font-bold text-gray-500 dark:text-gray-400">{{ formatCurrency(receivablesSummary.draft_amount) }}</p>
                <p class="mt-1 text-xs text-gray-500">Unissued draft invoice total</p>
              </div>
            </div>

            <!-- List by Customer -->
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
              <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-base font-bold text-gray-800 dark:text-white">Outstanding Receivables by Customer</h3>
              </div>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                  <thead class="bg-gray-50 dark:bg-gray-800/20">
                    <tr>
                      <th class="px-6 py-3.5 text-left text-xs font-bold uppercase text-gray-400">Customer Name</th>
                      <th class="px-6 py-3.5 text-right text-xs font-bold uppercase text-gray-400">Invoiced Count</th>
                      <th class="px-6 py-3.5 text-right text-xs font-bold uppercase text-gray-400">Outstanding Balance</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                    <tr v-for="cust in receivablesData" :key="cust.customer_id">
                      <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">{{ cust.customer_name }}</td>
                      <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-400">{{ cust.invoice_count }}</td>
                      <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">{{ formatCurrency(cust.balance) }}</td>
                    </tr>
                    <tr v-if="receivablesData.length === 0">
                      <td colspan="3" class="px-6 py-8 text-center text-gray-400">All customer balances are clear. No outstanding receivables!</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, watch, onMounted } from 'vue'
import { useCurrency } from '@/composables/useCurrency'
const { formatCurrency } = useCurrency()
import axios from 'axios'

const tabs = [
  { id: 'summary', name: 'Invoice Summary' },
  { id: 'payments', name: 'Payments Received (Receipts)' },
  { id: 'receivables', name: 'Outstanding Receivables' }
]

const activeTab = ref('summary')
const loading = ref(false)
const error = ref(null)

// Tab Data Store
const summaryData = ref([])
const summaryStats = ref([])
const paymentsData = ref([])
const paymentModes = ref([])
const receivablesData = ref([])
const receivablesSummary = ref({ total_outstanding: 0, overdue_amount: 0, draft_amount: 0 })

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' }).format(new Date(dateString))
}


const fetchActiveReport = async () => {
  loading.value = true
  error.value = null

  try {
    if (activeTab.value === 'summary') {
      const response = await axios.get(route('finance.reports.invoice-summary'))
      // Process Zoho summary format
      const invoiceData = response.data?.invoice_summary || {}
      
      summaryData.value = [
        { status: 'draft', count: invoiceData.draft_count || 0, amount: invoiceData.draft_amount || 0 },
        { status: 'sent', count: invoiceData.sent_count || 0, amount: invoiceData.sent_amount || 0 },
        { status: 'partially paid', count: invoiceData.partially_paid_count || 0, amount: invoiceData.partially_paid_amount || 0 },
        { status: 'paid', count: invoiceData.paid_count || 0, amount: invoiceData.paid_amount || 0 },
        { status: 'overdue', count: invoiceData.overdue_count || 0, amount: invoiceData.overdue_amount || 0 }
      ]

      summaryStats.value = [
        { label: 'Total Invoiced', value: formatCurrency(invoiceData.total_invoiced || 0), sub: 'Overall gross sales', tooltip: '' },
        { label: 'Payments Received', value: formatCurrency(invoiceData.total_payments || 0), sub: 'Cash collected', tooltip: '' },
        { label: 'Outstanding Receivables', value: formatCurrency(invoiceData.total_receivables || 0), sub: 'Due from clients', tooltip: '' },
        { label: 'Active Drafts', value: formatCurrency(invoiceData.draft_amount || 0), sub: 'Unissued billing documents', tooltip: '' }
      ]
    } else if (activeTab.value === 'payments') {
      const response = await axios.get(route('finance.reports.payments-received'))
      const payments = response.data?.payments_received || []
      paymentsData.value = payments

      // Group payments by mode to display summary cards
      const modesMap = {}
      payments.forEach(pay => {
        const mode = pay.payment_mode || 'Others'
        const amt = parseFloat(pay.amount) || 0
        if (!modesMap[mode]) {
          modesMap[mode] = { payment_mode: mode, count: 0, total: 0 }
        }
        modesMap[mode].count += 1
        modesMap[mode].total += amt
      })
      paymentModes.value = Object.values(modesMap)
    } else if (activeTab.value === 'receivables') {
      const response = await axios.get(route('finance.reports.outstanding-receivables'))
      const list = response.data?.receivables || []
      receivablesData.value = list

      let total = 0
      let overdue = 0
      let draft = 0

      list.forEach(c => {
        total += parseFloat(c.balance) || 0
        overdue += parseFloat(c.overdue_balance) || 0
        draft += parseFloat(c.draft_balance) || 0
      })

      receivablesSummary.value = {
        total_outstanding: total,
        overdue_amount: overdue,
        draft_amount: draft
      }
    }
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || err.message || 'Unknown network error.'
  } finally {
    loading.value = false
  }
}

watch(activeTab, () => {
  fetchActiveReport()
})

onMounted(() => {
  fetchActiveReport()
})
</script>
