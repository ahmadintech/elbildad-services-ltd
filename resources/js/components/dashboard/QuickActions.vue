<template>
  <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Quick Actions</h3>
    </div>

    <div class="grid grid-cols-2 gap-4 p-6">
      <Link href="/admin/users" class="flex items-center gap-3 rounded-lg border border-gray-200 p-4 transition-all hover:border-blue-400 hover:bg-blue-50 dark:border-gray-700 dark:hover:border-blue-400 dark:hover:bg-blue-500/10">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-500/10">
          <svg class="h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m4.646-7.354h.008v.008h-.008v-.008zM9 12a6 6 0 1112 0 6 6 0 01-12 0z" />
          </svg>
        </div>
        <div>
          <p class="text-xs font-semibold text-gray-600 dark:text-gray-400">Users</p>
          <p class="text-sm font-medium text-gray-900 dark:text-white/90">Manage Users</p>
        </div>
      </Link>

      <Link href="/admin/roles" class="flex items-center gap-3 rounded-lg border border-gray-200 p-4 transition-all hover:border-purple-400 hover:bg-purple-50 dark:border-gray-700 dark:hover:border-purple-400 dark:hover:bg-purple-500/10">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-500/10">
          <svg class="h-5 w-5 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
          </svg>
        </div>
        <div>
          <p class="text-xs font-semibold text-gray-600 dark:text-gray-400">Roles</p>
          <p class="text-sm font-medium text-gray-900 dark:text-white/90">Manage Roles</p>
        </div>
      </Link>

      <Link href="/finance/items" class="flex items-center gap-3 rounded-lg border border-gray-200 p-4 transition-all hover:border-green-400 hover:bg-green-50 dark:border-gray-700 dark:hover:border-green-400 dark:hover:bg-green-500/10">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-500/10">
          <svg class="h-5 w-5 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m0 0l8-4m8 4v10l-8 4m0-10L4 7m8 4v10m0-10l8-4" />
          </svg>
        </div>
        <div>
          <p class="text-xs font-semibold text-gray-600 dark:text-gray-400">Inventory</p>
          <p class="text-sm font-medium text-gray-900 dark:text-white/90">Manage Items</p>
        </div>
      </Link>

      <Link href="/finance/invoices" class="flex items-center gap-3 rounded-lg border border-gray-200 p-4 transition-all hover:border-orange-400 hover:bg-orange-50 dark:border-gray-700 dark:hover:border-orange-400 dark:hover:bg-orange-500/10">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-50 dark:bg-orange-500/10">
          <svg class="h-5 w-5 text-orange-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <div>
          <p class="text-xs font-semibold text-gray-600 dark:text-gray-400">Finance</p>
          <p class="text-sm font-medium text-gray-900 dark:text-white/90">View Invoices</p>
        </div>
      </Link>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>
