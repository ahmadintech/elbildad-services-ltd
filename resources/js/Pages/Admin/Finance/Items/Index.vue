<template>
  <AdminLayout>
    <Head title="Finance Items" />
    <div class="space-y-6">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Finance Items</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">Manage products/services synchronized with Zoho Invoice.</p>
        </div>
        <div>
          <button @click="openCreateModal" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 4.16666V15.8333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M4.16669 10H15.8334" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Add Item
          </button>
        </div>
      </div>

      <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="max-w-full overflow-x-auto custom-scrollbar">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Item Name</p></th>
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">SKU / Type</p></th>
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Rate</p></th>
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Zoho Sync</p></th>
                <th class="px-5 py-3 text-right sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Actions</p></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-4 sm:px-6">
                  <div class="flex items-center gap-3">
                    <div v-if="item.image_url" class="h-10 w-10 flex-shrink-0 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                      <img :src="item.image_url" class="h-full w-full object-cover" />
                    </div>
                    <div v-else class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-400">
                      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                    <div>
                      <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ item.name }}</p>
                      <p class="text-gray-500 text-theme-xs dark:text-gray-400 truncate max-w-[200px]">{{ item.description }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <p class="text-gray-800 text-theme-sm dark:text-gray-400 font-medium">{{ item.sku || '-' }}</p>
                  <p class="text-gray-500 text-theme-xs dark:text-gray-400 capitalize">{{ item.item_type }}</p>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <p class="text-gray-800 text-theme-sm dark:text-gray-400 font-medium">{{ formatCurrency(item.rate) }}</p>
                  <p class="text-gray-500 text-theme-xs dark:text-gray-400 uppercase">Per {{ item.unit || 'pcs' }}</p>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <span v-if="item.zoho_item_id" class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">
                    Synced
                  </span>
                  <button v-else @click="syncItem(item)" :disabled="syncingId === item.id" class="inline-flex items-center gap-1.5 rounded-full bg-warning-50 px-2.5 py-1 text-xs font-medium text-warning-700 hover:bg-warning-100 transition-colors dark:bg-warning-500/10 dark:text-warning-400 dark:hover:bg-warning-500/20 disabled:opacity-50">
                    <svg v-if="syncingId === item.id" class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" /></svg>
                    <svg v-else class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    {{ syncingId === item.id ? 'Syncing...' : 'Pending' }}
                  </button>
                </td>
                <td class="px-5 py-4 text-right sm:px-6">
                  <div class="flex items-center justify-end gap-3">
                    <button
                      @click="editItem(item)"
                      :disabled="deleteForm.processing && itemToDelete?.id === item.id"
                      class="text-gray-400 hover:text-brand-500 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      Edit
                    </button>
                    <button
                      @click="confirmDelete(item)"
                      :disabled="deleteForm.processing && itemToDelete?.id === item.id"
                      class="inline-flex items-center gap-1 text-gray-400 hover:text-error-500 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <svg v-if="deleteForm.processing && itemToDelete?.id === item.id" class="animate-spin h-3.5 w-3.5 text-error-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                      </svg>
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="items.length === 0">
                <td colspan="5" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                  No items found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Edit/Create Modal -->
    <div v-if="isModalOpen" class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity overflow-y-auto">
      <div class="relative w-full max-w-[600px] my-8 rounded-3xl bg-white p-6 dark:bg-gray-900 shadow-xl">
        <h3 class="mb-5 text-xl font-semibold text-gray-800 dark:text-white/90">{{ isEditing ? 'Edit Item' : 'Add Item' }}</h3>
        <form @submit.prevent="submitForm" class="space-y-5">
          <!-- Image Upload Drag & Drop -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Item Image</label>
            <div
              @dragover.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              @drop.prevent="handleDrop"
              :class="['relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed p-6 transition-all', isDragging ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10' : 'border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800']"
            >
              <input type="file" ref="fileInput" @change="handleFileSelect" accept="image/*" class="hidden" />

              <div v-if="imagePreview" class="relative w-32 h-32 mb-4 group">
                <img :src="imagePreview" class="w-full h-full object-cover rounded-lg shadow-sm border border-gray-200 dark:border-gray-700" />
                <button type="button" @click.stop="clearImage" class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-error-500 text-white shadow hover:bg-error-600 opacity-0 group-hover:opacity-100 transition-opacity">
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </div>
              <div v-else class="mb-2 rounded-full bg-gray-100 p-3 dark:bg-gray-800 text-gray-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
              </div>

              <div class="text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  <button type="button" @click="$refs.fileInput.click()" class="font-semibold text-brand-500 hover:text-brand-600 focus:outline-none">Click to upload</button> or drag and drop
                </p>
                <p class="mt-1 text-xs text-gray-400">PNG, JPG up to 5MB</p>
              </div>
            </div>
            <p v-if="form.errors.image" class="mt-1 text-xs text-error-500">{{ form.errors.image }}</p>
          </div>

          <!-- Basic Info -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Name</label>
              <input v-model="form.name" type="text" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white" required />
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">SKU (Optional)</label>
              <input v-model="form.sku" type="text" placeholder="e.g. PRD-001" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white" />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-1">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Item Type</label>
              <select v-model="form.item_type" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white" required>
                <option value="goods">Goods</option>
                <option value="service">Service</option>
              </select>
            </div>
            <div class="md:col-span-1">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Rate</label>
              <input v-model="form.rate" type="number" step="0.01" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white" required />
            </div>
            <div class="md:col-span-1">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Unit</label>
              <input v-model="form.unit" type="text" placeholder="e.g. pcs, kg" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white" />
            </div>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Description</label>
            <textarea v-model="form.description" rows="2" class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white"></textarea>
          </div>

          <div class="flex items-center gap-3 mt-6">
            <button @click="isModalOpen = false" type="button" class="w-full rounded-lg border border-gray-300 py-2.5 transition-colors dark:border-gray-700">Cancel</button>
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center justify-center gap-2 w-full rounded-lg bg-brand-500 py-2.5 text-white hover:bg-brand-600 transition-colors disabled:opacity-70 disabled:cursor-not-allowed"
            >
              <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
              </svg>
              {{ form.processing ? (isEditing ? 'Updating…' : 'Creating…') : (isEditing ? 'Update' : 'Create') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Modal -->
    <div v-if="isDeleteModalOpen" class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity">
      <div class="relative w-full max-w-md rounded-3xl bg-white p-8 text-center shadow-xl dark:bg-gray-900">
        <h3 class="mb-2 text-xl font-bold text-gray-800 dark:text-white/90">Delete Item</h3>
        <p class="mb-8 text-sm text-gray-500 dark:text-gray-400">Are you sure you want to delete this item? It will also be deleted from Zoho.</p>
        <div class="flex justify-center gap-3">
          <button @click="isDeleteModalOpen = false" type="button" class="w-full rounded-lg border border-gray-300 py-2.5 transition-colors dark:border-gray-700">Cancel</button>
          <button
            @click="executeDelete"
            type="button"
            :disabled="deleteForm.processing"
            class="inline-flex items-center justify-center gap-2 w-full rounded-lg bg-error-500 py-2.5 text-white hover:bg-error-600 transition-colors disabled:opacity-70 disabled:cursor-not-allowed"
          >
            <svg v-if="deleteForm.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            {{ deleteForm.processing ? 'Deleting…' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useCurrency } from '@/composables/useCurrency'
const { formatCurrency } = useCurrency()

const props = defineProps({
  items: Array
})

const isModalOpen = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

const isDragging = ref(false)
const imagePreview = ref(null)

const form = useForm({
  name: '',
  sku: '',
  item_type: 'goods',
  description: '',
  rate: '',
  unit: 'pcs',
  image: null,
})

const isDeleteModalOpen = ref(false)
const itemToDelete = ref(null)
const deleteForm = useForm({})

const syncingId = ref(null)

const syncItem = (item) => {
  syncingId.value = item.id
  router.post(route('finance.items.sync', item.id), {}, {
    preserveScroll: true,
    onFinish: () => {
      syncingId.value = null
    }
  })
}

const handleFile = (file) => {
  if (file && file.type.startsWith('image/')) {
    form.image = file
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const handleDrop = (e) => {
  isDragging.value = false
  const file = e.dataTransfer.files[0]
  handleFile(file)
}

const handleFileSelect = (e) => {
  const file = e.target.files[0]
  handleFile(file)
}

const clearImage = () => {
  form.image = null
  imagePreview.value = null
}

const openCreateModal = () => {
  isEditing.value = false
  editingId.value = null
  form.reset()
  form.clearErrors()
  clearImage()
  isModalOpen.value = true
}

const editItem = (item) => {
  isEditing.value = true
  editingId.value = item.id
  form.reset()
  form.clearErrors()

  form.name = item.name
  form.sku = item.sku || ''
  form.item_type = item.item_type || 'goods'
  form.description = item.description || ''
  form.rate = item.rate
  form.unit = item.unit || 'pcs'
  form.image = null

  imagePreview.value = item.image_url || null
  isModalOpen.value = true
}

const submitForm = () => {
  if (isEditing.value) {
    form.transform((data) => ({
      ...data,
      _method: 'put',
    })).post(route('finance.items.update', editingId.value), {
      forceFormData: true,
      onSuccess: () => isModalOpen.value = false,
      preserveScroll: true
    })
  } else {
    form.post(route('finance.items.store'), {
      forceFormData: true,
      onSuccess: () => isModalOpen.value = false,
      preserveScroll: true
    })
  }
}

const confirmDelete = (item) => {
  itemToDelete.value = item
  isDeleteModalOpen.value = true
}

const executeDelete = () => {
  if (itemToDelete.value) {
    deleteForm.delete(route('finance.items.destroy', itemToDelete.value.id), {
      onSuccess: () => {
        isDeleteModalOpen.value = false
        itemToDelete.value = null
      },
      preserveScroll: true
    })
  }
}
</script>
