<template>
  <AdminLayout>
    <Head title="RFQ Details" />
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="flex items-center gap-3">
            <Link :href="route('admin.rfqs.index')" class="text-gray-500 hover:text-brand-500 transition-colors">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </Link>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
              RFQ Details: {{ rfq.product_name }}
            </h2>
          </div>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-8">Tracking Token: {{ rfq.tracking_token }}</p>
        </div>
        
        <div class="flex items-center gap-3">
          <span :class="[
            'inline-flex items-center rounded-full px-3 py-1 text-sm font-medium',
            rfq.status.toLowerCase() === 'pending' ? 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500' :
            rfq.status.toLowerCase() === 'assigned' ? 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-500' :
            rfq.status.toLowerCase() === 'completed' ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' :
            rfq.status.toLowerCase() === 'not_found' ? 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-500' :
            'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
          ]">
            {{ rfq.status.replace('_', ' ') }}
          </span>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Request Details -->
          <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Request Details</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Product Name</p>
                <p class="font-medium text-gray-800 dark:text-white">{{ rfq.product_name }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Category</p>
                <p class="font-medium text-gray-800 dark:text-white">{{ rfq.category?.name || 'Uncategorized' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Quantity</p>
                <p class="font-medium text-gray-800 dark:text-white">{{ rfq.quantity }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Target Price</p>
                <p class="font-medium text-gray-800 dark:text-white">{{ rfq.target_price || 'Not specified' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Delivery Method</p>
                <p class="font-medium text-gray-800 dark:text-white">{{ rfq.delivery_method }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Delivery Location</p>
                <p class="font-medium text-gray-800 dark:text-white">{{ rfq.location || 'Not specified' }}</p>
              </div>
            </div>

            <div class="mb-6">
              <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Specifications</p>
              <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl text-gray-700 dark:text-gray-300 whitespace-pre-wrap text-sm">
                {{ rfq.specifications }}
              </div>
            </div>

            <div v-if="rfq.image_url" class="mb-6">
              <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Attached Image</p>
              <div class="p-2 bg-gray-50 dark:bg-gray-800/50 rounded-xl inline-block border border-gray-100 dark:border-gray-800">
                <a :href="rfq.image_url" target="_blank">
                  <img :src="rfq.image_url" alt="RFQ Image" class="max-h-64 object-contain rounded-lg hover:opacity-90 transition-opacity">
                </a>
              </div>
            </div>

            <div v-if="rfq.additional_requirements">
              <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Additional Requirements</p>
              <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl text-gray-700 dark:text-gray-300 whitespace-pre-wrap text-sm">
                {{ rfq.additional_requirements }}
              </div>
            </div>
          </div>

          <div v-if="rfq.ai_summary" class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
             <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">AI Sourcing Summary</h3>
             <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300" v-html="formatMarkdown(rfq.ai_summary)"></div>
          </div>
          <div v-else class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
             <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">AI Sourcing Summary</h3>
             <div class="text-gray-500 dark:text-gray-400 italic text-sm">
               AI analysis is currently pending or unavailable for this RFQ.
             </div>
          </div>

          <!-- Best Supplier -->
          <div v-if="bestSupplier" class="rounded-2xl border border-yellow-200 bg-yellow-50/50 p-6 dark:border-yellow-500/20 dark:bg-yellow-500/5">
            <div class="flex items-center gap-3 mb-4">
              <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-yellow-100 text-yellow-600 dark:bg-yellow-500/20 dark:text-yellow-400">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-gray-800 dark:text-white">⭐ Best Recommended Supplier</h3>
            </div>
            
            <div class="rounded-xl border border-yellow-200 dark:border-yellow-700/50 bg-white dark:bg-gray-800/50 p-4">
              <div v-if="bestSupplier.company_name" class="space-y-3">
                <div>
                  <h5 class="font-semibold text-gray-800 dark:text-white text-base">{{ bestSupplier.company_name }}</h5>
                  <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ bestSupplier.location }}
                  </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                  <div>
                    <span class="text-gray-500 dark:text-gray-400 block text-xs mb-0.5">Specialization</span>
                    <span class="text-gray-700 dark:text-gray-300">{{ bestSupplier.specialization }}</span>
                  </div>
                  <div>
                    <span class="text-gray-500 dark:text-gray-400 block text-xs mb-0.5">Estimated MOQ</span>
                    <span class="text-gray-700 dark:text-gray-300">{{ bestSupplier.estimated_moq }}</span>
                  </div>
                  <div>
                    <span class="text-gray-500 dark:text-gray-400 block text-xs mb-0.5">Phone</span>
                    <span class="text-gray-700 dark:text-gray-300">{{ bestSupplier.phone || 'N/A' }}</span>
                  </div>
                  <div>
                    <span class="text-gray-500 dark:text-gray-400 block text-xs mb-0.5">WhatsApp</span>
                    <span class="text-gray-700 dark:text-gray-300">{{ bestSupplier.whatsapp || 'N/A' }}</span>
                  </div>
                </div>
                
                <div class="text-sm">
                  <span class="text-gray-500 dark:text-gray-400 block text-xs mb-0.5">Why Recommended</span>
                  <span class="text-gray-700 dark:text-gray-300">{{ bestSupplier.why_recommended }}</span>
                </div>

                <div v-if="bestSupplier.selection_reason" class="text-sm">
                  <span class="text-gray-500 dark:text-gray-400 block text-xs mb-0.5">Selection Reason</span>
                  <span class="text-gray-700 dark:text-gray-300 italic">{{ bestSupplier.selection_reason }}</span>
                </div>
                
                <div class="mt-2 text-sm bg-yellow-50 dark:bg-yellow-500/10 p-2.5 rounded-lg text-yellow-700 dark:text-yellow-300 flex items-start gap-2">
                  <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span><span class="font-medium">Hint:</span> {{ bestSupplier.contact_hint }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Claude AI Suppliers -->
          <div v-if="claudeSuppliers.length > 0" class="rounded-2xl border border-brand-200 bg-brand-50/50 p-6 dark:border-brand-500/20 dark:bg-brand-500/5">
            <div class="flex items-center gap-3 mb-4">
              <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 text-brand-600 dark:bg-brand-500/20 dark:text-brand-400">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Claude AI Sourcing</h3>
            </div>
            
            <ul class="space-y-4">
              <li v-for="(supplier, idx) in claudeSuppliers" :key="idx" class="rounded-xl border border-gray-200 dark:border-gray-700/50 bg-white dark:bg-gray-800/50 p-4">
                <div v-if="supplier.company_name" class="space-y-3">
                  <div>
                    <h5 class="font-semibold text-gray-800 dark:text-white text-base">{{ supplier.company_name }}</h5>
                    <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-1">
                      <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                      {{ supplier.location }}
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div>
                      <span class="text-gray-500 dark:text-gray-400 block text-xs mb-0.5">Specialization</span>
                      <span class="text-gray-700 dark:text-gray-300">{{ supplier.specialization }}</span>
                    </div>
                    <div>
                      <span class="text-gray-500 dark:text-gray-400 block text-xs mb-0.5">Estimated MOQ</span>
                      <span class="text-gray-700 dark:text-gray-300">{{ supplier.estimated_moq }}</span>
                    </div>
                    <div>
                      <span class="text-gray-500 dark:text-gray-400 block text-xs mb-0.5">Phone</span>
                      <span class="text-gray-700 dark:text-gray-300">{{ supplier.phone || 'N/A' }}</span>
                    </div>
                    <div>
                      <span class="text-gray-500 dark:text-gray-400 block text-xs mb-0.5">WhatsApp</span>
                      <span class="text-gray-700 dark:text-gray-300">{{ supplier.whatsapp || 'N/A' }}</span>
                    </div>
                  </div>
                  
                  <div class="text-sm">
                    <span class="text-gray-500 dark:text-gray-400 block text-xs mb-0.5">Why Recommended</span>
                    <span class="text-gray-700 dark:text-gray-300">{{ supplier.why_recommended }}</span>
                  </div>
                  
                  <div class="mt-2 text-sm bg-brand-50 dark:bg-brand-500/10 p-2.5 rounded-lg text-brand-700 dark:text-brand-300 flex items-start gap-2">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span><span class="font-medium">Hint:</span> {{ supplier.contact_hint }}</span>
                  </div>
                </div>
                <div v-else class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                  <span class="text-brand-500 mt-0.5">•</span>
                  <span>{{ supplier.raw || supplier }}</span>
                </div>
              </li>
            </ul>
          </div>

          <!-- Qwen AI Suppliers -->
          <div v-if="qwenSuppliers.length > 0" class="rounded-2xl border border-purple-200 bg-purple-50/50 p-6 dark:border-purple-500/20 dark:bg-purple-500/5">
            <div class="flex items-center gap-3 mb-4">
              <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Qwen AI Sourcing</h3>
            </div>
            
            <ul class="space-y-4">
              <li v-for="(supplier, idx) in qwenSuppliers" :key="idx" class="rounded-xl border border-gray-200 dark:border-gray-700/50 bg-white dark:bg-gray-800/50 p-4">
                <div v-if="supplier.company_name" class="space-y-3">
                  <div>
                    <h5 class="font-semibold text-gray-800 dark:text-white text-base">{{ supplier.company_name }}</h5>
                    <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-1">
                      <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                      {{ supplier.location }}
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div>
                      <span class="text-gray-500 dark:text-gray-400 block text-xs mb-0.5">Specialization</span>
                      <span class="text-gray-700 dark:text-gray-300">{{ supplier.specialization }}</span>
                    </div>
                    <div>
                      <span class="text-gray-500 dark:text-gray-400 block text-xs mb-0.5">Estimated MOQ</span>
                      <span class="text-gray-700 dark:text-gray-300">{{ supplier.estimated_moq }}</span>
                    </div>
                    <div>
                      <span class="text-gray-500 dark:text-gray-400 block text-xs mb-0.5">Phone</span>
                      <span class="text-gray-700 dark:text-gray-300">{{ supplier.phone || 'N/A' }}</span>
                    </div>
                    <div>
                      <span class="text-gray-500 dark:text-gray-400 block text-xs mb-0.5">WhatsApp</span>
                      <span class="text-gray-700 dark:text-gray-300">{{ supplier.whatsapp || 'N/A' }}</span>
                    </div>
                  </div>
                  
                  <div class="text-sm">
                    <span class="text-gray-500 dark:text-gray-400 block text-xs mb-0.5">Why Recommended</span>
                    <span class="text-gray-700 dark:text-gray-300">{{ supplier.why_recommended }}</span>
                  </div>
                  
                  <div class="mt-2 text-sm bg-purple-50 dark:bg-purple-500/10 p-2.5 rounded-lg text-purple-700 dark:text-purple-300 flex items-start gap-2">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span><span class="font-medium">Hint:</span> {{ supplier.contact_hint }}</span>
                  </div>
                </div>
                <div v-else class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                  <span class="text-purple-500 mt-0.5">•</span>
                  <span>{{ supplier.raw || supplier }}</span>
                </div>
              </li>
            </ul>
          </div>

        </div>

        <!-- Sidebar Details -->
        <div class="space-y-6">
          <!-- Customer Info -->
          <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Customer Info</h3>
            
            <div v-if="rfq.customer" class="space-y-4">
              <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 font-semibold">
                  {{ rfq.customer.name.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <p class="font-medium text-gray-800 dark:text-white">{{ rfq.customer.name }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400" v-if="rfq.company_name">{{ rfq.company_name }}</p>
                </div>
              </div>
              
              <div class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-3 text-sm">
                <div class="flex items-start gap-3 text-gray-600 dark:text-gray-400" v-if="rfq.customer.email">
                  <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  <span class="break-all">{{ rfq.customer.email }}</span>
                </div>
                <div class="flex items-start gap-3 text-gray-600 dark:text-gray-400" v-if="rfq.customer.phone || rfq.customer.whatsapp_number">
                  <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  <span>{{ rfq.customer.whatsapp_number || rfq.customer.phone }}</span>
                </div>
              </div>
            </div>
            <div v-else class="text-sm text-gray-500 dark:text-gray-400">
              Customer details not found.
            </div>
          </div>

          <!-- Assignment Info -->
          <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Assignment</h3>
            
            <div v-if="rfq.assigned_agent" class="space-y-3">
              <p class="text-sm text-gray-500 dark:text-gray-400">Currently assigned to:</p>
              <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-xl">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-brand-600 dark:bg-brand-500/20 dark:text-brand-400 font-medium text-sm">
                  {{ rfq.assigned_agent.name.charAt(0).toUpperCase() }}
                </div>
                <p class="font-medium text-gray-800 dark:text-white text-sm">{{ rfq.assigned_agent.name }}</p>
              </div>
            </div>
            <div v-else class="text-sm text-gray-500 dark:text-gray-400 italic bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl">
              This RFQ is not currently assigned to any agent.
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800">
              <p class="text-xs text-gray-500 dark:text-gray-400">Submitted On</p>
              <p class="text-sm font-medium text-gray-800 dark:text-white mt-1">{{ rfq.created_at }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  rfq: Object
})

const parseSuppliers = (suppliers) => {
  if (!suppliers || !Array.isArray(suppliers)) return [];
  return suppliers.map(s => {
    if (typeof s === 'string') {
      try {
        return JSON.parse(s);
      } catch (e) {
        return { raw: s };
      }
    }
    return s;
  });
};

const bestSupplier = computed(() => {
  if (!props.rfq.best_supplier) return null;
  if (typeof props.rfq.best_supplier === 'string') {
    try {
      return JSON.parse(props.rfq.best_supplier);
    } catch(e) {
      return null;
    }
  }
  return props.rfq.best_supplier;
});

const claudeSuppliers = computed(() => parseSuppliers(props.rfq.claude_suppliers));
const qwenSuppliers = computed(() => parseSuppliers(props.rfq.qwen_suppliers));

// Simple markdown formatter for the AI summary
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
