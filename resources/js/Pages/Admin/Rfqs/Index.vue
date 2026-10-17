<template>
  <AdminLayout>
    <Head title="RFQ Management" />
    <div class="space-y-6">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">RFQ Management</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">View, assign, and manage customer requests for quotation.</p>
        </div>
      </div>

      <div v-if="$page.props.flash && $page.props.flash.error" class="bg-error-50 text-error-700 px-4 py-3 rounded-lg text-sm border border-error-200 dark:bg-error-500/15 dark:border-error-500/20 dark:text-error-500">
        {{ $page.props.flash.error }}
      </div>
      <div v-if="$page.props.flash && $page.props.flash.success" class="bg-success-50 text-success-700 px-4 py-3 rounded-lg text-sm border border-success-200 dark:bg-success-500/15 dark:border-success-500/20 dark:text-success-500">
        {{ $page.props.flash.success }}
      </div>

      <!-- Tabs -->
      <div class="flex gap-2 border-b border-gray-200 dark:border-gray-700">
        <button @click="activeTab = 'active'" :class="['px-4 py-2 text-sm font-medium border-b-2 transition-colors', activeTab === 'active' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700']">
          Active RFQs <span class="ml-1 rounded-full bg-blue-100 text-blue-600 text-xs px-2 py-0.5">{{ rfqs.length }}</span>
        </button>
        <button @click="activeTab = 'unpaid'" :class="['px-4 py-2 text-sm font-medium border-b-2 transition-colors', activeTab === 'unpaid' ? 'border-amber-500 text-amber-600' : 'border-transparent text-gray-500 hover:text-gray-700']">
          Awaiting Commitment Fee <span class="ml-1 rounded-full bg-amber-100 text-amber-600 text-xs px-2 py-0.5">{{ unpaidRfqs.length }}</span>
        </button>
      </div>

      <!-- Active RFQs Table -->
      <div v-if="activeTab === 'active'" class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="max-w-full overflow-x-auto custom-scrollbar">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Product</p></th>
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Customer</p></th>
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">RFQ Status</p></th>
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Payment</p></th>
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Agent</p></th>
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Date</p></th>
                <th class="px-5 py-3 text-right sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Actions</p></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr v-for="rfq in rfqs" :key="rfq.id" class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-4 sm:px-6">
                  <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ rfq.product_name }}</p>
                  <p class="text-gray-500 text-theme-xs dark:text-gray-400">#{{ rfq.tracking_token.substring(0, 8) }}</p>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <p class="text-gray-800 text-theme-sm dark:text-white/90">{{ rfq.customer_name }}</p>
                  <p class="text-gray-500 text-theme-xs dark:text-gray-400">{{ rfq.customer_email }}</p>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <span :class="rfqStatusClass(rfq.status)">{{ rfq.status.replace(/_/g, ' ') }}</span>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <span :class="paymentStatusClass(rfq.payment_status)">
                    {{ rfq.payment_status === 'successful' ? '✓ Paid' : rfq.payment_status === 'failed' ? '✗ Failed' : rfq.payment_status === 'pending' ? '⏳ Pending' : '—' }}
                  </span>
                  <p v-if="rfq.payment_reference" class="text-gray-400 text-theme-xs mt-0.5">{{ rfq.payment_reference }}</p>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <p class="text-gray-800 text-theme-sm dark:text-white/90">{{ rfq.assigned_agent_name }}</p>
                </td>
                <td class="px-5 py-4 sm:px-6 whitespace-nowrap">
                  <p class="text-gray-500 text-theme-xs dark:text-gray-400">{{ rfq.created_at }}</p>
                </td>
                <td class="px-5 py-4 text-right sm:px-6">
                  <div class="flex items-center justify-end gap-3">
                    <Link :href="route('admin.rfqs.show', rfq.id)" class="inline-flex items-center gap-1.5 rounded bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-500 dark:hover:bg-blue-500/20 transition-colors">View</Link>
                    <button @click="openAssignModal(rfq)" class="inline-flex items-center gap-1.5 rounded bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">Assign</button>
                    <button @click="confirmDelete(rfq)" class="inline-flex items-center gap-1.5 rounded bg-error-50 px-3 py-1.5 text-xs font-medium text-error-600 hover:bg-error-100 dark:bg-error-500/10 dark:text-error-500 dark:hover:bg-error-500/20 transition-colors">Delete</button>
                  </div>
                </td>
              </tr>
              <tr v-if="rfqs.length === 0">
                <td colspan="7" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No active RFQs found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Unpaid RFQs Tab -->
      <div v-if="activeTab === 'unpaid'" class="overflow-hidden rounded-2xl border border-amber-200 bg-white dark:border-amber-800/40 dark:bg-white/[0.03]">
        <div class="px-5 py-3 bg-amber-50 dark:bg-amber-900/10 border-b border-amber-200 dark:border-amber-800/40">
          <p class="text-sm text-amber-700 dark:text-amber-400 font-medium">⚠ These RFQs are awaiting commitment fee payment from the customer. They will not enter the processing pipeline until payment is confirmed.</p>
        </div>
        <div class="max-w-full overflow-x-auto custom-scrollbar">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs uppercase tracking-wider">Product</p></th>
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs uppercase tracking-wider">Customer</p></th>
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs uppercase tracking-wider">Zainpay Status</p></th>
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs uppercase tracking-wider">Amount</p></th>
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs uppercase tracking-wider">Submitted</p></th>
                <th class="px-5 py-3 text-right sm:px-6"><p class="font-medium text-gray-500 text-theme-xs uppercase tracking-wider">Actions</p></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr v-for="rfq in unpaidRfqs" :key="rfq.id" class="hover:bg-amber-50/30 transition-colors">
                <td class="px-5 py-4 sm:px-6">
                  <p class="font-medium text-gray-800 text-theme-sm">{{ rfq.product_name }}</p>
                  <p class="text-gray-400 text-theme-xs">#{{ rfq.tracking_token.substring(0, 8) }}</p>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <p class="text-gray-800 text-theme-sm">{{ rfq.customer_name }}</p>
                  <p class="text-gray-500 text-theme-xs">{{ rfq.customer_email }}</p>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <span :class="paymentStatusClass(rfq.payment_status)">
                    {{ rfq.payment_status === 'successful' ? '✓ Paid (webhook pending)' : rfq.payment_status === 'failed' ? '✗ Failed' : rfq.payment_status === 'pending' ? '⏳ Awaiting Transfer' : '— No transaction' }}
                  </span>
                  <p v-if="rfq.payment_reference" class="text-gray-400 text-theme-xs mt-0.5">{{ rfq.payment_reference }}</p>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <p class="text-gray-800 text-theme-sm font-medium">{{ rfq.payment_amount ? '₦' + Number(rfq.payment_amount).toLocaleString() : '—' }}</p>
                </td>
                <td class="px-5 py-4 sm:px-6 whitespace-nowrap">
                  <p class="text-gray-500 text-theme-xs">{{ rfq.created_at }}</p>
                </td>
                <td class="px-5 py-4 text-right sm:px-6">
                  <div class="flex items-center justify-end gap-3">
                    <button @click="openVerifyModal(rfq)" class="inline-flex items-center gap-1.5 rounded bg-green-50 px-3 py-1.5 text-xs font-medium text-green-600 hover:bg-green-100 transition-colors">Verify Payment</button>
                    <Link :href="route('admin.rfqs.show', rfq.id)" class="inline-flex items-center gap-1.5 rounded bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-100 transition-colors">View</Link>
                  </div>
                </td>
              </tr>
              <tr v-if="unpaidRfqs.length === 0">
                <td colspan="6" class="px-5 py-8 text-center text-gray-500">No RFQs awaiting payment.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Assign Modal -->
    <div v-if="isAssignModalOpen" class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity">
      <div class="relative w-full max-w-[500px] overflow-y-auto rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-11 max-h-[90vh] shadow-xl">
        <button @click="isAssignModalOpen = false" class="transition-color absolute right-5 top-5 z-999 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:bg-white/[0.05] dark:text-gray-400 dark:hover:bg-white/[0.07] dark:hover:text-gray-300">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>

        <h3 class="mb-5 text-xl font-semibold text-gray-800 dark:text-white/90">Assign/Reassign Agent</h3>
        
        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
          <p class="text-sm text-gray-600 dark:text-gray-300"><strong>Product:</strong> {{ selectedRfq?.product_name }}</p>
          <p class="text-sm text-gray-600 dark:text-gray-300"><strong>Current Agent:</strong> {{ selectedRfq?.assigned_agent_name }}</p>
        </div>

        <form @submit.prevent="submitAssignForm" class="space-y-5">
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Select Agent</label>
            <select v-model="assignForm.assigned_agent_id" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" required>
              <option value="" disabled>Select an agent</option>
              <option v-for="agent in agents" :key="agent.id" :value="agent.id">{{ agent.name }}</option>
            </select>
            <div v-if="assignForm.errors.assigned_agent_id" class="text-error-500 text-xs mt-1">{{ assignForm.errors.assigned_agent_id }}</div>
          </div>
          
          <div class="flex items-center gap-3 mt-8">
            <button @click="isAssignModalOpen = false" type="button" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 sm:w-auto">Cancel</button>
            <button type="submit" class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto" :disabled="assignForm.processing">
              <span v-if="assignForm.processing">Saving...</span>
              <span v-else>Assign Agent</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="isDeleteModalOpen" class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity">
      <div class="relative w-full max-w-md rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8 shadow-xl text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-error-50 dark:bg-error-500/10 mb-6">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-error-500">
            <path d="M12 8V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 16.0195V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>

        <h3 class="mb-2 text-xl font-bold text-gray-800 dark:text-white/90">Delete RFQ</h3>
        <p class="mb-8 text-sm text-gray-500 dark:text-gray-400">
          Are you sure you want to delete the RFQ for <span class="font-semibold text-gray-800 dark:text-white">{{ rfqToDelete?.product_name }}</span>? This action cannot be undone.
        </p>

        <div class="flex items-center justify-center gap-3">
          <button @click="isDeleteModalOpen = false" type="button" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 sm:w-auto transition-colors">
            Cancel
          </button>
          <button @click="executeDelete" type="button" class="w-full rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-error-600 sm:w-auto transition-colors" :disabled="deleteForm.processing">
            <span v-if="deleteForm.processing">Deleting...</span>
            <span v-else>Yes, Delete</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Payment Verification Modal -->
    <div v-if="isVerifyModalOpen" class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity">
      <div class="relative w-full max-w-md rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8 shadow-xl text-center">
        <button @click="isVerifyModalOpen = false" class="absolute right-5 top-5 flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:bg-white/[0.05] dark:text-gray-400 dark:hover:bg-white/[0.07] dark:hover:text-gray-300 transition-colors">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>

        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-50 dark:bg-green-500/10 mb-6">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-green-500">
            <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>

        <h3 class="mb-2 text-xl font-bold text-gray-800 dark:text-white/90">Verify Payment</h3>
        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
          How would you like to verify the payment for <span class="font-semibold text-gray-800 dark:text-white">{{ rfqToVerify?.product_name }}</span>?
        </p>

        <div class="flex flex-col gap-3">
          <button @click="checkZainpayStatus" type="button" class="w-full flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-3 text-sm font-medium text-white hover:bg-blue-700 transition-colors" :disabled="verifyProcessing">
            <svg v-if="verifyProcessing === 'zainpay'" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span v-else>Refresh Zainpay Status</span>
          </button>
          
          <button @click="forceApprovePayment" type="button" class="w-full flex items-center justify-center gap-2 rounded-lg border-2 border-green-600 bg-transparent px-4 py-3 text-sm font-medium text-green-600 hover:bg-green-50 dark:hover:bg-green-500/10 transition-colors" :disabled="verifyProcessing">
            <svg v-if="verifyProcessing === 'force'" class="animate-spin -ml-1 mr-2 h-4 w-4 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span v-else>Force Approve (Manual Override)</span>
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import { Head, useForm, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  rfqs: Array,
  unpaidRfqs: Array,
  agents: Array
})

