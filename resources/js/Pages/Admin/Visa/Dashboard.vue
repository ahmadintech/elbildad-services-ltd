<template>
  <AdminLayout>
    <Head title="Visa Admin Dashboard" />
    <div class="space-y-6">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Visa Admin Dashboard</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">Manage and assign all visa applications.</p>
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

      <!-- Applications Table -->
      <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">All Applications</h3>
        </div>
        <div class="max-w-full overflow-x-auto custom-scrollbar">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                <th class="px-5 py-3 text-left sm:px-6 text-sm font-medium text-gray-500 uppercase">Customer</th>
                <th class="px-5 py-3 text-left sm:px-6 text-sm font-medium text-gray-500 uppercase">Visa Type</th>
                <th class="px-5 py-3 text-left sm:px-6 text-sm font-medium text-gray-500 uppercase">Assigned To</th>
                <th class="px-5 py-3 text-left sm:px-6 text-sm font-medium text-gray-500 uppercase">Status</th>
                <th class="px-5 py-3 text-left sm:px-6 text-sm font-medium text-gray-500 uppercase">Created</th>
                <th class="px-5 py-3 text-right sm:px-6 text-sm font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr v-for="app in applications" :key="app.id" class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-4 sm:px-6">
                  <p class="font-medium text-gray-800 dark:text-white/90">{{ app.full_name }}</p>
                  <p class="text-xs text-gray-500">{{ app.email_address }}</p>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <p class="text-sm font-medium text-gray-800 dark:text-white/90 truncate max-w-[200px]" :title="app.preferred_visa_type">{{ app.preferred_visa_type }}</p>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <p class="text-sm text-gray-800 dark:text-white/90">{{ app.assigned_agent?.name || 'Unassigned' }}</p>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <span :class="[
                    'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium capitalize',
                    app.status === 'approved' ? 'bg-green-100 text-green-700' :
                    app.status === 'assigned' ? 'bg-blue-100 text-blue-700' :
                    app.status === 'processing' ? 'bg-purple-100 text-purple-700' :
                    app.status === 'rejected' ? 'bg-red-100 text-red-700' :
                    'bg-gray-100 text-gray-700'
                  ]">
                    {{ app.status }}
                  </span>
                </td>
                <td class="px-5 py-4 sm:px-6 text-sm text-gray-500">
                  {{ new Date(app.created_at).toLocaleDateString() }}
                </td>
                <td class="px-5 py-4 text-right sm:px-6">
                  <button @click="openAssignmentModal(app)" class="inline-flex items-center gap-1.5 rounded bg-brand-50 text-brand-600 px-3 py-1.5 text-xs font-medium hover:bg-brand-100 transition-colors">
                    Review / Assign
                  </button>
                </td>
              </tr>
              <tr v-if="applications.length === 0">
                <td colspan="6" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                  No applications received yet.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Review & Assignment Modal -->
    <div v-if="isModalOpen" class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity">
      <div class="no-scrollbar relative w-full max-w-2xl overflow-y-auto rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8 max-h-[90vh] shadow-xl">
        <button @click="isModalOpen = false" class="absolute right-5 top-5 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6L6 18M6 6l12 12"/>
          </svg>
        </button>

        <h3 class="mb-6 text-xl font-semibold text-gray-800 dark:text-white/90">Application Details & Assignment</h3>
        
        <div v-if="selectedApp" class="space-y-6">
          
          <div class="space-y-4 text-sm">
            <!-- Section: Applicant Details -->
            <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/30">
              <h4 class="font-semibold text-brand-600 dark:text-brand-400 mb-3 border-b border-gray-200 dark:border-gray-700 pb-2">Personal Information</h4>
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-gray-600 dark:text-gray-300">
                <p><span class="text-gray-400 block text-xs">Full Name</span><strong class="text-gray-800 dark:text-white">{{ selectedApp.full_name }}</strong></p>
                <p><span class="text-gray-400 block text-xs">Date of Birth</span>{{ formatDate(selectedApp.dob) }}</p>
                <p><span class="text-gray-400 block text-xs">Nationality</span>{{ selectedApp.nationality }}</p>
                <p><span class="text-gray-400 block text-xs">Gender</span><span class="capitalize">{{ selectedApp.gender }}</span></p>
                <p><span class="text-gray-400 block text-xs">Marital Status</span><span class="capitalize">{{ selectedApp.marital_status }}</span></p>
                <p><span class="text-gray-400 block text-xs">Phone</span>{{ selectedApp.phone_number }}</p>
                <p><span class="text-gray-400 block text-xs">Email</span>{{ selectedApp.email_address }}</p>
                <p class="sm:col-span-2"><span class="text-gray-400 block text-xs">Residential Address</span>{{ selectedApp.residential_address }}</p>
              </div>
            </div>

            <!-- Section: Occupation & Travel Intent -->
            <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/30">
              <h4 class="font-semibold text-brand-600 dark:text-brand-400 mb-3 border-b border-gray-200 dark:border-gray-700 pb-2">Employment & Travel Intent</h4>
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-gray-600 dark:text-gray-300">
                <p><span class="text-gray-400 block text-xs">Occupation</span>{{ selectedApp.occupation || 'N/A' }}</p>
                <p><span class="text-gray-400 block text-xs">Company Name</span>{{ selectedApp.company_name || 'N/A' }}</p>
                <p><span class="text-gray-400 block text-xs">Company Address</span>{{ selectedApp.company_address || 'N/A' }}</p>
                <p><span class="text-gray-400 block text-xs">Preferred Visa Type</span><strong class="text-gray-800 dark:text-white">{{ selectedApp.preferred_visa_type }}</strong></p>
                <p><span class="text-gray-400 block text-xs">Purpose of Visit</span>{{ selectedApp.purpose_of_visit }}</p>
                <p><span class="text-gray-400 block text-xs">Intended Arrival Date</span>{{ formatDate(selectedApp.intended_arrival_date) }}</p>
                <p><span class="text-gray-400 block text-xs">Intended Departure Date</span>{{ formatDate(selectedApp.intended_departure_date) }}</p>
                <p class="sm:col-span-2"><span class="text-gray-400 block text-xs">Cities to Visit</span>{{ selectedApp.cities_to_visit || 'N/A' }}</p>
              </div>
            </div>

            <!-- Section: Passport & Travel History -->
            <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/30">
              <h4 class="font-semibold text-brand-600 dark:text-brand-400 mb-3 border-b border-gray-200 dark:border-gray-700 pb-2">Passport & Travel History</h4>
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-gray-600 dark:text-gray-300">
                <p><span class="text-gray-400 block text-xs">Passport No.</span><strong class="text-gray-800 dark:text-white">{{ selectedApp.passport_number }}</strong></p>
                <p><span class="text-gray-400 block text-xs">Issue Date</span>{{ formatDate(selectedApp.passport_issue_date) }}</p>
                <p><span class="text-gray-400 block text-xs">Expiry Date</span>{{ formatDate(selectedApp.passport_expiry_date) }}</p>
                <p><span class="text-gray-400 block text-xs">Country of Issuance</span>{{ selectedApp.country_of_issuance }}</p>
                
                <p><span class="text-gray-400 block text-xs">Visited China Before?</span>{{ selectedApp.visited_china_before ? 'Yes' : 'No' }}</p>
                <p v-if="selectedApp.visited_china_before"><span class="text-gray-400 block text-xs">Most Recent Visit</span>{{ formatDate(selectedApp.most_recent_china_visit) }}</p>
                <p><span class="text-gray-400 block text-xs">Visits in last 5 years</span>{{ selectedApp.visits_in_last_5_years || '0' }}</p>
                <p class="sm:col-span-2" v-if="selectedApp.traveled_other_countries"><span class="text-gray-400 block text-xs">Other Countries Visited</span>{{ selectedApp.list_countries_visited || 'N/A' }}</p>
                <p><span class="text-gray-400 block text-xs">Attended Trade Fairs?</span>{{ selectedApp.attended_trade_fairs ? 'Yes' : 'No' }}</p>
                <p><span class="text-gray-400 block text-xs">Previous Chinese Visas?</span>{{ selectedApp.has_previous_chinese_visas ? 'Yes' : 'No' }}</p>
              </div>
            </div>

            <!-- Section: Documents -->
            <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/30">
              <h4 class="font-semibold text-brand-600 dark:text-brand-400 mb-3 border-b border-gray-200 dark:border-gray-700 pb-2">Uploaded Documents</h4>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-gray-600 dark:text-gray-300">
                <!-- Passport Photo -->
                <div>
                  <span class="text-gray-400 block text-xs mb-1">Passport Photograph</span>
                  <div v-if="selectedApp.passport_photo_path">
                    <img v-if="isImage(selectedApp.passport_photo_path)" :src="'/storage/' + selectedApp.passport_photo_path" class="w-full h-auto max-h-48 object-contain rounded border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800" alt="Passport Photo" />
                    <a v-else :href="'/storage/' + selectedApp.passport_photo_path" target="_blank" class="text-brand-500 hover:underline">Download / View Document</a>
                  </div>
                  <span v-else>N/A</span>
                </div>
                
                <!-- Passport Data Page -->
                <div>
                  <span class="text-gray-400 block text-xs mb-1">Passport Data Page</span>
                  <div v-if="selectedApp.passport_data_page_path">
                    <img v-if="isImage(selectedApp.passport_data_page_path)" :src="'/storage/' + selectedApp.passport_data_page_path" class="w-full h-auto max-h-48 object-contain rounded border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800" alt="Passport Data Page" />
                    <a v-else :href="'/storage/' + selectedApp.passport_data_page_path" target="_blank" class="text-brand-500 hover:underline">Download / View Document</a>
                  </div>
                  <span v-else>N/A</span>
                </div>

                <!-- Invitation Letter -->
                <div v-if="selectedApp.invitation_letter_path">
                  <span class="text-gray-400 block text-xs mb-1">Invitation Letter</span>
                  <img v-if="isImage(selectedApp.invitation_letter_path)" :src="'/storage/' + selectedApp.invitation_letter_path" class="w-full h-auto max-h-48 object-contain rounded border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800" alt="Invitation Letter" />
                  <a v-else :href="'/storage/' + selectedApp.invitation_letter_path" target="_blank" class="text-brand-500 hover:underline">Download / View Document</a>
                </div>

                <!-- Previous Visas -->
                <div v-if="selectedApp.previous_visa_pages_paths && selectedApp.previous_visa_pages_paths.length > 0" class="sm:col-span-2">
                  <span class="text-gray-400 block text-xs mb-1">Previous Chinese Visas</span>
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <div v-for="(path, idx) in selectedApp.previous_visa_pages_paths" :key="idx">
                      <img v-if="isImage(path)" :src="'/storage/' + path" class="w-full h-auto max-h-32 object-contain rounded border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800" alt="Previous Visa" />
                      <a v-else :href="'/storage/' + path" target="_blank" class="text-brand-500 hover:underline block truncate text-xs mt-1">Doc {{ idx + 1 }}</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <form @submit.prevent="submitAssignment" class="space-y-4 border-t border-gray-100 dark:border-gray-800 pt-6">
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Assign to Agent</label>
              <select v-model="form.assigned_agent_id" class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 dark:border-gray-700 dark:text-white">
                <option :value="null">Unassigned</option>
                <option v-for="agent in agents" :key="agent.id" :value="agent.id">{{ agent.name }}</option>
              </select>
            </div>
            
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Update Status</label>
              <select v-model="form.status" class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 dark:border-gray-700 dark:text-white" required>
                <option value="pending">Pending</option>
                <option value="assigned">Assigned</option>
                <option value="processing">Processing</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>

            <div class="flex items-center gap-3 mt-6">
              <button @click="isModalOpen = false" type="button" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 sm:w-auto">Cancel</button>
              <button type="submit" class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto" :disabled="form.processing">
                <span v-if="form.processing">Saving...</span>
                <span v-else>Update Application</span>
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
  applications: Array,
  stats: Array,
  agents: Array
})

const isModalOpen = ref(false)
const selectedApp = ref(null)

const form = useForm({
  assigned_agent_id: null,
  status: ''
})

const openAssignmentModal = (app) => {
  selectedApp.value = app
  form.assigned_agent_id = app.assigned_agent_id
  form.status = app.status
  isModalOpen.value = true
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString()
}

const isImage = (path) => {
  if (!path) return false
  return path.toLowerCase().match(/\.(jpeg|jpg|gif|png|webp|svg)$/) != null
}

const submitAssignment = () => {
  if (selectedApp.value) {
    form.patch(route('visa.applications.update-status', selectedApp.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        isModalOpen.value = false
      }
    })
  }
}
</script>
