<template>
  <AdminLayout>
    <Head title="My Dashboard" />
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Welcome, {{ $page.props.auth.user.name }}!</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">Track your sourcing requests and communicate with your agents.</p>
        </div>
        <a href="/rfq" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 4.16666V15.8333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M4.16669 10H15.8334" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          New Sourcing Request
        </a>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 md:gap-6">
        <div v-for="(stat, index) in stats" :key="index" class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] shadow-sm">
          <div class="flex items-center gap-4">
            <div :class="[
              'flex h-12 w-12 items-center justify-center rounded-xl text-lg font-bold',
              stat.color === 'blue' ? 'bg-blue-50 text-blue-500 dark:bg-blue-500/10' :
              stat.color === 'orange' ? 'bg-orange-50 text-orange-500 dark:bg-orange-500/10' :
              stat.color === 'purple' ? 'bg-purple-50 text-purple-500 dark:bg-purple-500/10' :
              'bg-green-50 text-green-500 dark:bg-green-500/10'
            ]">
              {{ stat.value }}
            </div>
            <div>
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">{{ stat.title }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Requests List -->
      <div class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">My Sourcing Requests</h3>
        
        <div v-if="rfqs.length === 0" class="rounded-2xl border border-dashed border-gray-300 p-12 text-center dark:border-gray-700">
           <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4 dark:bg-gray-800">
             <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
           </div>
           <p class="text-gray-500 dark:text-gray-400">You haven't submitted any requests yet.</p>
           <a href="/rfq" class="mt-4 inline-block text-brand-500 font-medium hover:underline">Submit your first request now</a>
        </div>

        <div v-else class="grid grid-cols-1 gap-6 lg:grid-cols-2">
          <div v-for="rfq in rfqs" :key="rfq.id" class="group relative rounded-3xl border border-gray-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-brand-400 hover:shadow-xl dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-brand-500/50">
            <div class="flex items-start justify-between mb-6">
              <div>
                <span class="text-[10px] font-bold uppercase tracking-widest text-brand-500 mb-1 block">Ref: ELB-{{ rfq.tracking_token.substring(0, 8).toUpperCase() }}</span>
                <h4 class="text-base font-bold text-gray-800 dark:text-white group-hover:text-brand-500 transition-colors">{{ rfq.product_name }}</h4>
                <p class="text-xs text-gray-500 mt-0.5">{{ rfq.created_at_formatted }}</p>
              </div>
              <div :class="[
                'rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-wider',
                getStatusClasses(rfq.status)
              ]">
                {{ rfq.status.replace('_', ' ') }}
              </div>
            </div>

            <!-- Multi-step Progress -->
            <div class="mb-10 mt-4 px-2">
              <div class="relative flex items-center justify-between">
                <!-- Background Line -->
                <div class="absolute left-0 top-1/2 h-0.5 w-full -translate-y-1/2 bg-gray-100 dark:bg-gray-800"></div>
                <!-- Progress Line -->
                <div 
                  class="absolute left-0 top-1/2 h-0.5 -translate-y-1/2 bg-brand-500 transition-all duration-1000 ease-out"
                  :style="{ width: getProgressPercentage(rfq.status) + '%' }"
                ></div>

                <!-- Steps -->
                <div v-for="step in progressSteps" :key="step.label" class="relative z-10 flex flex-col items-center">
                  <div 
                    :class="[
                      'flex h-8 w-8 items-center justify-center rounded-full border-2 transition-all duration-500',
                      isStepActive(rfq.status, step.value) 
                        ? 'bg-brand-500 border-brand-500 text-white shadow-lg shadow-brand-500/30 scale-110' 
                        : 'bg-white border-gray-200 text-gray-400 dark:bg-gray-900 dark:border-gray-800',
                      isCurrentStep(rfq.status, step.value) ? 'animate-pulse ring-4 ring-brand-500/20' : ''
                    ]"
                  >
                    <!-- Icons -->
                    <svg v-if="step.value === 'pending'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <svg v-else-if="step.value === 'assigned'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    <svg v-else-if="step.value === 'sourcing'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <svg v-else-if="step.value === 'purchased'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <svg v-else-if="step.value === 'shipped'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" /></svg>
                    <svg v-else-if="step.value === 'completed'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                  </div>
                  <span 
                    :class="[
                      'absolute top-10 whitespace-nowrap text-[9px] font-bold uppercase tracking-tight transition-colors duration-300',
                      isStepActive(rfq.status, step.value) ? 'text-brand-600 dark:text-brand-400' : 'text-gray-400'
                    ]"
                  >
                    {{ step.label }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Image (if any) -->
            <div v-if="rfq.image_url" class="mb-4 mt-2 px-2">
               <a :href="rfq.image_url" target="_blank" class="inline-flex items-center gap-1.5 text-[11px] font-bold text-brand-500 hover:text-brand-600 uppercase tracking-wider">
                 <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                 View Attached Image
               </a>
            </div>

            <!-- Agent Info & Action -->
            <div class="flex items-center justify-between border-t border-gray-100 pt-6 dark:border-gray-800">
              <div class="flex items-center gap-2">
                <template v-if="rfq.assigned_agent">
                  <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-500 text-[10px] font-bold dark:bg-indigo-500/10 dark:text-indigo-400">
                    {{ rfq.assigned_agent.name.charAt(0) }}
                  </div>
                  <div>
                    <p class="text-[11px] font-bold text-gray-800 dark:text-white leading-none">{{ rfq.assigned_agent.name }}</p>
                    <p class="text-[10px] text-gray-500">Your Agent</p>
                  </div>
                </template>
                <div v-else class="flex items-center gap-2 text-amber-500">
                  <svg class="w-4 h-4 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  <span class="text-[10px] font-medium uppercase tracking-wider">Assigning Agent...</span>
                </div>
              </div>
              
              <div class="flex gap-2">
                 <Link :href="`/tracking/${rfq.tracking_token}`" class="rounded-lg bg-gray-50 px-3 py-1.5 text-[10px] font-bold text-gray-600 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors uppercase tracking-wider">Details</Link>
                 <a v-if="rfq.assigned_agent && rfq.assigned_agent.whatsapp_number" 
                    :href="`https://wa.me/${formatWhatsApp(rfq.assigned_agent.whatsapp_number)}`" 
                    target="_blank"
                    class="rounded-lg bg-emerald-50 px-3 py-1.5 text-[10px] font-bold text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:hover:bg-emerald-500/20 transition-colors uppercase tracking-wider inline-flex items-center gap-1">
                   Chat
                 </a>
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
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
  rfqs: Array,
  stats: Array
})

