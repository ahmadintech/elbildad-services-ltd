<template>
  <AdminLayout>
    <Head :title="`Invoice #${invoice.invoice_number || invoice.zoho_invoice_id}`" />
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Back & Actions Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between no-print">
        <div class="flex items-center gap-3">
          <Link href="/finance/invoices" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-500 hover:text-gray-700 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400 dark:hover:text-white">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          </Link>
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Invoice Details</h2>
              <span :class="getStatusClass(invoice.status)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium uppercase">
                {{ invoice.status }}
              </span>
            </div>
            <p class="text-xs text-gray-400">Invoice Number: {{ invoice.invoice_number || 'Draft' }}</p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
          <!-- Download PDF -->
          <a :href="route('finance.invoices.pdf', invoice.id)" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-300 dark:hover:bg-white/[0.06] transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Download PDF
          </a>

          <!-- Print -->
          <button @click="printPage" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-300 dark:hover:bg-white/[0.06] transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
            Print
          </button>

          <!-- Send Invoice -->
          <button
            v-if="invoice.status === 'draft'"
            @click="sendInvoice"
            :disabled="actionForm.processing"
            class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors disabled:opacity-70 disabled:cursor-not-allowed"
          >
            <svg v-if="activeAction === 'send'" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            {{ activeAction === 'send' ? 'Sending…' : 'Send Invoice' }}
          </button>

          <!-- Record Payment (admin/owner only) -->
          <button
            v-if="invoice.status !== 'paid' && invoice.status !== 'voided' && isAdminOrOwner"
            @click="openPaymentModal"
            :disabled="actionForm.processing"
            class="inline-flex items-center gap-1.5 rounded-lg bg-success-500 px-4 py-2 text-sm font-medium text-white hover:bg-success-600 transition-colors disabled:opacity-70 disabled:cursor-not-allowed"
          >
            Record Payment
          </button>

          <!-- Void Invoice (admin/owner only) -->
          <button
            v-if="invoice.status !== 'voided' && invoice.status !== 'paid' && isAdminOrOwner"
            @click="voidInvoice"
            :disabled="actionForm.processing"
            class="inline-flex items-center gap-1.5 rounded-lg bg-error-500 px-4 py-2 text-sm font-medium text-white hover:bg-error-600 transition-colors disabled:opacity-70 disabled:cursor-not-allowed"
          >
            <svg v-if="activeAction === 'void'" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            {{ activeAction === 'void' ? 'Voiding…' : 'Void Invoice' }}
          </button>
        </div>
      </div>

      <!-- Invoice Presentation Card -->
      <div id="print-area" class="rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.03] shadow-sm space-y-8">
        <!-- Logo & Header -->
        <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between border-b border-gray-100 pb-6 dark:border-gray-800">
          <div class="flex items-center gap-3">
            <img class="h-12 w-auto" src="/images/logo/elbildad-logo.png" alt="Elbildad Services Ltd" />
            <div class="leading-none">
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Elbildad Services Ltd</h1>
              <p class="text-xs text-brand-500 font-semibold uppercase tracking-widest mt-0.5">Finance Department</p>
            </div>
          </div>
          <div class="text-left sm:text-right">
            <h2 class="text-2xl font-extrabold uppercase tracking-wide text-gray-500 dark:text-gray-400">Invoice</h2>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-1">No: {{ invoice.invoice_number || invoice.zoho_invoice_id }}</p>
          </div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Billing Party details -->
          <div class="space-y-2">
            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Billed To</h4>
            <div class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
              <p class="font-bold text-gray-900 dark:text-white text-base">{{ invoice.customer?.name || zoho.customer_name }}</p>
              <p>{{ invoice.customer?.email || zoho.email }}</p>
              <p v-if="invoice.customer?.whatsapp_number">WhatsApp: {{ invoice.customer.whatsapp_number }}</p>
              <p v-if="zoho.billing_address?.address">{{ zoho.billing_address.address }}</p>
            </div>
          </div>

          <!-- Date details -->
          <div class="space-y-2 md:text-right">
            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Details</h4>
            <div class="text-sm text-gray-700 dark:text-gray-300 space-y-1 inline-block md:text-right">
              <p><span class="font-medium text-gray-400">Date Issued:</span> {{ formatDate(zoho.date || invoice.issued_at) }}</p>
              <p><span class="font-medium text-gray-400">Due Date:</span> {{ formatDate(zoho.due_date || invoice.due_date) }}</p>
              <p v-if="invoice.estimate"><span class="font-medium text-gray-400">Linked Quote:</span> #{{ invoice.estimate.zoho_estimate_id }}</p>
              <p v-if="invoice.rfq"><span class="font-medium text-gray-400">Related RFQ:</span> #{{ invoice.rfq.product_name }}</p>
            </div>
          </div>
        </div>

        <!-- Line items table -->
        <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
          <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
            <thead class="bg-gray-50 dark:bg-gray-800/40">
              <tr>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Item</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Description</th>
                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Rate</th>
                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Qty</th>
                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr v-for="item in (zoho.line_items || [])" :key="item.line_item_id">
                <td class="px-5 py-4 text-sm font-semibold text-gray-900 dark:text-white">{{ item.name }}</td>
                <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ item.description || '-' }}</td>
                <td class="px-5 py-4 text-sm text-right text-gray-700 dark:text-gray-300">{{ formatCurrency(item.rate) }}</td>
                <td class="px-5 py-4 text-sm text-right text-gray-700 dark:text-gray-300">{{ item.quantity }}</td>
                <td class="px-5 py-4 text-sm text-right font-semibold text-gray-900 dark:text-white">{{ formatCurrency(item.item_total) }}</td>
              </tr>
              <!-- Fallback to local total if no Zoho line items -->
              <tr v-if="(!zoho.line_items || zoho.line_items.length === 0)">
                <td colspan="4" class="px-5 py-4 text-sm text-gray-500 text-right font-medium">Invoiced Amount</td>
                <td class="px-5 py-4 text-sm text-right font-bold text-gray-900 dark:text-white">{{ formatCurrency(invoice.total) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Totals summary & notes -->
        <div class="flex flex-col md:flex-row md:justify-between gap-6 pt-6 border-t border-gray-100 dark:border-gray-800">
          <div class="max-w-md space-y-2">
            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Notes & Terms</h4>
            <p class="text-sm text-gray-500 dark:text-gray-400 whitespace-pre-line">{{ zoho.notes || invoice.notes || 'Terms apply.' }}</p>
          </div>
          <div class="w-full md:w-80 space-y-3">
            <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
              <span>Subtotal:</span>
              <span>{{ formatCurrency(zoho.sub_total || invoice.total) }}</span>
            </div>
            <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
              <span>Tax/Other charges:</span>
              <span>{{ formatCurrency(zoho.tax_total || 0) }}</span>
            </div>
            <div class="flex justify-between text-sm font-semibold text-success-600 dark:text-success-400">
              <span>Amount Paid:</span>
              <span>{{ formatCurrency((zoho.total || invoice.total) - (zoho.balance || invoice.balance_due)) }}</span>
            </div>
            <div class="flex justify-between pt-3 border-t border-gray-100 dark:border-gray-800 text-lg font-bold text-gray-900 dark:text-white">
              <span>Balance Due:</span>
              <span>{{ formatCurrency(zoho.balance !== undefined ? zoho.balance : invoice.balance_due) }}</span>
            </div>
          </div>
        </div>

        <!-- Recorded Payments Log (Receipt generation visualizer) -->
        <div v-if="invoice.payments && invoice.payments.length > 0" class="pt-6 border-t border-gray-100 dark:border-gray-800 space-y-3 no-print">
          <h3 class="text-base font-bold text-gray-800 dark:text-white/90">Payments Log (Receipts)</h3>
          <div class="overflow-x-auto rounded-lg border border-gray-100 dark:border-gray-800">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
              <thead class="bg-gray-50 dark:bg-gray-800/20">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-bold uppercase tracking-wider text-gray-400">Payment Date</th>
                  <th class="px-4 py-2 text-left text-xs font-bold uppercase tracking-wider text-gray-400">Reference / Mode</th>
                  <th class="px-4 py-2 text-right text-xs font-bold uppercase tracking-wider text-gray-400">Amount Paid</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                <tr v-for="pay in invoice.payments" :key="pay.id">
                  <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ formatDate(pay.paid_at) }}</td>
                  <td class="px-4 py-2 text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center rounded bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                      {{ pay.payment_mode || 'Standard' }}
                    </span>
                    <span v-if="pay.zoho_payment_id" class="ml-2 text-xs text-gray-400">Ref: {{ pay.zoho_payment_id }}</span>
                  </td>
                  <td class="px-4 py-2 text-right font-semibold text-success-600 dark:text-success-400">{{ formatCurrency(pay.amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Record Payment Modal -->
    <div v-if="showPaymentModal" class="fixed inset-0 z-50 overflow-y-auto no-print">
      <div class="flex min-h-screen items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-950/60 transition-opacity" @click="showPaymentModal = false"></div>

        <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 shadow-xl transition-all dark:bg-dark-900 border border-gray-100 dark:border-gray-800">
          <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Record Payment</h3>
            <button @click="showPaymentModal = false" class="text-gray-400 hover:text-gray-500">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>

          <form @submit.prevent="submitPayment" class="mt-4 space-y-4">
            <!-- Amount -->
            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">Payment Amount ($)</label>
              <input v-model="paymentForm.amount" type="number" step="0.01" :max="invoice.balance_due" min="0.01" class="dark:bg-dark-950 h-10 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 dark:border-gray-700 dark:text-white" required />
            </div>

            <!-- Date -->
            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">Date Paid</label>
              <input v-model="paymentForm.paid_at" type="date" class="dark:bg-dark-950 h-10 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 dark:border-gray-700 dark:text-white" required />
            </div>

            <!-- Mode -->
            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">Payment Mode</label>
              <select v-model="paymentForm.payment_mode" class="dark:bg-dark-950 h-10 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 dark:border-gray-700 dark:text-white" required>
                <option value="Cash">Cash</option>
                <option value="Bank Remittance">Bank Remittance / Transfer</option>
                <option value="Cheque">Cheque</option>
                <option value="Credit Card">Credit Card</option>
                <option value="Others">Others</option>
              </select>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-800">
              <button type="button" @click="showPaymentModal = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">Cancel</button>
              <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-lg bg-success-500 px-4 py-2 text-sm font-medium text-white hover:bg-success-600 transition-colors disabled:opacity-70 disabled:cursor-not-allowed"
                :disabled="paymentForm.processing"
              >
                <svg v-if="paymentForm.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                {{ paymentForm.processing ? 'Recording…' : 'Record Payment' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { useCurrency } from '@/composables/useCurrency'
const { formatCurrency } = useCurrency()

const props = defineProps({
  invoice: Object,
  zoho: Object
})

const page = usePage()
const isAdminOrOwner = computed(() => {
  const roles = page.props.auth?.user?.roles ?? []
  return roles.some(r => ['owner', 'admin'].includes(r))
})

const showPaymentModal = ref(false)
const activeAction = ref(null)
const actionForm = useForm({})
const paymentForm = useForm({
  amount: props.invoice.balance_due,
  paid_at: new Date().toISOString().split('T')[0],
  payment_mode: 'Bank Remittance'
})

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  }).format(new Date(dateString))
}

