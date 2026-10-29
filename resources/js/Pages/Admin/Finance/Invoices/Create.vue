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
          <p class="text-sm text-gray-500 dark:text-gray-400">Generate a customer invoice, include sourcing &amp; supplier intelligence, and sync with Zoho.</p>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- 1. Customer & Dates Card -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03] space-y-6 shadow-xs">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Customer -->
            <div class="md:col-span-1">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Customer <span class="text-error-500">*</span>
              </label>
              <CustomerSelect
                id="invoice-customer-select"
                v-model="form.customer_id"
                :customers="customers"
                placeholder="Search & select customer…"
                @update:modelValue="onCustomerChange"
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
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Customer Terms / Notes</label>
            <textarea v-model="form.notes" rows="2" placeholder="e.g. Terms: Net 15 days. Payment via bank transfer or Zainpay." class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white"></textarea>
            <p v-if="form.errors.notes" class="mt-1 text-xs text-error-500">{{ form.errors.notes }}</p>
          </div>
        </div>

        <!-- 2. Sourcing & Supplier Intelligence (New Dedicated Card) -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03] space-y-5 shadow-xs">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-gray-100 dark:border-gray-800 gap-2">
            <div class="flex items-center gap-2.5">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div>
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Sourcing &amp; Supplier Details</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Associate with client's sourcing RFQ and record Chinese factory / supplier information.</p>
              </div>
            </div>
            <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-400 self-start sm:self-auto">
              Agent Sourcing
            </span>
          </div>

          <!-- RFQ Selection & AI Supplier Quick-Fill -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Linked RFQ -->
            <div>
              <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                Linked Sourcing RFQ (Optional)
              </label>
              <select
                v-model="form.rfq_id"
                @change="onRfqSelect"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white"
              >
                <option value="">— No Linked RFQ (Independent Invoice) —</option>
                <option v-for="rfq in filteredRfqs" :key="rfq.id" :value="rfq.id">
                  {{ rfq.product_name }} ({{ rfq.tracking_token ? rfq.tracking_token.substring(0, 8) : rfq.id.substring(0, 8) }}) — {{ formatStatusLabel(rfq.status) }}
                </option>
              </select>
              <p v-if="selectedRfq" class="mt-1.5 text-xs text-brand-600 dark:text-brand-400 flex items-center gap-1">
                <span>✓ Linked to RFQ: <strong>{{ selectedRfq.product_name }}</strong></span>
              </p>
            </div>

            <!-- Sourcing Company Directory Pick -->
            <div>
              <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                Registered Sourcing Partner (Directory)
              </label>
              <select
                v-model="form.sourcing_company_id"
                @change="onSourcingCompanySelect"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white"
              >
                <option value="">— Custom / Direct Chinese Supplier —</option>
                <option v-for="company in sourcingCompanies" :key="company.id" :value="company.id">
                  {{ company.name }} {{ company.location ? `(${company.location})` : '' }}
                </option>
              </select>
              <p class="mt-1.5 text-xs text-gray-400">
                Select an established sourcing partner or enter direct factory details below.
              </p>
            </div>
          </div>

          <!-- Quick-Fill Pills from RFQ AI Suppliers if available -->
          <div v-if="suggestedSuppliers.length > 0" class="rounded-xl border border-brand-100 bg-brand-50/40 p-3.5 dark:border-brand-900/30 dark:bg-brand-500/5 space-y-2">
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold text-brand-800 dark:text-brand-300 flex items-center gap-1.5">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                Suggested Suppliers from RFQ:
              </span>
              <span class="text-[11px] text-brand-600 dark:text-brand-400">Click to pre-fill</span>
            </div>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="(sup, sIdx) in suggestedSuppliers"
                :key="sIdx"
                type="button"
                @click="applySupplierSuggestion(sup)"
                class="inline-flex items-center gap-1.5 rounded-lg border border-brand-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-800 shadow-xs hover:border-brand-400 hover:bg-brand-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-brand-900/30 transition-all"
              >
                <span class="font-bold text-brand-600 dark:text-brand-400">#{{ sIdx + 1 }}</span>
                <span>{{ sup.company_name || sup.name }}</span>
                <span v-if="sup.location" class="text-gray-400 text-[10px]">({{ sup.location }})</span>
              </button>
            </div>
          </div>

          <!-- Direct Supplier Information Inputs -->
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 pt-2">
            <!-- Supplier / Vendor Name -->
            <div class="sm:col-span-1 md:col-span-1">
              <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Supplier / Factory Name</label>
              <input
                v-model="form.supplier_name"
                type="text"
                placeholder="e.g. Guangzhou Shengfeng Electronic Co."
                class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white"
              />
              <p v-if="form.errors.supplier_name" class="mt-1 text-xs text-error-500">{{ form.errors.supplier_name }}</p>
            </div>

            <!-- Supplier Contact Details -->
            <div class="sm:col-span-1 md:col-span-1">
              <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Supplier Contact / WeChat / Phone</label>
              <input
                v-model="form.supplier_contact"
                type="text"
                placeholder="e.g. WeChat: wx_supplier | +86 138 0000"
                class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white"
              />
              <p v-if="form.errors.supplier_contact" class="mt-1 text-xs text-error-500">{{ form.errors.supplier_contact }}</p>
            </div>

            <!-- Location / City -->
            <div class="sm:col-span-2 md:col-span-1">
              <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Location / City</label>
              <input
                v-model="form.supplier_location"
                type="text"
                placeholder="e.g. Guangzhou, Yiwu, Shenzhen"
                class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white"
              />
              <p v-if="form.errors.supplier_location" class="mt-1 text-xs text-error-500">{{ form.errors.supplier_location }}</p>
            </div>

            <!-- Supplier / Catalog URL -->
            <div class="sm:col-span-2 md:col-span-3">
              <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Supplier Link / 1688 / Alibaba Product URL</label>
              <input
                v-model="form.supplier_url"
                type="url"
                placeholder="https://detail.1688.com/offer/... or https://alibaba.com/..."
                class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white"
              />
              <p v-if="form.errors.supplier_url" class="mt-1 text-xs text-error-500">{{ form.errors.supplier_url }}</p>
            </div>

            <!-- Sourcing Notes -->
            <div class="sm:col-span-2 md:col-span-3">
              <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Sourcing Notes &amp; Quotation Specs</label>
              <textarea
                v-model="form.sourcing_notes"
                rows="2"
                placeholder="e.g. Factory price RMB 48/unit. MOQ 300 pcs. Lead time 7 days. Verified manufacturer on 1688."
                class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 px-3 py-2 text-xs text-gray-800 focus:border-brand-300 dark:border-gray-700 dark:text-white"
              ></textarea>
              <p v-if="form.errors.sourcing_notes" class="mt-1 text-xs text-error-500">{{ form.errors.sourcing_notes }}</p>
            </div>
          </div>
        </div>

        <!-- 3. Line Items -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03] space-y-4 shadow-xs">
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
                <div class="col-span-4 sm:col-span-2 text-right">
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
import { computed, onMounted } from 'vue'
import { useCurrency } from '@/composables/useCurrency'
const { formatCurrency } = useCurrency()

