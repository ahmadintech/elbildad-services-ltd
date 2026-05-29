<template>
  <AdminLayout>
    <Head title="Finance Estimates" />
    <div class="space-y-6">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Estimates</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">View and manage Zoho Estimates.</p>
        </div>
        <Link href="/finance/estimates/create" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          Create Estimate
        </Link>
      </div>

      <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="max-w-full overflow-x-auto custom-scrollbar">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Date</p></th>
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Customer</p></th>
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Amount</p></th>
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Status</p></th>
                <th class="px-5 py-3 text-right sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Actions</p></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr v-for="estimate in estimates" :key="estimate.id" class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-4 sm:px-6">
                  <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ formatDate(estimate.created_at) }}</p>
                  <p class="text-gray-500 text-theme-xs dark:text-gray-400">ID: {{ estimate.zoho_estimate_id }}</p>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <p class="text-gray-800 text-theme-sm dark:text-gray-400 font-medium">{{ estimate.customer?.name || 'Unknown' }}</p>
                  <p class="text-gray-500 text-theme-xs dark:text-gray-400">{{ estimate.customer?.email }}</p>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <p class="text-gray-800 text-theme-sm dark:text-gray-400 font-semibold">{{ formatCurrency(estimate.total || 0) }}</p>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <span :class="getStatusClass(estimate.status)" class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium uppercase">
                    {{ estimate.status }}
                  </span>
                </td>
                <td class="px-5 py-4 text-right sm:px-6">
                  <div class="flex items-center justify-end gap-3">
                    <button
                      @click="viewEstimate(estimate)"
                      :disabled="isLoading(estimate.id)"
                      class="inline-flex items-center gap-1 text-brand-500 hover:text-brand-600 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <svg v-if="isLoading(estimate.id, 'view')" class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                      </svg>
                      View
                    </button>

                    <!-- Send -->
                    <button
                      v-if="estimate.status === 'draft'"
                      @click="sendEstimate(estimate)"
                      :disabled="isLoading(estimate.id)"
                      class="inline-flex items-center gap-1 text-brand-500 hover:text-brand-600 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <svg v-if="isLoading(estimate.id, 'send')" class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                      </svg>
                      Send
                    </button>

                    <!-- Accept -->
                    <button
                      v-if="estimate.status === 'sent'"
                      @click="acceptEstimate(estimate)"
                      :disabled="isLoading(estimate.id)"
                      class="inline-flex items-center gap-1 text-success-500 hover:text-success-600 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <svg v-if="isLoading(estimate.id, 'accept')" class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                      </svg>
                      Accept
                    </button>

                    <!-- Decline -->
                    <button
                      v-if="estimate.status === 'sent'"
                      @click="declineEstimate(estimate)"
                      :disabled="isLoading(estimate.id)"
                      class="inline-flex items-center gap-1 text-warning-500 hover:text-warning-600 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <svg v-if="isLoading(estimate.id, 'decline')" class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                      </svg>
                      Decline
                    </button>

                    <!-- Delete -->
                    <button
                      v-if="!$page.props.auth.user.roles.some(r => ['agent','super_agent'].includes(r))"
                      @click="confirmDelete(estimate)"
                      :disabled="isLoading(estimate.id)"
                      class="inline-flex items-center gap-1 text-error-400 hover:text-error-500 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="estimates.length === 0">
                <td colspan="5" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                  No estimates found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <div v-if="isDeleteModalOpen" class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity">
      <div class="relative w-full max-w-md rounded-3xl bg-white p-8 text-center shadow-xl dark:bg-gray-900">
        <h3 class="mb-2 text-xl font-bold text-gray-800 dark:text-white/90">Delete Estimate</h3>
        <p class="mb-8 text-sm text-gray-500 dark:text-gray-400">Are you sure you want to delete this estimate? It will also be deleted from Zoho.</p>
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
  estimates: Array
})

const actionForm = useForm({})

const isDeleteModalOpen = ref(false)
const estimateToDelete = ref(null)

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
    case 'accepted': return 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400'
    case 'declined': return 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-400'
    case 'sent': return 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-400'
    default: return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400'
  }
}

const viewEstimate = (estimate) => {
  loadingAction.value = `${estimate.id}-view`
  router.visit(`/finance/estimates/${estimate.id}`)
}

const sendEstimate = (estimate) => {
  loadingAction.value = `${estimate.id}-send`
  actionForm.post(route('finance.estimates.send', estimate.id), {
    preserveScroll: true,
    onFinish: () => { loadingAction.value = null }
  })
}

const acceptEstimate = (estimate) => {
  loadingAction.value = `${estimate.id}-accept`
  actionForm.post(route('finance.estimates.accept', estimate.id), {
    preserveScroll: true,
    onFinish: () => { loadingAction.value = null }
  })
}

const declineEstimate = (estimate) => {
  loadingAction.value = `${estimate.id}-decline`
  actionForm.post(route('finance.estimates.decline', estimate.id), {
    preserveScroll: true,
    onFinish: () => { loadingAction.value = null }
  })
}

const confirmDelete = (estimate) => {
  estimateToDelete.value = estimate
  isDeleteModalOpen.value = true
}

const executeDelete = () => {
  if (estimateToDelete.value) {
    actionForm.delete(route('finance.estimates.destroy', estimateToDelete.value.id), {
      onSuccess: () => {
        isDeleteModalOpen.value = false
        estimateToDelete.value = null
      },
      preserveScroll: true
    })
  }
}
</script>
