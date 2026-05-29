<template>
  <AdminLayout>
    <Head title="Finance Invoices" />
    <div class="space-y-6">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Invoices</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">View and manage Zoho Invoices.</p>
        </div>
        <Link v-if="!$page.props.auth.user.roles.includes('agent')" href="/finance/invoices/create" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          Create Invoice
        </Link>
      </div>

      <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="max-w-full overflow-x-auto custom-scrollbar">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Date &amp; No</p></th>
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Customer</p></th>
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Amount / Balance</p></th>
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Status</p></th>
                <th class="px-5 py-3 text-right sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Actions</p></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr v-for="invoice in invoices" :key="invoice.id" class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-4 sm:px-6">
                  <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ invoice.invoice_number || 'Draft' }}</p>
                  <p class="text-gray-500 text-theme-xs dark:text-gray-400">{{ formatDate(invoice.created_at) }}</p>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <p class="text-gray-800 text-theme-sm dark:text-gray-400 font-medium">{{ invoice.customer?.name || 'Unknown' }}</p>
                  <p class="text-gray-500 text-theme-xs dark:text-gray-400">{{ invoice.customer?.email }}</p>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <p class="text-gray-800 text-theme-sm dark:text-gray-400 font-semibold">{{ formatCurrency(invoice.total || 0) }}</p>
                  <p class="text-gray-500 text-theme-xs dark:text-gray-400">Due: <span class="font-medium text-error-500">{{ formatCurrency(invoice.balance_due || 0) }}</span></p>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <span :class="getStatusClass(invoice.status)" class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium uppercase">
                    {{ invoice.status }}
                  </span>
                </td>
                <td class="px-5 py-4 text-right sm:px-6">
                  <div class="flex items-center justify-end gap-3">
                    <button
                      @click="viewInvoice(invoice)"
                      :disabled="isLoading(invoice.id)"
                      class="inline-flex items-center gap-1 text-brand-500 hover:text-brand-600 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <svg v-if="isLoading(invoice.id, 'view')" class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                      </svg>
                      View
                    </button>

                    <!-- Send -->
                    <button
                      v-if="invoice.status === 'draft'"
                      @click="sendInvoice(invoice)"
                      :disabled="isLoading(invoice.id)"
                      class="inline-flex items-center gap-1 text-brand-500 hover:text-brand-600 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <svg v-if="isLoading(invoice.id, 'send')" class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                      </svg>
                      Send
                    </button>

                    <!-- Payment -->
                    <button
                      v-if="invoice.balance_due > 0 && invoice.status !== 'void' && !$page.props.auth.user.roles.some(r => ['agent','super_agent'].includes(r))"
                      @click="openPaymentModal(invoice)"
                      :disabled="isLoading(invoice.id)"
                      class="inline-flex items-center gap-1 text-success-500 hover:text-success-600 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      Payment
                    </button>

                    <!-- Void -->
                    <button
                      v-if="invoice.status !== 'void' && invoice.status !== 'paid' && !$page.props.auth.user.roles.some(r => ['agent','super_agent'].includes(r))"
                      @click="voidInvoice(invoice)"
                      :disabled="isLoading(invoice.id)"
                      class="inline-flex items-center gap-1 text-warning-500 hover:text-warning-600 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <svg v-if="isLoading(invoice.id, 'void')" class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                      </svg>
                      Void
                    </button>

                    <!-- Delete -->
                    <button
                      v-if="!$page.props.auth.user.roles.some(r => ['agent','super_agent'].includes(r))"
                      @click="confirmDelete(invoice)"
                      :disabled="isLoading(invoice.id)"
                      class="inline-flex items-center gap-1 text-error-400 hover:text-error-500 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="invoices.length === 0">
                <td colspan="5" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                  No invoices found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Payment Modal -->
    <div v-if="isPaymentModalOpen" class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity">
      <div class="relative w-full max-w-[400px] rounded-3xl bg-white p-6 dark:bg-gray-900 shadow-xl">
        <h3 class="mb-5 text-xl font-semibold text-gray-800 dark:text-white/90">Record Payment</h3>
        <form @submit.prevent="submitPayment" class="space-y-4">
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Amount</label>
            <input v-model="paymentForm.amount" type="number" step="0.01" :max="selectedInvoice?.balance_due" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white" required />
            <p class="mt-1 text-xs text-gray-500">Max: {{ formatCurrency(selectedInvoice?.balance_due) }}</p>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Date Paid</label>
            <input v-model="paymentForm.paid_at" type="date" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white" required />
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Payment Mode</label>
            <select v-model="paymentForm.payment_mode" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white" required>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="cash">Cash</option>
              <option value="credit_card">Credit Card</option>
            </select>
          </div>
          <div class="flex items-center gap-3 mt-6">
            <button @click="isPaymentModalOpen = false" type="button" class="w-full rounded-lg border border-gray-300 py-2.5 transition-colors dark:border-gray-700">Cancel</button>
            <button
              type="submit"
              :disabled="paymentForm.processing"
              class="inline-flex items-center justify-center gap-2 w-full rounded-lg bg-success-500 py-2.5 text-white hover:bg-success-600 transition-colors disabled:opacity-70 disabled:cursor-not-allowed"
            >
              <svg v-if="paymentForm.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
              </svg>
              {{ paymentForm.processing ? 'Recording…' : 'Record' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Modal -->
    <div v-if="isDeleteModalOpen" class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity">
      <div class="relative w-full max-w-md rounded-3xl bg-white p-8 text-center shadow-xl dark:bg-gray-900">
        <h3 class="mb-2 text-xl font-bold text-gray-800 dark:text-white/90">Delete Invoice</h3>
        <p class="mb-8 text-sm text-gray-500 dark:text-gray-400">Are you sure you want to delete this invoice? It will also be deleted from Zoho.</p>
        <div class="flex justify-center gap-3">
          <button @click="isDeleteModalOpen = false" type="button" class="w-full rounded-lg border border-gray-300 py-2.5 transition-colors dark:border-gray-700">Cancel</button>
          <button
            @click="executeDelete"
            type="button"
            :disabled="actionForm.processing"
            class="inline-flex items-center justify-center gap-2 w-full rounded-lg bg-error-500 py-2.5 text-white hover:bg-error-600 transition-colors disabled:opacity-70 disabled:cursor-not-allowed"
          >
            <svg v-if="actionForm.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            {{ actionForm.processing ? 'Deleting…' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useCurrency } from '@/composables/useCurrency'
const { formatCurrency } = useCurrency()

const props = defineProps({
  invoices: Array
})

const actionForm = useForm({})
const paymentForm = useForm({
  amount: '',
  paid_at: new Date().toISOString().split('T')[0],
  payment_mode: 'bank_transfer'
})

const isPaymentModalOpen = ref(false)
const selectedInvoice = ref(null)

const isDeleteModalOpen = ref(false)
const invoiceToDelete = ref(null)

// Tracks which row + action is currently loading, e.g. "42-send"
const loadingAction = ref(null)

const isLoading = (id, action = null) => {
  if (!loadingAction.value) return false
  if (action) return loadingAction.value === `${id}-${action}`
  return loadingAction.value?.startsWith(`${id}-`)
}

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
    case 'paid': return 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400'
    case 'partially_paid': return 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400'
    case 'void': return 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-400'
    case 'sent': return 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-400'
    default: return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400'
  }
}