const activeTab = ref('active')

// Assign Modal State
const isAssignModalOpen = ref(false)
const selectedRfq = ref(null)

const assignForm = useForm({
  assigned_agent_id: ''
})

// Delete Modal State
const isDeleteModalOpen = ref(false)
const rfqToDelete = ref(null)
const deleteForm = useForm({})

// Verify Modal State
const isVerifyModalOpen = ref(false)
const rfqToVerify = ref(null)
const verifyProcessing = ref(false)

const openAssignModal = (rfq) => {
  selectedRfq.value = rfq
  assignForm.assigned_agent_id = rfq.assigned_agent_id || ''
  assignForm.clearErrors()
  isAssignModalOpen.value = true
}

const submitAssignForm = () => {
  assignForm.put(route('admin.rfqs.update', selectedRfq.value.id), {
    onSuccess: () => isAssignModalOpen.value = false,
    preserveScroll: true
  })
}

const openVerifyModal = (rfq) => {
  rfqToVerify.value = rfq
  isVerifyModalOpen.value = true
}

const checkZainpayStatus = () => {
  if (rfqToVerify.value) {
    verifyProcessing.value = 'zainpay'
    router.post(route('admin.rfqs.check-zainpay', rfqToVerify.value.id), {}, {
      preserveScroll: true,
      onFinish: () => {
        verifyProcessing.value = false
        isVerifyModalOpen.value = false
      }
    })
  }
}