const isStepActive = (currentStatus, stepValue) => {
  const statusWeight = {
    'pending': 0,
    'queued': 0,
    'assigned': 1,
    'sourcing': 2,
    'purchased': 3,
    'shipped': 4,
    'completed': 5,
    'not_found': -1
  }
  return statusWeight[currentStatus.toLowerCase()] >= statusWeight[stepValue]
}

const isCurrentStep = (currentStatus, stepValue) => {
  return currentStatus.toLowerCase() === stepValue.toLowerCase()
}

const progressSteps = [
  { label: 'Pending', value: 'pending' },
  { label: 'Assigned', value: 'assigned' },
  { label: 'Sourcing', value: 'sourcing' },
  { label: 'Purchase', value: 'purchased' },
  { label: 'Shipped', value: 'shipped' },
  { label: 'Done', value: 'completed' },
]

const getProgressPercentage = (status) => {
  const map = {
    'pending': 0,
    'queued': 0,
    'not_found': 0,
    'assigned': 20,
    'sourcing': 40,
    'purchased': 60,
    'shipped': 80,
    'completed': 100
  }
  return map[status.toLowerCase()] || 0
}

const getStatusClasses = (status) => {
  const s = status.toLowerCase()
  if (s === 'completed') return 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-500'
  if (s === 'not_found') return 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-500'
  if (['pending', 'queued', 'assigned'].includes(s)) return 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-500'
  if (['sourcing', 'purchased'].includes(s)) return 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-500'
  return 'bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-500'
}

const formatWhatsApp = (number) => {
  if (!number) return '';
  let clean = number.replace(/\D/g, '');
  if (clean.startsWith('0')) {
    clean = '234' + clean.substring(1);
  }
  return clean;
}
</script>

<style scoped>
.whitespace-nowrap {
  white-space: nowrap;
}
</style>
