<template>
  <AdminLayout>
    <Head title="Send Mail" />
    <div class="space-y-6">
      <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white/90">Send Mail to Customer</h3>
        
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Customer</label>
            <select v-model="form.customer_id" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
              <option value="">-- Select a Customer --</option>
              <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                {{ customer.name }} ({{ customer.email }})
              </option>
            </select>
            <div v-if="form.errors.customer_id" class="mt-1 text-sm text-red-500">{{ form.errors.customer_id }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject</label>
            <input type="text" v-model="form.subject" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
            <div v-if="form.errors.subject" class="mt-1 text-sm text-red-500">{{ form.errors.subject }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Message</label>
            <WysiwygEditor v-model="form.message" />
            <div v-if="form.errors.message" class="mt-1 text-sm text-red-500">{{ form.errors.message }}</div>
          </div>

          <div class="flex justify-end">
            <button type="submit" :disabled="form.processing" class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-50">
              <span v-if="form.processing">Sending...</span>
              <span v-else>Send Mail</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import WysiwygEditor from '@/components/WysiwygEditor.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

const props = defineProps({
  customers: Array
})

const form = useForm({
  customer_id: '',
  subject: '',
  message: ''
})

const submit = () => {
  form.post('/admin/mail/send', {
    onSuccess: () => {
      form.reset()
      toast.success('Mail sent successfully')
    },
    onError: (errors) => {
      toast.error('Failed to send mail. Check fields.')
    }
  })
}
</script>
