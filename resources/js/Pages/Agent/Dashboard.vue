<template>
  <AdminLayout>
    <Head title="Agent Dashboard" />
    <div class="space-y-6">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Agent Dashboard</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">Manage your assigned requests and sourcing tasks.</p>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 md:gap-6">
        <div v-for="(stat, index) in stats" :key="index" class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 shadow-sm">
          <div class="flex items-center gap-4">
            <div :class="[
              'flex h-12 w-12 items-center justify-center rounded-xl',
              stat.color === 'blue' ? 'bg-blue-50 text-blue-500 dark:bg-blue-500/10' :
              stat.color === 'orange' ? 'bg-orange-50 text-orange-500 dark:bg-orange-500/10' :
              stat.color === 'purple' ? 'bg-purple-50 text-purple-500 dark:bg-purple-500/10' :
              'bg-green-50 text-green-500 dark:bg-green-500/10'
            ]">
              <span class="text-xl font-bold">{{ stat.value }}</span>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ stat.title }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- RFQs Table -->
      <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">My Assigned RFQs</h3>
        </div>
        <div class="max-w-full overflow-x-auto custom-scrollbar">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                <th class="px-5 py-3 text-left sm:px-6 text-sm font-medium text-gray-500 uppercase">Tracking Token</th>
                <th class="px-5 py-3 text-left sm:px-6 text-sm font-medium text-gray-500 uppercase">Customer</th>
                <th class="px-5 py-3 text-left sm:px-6 text-sm font-medium text-gray-500 uppercase">Product</th>
                <th class="px-5 py-3 text-left sm:px-6 text-sm font-medium text-gray-500 uppercase">Status</th>
                <th class="px-5 py-3 text-left sm:px-6 text-sm font-medium text-gray-500 uppercase">Created</th>
                <th class="px-5 py-3 text-right sm:px-6 text-sm font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr v-for="rfq in rfqs" :key="rfq.id" class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-4 sm:px-6 text-sm">
                  <span class="font-mono text-gray-600 dark:text-gray-400">ELB-{{ rfq.tracking_token.substring(0, 8).toUpperCase() }}</span>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <p class="font-medium text-gray-800 dark:text-white/90">{{ rfq.customer?.name || 'N/A' }}</p>
                  <p class="text-xs text-gray-500">{{ rfq.customer?.email }}</p>
                  <p v-if="rfq.customer?.whatsapp_number" class="text-xs text-brand-500">{{ rfq.customer?.whatsapp_number }}</p>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <p class="text-sm font-medium text-gray-800 dark:text-white/90 truncate max-w-[200px]" :title="rfq.product_name">{{ rfq.product_name }}</p>
                  <p class="text-xs text-gray-500">Qty: {{ rfq.quantity }}</p>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <span :class="[
                    'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium capitalize',
                    rfq.status === 'completed' ? 'bg-green-100 text-green-700' :
                    rfq.status === 'assigned' ? 'bg-blue-100 text-blue-700' :
                    rfq.status === 'sourcing' ? 'bg-purple-100 text-purple-700' :
                    rfq.status === 'purchased' ? 'bg-indigo-100 text-indigo-700' :
                    rfq.status === 'shipped' ? 'bg-orange-100 text-orange-700' :
                    rfq.status === 'not_found' ? 'bg-red-100 text-red-700' :
                    'bg-gray-100 text-gray-700'
                  ]">
                    {{ rfq.status.replace('_', ' ') }}
                  </span>
                </td>
                <td class="px-5 py-4 sm:px-6 text-sm text-gray-500">
                  {{ rfq.created_at_formatted }}
                </td>
                <td class="px-5 py-4 text-right sm:px-6">
                  <button @click="openStatusModal(rfq)" class="inline-flex items-center gap-1.5 rounded bg-brand-50 text-brand-600 px-3 py-1.5 text-xs font-medium hover:bg-brand-100 transition-colors">
                    View / Update
                  </button>
                </td>
              </tr>
              <tr v-if="rfqs.length === 0">
                <td colspan="6" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                  No RFQs assigned yet.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- View / Update Modal -->
    <div v-if="isModalOpen" class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity">
      <div class="no-scrollbar relative w-full max-w-2xl overflow-y-auto rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8 max-h-[90vh] shadow-xl">
        <button @click="isModalOpen = false" class="absolute right-5 top-5 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6L6 18M6 6l12 12"/>
          </svg>
        </button>

        <h3 class="mb-6 text-xl font-semibold text-gray-800 dark:text-white/90">RFQ Details & Status Update</h3>
        
        <div v-if="selectedRfq" class="space-y-6">
          <!-- Customer & Request Summary Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/30">
              <h4 class="font-semibold text-gray-800 dark:text-white mb-3 flex items-center gap-1.5">
                <i class="fa-solid fa-user-tie text-brand-500 mr-1.5"></i> Customer Details
              </h4>
              <div class="space-y-2 text-gray-600 dark:text-gray-300">
                <p><span class="text-gray-400 dark:text-gray-500 block text-xs">Name</span><strong class="text-gray-800 dark:text-white font-medium">{{ selectedRfq.customer?.name }}</strong></p>
                <p><span class="text-gray-400 dark:text-gray-500 block text-xs">Email</span>{{ selectedRfq.customer?.email || 'N/A' }}</p>
                <p>
                  <span class="text-gray-400 dark:text-gray-500 block text-xs">WhatsApp</span>
                  <a v-if="selectedRfq.customer?.whatsapp_number" :href="`https://wa.me/${formatWhatsApp(selectedRfq.customer?.whatsapp_number)}`" target="_blank" class="text-brand-500 hover:underline inline-flex items-center gap-1 font-medium mt-0.5 animate-pulse">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.558 4.116 1.535 5.846L.057 23.625a.75.75 0 00.92.92l5.78-1.477A11.955 11.955 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75c-1.99 0-3.865-.553-5.463-1.515l-.392-.232-4.054 1.036 1.054-3.953-.255-.406A9.712 9.712 0 012.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/></svg>
                    {{ selectedRfq.customer?.whatsapp_number }}
                  </a>
                  <span v-else>N/A</span>
                </p>
                <p v-if="selectedRfq.company_name"><span class="text-gray-400 dark:text-gray-500 block text-xs">Customer Company</span>{{ selectedRfq.company_name }}</p>
              </div>
            </div>

            <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/30">
              <h4 class="font-semibold text-gray-800 dark:text-white mb-3 flex items-center gap-1.5">
                <i class="fa-solid fa-file-invoice text-brand-500 mr-1.5"></i> Request Summary
              </h4>
              <div class="space-y-2 text-gray-600 dark:text-gray-300">
                <p><span class="text-gray-400 dark:text-gray-500 block text-xs">Product Name</span><strong class="text-gray-800 dark:text-white font-medium">{{ selectedRfq.product_name }}</strong></p>
                <p><span class="text-gray-400 dark:text-gray-500 block text-xs">Tracking ID</span><code>ELB-{{ selectedRfq.tracking_token.substring(0, 8).toUpperCase() }}</code></p>
                <p v-if="selectedRfq.location"><span class="text-gray-400 dark:text-gray-500 block text-xs">Delivery Destination (Nigeria)</span><i class="fa-solid fa-location-dot text-brand-500 mr-1"></i> {{ selectedRfq.location }}</p>
                <p><span class="text-gray-400 dark:text-gray-500 block text-xs">Date Submitted</span>{{ selectedRfq.created_at_formatted }}</p>
              </div>
            </div>
          </div>

          <!-- Specs & Requirements Details -->
          <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/30 text-sm">
            <h4 class="font-semibold text-gray-800 dark:text-white mb-3 flex items-center gap-1.5">
              <i class="fa-solid fa-clipboard-list text-brand-500 mr-1.5"></i> Requirements & Specs
            </h4>
            <div class="space-y-4">
              <div>
                <span class="block text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Specifications</span>
                <p class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap bg-white dark:bg-gray-900 rounded-lg p-3 border border-gray-150 dark:border-gray-800">{{ selectedRfq.specifications }}</p>
              </div>

              <div v-if="selectedRfq.additional_requirements">
                <span class="block text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Additional Requirements</span>
                <p class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap bg-white dark:bg-gray-900 rounded-lg p-3 border border-gray-150 dark:border-gray-800">{{ selectedRfq.additional_requirements }}</p>
              </div>

              <div v-if="selectedRfq.image_url" class="mt-4 border-t border-gray-100 dark:border-gray-800 pt-3">
                <span class="block text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Attached Image</span>
                <div class="p-2 bg-white dark:bg-gray-900 rounded-lg inline-block border border-gray-150 dark:border-gray-800 mt-1">
                  <a :href="selectedRfq.image_url" target="_blank">
                    <img :src="selectedRfq.image_url" alt="RFQ Image" class="max-h-48 object-contain rounded-md hover:opacity-90 transition-opacity">
                  </a>
                </div>
              </div>

              <div class="grid grid-cols-3 gap-4 border-t border-gray-100 dark:border-gray-800 pt-3 text-center">
                <div>
                  <span class="block text-xs text-gray-400 dark:text-gray-500 mb-0.5">Quantity</span>
                  <span class="font-semibold text-gray-800 dark:text-white capitalize">{{ selectedRfq.quantity }}</span>
                </div>
                <div>
                  <span class="block text-xs text-gray-400 dark:text-gray-500 mb-0.5">Delivery Method</span>
                  <span class="font-semibold text-gray-800 dark:text-white capitalize">{{ selectedRfq.delivery_method }}</span>
                </div>
                <div>
                  <span class="block text-xs text-gray-400 dark:text-gray-500 mb-0.5">Target Price</span>
                  <span class="font-semibold text-gray-800 dark:text-white">{{ selectedRfq.target_price || 'N/A' }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Claude AI Sourcing Report -->
          <div v-if="selectedRfq.ai_summary || (selectedRfq.ai_suppliers && selectedRfq.ai_suppliers.length > 0)" class="rounded-xl border border-brand-100 bg-brand-50/30 p-5 dark:border-brand-500/10 dark:bg-brand-500/[0.02]">
            <div class="flex items-center gap-2 mb-3">
              <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/20 dark:text-brand-400">
                <i class="fa-solid fa-wand-magic-sparkles text-brand-600 dark:text-brand-400"></i>
              </span>
              <h4 class="text-sm font-semibold text-brand-900 dark:text-brand-300">Claude AI Sourcing Intelligence</h4>
            </div>
            
            <div v-if="selectedRfq.ai_summary" class="prose prose-sm dark:prose-invert max-w-none text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-4" v-html="formatMarkdown(selectedRfq.ai_summary)">
            </div>

            <div v-if="selectedRfq.ai_suppliers && selectedRfq.ai_suppliers.length > 0" class="space-y-3 mt-4">
              <h5 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Recommended Chinese Suppliers ({{ selectedRfq.ai_suppliers.length }})</h5>
              <div v-for="(supplier, idx) in selectedRfq.ai_suppliers" :key="idx" class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900/60">
                <div class="flex items-start justify-between gap-2 mb-1.5">
                  <span class="text-sm font-semibold text-gray-800 dark:text-white">{{ supplier.company_name }}</span>
                  <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400 font-medium">
                    <i class="fa-solid fa-location-dot text-brand-500 mr-1"></i> {{ supplier.location || 'China' }}
                  </span>
                </div>
                <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-gray-600 dark:text-gray-400 mb-2">
                  <div><strong class="text-gray-700 dark:text-gray-300">Specialization:</strong> {{ supplier.specialization || 'N/A' }}</div>
                  <div><strong class="text-gray-700 dark:text-gray-300">Estimated MOQ:</strong> {{ supplier.estimated_moq || 'N/A' }}</div>
                  <div class="col-span-2 mt-0.5"><strong class="text-gray-700 dark:text-gray-300">Sourcing Hint:</strong> <span class="italic text-gray-500">{{ supplier.contact_hint }}</span></div>
                </div>
                <div v-if="supplier.why_recommended" class="rounded bg-green-50/50 p-2 text-xs text-green-700 dark:bg-green-500/5 dark:text-green-400">
                  <strong>Why Recommended:</strong> {{ supplier.why_recommended }}
                </div>
              </div>
            </div>
          </div>

          <form @submit.prevent="submitStatusUpdate" class="space-y-4 border-t border-gray-100 dark:border-gray-800 pt-6">
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Update Status</label>
              <select v-model="form.status" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" required>
                <option value="pending">Pending</option>
                <option value="sourcing">Sourcing (Looking for suppliers)</option>
                <option value="not_found">Not Found (Item unavailable)</option>
                <option value="purchased">Purchased</option>
                <option value="shipped">Shipped</option>
                <option value="completed">Completed</option>
              </select>
              <div v-if="form.errors.status" class="text-error-500 text-xs mt-1">{{ form.errors.status }}</div>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Internal Notes (Optional)</label>
              <textarea v-model="form.notes" rows="3" class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" placeholder="Add any private notes here..."></textarea>
            </div>

            <div class="flex items-center gap-3 mt-6">
              <button @click="isModalOpen = false" type="button" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 sm:w-auto">Cancel</button>
              <button type="submit" class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto" :disabled="form.processing">
                <span v-if="form.processing">Saving...</span>
                <span v-else>Update Status</span>
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
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  rfqs: Array,
  stats: Array
})

const isModalOpen = ref(false)
const selectedRfq = ref(null)

const form = useForm({
  status: '',
  notes: ''
})

const openStatusModal = (rfq) => {
  selectedRfq.value = rfq
  form.status = rfq.status
  form.notes = '' // Optional: fetch existing notes if added to model
  form.clearErrors()
  isModalOpen.value = true
}

const submitStatusUpdate = () => {
  if (selectedRfq.value) {
    form.patch(route('agent.rfqs.update-status', selectedRfq.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        isModalOpen.value = false
      }
    })
  }
}

const formatWhatsApp = (number) => {
  if (!number) return '';
  let clean = number.replace(/\D/g, '');
  if (clean.startsWith('0')) {
    clean = '234' + clean.substring(1);
  }
  return clean;
}

const formatMarkdown = (text) => {
  if (!text) return '';
  let formatted = text
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    .replace(/\n\n/g, '</p><p class="mt-4">')
    .replace(/\n/g, '<br>')
    .replace(/- (.*?)<br>/g, '<li class="ml-4 list-disc">$1</li>');
  
  return `<p>${formatted}</p>`;
}
</script>
