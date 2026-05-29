<template>
  <AdminLayout>
    <Head title="User Management" />
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">User Management</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">Manage your system users and their roles.</p>
        </div>
        <button @click="openCreateModal" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 4.16666V15.8333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M4.16669 10H15.8334" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Add New User
        </button>
      </div>

      <!-- Flash error -->
      <div v-if="$page.props.flash?.error" class="bg-error-50 text-error-700 px-4 py-3 rounded-lg text-sm border border-error-200 dark:bg-error-500/15 dark:border-error-500/20 dark:text-error-500">
        {{ $page.props.flash.error }}
      </div>

      <!-- Filter bar -->
      <div class="flex flex-col sm:flex-row gap-3">
        <!-- Search -->
        <div class="relative flex-1">
          <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
          </svg>
          <input
            id="user-search"
            v-model="searchInput"
            @input="debouncedSearch"
            type="text"
            placeholder="Search by name or email…"
            class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 pl-9 pr-4 text-sm text-gray-800 focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-300/30 dark:border-gray-700 dark:text-white"
          />
        </div>
        <!-- Role filter -->
        <select
          id="user-role-filter"
          v-model="roleInput"
          @change="applyFilters"
          class="dark:bg-dark-900 h-10 rounded-lg border border-gray-300 px-3 text-sm text-gray-800 focus:border-brand-400 focus:outline-none dark:border-gray-700 dark:text-white min-w-[160px]"
        >
          <option value="">All Roles</option>
          <option v-for="role in availableRoles" :key="role" :value="role" class="capitalize">
            {{ role.replace('_', ' ') }}
          </option>
        </select>
        <!-- Clear -->
        <button
          v-if="searchInput || roleInput"
          @click="clearFilters"
          class="h-10 px-4 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.04] transition-colors"
        >
          Clear
        </button>
      </div>

      <!-- Table -->
      <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="max-w-full overflow-x-auto custom-scrollbar">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">User</p></th>
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Role</p></th>
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Status</p></th>
                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Zoho Sync</p></th>
                <th class="px-5 py-3 text-right sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Actions</p></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-4 sm:px-6">
                  <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full overflow-hidden bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400">
                      <img v-if="user.avatar" :src="'/storage/' + user.avatar" alt="Avatar" class="h-full w-full object-cover" />
                      <span v-else class="text-sm font-semibold">{{ user.name.charAt(0).toUpperCase() }}</span>
                    </div>
                    <div>
                      <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ user.name }}</p>
                      <p class="text-gray-500 text-theme-xs dark:text-gray-400">{{ user.email }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <p class="text-gray-500 text-theme-sm dark:text-gray-400 capitalize">{{ user.role.replace('_', ' ') }}</p>
                  <p v-if="user.role === 'agent'" class="text-[10px] text-brand-500 font-medium truncate max-w-[150px]" :title="user.category_names">
                    {{ user.category_names || 'No Categories' }}
                  </p>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <span class="inline-flex items-center rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Active</span>
                </td>
                <td class="px-5 py-4 sm:px-6">
                  <span v-if="user.zoho_contact_id" class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2 py-0.5 text-theme-xs font-medium text-blue-600 dark:bg-blue-500/15 dark:text-blue-500">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" class="w-3 h-3"><path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Synced
                  </span>
                  <button v-else @click="syncUser(user)" :disabled="syncingId === user.id" class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs font-medium text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                    <span v-if="syncingId === user.id" class="inline-block w-3 h-3 border-2 border-gray-400 border-t-transparent rounded-full animate-spin"></span>
                    <span v-else>Not Synced (Click to Sync)</span>
                  </button>
                </td>
                <td class="px-5 py-4 text-right sm:px-6">
                  <div class="flex items-center justify-end gap-3">
                    <button @click="editUser(user)" class="inline-flex items-center gap-1.5 rounded bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">Edit</button>
                    <button @click="confirmDelete(user)" class="inline-flex items-center gap-1.5 rounded bg-error-50 px-3 py-1.5 text-xs font-medium text-error-600 hover:bg-error-100 dark:bg-error-500/10 dark:text-error-500 dark:hover:bg-error-500/20 transition-colors">Delete</button>
                  </div>
                </td>
              </tr>
              <tr v-if="users.data.length === 0">
                <td colspan="5" class="px-5 py-12 text-center">
                  <p class="text-gray-400 dark:text-gray-500 text-sm">No users found matching your filters.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="users.meta.last_page > 1" class="flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-gray-100 dark:border-gray-800 px-6 py-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Showing <span class="font-medium text-gray-800 dark:text-white">{{ users.meta.from }}</span>–<span class="font-medium text-gray-800 dark:text-white">{{ users.meta.to }}</span> of <span class="font-medium text-gray-800 dark:text-white">{{ users.meta.total }}</span> users
          </p>
          <div class="flex items-center gap-1">
            <!-- Prev -->
            <button
              @click="goToPage(users.meta.current_page - 1)"
              :disabled="users.meta.current_page === 1"
              class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.04] transition-colors"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <!-- Page numbers -->
            <template v-for="page in visiblePages" :key="page">
              <span v-if="page === '...'" class="inline-flex h-9 w-9 items-center justify-center text-sm text-gray-400">…</span>
              <button
                v-else
                @click="goToPage(page)"
                :class="[
                  'inline-flex h-9 w-9 items-center justify-center rounded-lg text-sm font-medium transition-colors',
                  page === users.meta.current_page
                    ? 'bg-brand-500 text-white'
                    : 'border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.04]'
                ]"
              >{{ page }}</button>
            </template>

            <!-- Next -->
            <button
              @click="goToPage(users.meta.current_page + 1)"
              :disabled="users.meta.current_page === users.meta.last_page"
              class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.04] transition-colors"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit/Create Modal -->
    <div v-if="isModalOpen" class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <div class="no-scrollbar relative w-full max-w-[500px] overflow-y-auto rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-11 max-h-[90vh] shadow-xl">
        <button @click="isModalOpen = false" class="absolute right-5 top-5 z-999 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 dark:bg-white/[0.05] dark:text-gray-400 dark:hover:bg-white/[0.07]">
          <svg width="24" height="24" fill="none"><path d="M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <h3 class="mb-5 text-xl font-semibold text-gray-800 dark:text-white/90">{{ isEditing ? 'Edit User' : 'Create New User' }}</h3>
        <form @submit.prevent="submitForm" class="space-y-5">
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Full Name</label>
            <input v-model="form.name" type="text" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" required />
            <div v-if="form.errors.name" class="text-error-500 text-xs mt-1">{{ form.errors.name }}</div>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email Address</label>
            <input v-model="form.email" type="email" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" required />
            <div v-if="form.errors.email" class="text-error-500 text-xs mt-1">{{ form.errors.email }}</div>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Role</label>
            <select v-model="form.role" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" required>
              <option value="" disabled>Select a role</option>
              <option v-for="role in availableRoles" :key="role" :value="role" class="capitalize">{{ role.replace('_', ' ') }}</option>
            </select>
            <div v-if="form.errors.role" class="text-error-500 text-xs mt-1">{{ form.errors.role }}</div>
          </div>
          <div v-if="form.role === 'agent'" class="space-y-3">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Assigned Categories</label>
            <div class="grid grid-cols-2 gap-3 max-h-40 overflow-y-auto p-3 rounded-lg border border-gray-200 dark:border-gray-700 custom-scrollbar">
              <div v-for="category in availableCategories" :key="category.id" class="flex items-center gap-2">
                <input type="checkbox" :id="`cat-${category.id}`" v-model="form.category_ids" :value="category.id" class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800" />
                <label :for="`cat-${category.id}`" class="text-xs text-gray-600 dark:text-gray-400 cursor-pointer select-none">{{ category.name }}</label>
              </div>
            </div>
            <p class="text-[10px] text-gray-500 italic">This agent will handle RFQs submitted for these categories.</p>
            <div v-if="form.errors.category_ids" class="text-error-500 text-xs mt-1">{{ form.errors.category_ids }}</div>
          </div>
          <div v-if="!isEditing">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Password</label>
            <input v-model="form.password" type="password" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" :required="!isEditing" />
            <div v-if="form.errors.password" class="text-error-500 text-xs mt-1">{{ form.errors.password }}</div>
          </div>
          <div class="flex items-center gap-3 mt-8">
            <button @click="isModalOpen = false" type="button" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 sm:w-auto">Cancel</button>
            <button type="submit" class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto" :disabled="form.processing">
              <span v-if="form.processing">Saving...</span>
              <span v-else>{{ isEditing ? 'Update User' : 'Create User' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="isDeleteModalOpen" class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <div class="relative w-full max-w-md rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8 shadow-xl text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-error-50 dark:bg-error-500/10 mb-6">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" class="text-error-500"><path d="M12 8V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 16.0195V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/></svg>
        </div>
        <h3 class="mb-2 text-xl font-bold text-gray-800 dark:text-white/90">Delete User</h3>
        <p class="mb-8 text-sm text-gray-500 dark:text-gray-400">Are you sure you want to delete <span class="font-semibold text-gray-800 dark:text-white">{{ userToDelete?.name }}</span>? This action cannot be undone.</p>
        <div class="flex items-center justify-center gap-3">
          <button @click="isDeleteModalOpen = false" type="button" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 sm:w-auto transition-colors">Cancel</button>
          <button @click="executeDelete" type="button" class="w-full rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-error-600 sm:w-auto transition-colors" :disabled="deleteForm.processing">
            <span v-if="deleteForm.processing">Deleting...</span>
            <span v-else>Yes, Delete</span>
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  users: Object,          // paginator: { data, meta, links }
  availableRoles: Array,
  availableCategories: Array,
  filters: Object,
})