const props = defineProps({
  customers: {
    type: Array,
    default: () => []
  },
  items: {
    type: Array,
    default: () => []
  },
  taxes: {
    type: Array,
    default: () => []
  },
  rfqs: {
    type: Array,
    default: () => []
  },
  sourcingCompanies: {
    type: Array,
    default: () => []
  }
})

const form = useForm({
  customer_id: '',
  rfq_id: '',
  sourcing_company_id: '',
  supplier_name: '',
  supplier_contact: '',
  supplier_location: '',
  supplier_url: '',
  sourcing_notes: '',
  date: new Date().toISOString().split('T')[0],
  due_date: new Date(Date.now() + 14 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
  notes: '',
  global_tax_id: '',
  shipping_charge: 0,
  line_items: [
    { name: '', description: '', rate: 0.00, quantity: 1, zoho_item_id: null }
  ]
})

// Filtered RFQs based on selected customer, or all available if none selected
const filteredRfqs = computed(() => {
  if (!props.rfqs) return []
  if (!form.customer_id) return props.rfqs
  return props.rfqs.filter(r => r.customer_id === form.customer_id)
})

// Current selected RFQ object
const selectedRfq = computed(() => {
  if (!form.rfq_id || !props.rfqs) return null
  return props.rfqs.find(r => r.id === form.rfq_id) || null
})

// Suggested suppliers extracted from selected RFQ (AI suppliers / best supplier)
const suggestedSuppliers = computed(() => {
  if (!selectedRfq.value) return []
  const list = []
  if (selectedRfq.value.best_supplier && typeof selectedRfq.value.best_supplier === 'object') {
    list.push({
      ...selectedRfq.value.best_supplier,
      is_best: true
    })
  }
  if (Array.isArray(selectedRfq.value.ai_suppliers)) {
    selectedRfq.value.ai_suppliers.forEach(s => {
      if (!list.some(existing => (existing.company_name || existing.name) === (s.company_name || s.name))) {
        list.push(s)
      }
    })
  }
  return list
})

const formatStatusLabel = (status) => {
  if (!status) return ''
  return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

// When customer changes, if current RFQ does not belong to customer, reset RFQ
const onCustomerChange = (newCustomerId) => {
  if (form.rfq_id && selectedRfq.value && selectedRfq.value.customer_id !== newCustomerId) {
    form.rfq_id = ''
  }
}

// When RFQ is selected
const onRfqSelect = () => {
  if (!selectedRfq.value) return
  // Auto-fill customer if empty
  if (!form.customer_id && selectedRfq.value.customer_id) {
    form.customer_id = selectedRfq.value.customer_id
  }
  // If first line item is blank, suggest RFQ product name
  if (form.line_items.length === 1 && !form.line_items[0].name) {
    form.line_items[0].name = selectedRfq.value.product_name
    form.line_items[0].description = selectedRfq.value.specifications || ''
  }
}

// Quick apply supplier suggestion from RFQ
const applySupplierSuggestion = (sup) => {
  form.supplier_name = sup.company_name || sup.name || ''
  form.supplier_location = sup.location || 'China'
  if (sup.contact_hint) {
    form.supplier_contact = sup.contact_hint
  }
  if (sup.why_recommended || sup.specialization) {
    form.sourcing_notes = [
      sup.specialization ? `Specialization: ${sup.specialization}` : '',
      sup.estimated_moq ? `Estimated MOQ: ${sup.estimated_moq}` : '',
      sup.why_recommended ? `Notes: ${sup.why_recommended}` : '',
    ].filter(Boolean).join(' | ')
  }
}

// When Sourcing Company is selected from dropdown
const onSourcingCompanySelect = () => {
  if (!form.sourcing_company_id) return
  const company = (props.sourcingCompanies || []).find(c => c.id === form.sourcing_company_id)
  if (company) {
    form.supplier_name = company.name
    form.supplier_location = company.location || ''
    form.supplier_contact = [company.phone, company.email].filter(Boolean).join(' | ')
    if (company.website) {
      form.supplier_url = company.website
    }
  }
}

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

onMounted(() => {
  // Check URL query parameters (e.g. ?rfq_id=... or ?customer_id=...)
  if (typeof window !== 'undefined') {
    const params = new URLSearchParams(window.location.search)
    const urlRfqId = params.get('rfq_id')
    const urlCustomerId = params.get('customer_id')

    if (urlCustomerId) {
      form.customer_id = urlCustomerId
    }
    if (urlRfqId) {
      form.rfq_id = urlRfqId
      onRfqSelect()
    }
  }
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
