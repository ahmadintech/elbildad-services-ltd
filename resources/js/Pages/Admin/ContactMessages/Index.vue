<template>
  <AdminLayout>
    <Head title="Contact Messages" />
    <div class="space-y-6">
      <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800 flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Contact Messages</h3>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-800">
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-400">Name</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-400">Email</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-400">Subject</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-400">Status</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-400">Date</th>
                <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600 dark:text-gray-400">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="msg in messages"
                :key="msg.id"
                class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800/30 transition-colors"
                :class="{'bg-gray-50 dark:bg-gray-800/30 font-semibold': !msg.is_read}"
              >
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white/90">{{ msg.name }}</td>
                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ msg.email }}</td>
                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ msg.subject || 'N/A' }}</td>
                <td class="px-6 py-4">
                  <span
                    v-if="msg.replied_at"
                    class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 dark:bg-green-500/15 dark:text-green-400"
                  >
                    Replied
                  </span>
                  <span
                    v-else-if="!msg.is_read"
                    class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-500/15 dark:text-blue-400"
                  >
                    New
                  </span>
                  <span
                    v-else
                    class="inline-flex rounded-full bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-500/15 dark:text-gray-400"
                  >
                    Read
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ new Date(msg.created_at).toLocaleDateString() }}</td>
                <td class="px-6 py-4 text-right text-sm">
                  <button @click="openMessage(msg)" class="text-brand-500 hover:text-brand-600">View & Reply</button>
                </td>
              </tr>
              <tr v-if="messages.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                  No contact messages found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- View & Reply Modal -->
    <div v-if="selectedMessage" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 max-h-[90vh] overflow-y-auto">
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white">Contact Message</h3>
          <button @click="closeMessage" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">&times;</button>
        </div>
        
        <div class="space-y-4 mb-6 text-sm text-gray-700 dark:text-gray-300">
          <div class="grid grid-cols-2 gap-4 border-b border-gray-100 pb-4 dark:border-gray-800">
            <div><span class="font-semibold">From:</span> {{ selectedMessage.name }}</div>
            <div><span class="font-semibold">Email:</span> <a :href="'mailto:'+selectedMessage.email" class="text-brand-500">{{ selectedMessage.email }}</a></div>
            <div><span class="font-semibold">Phone:</span> {{ selectedMessage.phone || 'N/A' }}</div>
            <div><span class="font-semibold">Subject:</span> {{ selectedMessage.subject || 'N/A' }}</div>
          </div>
          <div>
            <span class="font-semibold block mb-1">Message:</span>
            <div class="bg-gray-50 p-4 rounded-lg dark:bg-gray-800 whitespace-pre-wrap">{{ selectedMessage.message }}</div>
          </div>
        </div>

        <form @submit.prevent="submitReply">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Your Reply</label>
            <WysiwygEditor v-model="replyForm.reply_message" />
            <div v-if="replyForm.errors.reply_message" class="mt-1 text-sm text-red-500">{{ replyForm.errors.reply_message }}</div>
          </div>

          <div class="flex justify-end gap-3">
            <button type="button" @click="closeMessage" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Close</button>
            <button type="submit" :disabled="replyForm.processing" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 disabled:opacity-50">
              <span v-if="replyForm.processing">Sending...</span>
              <span v-else>Send Reply</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import WysiwygEditor from '@/components/WysiwygEditor.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

const props = defineProps({
  messages: Array
})

const selectedMessage = ref(null)

const replyForm = useForm({
  reply_message: ''
})

const openMessage = (msg) => {
  selectedMessage.value = msg
  replyForm.reset()
  if (!msg.is_read) {
    router.patch(`/admin/contact-messages/${msg.id}/read`, {}, { preserveScroll: true })
  }
}

const closeMessage = () => {
  selectedMessage.value = null
}

const submitReply = () => {
  replyForm.post(`/admin/contact-messages/${selectedMessage.value.id}/reply`, {
    onSuccess: () => {
      toast.success('Reply sent successfully')
      closeMessage()
    },
    onError: () => {
      toast.error('Failed to send reply.')
    }
  })
}
</script>