// ── Filters ──────────────────────────────────────────────────────────
const searchInput = ref(props.filters?.search ?? '')
const roleInput   = ref(props.filters?.role ?? '')

let debounceTimer = null
const debouncedSearch = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(applyFilters, 350)
}

const applyFilters = () => {
  router.get(route('admin.users'), {
    search: searchInput.value || undefined,
    role:   roleInput.value   || undefined,
  }, { preserveState: true, replace: true })
}

const clearFilters = () => {
  searchInput.value = ''
  roleInput.value   = ''
  applyFilters()
}

// ── Pagination ────────────────────────────────────────────────────────
const goToPage = (page) => {
  router.get(route('admin.users'), {
    page,
    search: searchInput.value || undefined,
    role:   roleInput.value   || undefined,
  }, { preserveState: true })
}

const visiblePages = computed(() => {
  const current = props.users.meta.current_page
  const last    = props.users.meta.last_page
  const pages   = []

  for (let i = 1; i <= last; i++) {
    if (i === 1 || i === last || (i >= current - 1 && i <= current + 1)) {
      pages.push(i)
    } else if (pages[pages.length - 1] !== '...') {
      pages.push('...')
    }
  }
  return pages
})

// ── Sync ─────────────────────────────────────────────────────────────
const syncingId = ref(null)
const syncUser = (user) => {
  if (syncingId.value) return
  syncingId.value = user.id
  router.post(route('admin.customers.sync-zoho', user.id), {}, {
    preserveScroll: true,
    onFinish: () => { syncingId.value = null },
  })
}

