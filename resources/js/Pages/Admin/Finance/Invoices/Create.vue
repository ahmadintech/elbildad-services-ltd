<template>
  <AdminLayout>
    <Head title="Create Invoice" />
    <div class="max-w-4xl mx-auto space-y-6">
      <div class="flex items-center gap-4">
        <Link href="/finance/invoices" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-500 hover:text-gray-700 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400 dark:hover:text-white">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        </Link>
        <div>
          <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Create Invoice</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">Generate a new manual invoice and sync with Zoho Invoice.</p>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03] space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Customer -->
            <div class="md:col-span-1">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Customer</label>
              <CustomerSelect
                id="invoice-customer-select"
                v-model="form.customer_id"
                :customers="customers"
                placeholder="Search & select customer…"
              />
              <p v-if="form.errors.customer_id" class="mt-1 text-xs text-error-500">{{ form.errors.customer_id }}</p>
            </div>

            <!-- Date -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Invoice Date</label>
              <input v-model="form.date" type="date" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white" required />
              <p v-if="form.errors.date" class="mt-1 text-xs text-error-500">{{ form.errors.date }}</p>
            </div>

            <!-- Due Date -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Due Date</label>
              <input v-model="form.due_date" type="date" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white" required />
              <p v-if="form.errors.due_date" class="mt-1 text-xs text-error-500">{{ form.errors.due_date }}</p>
            </div>
          </div>

          <!-- Notes -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Notes / Terms</label>
            <textarea v-model="form.notes" rows="3" placeholder="e.g. Terms: Net 15 days." class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white"></textarea>
            <p v-if="form.errors.notes" class="mt-1 text-xs text-error-500">{{ form.errors.notes }}</p>
          </div>
        </div>

        <!-- Line Items -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03] space-y-4">
          <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Line Items</h3>
            <div class="flex items-center gap-2">
              <Link href="/finance/items" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.02] transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                Create New Item
              </Link>
              <button type="button" @click="addLineItem" class="inline-flex items-center gap-1.5 rounded-lg border border-brand-500 px-3 py-1.5 text-xs font-medium text-brand-500 hover:bg-brand-50 dark:hover:bg-brand-500/10 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Add Item
              </button>
            </div>
          </div>

          <div class="space-y-3">
            <div v-for="(item, index) in form.line_items" :key="index" class="rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-white/[0.01] p-4">
              <!-- Top row: Preset + Delete -->
              <div class="flex items-start gap-3 mb-3">
                <div class="flex-1 min-w-0">
                  <label class="mb-1 block text-xs font-medium text-gray-500">Preset Product/Service</label>
                  <select @change="onPresetItemChange(index, $event)" class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white">
                    <option value="">Custom Item</option>
                    <option v-for="preset in items" :key="preset.id" :value="preset.id">
                      {{ preset.name }} ({{ formatCurrency(preset.rate) }})
                    </option>
                  </select>
                </div>
                <button v-if="form.line_items.length > 1" type="button" @click="removeItemLine(index)" class="mt-6 flex-shrink-0 rounded-lg p-1.5 text-error-400 hover:text-error-600 hover:bg-error-50 dark:hover:bg-error-500/10 transition-colors">
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
              </div>
              <!-- Bottom row: Name, Description, Rate, Qty, Total -->
              <div class="grid grid-cols-12 gap-3 items-end">
                <!-- Name -->
                <div class="col-span-12 sm:col-span-5">
                  <label class="mb-1 block text-xs font-medium text-gray-500">Item Name</label>
                  <input v-model="item.name" type="text" class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white" placeholder="Name" required />
                  <input v-model="item.description" type="text" class="mt-1.5 dark:bg-dark-900 h-9 w-full rounded-lg border border-gray-300 px-3 py-1 text-xs text-gray-500 focus:border-brand-300 dark:border-gray-700 dark:text-white" placeholder="Optional details/specs" />
                </div>
                <!-- Rate -->
                <div class="col-span-5 sm:col-span-3">
                  <label class="mb-1 block text-xs font-medium text-gray-500">Rate ($)</label>
                  <input v-model="item.rate" type="number" step="0.01" min="0" class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white" required />
                </div>
                <!-- Qty -->
                <div class="col-span-3 sm:col-span-2">
                  <label class="mb-1 block text-xs font-medium text-gray-500">Qty</label>
                  <input v-model="item.quantity" type="number" min="1" class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white" required />
                </div>
                <!-- Total -->
                <div class="col-span-4 sm:col-span-4 text-right">
                  <p class="text-xs text-gray-400 mb-1">Total</p>
                  <p class="font-semibold text-sm text-gray-800 dark:text-white whitespace-nowrap">{{ formatCurrency(item.rate * item.quantity) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Total summary -->
          <div class="flex flex-col md:flex-row md:items-end justify-between pt-4 border-t border-gray-100 dark:border-gray-800 gap-6">
            <div class="w-full md:w-1/3">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Apply Tax to All Items</label>
              <select v-model="form.global_tax_id" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white">
                <option value="">No Tax</option>
                <option v-for="tax in taxes" :key="tax.tax_id" :value="tax.tax_id">
                  {{ tax.tax_name }} ({{ tax.tax_percentage }}%)
                </option>
              </select>
              <p class="mt-1 text-xs text-gray-500">To apply multiple taxes (e.g. VAT + Service Charge), create a "Tax Group" in your Zoho Settings first.</p>
            </div>
            <div class="w-full md:w-1/4">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Shipping Charge (₦)</label>
              <input v-model="form.shipping_charge" type="number" step="0.01" min="0" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white" />
            </div>
            <div class="text-right space-y-2 w-full md:w-auto">
              <div class="flex justify-end gap-8 text-sm text-gray-500 dark:text-gray-400">
                <span>Subtotal:</span>
                <span class="font-medium text-gray-800 dark:text-gray-300">{{ formatCurrency(subTotal) }}</span>
              </div>
              <div v-if="form.global_tax_id" class="flex justify-end gap-8 text-sm text-gray-500 dark:text-gray-400">
                <span>Tax Amount:</span>
                <span class="font-medium text-gray-800 dark:text-gray-300">{{ formatCurrency(taxAmount) }}</span>
              </div>
              <div v-if="form.shipping_charge > 0" class="flex justify-end gap-8 text-sm text-gray-500 dark:text-gray-400">
                <span>Shipping Charge:</span>
                <span class="font-medium text-gray-800 dark:text-gray-300">{{ formatCurrency(form.shipping_charge) }}</span>
              </div>
              <div class="flex justify-end gap-8 pt-2 border-t border-gray-100 dark:border-gray-800">
                <span class="text-sm font-bold text-gray-800 dark:text-gray-200 mt-1">Total Invoice Amount:</span>
                <span class="text-2xl font-bold text-brand-600 dark:text-brand-400">{{ formatCurrency(overallTotal) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3">
          <Link href="/finance/invoices" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.02]">Cancel</Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors disabled:opacity-70 disabled:cursor-not-allowed"
          >
            <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            {{ form.processing ? 'Creating…' : 'Create Invoice' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import CustomerSelect from '@/components/ui/CustomerSelect.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useCurrency } from '@/composables/useCurrency'
const { formatCurrency } = useCurrency()

const props = defineProps({
  customers: Array,
  items: Array,
  taxes: Array
})

const form = useForm({
  customer_id: '',
  date: new Date().toISOString().split('T')[0],
  due_date: new Date(Date.now() + 14 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
  notes: '',
  global_tax_id: '',
  shipping_charge: 0,
  line_items: [
    { name: '', description: '', rate: 0.00, quantity: 1, zoho_item_id: null }
  ]
})


const addLineItem = () => {
  form.line_items.push({ name: '', description: '', rate: 0.00, quantity: 1, zoho_item_id: null })
}

const removeItemLine = (index) => {
  form.line_items.splice(index, 1)
}

const onPresetItemChange = (index, event) => {
  const presetId = event.target.value
  if (!presetId) {
    form.line_items[index].name = ''
    form.line_items[index].description = ''
    form.line_items[index].rate = 0.00
    form.line_items[index].zoho_item_id = null
    return
  }

  const selectedPreset = props.items.find(item => item.id === presetId)
  if (selectedPreset) {
    form.line_items[index].name = selectedPreset.name
    form.line_items[index].description = selectedPreset.description || ''
    form.line_items[index].rate = parseFloat(selectedPreset.rate) || 0.00
    form.line_items[index].zoho_item_id = selectedPreset.zoho_item_id
  }
}

const subTotal = computed(() => {
  return form.line_items.reduce((sum, item) => {
    const rate = parseFloat(item.rate) || 0;
    const qty = parseFloat(item.quantity) || 0;
    return sum + (rate * qty);
  }, 0);
})

const taxAmount = computed(() => {
  if (!form.global_tax_id) return 0;
  const tax = props.taxes?.find(t => t.tax_id === form.global_tax_id);
  const pct = tax ? parseFloat(tax.tax_percentage) : 0;
  return subTotal.value * (pct / 100);
})

const overallTotal = computed(() => {
  return subTotal.value + taxAmount.value + parseFloat(form.shipping_charge || 0);
})

const submit = () => {
  form.transform((data) => ({
    ...data,
    line_items: data.line_items.map(item => ({
      ...item,
      tax_id: data.global_tax_id || null
    }))
  })).post(route('finance.invoices.store'))
}
</script>
