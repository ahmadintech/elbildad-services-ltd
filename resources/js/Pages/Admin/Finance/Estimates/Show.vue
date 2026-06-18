<template>
  <AdminLayout>
    <Head :title="`Estimate #${estimate.zoho_estimate_id}`" />
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Back & Actions Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between no-print">
        <div class="flex items-center gap-3">
          <Link href="/finance/estimates" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-500 hover:text-gray-700 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400 dark:hover:text-white">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          </Link>
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Estimate Details</h2>
              <span :class="getStatusClass(estimate.status)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium uppercase">
                {{ estimate.status }}
              </span>
            </div>
            <p class="text-xs text-gray-400">Zoho ID: {{ estimate.zoho_estimate_id }}</p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
          <!-- Download PDF -->
          <a :href="route('finance.estimates.pdf', estimate.id)" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-300 dark:hover:bg-white/[0.06] transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Download PDF
          </a>

          <!-- Print -->
          <button @click="printPage" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-300 dark:hover:bg-white/[0.06] transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
            Print
          </button>

          <!-- Send Estimate -->
          <button
            v-if="estimate.status === 'draft'"
            @click="sendEstimate"
            :disabled="actionForm.processing"
            class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors disabled:opacity-70 disabled:cursor-not-allowed"
          >
            <svg v-if="activeAction === 'send'" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            {{ activeAction === 'send' ? 'Sending…' : 'Send Estimate' }}
          </button>

          <!-- Accept -->
          <button
            v-if="estimate.status === 'sent'"
            @click="acceptEstimate"
            :disabled="actionForm.processing"
            class="inline-flex items-center gap-1.5 rounded-lg bg-success-500 px-4 py-2 text-sm font-medium text-white hover:bg-success-600 transition-colors disabled:opacity-70 disabled:cursor-not-allowed"
          >
            <svg v-if="activeAction === 'accept'" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            {{ activeAction === 'accept' ? 'Accepting…' : 'Accept' }}
          </button>

          <!-- Decline -->
          <button
            v-if="estimate.status === 'sent'"
            @click="declineEstimate"
            :disabled="actionForm.processing"
            class="inline-flex items-center gap-1.5 rounded-lg bg-error-500 px-4 py-2 text-sm font-medium text-white hover:bg-error-600 transition-colors disabled:opacity-70 disabled:cursor-not-allowed"
          >
            <svg v-if="activeAction === 'decline'" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            {{ activeAction === 'decline' ? 'Declining…' : 'Decline' }}
          </button>

          <!-- Convert to Invoice -->
          <button
            v-if="estimate.status === 'accepted' && canConvert"
            @click="convertToInvoice"
            :disabled="actionForm.processing"
            class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors disabled:opacity-70 disabled:cursor-not-allowed"
          >
            <svg v-if="activeAction === 'convert'" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            {{ activeAction === 'convert' ? 'Converting…' : 'Convert to Invoice' }}
          </button>
        </div>
      </div>

      <!-- Quotation Presentation Card -->
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
            <h2 class="text-2xl font-extrabold uppercase tracking-wide text-gray-500 dark:text-gray-400">Quotation</h2>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-1">Ref: {{ zoho.estimate_number || estimate.zoho_estimate_id }}</p>
          </div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Billing Party details -->
          <div class="space-y-2">
            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Prepared For</h4>
            <div class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
              <p class="font-bold text-gray-900 dark:text-white text-base">{{ estimate.customer?.name || zoho.customer_name }}</p>
              <p>{{ estimate.customer?.email || zoho.email }}</p>
              <p v-if="estimate.customer?.whatsapp_number">WhatsApp: {{ estimate.customer.whatsapp_number }}</p>
              <p v-if="zoho.billing_address?.address">{{ zoho.billing_address.address }}</p>
            </div>
          </div>

          <!-- Date details -->
          <div class="space-y-2 md:text-right">
            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Details</h4>
            <div class="text-sm text-gray-700 dark:text-gray-300 space-y-1 inline-block md:text-right">
              <p><span class="font-medium text-gray-400">Date Issued:</span> {{ formatDate(zoho.date || estimate.created_at) }}</p>
              <p><span class="font-medium text-gray-400">Valid Until:</span> {{ formatDate(zoho.expiry_date || estimate.valid_date) }}</p>
              <p v-if="estimate.rfq"><span class="font-medium text-gray-400">Related RFQ:</span> #{{ estimate.rfq.product_name }}</p>
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
                <td colspan="4" class="px-5 py-4 text-sm text-gray-500 text-right font-medium">Estimated Amount</td>
                <td class="px-5 py-4 text-sm text-right font-bold text-gray-900 dark:text-white">{{ formatCurrency(estimate.total) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Totals summary & notes -->
        <div class="flex flex-col md:flex-row md:justify-between gap-6 pt-6 border-t border-gray-100 dark:border-gray-800">
          <div class="max-w-md space-y-2">
            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Notes & Terms</h4>
            <p class="text-sm text-gray-500 dark:text-gray-400 whitespace-pre-line">{{ zoho.notes || estimate.notes || 'No special terms.' }}</p>
          </div>
          <div class="w-full md:w-80 space-y-3">
            <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
              <span>Subtotal:</span>
              <span>{{ formatCurrency(zoho.sub_total || estimate.total) }}</span>
            </div>
            <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
              <span>Tax/Other charges:</span>
              <span>{{ formatCurrency(zoho.tax_total || 0) }}</span>
            </div>
            <div class="flex justify-between pt-3 border-t border-gray-100 dark:border-gray-800 text-lg font-bold text-gray-900 dark:text-white">
              <span>Total Quote:</span>
              <span>{{ formatCurrency(zoho.total || estimate.total) }}</span>
            </div>
          </div>
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
  estimate: Object,
  zoho: Object
})

const page = usePage()
const canConvert = computed(() => {
  const roles = page.props.auth?.user?.roles ?? []
  return roles.some(r => ['owner', 'admin', 'agent', 'super_agent'].includes(r))
})

const actionForm = useForm({})
const activeAction = ref(null)

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
    case 'accepted': return 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400'
    case 'declined': return 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-400'
    case 'sent': return 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-400'
    default: return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400'
  }
}

const sendEstimate = () => {
  activeAction.value = 'send'
  actionForm.post(route('finance.estimates.send', props.estimate.id), {
    onFinish: () => { activeAction.value = null }
  })
}

const acceptEstimate = () => {
  activeAction.value = 'accept'
  actionForm.post(route('finance.estimates.accept', props.estimate.id), {
    onFinish: () => { activeAction.value = null }
  })
}

const declineEstimate = () => {
  activeAction.value = 'decline'
  actionForm.post(route('finance.estimates.decline', props.estimate.id), {
    onFinish: () => { activeAction.value = null }
  })
}

const convertToInvoice = () => {
  activeAction.value = 'convert'
  actionForm.post(route('finance.invoices.convert', props.estimate.id), {
    onFinish: () => { activeAction.value = null }
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
