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

      <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="max-w-full overflow-x-auto custom-scrollbar">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                <th class="px-5 py-3 text-left sm:px-6">
                  <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Product</p>
                </th>
                <th class="px-5 py-3 text-left sm:px-6">
                  <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Customer</p>
                </th>
                <th class="px-5 py-3 text-left sm:px-6">
                  <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Status</p>
                </th>
                <th class="px-5 py-3 text-left sm:px-6">
                  <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Agent</p>
                </th>
                <th class="px-5 py-3 text-left sm:px-6">
                  <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Date</p>
                </th>
                <th class="px-5 py-3 text-right sm:px-6">
                  <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Actions</p>
                </th>
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
                  <span :class="[
                    'inline-flex items-center rounded-full px-2 py-0.5 text-theme-xs font-medium capitalize',
                    rfq.status.toLowerCase() === 'pending' ? 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500' :
                    rfq.status.toLowerCase() === 'assigned' ? 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-500' :
                    rfq.status.toLowerCase() === 'completed' ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' :
                    rfq.status.toLowerCase() === 'not_found' ? 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-500' :
                    'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
                  ]">
                    {{ (rfq.status.value || rfq.status).replace('_', ' ') }}
                  </span>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <p class="text-gray-800 text-theme-sm dark:text-white/90">{{ rfq.assigned_agent_name }}</p>
                </td>
                <td class="px-5 py-4 sm:px-6 whitespace-nowrap">
                  <p class="text-gray-500 text-theme-xs dark:text-gray-400">{{ rfq.created_at }}</p>
                </td>
                <td class="px-5 py-4 text-right sm:px-6">
                  <div class="flex items-center justify-end gap-3">
                    <Link :href="route('admin.rfqs.show', rfq.id)" class="inline-flex items-center gap-1.5 rounded bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-500 dark:hover:bg-blue-500/20 transition-colors">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                      View
                    </Link>
                    <button @click="openAssignModal(rfq)" class="inline-flex items-center gap-1.5 rounded bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 4V20M20 12H4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                      Assign
                    </button>
                    <button @click="confirmDelete(rfq)" class="inline-flex items-center gap-1.5 rounded bg-error-50 px-3 py-1.5 text-xs font-medium text-error-600 hover:bg-error-100 dark:bg-error-500/10 dark:text-error-500 dark:hover:bg-error-500/20 transition-colors">
                      <svg width="14" height="14" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13.5 4.5L4.5 13.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M4.5 4.5L13.5 13.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="rfqs.length === 0">
                <td colspan="6" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                  No RFQs found.
                </td>
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
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  rfqs: Array,
  agents: Array
})

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
</script>