// ── Modal ─────────────────────────────────────────────────────────────
const isModalOpen = ref(false)
const isEditing   = ref(false)
const editingId   = ref(null)

const form = useForm({
  name: '', email: '', role: '', password: '', category_ids: []
})

const openCreateModal = () => {
  isEditing.value = false
  editingId.value = null
  form.reset()
  form.category_ids = []
  form.clearErrors()
  isModalOpen.value = true
}

const editUser = (user) => {
  isEditing.value     = true
  editingId.value     = user.id
  form.name           = user.name
  form.email          = user.email
  form.role           = user.role === 'No Role' ? '' : user.role
  form.category_ids   = user.category_ids || []
  form.password       = ''
  form.clearErrors()
  isModalOpen.value   = true
}

const submitForm = () => {
  if (isEditing.value) {
    form.put(route('admin.users.update', editingId.value), {
      onSuccess: () => { isModalOpen.value = false },
      preserveScroll: true,
    })
  } else {
    form.post(route('admin.users.store'), {
      onSuccess: () => { isModalOpen.value = false },
      preserveScroll: true,
    })
  }
}

// ── Delete ────────────────────────────────────────────────────────────
const isDeleteModalOpen = ref(false)
const userToDelete      = ref(null)
const deleteForm        = useForm({})

const confirmDelete = (user) => {
  userToDelete.value      = user
  isDeleteModalOpen.value = true
}

const executeDelete = () => {
  if (!userToDelete.value) return
  deleteForm.delete(route('admin.users.destroy', userToDelete.value.id), {
    onSuccess: () => {
      isDeleteModalOpen.value = false
      userToDelete.value      = null
    },
    preserveScroll: true,
  })
}
</script>
