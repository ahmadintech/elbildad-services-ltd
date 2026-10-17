<template>
  <AdminLayout>
    <Head title="Expense Management" />

    <div class="space-y-7 pb-10">
      <!-- Top Title & Controls Bar -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="flex items-center gap-3">
            <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">
              Expenses & Disbursements
            </h1>
            <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-bold text-brand-700 dark:bg-brand-950/40 dark:text-brand-300 border border-brand-200 dark:border-brand-800">
              <i class="fa-solid fa-shield-halved text-[10px]"></i>
              Admin & Owner
            </span>
          </div>
          <p class="mt-1.5 text-base text-gray-600 dark:text-gray-300">
            Track procurement purchases, RFQ cargo shipments, running transit logistics, and agent payroll.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <!-- Export CSV -->
          <a
            :href="exportUrl"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 shadow-xs transition"
            title="Download CSV financial ledger"
          >
            <i class="fa-solid fa-file-csv text-emerald-600 text-base"></i>
            <span>Export CSV</span>
          </a>

          <!-- Record Expense Button -->
          <button
            type="button"
            @click="openCreateModal"
            class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-600 shadow-md transition-all active:scale-95"
          >
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Record Expense</span>
          </button>
        </div>
      </div>

      <!-- Financial Metric KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Disbursements -->
        <div class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60">
          <div class="flex items-center justify-between">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
              Total Expenses
            </p>
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/40 dark:text-brand-400">
              <i class="fa-solid fa-wallet text-base"></i>
            </div>
          </div>
          <div class="mt-3">
            <p class="text-2xl font-black text-gray-900 dark:text-white">
              {{ formatNaira(stats.total_expenses) }}
            </p>
            <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
              <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-bold">
                <i class="fa-solid fa-calendar-check text-[10px] mr-1"></i>
                This Month:
              </span>
              <span>{{ formatNaira(stats.this_month) }}</span>
            </p>
          </div>
        </div>

        <!-- Card 2: Purchases -->
        <div class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60">
          <div class="flex items-center justify-between">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
              Purchases
            </p>
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
              <i class="fa-solid fa-cart-shopping text-base"></i>
            </div>
          </div>
          <div class="mt-3">
            <p class="text-2xl font-black text-gray-900 dark:text-white">
              {{ formatNaira(stats.total_purchase) }}
            </p>
            <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">
              Goods procurement & supplier costs
            </p>
          </div>
        </div>

        <!-- Card 3: Freight & Running Shipment -->
        <div class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60">
          <div class="flex items-center justify-between">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
              Shipment & Logistics
            </p>
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
              <i class="fa-solid fa-plane-departure text-base"></i>
            </div>
          </div>
          <div class="mt-3">
            <p class="text-2xl font-black text-gray-900 dark:text-white">
              {{ formatNaira(stats.total_shipment + stats.total_running_shipment) }}
            </p>
            <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
              <span>Cargo: {{ formatNaira(stats.total_shipment) }}</span>
              <span class="text-gray-300 dark:text-gray-700">•</span>
              <span class="text-amber-600 dark:text-amber-400 font-bold">Transit: {{ formatNaira(stats.total_running_shipment) }}</span>
            </p>
          </div>
        </div>

        <!-- Card 4: Staff & Agent Salaries -->
        <div class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60">
          <div class="flex items-center justify-between">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
              Staff Salaries
            </p>
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
              <i class="fa-solid fa-users-gear text-base"></i>
            </div>
          </div>
          <div class="mt-3">
            <p class="text-2xl font-black text-gray-900 dark:text-white">
              {{ formatNaira(stats.total_salary) }}
            </p>
            <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">
              Agent compensation & payroll disbursements
            </p>
          </div>
        </div>
      </div>

      <!-- Filter Toolbar -->
      <div class="rounded-2xl border border-gray-200/80 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900/60 space-y-4">
        <!-- Category Filter Tabs -->
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-3 dark:border-gray-800">
          <div class="flex flex-wrap items-center gap-1.5">
            <button
              v-for="cat in categoryTabs"
              :key="cat.value"
              type="button"
              @click="setCategory(cat.value)"
              :class="[
                'inline-flex items-center gap-2 rounded-xl px-3.5 py-2 text-xs font-bold transition-all',
                filterForm.category === cat.value
                  ? 'bg-brand-500 text-white shadow-xs'
                  : 'bg-gray-50 text-gray-600 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
              ]"
            >
              <i :class="cat.icon"></i>
              <span>{{ cat.label }}</span>
            </button>
          </div>

          <!-- View Mode (Table vs Cards) -->
          <div class="inline-flex rounded-xl border border-gray-200 bg-gray-50 p-1 dark:border-gray-700 dark:bg-gray-800 shadow-xs">
            <button
              type="button"
              @click="viewMode = 'table'"
              :class="[
                'inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition',
                viewMode === 'table'
                  ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-700 dark:text-white'
                  : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'
              ]"
            >
              <i class="fa-solid fa-table-list"></i>
              <span>Table</span>
            </button>
            <button
              type="button"
              @click="viewMode = 'cards'"
              :class="[
                'inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition',
                viewMode === 'cards'
                  ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-700 dark:text-white'
                  : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'
              ]"
            >
              <i class="fa-solid fa-table-columns"></i>
              <span>Cards</span>
            </button>
          </div>
        </div>

        <!-- Search & Dropdown Filters -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          <!-- Search input -->
          <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input
              v-model="filterForm.search"
              @keyup.enter="applyFilters"
              type="text"
              placeholder="Search expenses, notes, vendors..."
              class="h-10 w-full rounded-xl border border-gray-200 pl-9 pr-3 text-xs font-medium text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            />
          </div>

          <!-- RFQ Filter Dropdown -->
          <div>
            <select
              v-model="filterForm.rfq_id"
              @change="applyFilters"
              class="h-10 w-full rounded-xl border border-gray-200 px-3 text-xs font-medium text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            >
              <option value="all">All Linked RFQs</option>
              <option v-for="rfq in rfqs" :key="rfq.id" :value="rfq.id">
                RFQ: {{ rfq.product_name }} (#{{ rfq.tracking_token.substring(0, 8) }})
              </option>
            </select>
          </div>

          <!-- Agent Filter Dropdown -->
          <div>
            <select
              v-model="filterForm.agent_id"
              @change="applyFilters"
              class="h-10 w-full rounded-xl border border-gray-200 px-3 text-xs font-medium text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            >
              <option value="all">All Agents (Salaries)</option>
              <option v-for="agent in agents" :key="agent.id" :value="agent.id">
                {{ agent.name }}
              </option>
            </select>
          </div>

          <!-- Filter Action Buttons -->
          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="applyFilters"
              class="h-10 flex-1 rounded-xl bg-brand-500 px-3 text-xs font-bold text-white hover:bg-brand-600 transition shadow-xs"
            >
              <i class="fa-solid fa-filter mr-1"></i> Filter
            </button>
            <button
              v-if="hasActiveFilters"
              type="button"
              @click="resetFilters"
              class="h-10 rounded-xl border border-gray-200 px-3 text-xs font-bold text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
              title="Reset Filters"
            >
              <i class="fa-solid fa-rotate-left"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Main Expenses List (Table View) -->
      <div v-if="expenses.data.length === 0" class="rounded-2xl border border-gray-200 bg-white py-20 text-center shadow-xs dark:border-gray-800 dark:bg-gray-900/60">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800">
          <i class="fa-solid fa-receipt text-2xl"></i>
        </div>
        <h3 class="mt-4 text-base font-bold text-gray-900 dark:text-white">No expenses recorded</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Click "Record Expense" to log purchases, shipments, transit costs, or salaries.
        </p>
      </div>

      <!-- Standard Responsive Table View -->
      <div v-else-if="viewMode === 'table'" class="overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900/60">
        <div class="overflow-x-auto custom-scrollbar">
          <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50/90 dark:bg-gray-800/80 text-gray-600 dark:text-gray-300 uppercase tracking-wider text-xs font-bold border-b border-gray-200 dark:border-gray-800">
              <tr>
                <th class="w-36 px-4 py-3.5">Ref / Date</th>
                <th class="w-40 px-4 py-3.5 text-center">Category</th>
                <th class="min-w-[280px] px-5 py-3.5">Title & Notes</th>
                <th class="min-w-[200px] px-4 py-3.5">Linked Context</th>
                <th class="min-w-[150px] px-4 py-3.5 text-right">Amount (NGN)</th>
                <th class="w-24 px-4 py-3.5 text-center">Receipt</th>
                <th class="w-24 px-4 py-3.5 text-right pr-5">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-transparent">
              <tr
                v-for="expense in expenses.data"
                :key="expense.id"
                class="transition-colors hover:bg-gray-50/80 dark:hover:bg-gray-800/40"
              >
                <!-- Reference & Date -->
                <td class="px-4 py-4 align-top">
                  <div class="space-y-0.5">
                    <span class="font-mono text-xs font-extrabold text-brand-600 dark:text-brand-400">
                      {{ expense.expense_number }}
                    </span>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                      {{ expense.expense_date }}
                    </p>
                  </div>
                </td>

                <!-- Category Pill Badge -->
                <td class="px-4 py-4 text-center align-middle">
                  <span
                    :class="getCategoryBadgeClass(expense.category)"
                    class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-extrabold shadow-xs"
                  >
                    <i :class="getCategoryIcon(expense.category)"></i>
                    <span>{{ getCategoryLabel(expense.category) }}</span>
                  </span>
                </td>

                <!-- Title & Notes -->
                <td class="px-5 py-4 align-top">
                  <div class="space-y-1">
                    <p class="text-sm font-bold text-gray-900 dark:text-white leading-snug">
                      {{ expense.title }}
                    </p>
                    <p v-if="expense.note" class="text-xs text-gray-600 dark:text-gray-300 font-medium line-clamp-2">
                      <span class="font-bold text-gray-700 dark:text-gray-200">Note:</span> {{ expense.note }}
                    </p>
                    <div v-if="expense.vendor_name" class="inline-flex items-center gap-1 text-[11px] font-semibold text-gray-500 dark:text-gray-400">
                      <i class="fa-solid fa-store text-gray-400 text-[10px]"></i>
                      <span>Vendor: {{ expense.vendor_name }}</span>
                    </div>
                  </div>
                </td>

                <!-- Linked Context (RFQ, Item Number, Agent Salary) -->
                <td class="px-4 py-4 align-top">
                  <div class="space-y-1.5">
                    <!-- If Linked to RFQ -->
                    <div v-if="expense.rfq" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200/60 dark:border-blue-900">
                      <i class="fa-solid fa-boxes-packing text-[10px]"></i>
                      <span class="truncate max-w-[140px]" :title="expense.rfq.product_name">
                        {{ expense.rfq.product_name }}
                      </span>
                    </div>

                    <!-- If Item Number or Waybill present -->
                    <div v-if="expense.item_number" class="flex items-center gap-1 text-xs font-mono font-bold text-gray-700 dark:text-gray-300">
                      <i class="fa-solid fa-tag text-gray-400 text-[10px]"></i>
                      <span>Item/Waybill: {{ expense.item_number }}</span>
                    </div>

                    <!-- If Salary (Agent) -->
                    <div v-if="expense.agent" class="flex items-center gap-2">
                      <div class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-[11px] font-black text-white">
                        {{ expense.agent.name.charAt(0) }}
                      </div>
                      <div class="min-w-0">
                        <p class="text-xs font-bold text-gray-900 dark:text-white truncate max-w-[120px]">
                          {{ expense.agent.name }}
                        </p>
                        <p v-if="expense.salary_period" class="text-[10px] font-medium text-emerald-600 dark:text-emerald-400">
                          {{ expense.salary_period }}
                        </p>
                      </div>
                    </div>

                    <span v-if="!expense.rfq && !expense.item_number && !expense.agent" class="text-xs text-gray-400 font-medium">
                      General / Direct
                    </span>
                  </div>
                </td>

                <!-- Amount strictly in NGN -->
                <td class="px-4 py-4 text-right align-middle">
                  <div class="font-mono text-base font-black text-gray-950 dark:text-white">
                    {{ formatNaira(expense.amount) }}
                  </div>
                  <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    {{ expense.payment_method.replace('_', ' ') }}
                  </span>
                </td>

                <!-- Receipt Attachment -->
                <td class="px-4 py-4 text-center align-middle">
                  <div v-if="expense.receipt_url" class="inline-flex items-center justify-center">
                    <button
                      type="button"
                      @click="previewReceipt(expense.receipt_url)"
                      class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-700 hover:bg-gray-100 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:text-white transition shadow-xs"
                      title="View Receipt Attachment"
                    >
                      <i class="fa-solid fa-paperclip text-xs"></i>
                    </button>
                  </div>
                  <span v-else class="text-xs text-gray-300 dark:text-gray-600">—</span>
                </td>

                <!-- Actions (Edit & Delete) -->
                <td class="px-4 py-4 text-right align-middle pr-5">
                  <div class="flex items-center justify-end gap-1.5">
                    <button
                      type="button"
                      @click="openEditModal(expense)"
                      class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-white transition"
                      title="Edit Expense"
                    >
                      <i class="fa-solid fa-pen-to-square text-xs"></i>
                    </button>
                    <button
                      type="button"
                      @click="confirmDelete(expense)"
                      class="rounded-lg p-2 text-gray-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40 transition"
                      title="Delete Expense"
                    >
                      <i class="fa-solid fa-trash-can text-xs"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="expenses.links && expenses.links.length > 3" class="flex items-center justify-between border-t border-gray-200/80 px-5 py-3.5 dark:border-gray-800">
          <p class="text-xs text-gray-500 dark:text-gray-400">
            Showing {{ expenses.from }} to {{ expenses.to }} of {{ expenses.total }} expenses
          </p>
          <div class="flex items-center gap-1">
            <template v-for="(link, i) in expenses.links" :key="i">
              <button
                v-if="link.url"
                type="button"
                @click="goToPage(link.url)"
                :class="[
                  'rounded-lg px-3 py-1.5 text-xs font-bold transition',
                  link.active
                    ? 'bg-brand-500 text-white'
                    : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'
                ]"
                v-html="link.label"
              />
            </template>
          </div>
        </div>
      </div>

      <!-- Cards Grid View (Alternative View for responsive display) -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="expense in expenses.data"
          :key="expense.id"
          class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60 flex flex-col justify-between"
        >
          <div>
            <div class="flex items-center justify-between mb-3">
              <span
                :class="getCategoryBadgeClass(expense.category)"
                class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-extrabold shadow-xs"
              >
                <i :class="getCategoryIcon(expense.category)"></i>
                <span>{{ getCategoryLabel(expense.category) }}</span>
              </span>
              <span class="font-mono text-xs font-bold text-gray-400">
                {{ expense.expense_number }}
              </span>
            </div>

            <h3 class="text-base font-bold text-gray-900 dark:text-white leading-snug">
              {{ expense.title }}
            </h3>

            <p v-if="expense.note" class="mt-2 text-xs text-gray-600 dark:text-gray-300">
              <span class="font-bold">Note:</span> {{ expense.note }}
            </p>

            <!-- Linked context -->
            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 space-y-1.5 text-xs">
              <div v-if="expense.rfq" class="flex items-center gap-1.5 text-blue-600 dark:text-blue-400 font-semibold">
                <i class="fa-solid fa-boxes-packing text-[11px]"></i>
                <span class="truncate">RFQ: {{ expense.rfq.product_name }}</span>
              </div>
              <div v-if="expense.item_number" class="flex items-center gap-1.5 font-mono text-gray-700 dark:text-gray-300">
                <i class="fa-solid fa-tag text-[10px] text-gray-400"></i>
                <span>Item/Waybill: {{ expense.item_number }}</span>
              </div>
              <div v-if="expense.agent" class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-semibold">
                <i class="fa-solid fa-user-check text-[11px]"></i>
                <span>Agent: {{ expense.agent.name }}</span>
              </div>
            </div>
          </div>

          <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <div>
              <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Amount</p>
              <p class="font-mono text-lg font-black text-gray-950 dark:text-white">
                {{ formatNaira(expense.amount) }}
              </p>
            </div>
            <div class="flex items-center gap-1.5">
              <button
                v-if="expense.receipt_url"
                type="button"
                @click="previewReceipt(expense.receipt_url)"
                class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                title="View Receipt"
              >
                <i class="fa-solid fa-paperclip"></i>
              </button>
              <button
                type="button"
                @click="openEditModal(expense)"
                class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                title="Edit"
              >
                <i class="fa-solid fa-pen-to-square"></i>
              </button>
              <button
                type="button"
                @click="confirmDelete(expense)"
                class="rounded-lg p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40"
                title="Delete"
              >
                <i class="fa-solid fa-trash-can"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Create / Edit Dynamic Modal -->
      <div
        v-if="showModal"
        class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-gray-900/70 backdrop-blur-xs overflow-y-auto"
        @click.self="showModal = false"
      >
        <div class="w-full max-w-2xl rounded-3xl bg-white shadow-2xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 my-8 overflow-hidden transition-all">
          <!-- Modal Header -->
          <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4.5 dark:border-gray-800">
            <div>
              <h2 class="text-xl font-black text-gray-900 dark:text-white">
                {{ isEditing ? 'Edit Expense Record' : 'Record New Expense' }}
              </h2>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                All amounts are strictly calculated in Nigerian Naira (₦).
              </p>
            </div>
            <button
              type="button"
              @click="showModal = false"
              class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 transition"
            >
              <i class="fa-solid fa-xmark text-sm"></i>
            </button>
          </div>

          <!-- Modal Body Form -->
          <form @submit.prevent="submitExpense" class="p-6 space-y-5">
            <!-- Category Selector Tabs inside Modal -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-2">
                Expense Category *
              </label>
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <button
                  type="button"
                  v-for="cat in availableCategories"
                  :key="cat.value"
                  @click="form.category = cat.value"
                  :class="[
                    'flex flex-col items-center justify-center rounded-xl border p-3 text-center transition-all',
                    form.category === cat.value
                      ? 'border-brand-500 bg-brand-50/70 text-brand-700 shadow-xs dark:border-brand-500 dark:bg-brand-950/40 dark:text-brand-300 font-extrabold'
                      : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 font-semibold'
                  ]"
                >
                  <i :class="cat.icon" class="text-base mb-1.5"></i>
                  <span class="text-xs">{{ cat.label }}</span>
                </button>
              </div>
            </div>

            <!-- Dynamic Context Notice -->
            <div
              :class="[
                'rounded-xl border p-3 text-xs font-semibold flex items-start gap-2.5',
                form.category === 'purchase' ? 'border-indigo-200 bg-indigo-50/60 text-indigo-900 dark:bg-indigo-950/30 dark:text-indigo-300' :
                form.category === 'shipment' ? 'border-blue-200 bg-blue-50/60 text-blue-900 dark:bg-blue-950/30 dark:text-blue-300' :
                form.category === 'running_shipment' ? 'border-amber-200 bg-amber-50/60 text-amber-900 dark:bg-amber-950/30 dark:text-amber-300' :
                'border-emerald-200 bg-emerald-50/60 text-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300'
              ]"
            >
              <i class="fa-solid fa-circle-info text-sm mt-0.5"></i>
              <div>
                <p v-if="form.category === 'purchase'">
                  <span class="font-bold">Purchase:</span> Record supplier procurement, invoice costs, and goods purchased for orders.
                </p>
                <p v-else-if="form.category === 'shipment'">
                  <span class="font-bold">Shipment:</span> Select the target RFQ and specify the exact RFQ item/waybill number.
                </p>
                <p v-else-if="form.category === 'running_shipment'">
                  <span class="font-bold">Running Shipment:</span> Record ongoing operational logistics, customs clearing, transport, and offloading fees [Amount + Note].
                </p>
                <p v-else-if="form.category === 'salary'">
                  <span class="font-bold">Salary:</span> Select the agent receiving the payroll disbursement and specify amount [Agent + Amount].
                </p>
              </div>
            </div>

            <!-- Title / Subject -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                Expense Title / Description *
              </label>
              <input
                v-model="form.title"
                type="text"
                required
                :placeholder="getTitlePlaceholder(form.category)"
                class="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm font-semibold text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              />
            </div>

            <!-- Conditional Section: SHIPMENT (Dropdown to select RFQ, and item number [RFQ]) -->
            <div v-if="form.category === 'shipment'" class="grid grid-cols-1 sm:grid-cols-2 gap-4 rounded-2xl border border-blue-100 bg-blue-50/30 p-4 dark:border-blue-900/40 dark:bg-blue-950/20">
              <div class="sm:col-span-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-blue-900 dark:text-blue-300 mb-1.5">
                  <i class="fa-solid fa-boxes-packing mr-1"></i> Select RFQ Order *
                </label>
                <select
                  v-model="form.rfq_id"
                  required
                  class="h-11 w-full rounded-xl border border-blue-200 px-3.5 text-xs font-semibold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-blue-800 dark:bg-gray-800 dark:text-white"
                >
                  <option :value="null" disabled>Choose RFQ from list...</option>
                  <option v-for="rfq in rfqs" :key="rfq.id" :value="rfq.id">
                    {{ rfq.product_name }} — Ref: #{{ rfq.tracking_token.substring(0, 8) }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-blue-900 dark:text-blue-300 mb-1.5">
                  <i class="fa-solid fa-hashtag mr-1"></i> Item Number [RFQ] / Waybill *
                </label>
                <input
                  v-model="form.item_number"
                  type="text"
                  required
                  placeholder="e.g., Item #01, Waybill WB-8823"
                  class="h-11 w-full rounded-xl border border-blue-200 px-3.5 text-xs font-semibold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-blue-800 dark:bg-gray-800 dark:text-white"
                />
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-blue-900 dark:text-blue-300 mb-1.5">
                  <i class="fa-solid fa-plane-departure mr-1"></i> Shipping Mode
                </label>
                <select
                  v-model="form.shipping_method"
                  class="h-11 w-full rounded-xl border border-blue-200 px-3.5 text-xs font-semibold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-blue-800 dark:bg-gray-800 dark:text-white"
                >
                  <option value="Air Freight">Air Freight (Cargo)</option>
                  <option value="Sea Cargo (LCL)">Sea Cargo (LCL Container)</option>
                  <option value="Sea Cargo (FCL)">Sea Cargo (Full Container)</option>
                  <option value="Express Courier">Express Courier (DHL/FedEx)</option>
                  <option value="Local Transit">Local Haulage / Transit</option>
                </select>
              </div>
            </div>

            <!-- Conditional Section: RUNNING SHIPMENT [Amount, Note] -->
            <div v-if="form.category === 'running_shipment'" class="space-y-4 rounded-2xl border border-amber-200 bg-amber-50/30 p-4 dark:border-amber-900/40 dark:bg-amber-950/20">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-amber-900 dark:text-amber-300 mb-1.5">
                  <i class="fa-solid fa-note-sticky mr-1"></i> Running Shipment Note (Breakdown) *
                </label>
                <textarea
                  v-model="form.note"
                  rows="3"
                  required
                  placeholder="e.g., Demurrage fee at terminal, offloading labor charge, clearing agent levy, fuel for dispatch truck..."
                  class="w-full rounded-xl border border-amber-200 p-3.5 text-xs font-medium text-gray-900 focus:border-brand-500 focus:outline-none dark:border-amber-800 dark:bg-gray-800 dark:text-white"
                ></textarea>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-amber-900 dark:text-amber-300 mb-1.5">
                  Linked RFQ (Optional)
                </label>
                <select
                  v-model="form.rfq_id"
                  class="h-11 w-full rounded-xl border border-amber-200 px-3.5 text-xs font-semibold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-amber-800 dark:bg-gray-800 dark:text-white"
                >
                  <option :value="null">None / General Logistics</option>
                  <option v-for="rfq in rfqs" :key="rfq.id" :value="rfq.id">
                    {{ rfq.product_name }} — Ref: #{{ rfq.tracking_token.substring(0, 8) }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Conditional Section: SALARY [Agent, Amount] -->
            <div v-if="form.category === 'salary'" class="grid grid-cols-1 sm:grid-cols-2 gap-4 rounded-2xl border border-emerald-200 bg-emerald-50/30 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/20">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-emerald-900 dark:text-emerald-300 mb-1.5">
                  <i class="fa-solid fa-user-check mr-1"></i> Select Agent *
                </label>
                <select
                  v-model="form.agent_id"
                  required
                  class="h-11 w-full rounded-xl border border-emerald-200 px-3.5 text-xs font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-emerald-800 dark:bg-gray-800 dark:text-white"
                >
                  <option :value="null" disabled>Select agent to receive payment...</option>
                  <option v-for="agent in agents" :key="agent.id" :value="agent.id">
                    {{ agent.name }} ({{ agent.email }})
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-emerald-900 dark:text-emerald-300 mb-1.5">
                  <i class="fa-solid fa-calendar-day mr-1"></i> Salary Period (Month / Year)
                </label>
                <input
                  v-model="form.salary_period"
                  type="text"
                  placeholder="e.g., October 2026, Bi-weekly Advance"
                  class="h-11 w-full rounded-xl border border-emerald-200 px-3.5 text-xs font-semibold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-emerald-800 dark:bg-gray-800 dark:text-white"
                />
              </div>
            </div>

            <!-- Conditional Section: PURCHASE (Vendor name & RFQ link) -->
            <div v-if="form.category === 'purchase'" class="grid grid-cols-1 sm:grid-cols-2 gap-4 rounded-2xl border border-indigo-100 bg-indigo-50/30 p-4 dark:border-indigo-900/40 dark:bg-indigo-950/20">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-indigo-900 dark:text-indigo-300 mb-1.5">
                  <i class="fa-solid fa-store mr-1"></i> Supplier / Vendor Name
                </label>
                <input
                  v-model="form.vendor_name"
                  type="text"
                  placeholder="e.g., Guangzhou Huadu Factory, Yiwu Trade Co."
                  class="h-11 w-full rounded-xl border border-indigo-200 px-3.5 text-xs font-semibold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-indigo-800 dark:bg-gray-800 dark:text-white"
                />
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-indigo-900 dark:text-indigo-300 mb-1.5">
                  Linked RFQ (Optional)
                </label>
                <select
                  v-model="form.rfq_id"
                  class="h-11 w-full rounded-xl border border-indigo-200 px-3.5 text-xs font-semibold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-indigo-800 dark:bg-gray-800 dark:text-white"
                >
                  <option :value="null">None / General Inventory</option>
                  <option v-for="rfq in rfqs" :key="rfq.id" :value="rfq.id">
                    {{ rfq.product_name }} — Ref: #{{ rfq.tracking_token.substring(0, 8) }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Amount strictly in NGN & Expense Date -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                  Amount (₦ NGN) *
                </label>
                <div class="relative">
                  <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-mono font-bold text-gray-500 text-sm">₦</span>
                  <input
                    v-model="form.amount"
                    type="number"
                    step="0.01"
                    min="1"
                    required
                    placeholder="0.00"
                    class="h-11 w-full rounded-xl border border-gray-300 pl-9 pr-4 text-sm font-mono font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                  />
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                  Payment Date *
                </label>
                <input
                  v-model="form.expense_date"
                  type="date"
                  required
                  class="h-11 w-full rounded-xl border border-gray-300 px-3.5 text-xs font-semibold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />
              </div>
            </div>

            <!-- Payment Method & Receipt Upload -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                  Payment Method
                </label>
                <select
                  v-model="form.payment_method"
                  class="h-11 w-full rounded-xl border border-gray-300 px-3.5 text-xs font-semibold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                  <option value="bank_transfer">Bank Transfer</option>
                  <option value="cash">Cash</option>
                  <option value="pos">POS Terminal</option>
                  <option value="online">Online Payment / Zainpay</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                  Receipt / Invoice Proof (Image or PDF)
                </label>
                <input
                  type="file"
                  accept="image/*,application/pdf"
                  @change="handleFileUpload"
                  class="block w-full text-xs text-gray-500 file:mr-3 file:rounded-xl file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-xs file:font-bold file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-gray-800 dark:file:text-gray-300"
                />
              </div>
            </div>

            <!-- Notes (For Purchase, Shipment, or General) -->
            <div v-if="form.category !== 'running_shipment'">
              <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                Additional Notes
              </label>
              <textarea
                v-model="form.note"
                rows="2"
                placeholder="Any special remarks or breakdown notes..."
                class="w-full rounded-xl border border-gray-300 p-3 text-xs font-medium text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              ></textarea>
            </div>

            <!-- Modal Footer Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
              <button
                type="button"
                @click="showModal = false"
                class="rounded-xl border border-gray-200 px-5 py-2.5 text-xs font-bold text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="submitting"
                class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-6 py-2.5 text-xs font-extrabold text-white hover:bg-brand-600 shadow-md transition disabled:opacity-50"
              >
                <i v-if="submitting" class="fa-solid fa-spinner fa-spin"></i>
                <i v-else class="fa-solid fa-check"></i>
                <span>{{ isEditing ? 'Update Expense' : 'Save Expense' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Receipt Preview Modal -->
      <div
        v-if="showReceiptModal"
        class="fixed inset-0 z-[9999999] flex items-center justify-center p-4 bg-gray-900/80 backdrop-blur-xs"
        @click.self="showReceiptModal = false"
      >
        <div class="relative max-w-3xl w-full rounded-2xl bg-white p-4 shadow-2xl dark:bg-gray-900">
          <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Receipt Attachment Preview</h3>
            <button
              type="button"
              @click="showReceiptModal = false"
              class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800"
            >
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <div class="mt-4 flex items-center justify-center min-h-[300px] max-h-[70vh] overflow-auto">
            <img
              v-if="isImage(activeReceiptUrl)"
              :src="activeReceiptUrl"
              alt="Receipt Attachment"
              class="max-h-[65vh] w-auto rounded-lg object-contain"
            />
            <div v-else class="text-center py-10">
              <i class="fa-solid fa-file-pdf text-5xl text-rose-500 mb-3"></i>
              <p class="text-sm font-bold text-gray-900 dark:text-white">Document Attachment</p>
              <a
                :href="activeReceiptUrl"
                target="_blank"
                class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white hover:bg-brand-600 transition"
              >
                <i class="fa-solid fa-arrow-down"></i>
                <span>Open in New Tab</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import { toast } from 'vue3-toastify'

const props = defineProps({
  expenses: Object,
  stats: Object,
  rfqs: Array,
  agents: Array,
  filters: Object,
})

const page = usePage()
const viewMode = ref('table')
const showModal = ref(false)
const isEditing = ref(false)
const editingExpenseId = ref(null)
const submitting = ref(false)

const showReceiptModal = ref(false)
const activeReceiptUrl = ref('')

// Category tab filters
const categoryTabs = [
  { value: 'all', label: 'All Expenses', icon: 'fa-solid fa-layer-group' },
  { value: 'purchase', label: 'Purchase', icon: 'fa-solid fa-cart-shopping' },
  { value: 'shipment', label: 'Shipment', icon: 'fa-solid fa-plane-departure' },
  { value: 'running_shipment', label: 'Running Shipment', icon: 'fa-solid fa-truck-fast' },
  { value: 'salary', label: 'Salary', icon: 'fa-solid fa-users-gear' },
]

const availableCategories = [
  { value: 'purchase', label: 'Purchase', icon: 'fa-solid fa-cart-shopping' },
  { value: 'shipment', label: 'Shipment', icon: 'fa-solid fa-plane-departure' },
  { value: 'running_shipment', label: 'Running Shipment', icon: 'fa-solid fa-truck-fast' },
  { value: 'salary', label: 'Salary', icon: 'fa-solid fa-users-gear' },
]

// Filter form state
const filterForm = ref({
  category: props.filters?.category || 'all',
  search: props.filters?.search || '',
  rfq_id: props.filters?.rfq_id || 'all',
  agent_id: props.filters?.agent_id || 'all',
  start_date: props.filters?.start_date || '',
  end_date: props.filters?.end_date || '',
})

// Modal form state
const form = ref({
  category: 'purchase',
  title: '',
  amount: '',
  expense_date: new Date().toISOString().split('T')[0],
  payment_method: 'bank_transfer',
  note: '',
  rfq_id: null,
  item_number: '',
  shipping_method: 'Air Freight',
  vendor_name: '',
  agent_id: null,
  salary_period: '',
  receipt: null,
})

const hasActiveFilters = computed(() => {
  return (
    filterForm.value.category !== 'all' ||
    filterForm.value.search !== '' ||
    filterForm.value.rfq_id !== 'all' ||
    filterForm.value.agent_id !== 'all' ||
    filterForm.value.start_date !== '' ||
    filterForm.value.end_date !== ''
  )
})

const exportUrl = computed(() => {
  const params = new URLSearchParams()
  if (filterForm.value.category !== 'all') params.append('category', filterForm.value.category)
  if (filterForm.value.rfq_id !== 'all') params.append('rfq_id', filterForm.value.rfq_id)
  if (filterForm.value.agent_id !== 'all') params.append('agent_id', filterForm.value.agent_id)
  return `/finance/expenses/export?${params.toString()}`
})

const formatNaira = (amount) => {
  if (amount === undefined || amount === null) return '₦0.00'
  return '₦' + Number(amount).toLocaleString('en-NG', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

const getCategoryLabel = (category) => {
  switch (category) {
    case 'purchase':
      return 'Purchase'
    case 'shipment':
      return 'Shipment'
    case 'running_shipment':
      return 'Running Shipment'
    case 'salary':
      return 'Salary'
    default:
      return 'Expense'
  }
}

const getCategoryIcon = (category) => {
  switch (category) {
    case 'purchase':
      return 'fa-solid fa-cart-shopping'
    case 'shipment':
      return 'fa-solid fa-plane-departure'
    case 'running_shipment':
      return 'fa-solid fa-truck-fast'
    case 'salary':
      return 'fa-solid fa-users-gear'
    default:
      return 'fa-solid fa-receipt'
  }
}

const getCategoryBadgeClass = (category) => {
  switch (category) {
    case 'purchase':
      return 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800'
    case 'shipment':
      return 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200 dark:border-blue-800'
    case 'running_shipment':
      return 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800'
    case 'salary':
      return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800'
    default:
      return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
  }
}

const getTitlePlaceholder = (category) => {
  switch (category) {
    case 'purchase':
      return 'e.g., Procurement of 200pcs solar inverters from Guangzhou'
    case 'shipment':
      return 'e.g., Air freight charges for Guangzhou to Lagos shipment'
    case 'running_shipment':
      return 'e.g., Port clearance & demurrage fees'
    case 'salary':
      return 'e.g., Monthly salary payment for agent'
    default:
      return 'e.g., Expense description'
  }
}

const setCategory = (cat) => {
  filterForm.value.category = cat
  applyFilters()
}

const applyFilters = () => {
  router.get('/finance/expenses', filterForm.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

const resetFilters = () => {
  filterForm.value = {
    category: 'all',
    search: '',
    rfq_id: 'all',
    agent_id: 'all',
    start_date: '',
    end_date: '',
  }
  applyFilters()
}

const goToPage = (url) => {
  router.visit(url, {
    preserveState: true,
    preserveScroll: true,
  })
}

const openCreateModal = () => {
  isEditing.value = false
  editingExpenseId.value = null
  form.value = {
    category: filterForm.value.category !== 'all' ? filterForm.value.category : 'purchase',
    title: '',
    amount: '',
    expense_date: new Date().toISOString().split('T')[0],
    payment_method: 'bank_transfer',
    note: '',
    rfq_id: filterForm.value.rfq_id !== 'all' ? filterForm.value.rfq_id : null,
    item_number: '',
    shipping_method: 'Air Freight',
    vendor_name: '',
    agent_id: filterForm.value.agent_id !== 'all' ? filterForm.value.agent_id : null,
    salary_period: '',
    receipt: null,
  }
  showModal.value = true
}

const openEditModal = (expense) => {
  isEditing.value = true
  editingExpenseId.value = expense.id
  form.value = {
    category: expense.category,
    title: expense.title,
    amount: expense.amount,
    expense_date: expense.expense_date,
    payment_method: expense.payment_method || 'bank_transfer',
    note: expense.note || '',
    rfq_id: expense.rfq_id || null,
    item_number: expense.item_number || '',
    shipping_method: expense.shipping_method || 'Air Freight',
    vendor_name: expense.vendor_name || '',
    agent_id: expense.agent_id || null,
    salary_period: expense.salary_period || '',
    receipt: null,
  }
  showModal.value = true
}

const handleFileUpload = (e) => {
  const file = e.target.files[0]
  if (file) {
    form.value.receipt = file
  }
}

const submitExpense = () => {
  submitting.value = true

  const formData = new FormData()
  formData.append('category', form.value.category)
  formData.append('title', form.value.title)
  formData.append('amount', form.value.amount)
  formData.append('expense_date', form.value.expense_date)
  formData.append('payment_method', form.value.payment_method)
  if (form.value.note) formData.append('note', form.value.note)
  if (form.value.rfq_id) formData.append('rfq_id', form.value.rfq_id)
  if (form.value.item_number) formData.append('item_number', form.value.item_number)
  if (form.value.shipping_method) formData.append('shipping_method', form.value.shipping_method)
  if (form.value.vendor_name) formData.append('vendor_name', form.value.vendor_name)
  if (form.value.agent_id) formData.append('agent_id', form.value.agent_id)
  if (form.value.salary_period) formData.append('salary_period', form.value.salary_period)
  if (form.value.receipt) formData.append('receipt', form.value.receipt)

  if (isEditing.value) {
    formData.append('_method', 'PUT')
    router.post(`/finance/expenses/${editingExpenseId.value}`, formData, {
      preserveScroll: true,
      onSuccess: () => {
        showModal.value = false
        toast.success('Expense record updated successfully!')
      },
      onError: (errors) => {
        const firstError = Object.values(errors)[0]
        toast.error(firstError || 'Failed to update expense')
      },
      onFinish: () => {
        submitting.value = false
      },
    })
  } else {
    router.post('/finance/expenses', formData, {
      preserveScroll: true,
      onSuccess: () => {
        showModal.value = false
        toast.success('Expense record created successfully!')
      },
      onError: (errors) => {
        const firstError = Object.values(errors)[0]
        toast.error(firstError || 'Failed to record expense')
      },
      onFinish: () => {
        submitting.value = false
      },
    })
  }
}

const confirmDelete = (expense) => {
  if (confirm(`Are you sure you want to delete expense ${expense.expense_number} (${formatNaira(expense.amount)})?`)) {
    router.delete(`/finance/expenses/${expense.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Expense record deleted successfully')
      },
      onError: () => {
        toast.error('Failed to delete expense record')
      },
    })
  }
}

const previewReceipt = (url) => {
  activeReceiptUrl.value = url
  showReceiptModal.value = true
}

const isImage = (url) => {
  if (!url) return false
  return /\.(jpeg|jpg|png|webp|gif)$/i.test(url)
}
</script>