const forceApprovePayment = () => {
  if (rfqToVerify.value) {
    verifyProcessing.value = 'force'
    router.post(route('admin.rfqs.verify-payment', rfqToVerify.value.id), {}, {
      preserveScroll: true,
      onFinish: () => {
        verifyProcessing.value = false
        isVerifyModalOpen.value = false
      }
    })
  }
}

const confirmDelete = (rfq) => {
  rfqToDelete.value = rfq
  isDeleteModalOpen.value = true
}

const executeDelete = () => {
  if (rfqToDelete.value) {
    deleteForm.delete(route('admin.rfqs.destroy', rfqToDelete.value.id), {
      onSuccess: () => {
        isDeleteModalOpen.value = false
        rfqToDelete.value = null
      },
      preserveScroll: true
    })
  }
}

const rfqStatusClass = (status) => {
  const s = (status || '').toLowerCase()
  const base = 'inline-flex items-center rounded-full px-2 py-0.5 text-theme-xs font-medium capitalize'
  if (s === 'pending') return `${base} bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500`
  if (s === 'commitment_fee_pending') return `${base} bg-amber-50 text-amber-600`
  if (s === 'awaiting_payment') return `${base} bg-amber-100 text-amber-700`
  if (s === 'payment_received') return `${base} bg-green-100 text-green-700`
  if (s === 'assigned') return `${base} bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-500`
  if (s === 'completed') return `${base} bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500`
  if (s === 'not_found') return `${base} bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-500`
  if (s === 'queued') return `${base} bg-purple-50 text-purple-600`
  if (s === 'sourcing') return `${base} bg-indigo-50 text-indigo-600`
  if (s === 'purchased') return `${base} bg-teal-50 text-teal-600`
  if (s === 'shipped') return `${base} bg-cyan-50 text-cyan-600`
  if (s === 'quoted') return `${base} bg-orange-50 text-orange-600`
  return `${base} bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400`
}

const paymentStatusClass = (status) => {
  const base = 'inline-flex items-center rounded-full px-2 py-0.5 text-theme-xs font-medium'
  if (status === 'successful') return `${base} bg-green-100 text-green-700`
  if (status === 'failed') return `${base} bg-red-100 text-red-700`
  if (status === 'pending') return `${base} bg-amber-100 text-amber-700`
  return `${base} bg-gray-100 text-gray-500`
}
</script>