const getStatusClass = (status) => {
  switch (status?.toLowerCase()) {
    case 'paid': return 'bg-success-5 text-success-700 dark:bg-success-500/10 dark:text-success-400'
    case 'sent': return 'bg-brand-5 text-brand-700 dark:bg-brand-500/10 dark:text-brand-400'
    case 'voided': return 'bg-error-5 text-error-700 dark:bg-error-500/10 dark:text-error-400'
    case 'partially_paid': return 'bg-warning-5 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400'
    default: return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400'
  }
}

const sendInvoice = () => {
  activeAction.value = 'send'
  actionForm.post(route('finance.invoices.send', props.invoice.id), {
    onFinish: () => { activeAction.value = null }
  })
}

const voidInvoice = () => {
  if (confirm('Are you sure you want to void this invoice in Zoho?')) {
    activeAction.value = 'void'
    actionForm.post(route('finance.invoices.void', props.invoice.id), {
      onFinish: () => { activeAction.value = null }
    })
  }
}

const openPaymentModal = () => {
  paymentForm.amount = props.invoice.balance_due
  showPaymentModal.value = true
}

const submitPayment = () => {
  paymentForm.post(route('finance.invoices.payment', props.invoice.id), {
    onSuccess: () => {
      showPaymentModal.value = false
    }
  })
}

const printPage = () => {
  window.print()
}
</script>

<style>
@media print {
  .no-print {
    display: none !important;
  }
  #print-area {
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    padding: 0 !important;
    margin: 0 !important;
    color: black !important;
  }
}
</style>
