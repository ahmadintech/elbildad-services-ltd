<template>
  <div class="relative" ref="wrapper">
    <!-- Trigger button -->
    <button
      type="button"
      :id="id"
      @click="toggle"
      class="dark:bg-dark-900 h-11 w-full rounded-lg border px-4 py-2.5 text-sm text-left flex items-center justify-between transition-colors"
      :class="[
        open
          ? 'border-brand-400 ring-2 ring-brand-300/30 dark:border-brand-500'
          : 'border-gray-300 dark:border-gray-700',
        selected
          ? 'text-gray-800 dark:text-white'
          : 'text-gray-400 dark:text-gray-500'
      ]"
    >
      <span class="truncate">{{ selected ? selected.name + ' — ' + selected.email : placeholder }}</span>
      <svg
        class="h-4 w-4 flex-shrink-0 text-gray-400 transition-transform duration-200"
        :class="{ 'rotate-180': open }"
        fill="none" viewBox="0 0 24 24" stroke="currentColor"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <!-- Dropdown panel -->
    <Transition
      enter-active-class="transition ease-out duration-150"
      enter-from-class="opacity-0 translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-1"
    >
      <div
        v-if="open"
        class="absolute z-50 mt-1.5 w-full rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900"
      >
        <!-- Search input -->
        <div class="p-2 border-b border-gray-100 dark:border-gray-800">
          <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" />
            </svg>
            <input
              ref="searchInput"
              v-model="query"
              type="text"
              placeholder="Search customer…"
              class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-9 pr-3 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-400 focus:outline-none focus:ring-1 focus:ring-brand-300/40 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500"
            />
          </div>
        </div>

        <!-- List -->
        <ul class="max-h-56 overflow-y-auto py-1.5" role="listbox">
          <li v-if="filtered.length === 0" class="px-4 py-3 text-sm text-gray-400 dark:text-gray-500 text-center">
            No customers found
          </li>
          <li
            v-for="customer in filtered"
            :key="customer.id"
            @click="select(customer)"
            role="option"
            :aria-selected="modelValue === customer.id"
            class="flex flex-col px-4 py-2.5 cursor-pointer text-sm transition-colors hover:bg-brand-50 dark:hover:bg-brand-500/10"
            :class="modelValue === customer.id ? 'bg-brand-50 dark:bg-brand-500/10' : ''"
          >
            <span class="font-medium text-gray-800 dark:text-white">{{ customer.name }}</span>
            <span class="text-xs text-gray-400 dark:text-gray-500">{{ customer.email }}</span>
          </li>
        </ul>

        <!-- Clear option if a value is selected -->
        <div v-if="selected" class="border-t border-gray-100 dark:border-gray-800 p-1.5">
          <button
            type="button"
            @click="clear"
            class="w-full rounded-lg px-3 py-1.5 text-xs text-error-500 hover:bg-error-50 dark:hover:bg-error-500/10 text-left transition-colors"
          >
            Clear selection
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  customers: { type: Array, default: () => [] },
  placeholder: { type: String, default: 'Select Customer' },
  id: { type: String, default: 'customer-select' },
})

const emit = defineEmits(['update:modelValue'])

const open = ref(false)
const query = ref('')
const wrapper = ref(null)
const searchInput = ref(null)

const selected = computed(() =>
  props.customers.find(c => c.id === props.modelValue) ?? null
)

const filtered = computed(() => {
  const q = query.value.toLowerCase().trim()
  if (!q) return props.customers
  return props.customers.filter(
    c => c.name.toLowerCase().includes(q) || c.email.toLowerCase().includes(q)
  )
})

function toggle() {
  open.value = !open.value
  if (open.value) {
    query.value = ''
    nextTick(() => searchInput.value?.focus())
  }
}

function select(customer) {
  emit('update:modelValue', customer.id)
  open.value = false
  query.value = ''
}

function clear() {
  emit('update:modelValue', '')
  open.value = false
  query.value = ''
}

function handleOutsideClick(e) {
  if (wrapper.value && !wrapper.value.contains(e.target)) {
    open.value = false
  }
}

onMounted(() => document.addEventListener('mousedown', handleOutsideClick))
onBeforeUnmount(() => document.removeEventListener('mousedown', handleOutsideClick))
</script>