const viewInvoice = (invoice) => {
  loadingAction.value = `${invoice.id}-view`
  router.visit(`/finance/invoices/${invoice.id}`)
}

const sendInvoice = (invoice) => {
  loadingAction.value = `${invoice.id}-send`
  actionForm.post(route('finance.invoices.send', invoice.id), {
    preserveScroll: true,
    onFinish: () => { loadingAction.value = null }
  })
}

const voidInvoice = (invoice) => {
  loadingAction.value = `${invoice.id}-void`
  actionForm.post(route('finance.invoices.void', invoice.id), {
    preserveScroll: true,
    onFinish: () => { loadingAction.value = null }
  })
}

const openPaymentModal = (invoice) => {
  selectedInvoice.value = invoice
  paymentForm.amount = invoice.balance_due
  paymentForm.paid_at = new Date().toISOString().split('T')[0]
  paymentForm.payment_mode = 'bank_transfer'
  isPaymentModalOpen.value = true
}

const submitPayment = () => {
  if (selectedInvoice.value) {
    paymentForm.post(route('finance.invoices.payment', selectedInvoice.value.id), {
      onSuccess: () => {
        isPaymentModalOpen.value = false
        selectedInvoice.value = null
      },
      preserveScroll: true
    })
  }
}

const confirmDelete = (invoice) => {
  invoiceToDelete.value = invoice
  isDeleteModalOpen.value = true
}

const executeDelete = () => {
  if (invoiceToDelete.value) {
    actionForm.delete(route('finance.invoices.destroy', invoiceToDelete.value.id), {
      onSuccess: () => {
        isDeleteModalOpen.value = false
        invoiceToDelete.value = null
      },
      preserveScroll: true
    })
  }
}
</script>
