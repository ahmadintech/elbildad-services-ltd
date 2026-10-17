<template>
  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
      <h3 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Quick Actions</h3>
    </div>

    <div class="grid grid-cols-2 gap-3.5 p-6 sm:grid-cols-3">
      <!-- Tasks & Schedule -->
      <Link href="/tasks" class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-3.5 shadow-xs transition-all hover:border-purple-400 hover:bg-purple-50/50 hover:shadow-sm dark:border-gray-800 dark:bg-gray-900/60 dark:hover:border-purple-500 dark:hover:bg-purple-950/20">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-950 dark:text-purple-400 shadow-xs">
          <i class="fa-solid fa-calendar-check text-base"></i>
        </div>
        <div class="min-w-0">
          <p class="text-xs font-bold uppercase tracking-wider text-purple-700 dark:text-purple-400">Team</p>
          <p class="text-sm font-bold text-gray-900 dark:text-white truncate">Tasks & Talks</p>
        </div>
      </Link>

      <!-- Users -->
      <Link href="/admin/users" class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-3.5 shadow-xs transition-all hover:border-blue-400 hover:bg-blue-50/50 hover:shadow-sm dark:border-gray-800 dark:bg-gray-900/60 dark:hover:border-blue-500 dark:hover:bg-blue-950/20">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400 shadow-xs">
          <i class="fa-solid fa-users text-base"></i>
        </div>
        <div class="min-w-0">
          <p class="text-xs font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400">Users</p>
          <p class="text-sm font-bold text-gray-900 dark:text-white truncate">Manage Users</p>
        </div>
      </Link>

      <!-- Agent Activity -->
      <Link href="/admin/agent-activity" class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-3.5 shadow-xs transition-all hover:border-indigo-400 hover:bg-indigo-50/50 hover:shadow-sm dark:border-gray-800 dark:bg-gray-900/60 dark:hover:border-indigo-500 dark:hover:bg-indigo-950/20">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-400 shadow-xs">
          <i class="fa-solid fa-user-clock text-base"></i>
        </div>
        <div class="min-w-0">
          <p class="text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400">Activity</p>
          <p class="text-sm font-bold text-gray-900 dark:text-white truncate">Agent Tracking</p>
        </div>
      </Link>

      <!-- Roles -->
      <Link href="/admin/roles" class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-3.5 shadow-xs transition-all hover:border-amber-400 hover:bg-amber-50/50 hover:shadow-sm dark:border-gray-800 dark:bg-gray-900/60 dark:hover:border-amber-500 dark:hover:bg-amber-950/20">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-400 shadow-xs">
          <i class="fa-solid fa-shield-halved text-base"></i>
        </div>
        <div class="min-w-0">
          <p class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Access</p>
          <p class="text-sm font-bold text-gray-900 dark:text-white truncate">Manage Roles</p>
        </div>
      </Link>

      <!-- Inventory Items -->
      <Link href="/finance/items" class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-3.5 shadow-xs transition-all hover:border-emerald-400 hover:bg-emerald-50/50 hover:shadow-sm dark:border-gray-800 dark:bg-gray-900/60 dark:hover:border-emerald-500 dark:hover:bg-emerald-950/20">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400 shadow-xs">
          <i class="fa-solid fa-boxes-stacked text-base"></i>
        </div>
        <div class="min-w-0">
          <p class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Inventory</p>
          <p class="text-sm font-bold text-gray-900 dark:text-white truncate">Manage Items</p>
        </div>
      </Link>

      <!-- Finance Invoices -->
      <Link href="/finance/invoices" class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-3.5 shadow-xs transition-all hover:border-rose-400 hover:bg-rose-50/50 hover:shadow-sm dark:border-gray-800 dark:bg-gray-900/60 dark:hover:border-rose-500 dark:hover:bg-rose-950/20">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950 dark:text-rose-400 shadow-xs">
          <i class="fa-solid fa-file-invoice-dollar text-base"></i>
        </div>
        <div class="min-w-0">
          <p class="text-xs font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400">Finance</p>
          <p class="text-sm font-bold text-gray-900 dark:text-white truncate">View Invoices</p>
        </div>
      </Link>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>
