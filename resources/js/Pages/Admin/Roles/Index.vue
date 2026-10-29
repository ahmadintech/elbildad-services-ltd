<template>
  <AdminLayout>
    <Head title="Roles & Permissions Management" />
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2.5">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">
              <i class="fa-solid fa-shield-halved text-lg"></i>
            </span>
            Roles & Permissions
          </h2>
          <p class="mt-1 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
            Define system roles, specify exact sidebar menu visibility, and configure granular action capabilities.
          </p>
        </div>
        <div class="flex items-center gap-3">
          <button
            @click="openCreateModal"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs sm:text-sm font-semibold text-white shadow-xs hover:bg-brand-600 active:scale-95 transition-all"
          >
            <i class="fa-solid fa-plus text-xs"></i>
            Create Role
          </button>
        </div>
      </div>

      <!-- KPI Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-gray-200/80 bg-white p-4 sm:p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Roles</span>
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/40 dark:text-brand-400">
              <i class="fa-solid fa-user-shield text-sm"></i>
            </span>
          </div>
          <div class="mt-3">
            <p class="text-2xl font-black text-gray-900 dark:text-white">{{ roles.length }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Configured access tiers</p>
          </div>
        </div>

        <div class="rounded-2xl border border-gray-200/80 bg-white p-4 sm:p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Available Menus</span>
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
              <i class="fa-solid fa-compass text-sm"></i>
            </span>
          </div>
          <div class="mt-3">
            <p class="text-2xl font-black text-gray-900 dark:text-white">{{ totalMenusCount }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Sidebar navigation items</p>
          </div>
        </div>

        <div class="rounded-2xl border border-gray-200/80 bg-white p-4 sm:p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Action Permissions</span>
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
              <i class="fa-solid fa-key text-sm"></i>
            </span>
          </div>
          <div class="mt-3">
            <p class="text-2xl font-black text-gray-900 dark:text-white">{{ totalActionsCount }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Granular operation rules</p>
          </div>
        </div>

        <div class="rounded-2xl border border-gray-200/80 bg-white p-4 sm:p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Assigned Users</span>
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400">
              <i class="fa-solid fa-users text-sm"></i>
            </span>
          </div>
          <div class="mt-3">
            <p class="text-2xl font-black text-gray-900 dark:text-white">{{ totalAssignedUsers }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Users with roles</p>
          </div>
        </div>
      </div>

      <!-- Main Table Container -->
      <div class="overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900/60">
        <!-- Search and Filter Bar -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-b border-gray-100 p-4 dark:border-gray-800">
          <div class="relative w-full sm:w-72">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search roles by name..."
              class="h-9 w-full rounded-xl border border-gray-200 bg-gray-50/50 pl-9 pr-4 text-xs font-medium text-gray-900 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            />
          </div>
          <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">
            Showing <span class="text-gray-900 dark:text-white font-bold">{{ filteredRoles.length }}</span> of {{ roles.length }} roles
          </div>
        </div>

        <div class="max-w-full overflow-x-auto custom-scrollbar">
          <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800 text-left">
            <thead>
              <tr class="bg-gray-50/70 dark:bg-gray-800/50 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                <th class="px-5 py-3.5 sm:px-6">Role Details</th>
                <th class="px-5 py-3.5 sm:px-6">Menu Visibility</th>
                <th class="px-5 py-3.5 sm:px-6">Action Capabilities</th>
                <th class="px-5 py-3.5 sm:px-6">Users</th>
                <th class="px-5 py-3.5 sm:px-6 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
              <tr
                v-for="role in filteredRoles"
                :key="role.id"
                class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors"
              >
                <!-- Role Name -->
                <td class="px-5 py-4 sm:px-6">
                  <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold uppercase">
                      {{ role.name.substring(0, 2) }}
                    </div>
                    <div>
                      <div class="flex items-center gap-2">
                        <span class="font-bold text-gray-900 dark:text-white text-sm capitalize">
                          {{ role.name.replace(/_/g, ' ') }}
                        </span>
                        <span
                          v-if="isCoreRole(role.name)"
                          class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800"
                        >
                          <i class="fa-solid fa-lock text-[9px]"></i> System
                        </span>
                      </div>
                      <span class="text-[11px] font-mono text-gray-400">slug: {{ role.name }}</span>
                    </div>
                  </div>
                </td>

                <!-- Menu Visibility -->
                <td class="px-5 py-4 sm:px-6">
                  <div class="flex items-center gap-2">
                    <span
                      :class="[
                        'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold',
                        isSuperRole(role.name) || getRoleMenuCount(role) >= totalMenusCount
                          ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800'
                          : getRoleMenuCount(role) > 0
                          ? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700'
                          : 'bg-rose-50 text-rose-600 dark:bg-rose-950/30 dark:text-rose-400 border border-rose-200 dark:border-rose-900/50'
                      ]"
                    >
                      <i class="fa-solid fa-compass text-[11px]"></i>
                      <span v-if="isSuperRole(role.name)">All Menus (Full)</span>
                      <span v-else>{{ getRoleMenuCount(role) }} / {{ totalMenusCount }} Menus</span>
                    </span>
                  </div>
                </td>

                <!-- Action Capabilities -->
                <td class="px-5 py-4 sm:px-6">
                  <div class="flex items-center gap-2">
                    <span
                      :class="[
                        'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold',
                        isSuperRole(role.name) || getRoleActionCount(role) >= totalActionsCount
                          ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800'
                          : getRoleActionCount(role) > 0
                          ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800'
                          : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
                      ]"
                    >
                      <i class="fa-solid fa-key text-[11px]"></i>
                      <span v-if="isSuperRole(role.name)">Full Permissions</span>
                      <span v-else>{{ getRoleActionCount(role) }} / {{ totalActionsCount }} Actions</span>
                    </span>
                  </div>
                </td>

                <!-- Users Count -->
                <td class="px-5 py-4 sm:px-6">
                  <span class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <i class="fa-solid fa-user text-[10px]"></i>
                    {{ role.users_count || 0 }} {{ (role.users_count || 0) === 1 ? 'user' : 'users' }}
                  </span>
                </td>

                <!-- Actions -->
                <td class="px-5 py-4 sm:px-6 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button
                      type="button"
                      @click="openPreviewModal(role)"
                      class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition"
                      title="Inspect granted menus and actions"
                    >
                      <i class="fa-regular fa-eye text-xs"></i>
                      <span>Inspect</span>
                    </button>

                    <button
                      type="button"
                      @click="editRole(role)"
                      class="inline-flex items-center gap-1 rounded-lg bg-brand-50 px-2.5 py-1.5 text-xs font-semibold text-brand-600 hover:bg-brand-100 dark:bg-brand-950/50 dark:text-brand-400 dark:hover:bg-brand-900/60 transition"
                    >
                      <i class="fa-solid fa-pen-to-square text-xs"></i>
                      <span>Configure</span>
                    </button>

                    <button
                      v-if="!isCoreRole(role.name)"
                      type="button"
                      @click="confirmDelete(role)"
                      :disabled="(role.users_count || 0) > 0"
                      :class="[
                        'inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold transition',
                        (role.users_count || 0) > 0
                          ? 'opacity-40 cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500'
                          : 'bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-400 dark:hover:bg-rose-900/50'
                      ]"
                      :title="(role.users_count || 0) > 0 ? 'Cannot delete role with assigned users' : 'Delete role'"
                    >
                      <i class="fa-solid fa-trash-can text-xs"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="filteredRoles.length === 0">
                <td colspan="5" class="px-5 py-12 text-center text-gray-500 dark:text-gray-400">
                  <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800 mb-3">
                    <i class="fa-solid fa-magnifying-glass text-lg text-gray-400"></i>
                  </div>
                  <p class="font-bold text-gray-700 dark:text-gray-300">No roles matched your search</p>
                  <p class="text-xs text-gray-400 mt-1">Try searching for a different role name or clear the search input.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Create / Configure Role Modal (Large, Full Control) -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm transition-opacity"
    >
      <div class="relative w-full max-w-5xl rounded-3xl bg-white shadow-2xl dark:bg-gray-900 flex flex-col max-h-[92vh] overflow-hidden border border-gray-200 dark:border-gray-800">
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800 shrink-0">
          <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">
              <i class="fa-solid fa-sliders text-base"></i>
            </span>
            <div>
              <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">
                {{ isEditing ? `Configure Role: ${form.name.replace(/_/g, ' ')}` : 'Create New System Role' }}
              </h3>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                Specify role name, toggle sidebar navigation menus, and assign permitted actions.
              </p>
            </div>
          </div>
          <button
            type="button"
            @click="isModalOpen = false"
            class="flex h-9 w-9 items-center justify-center rounded-xl bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:text-white transition"
          >
            <i class="fa-solid fa-xmark text-sm"></i>
          </button>
        </div>

        <!-- Modal Scrollable Body -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6 custom-scrollbar">
          <!-- Role Name & Quick Presets -->
          <div class="rounded-2xl border border-gray-200/80 bg-gray-50/50 p-4 sm:p-5 dark:border-gray-800 dark:bg-gray-800/40 space-y-4">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                Role Name *
              </label>
              <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
                <div class="relative flex-1 w-full">
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    :disabled="isCoreRole(editingRoleOriginalName)"
                    placeholder="e.g., Procurement Manager, Accounts Clerk, Support Officer"
                    class="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 text-xs sm:text-sm font-semibold text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white disabled:opacity-60 disabled:cursor-not-allowed"
                  />
                </div>
                <span class="text-xs font-mono text-gray-400 shrink-0">
                  DB identifier: <span class="font-bold text-gray-700 dark:text-gray-300">{{ formatSlug(form.name) || '---' }}</span>
                </span>
              </div>
              <div v-if="form.errors.name" class="text-rose-500 text-xs mt-1 font-semibold">{{ form.errors.name }}</div>
              <div v-if="form.errors.permissions" class="text-rose-500 text-xs mt-1 font-semibold">{{ form.errors.permissions }}</div>
              <div v-if="Object.keys(form.errors).length > 0 && !form.errors.name && !form.errors.permissions" class="text-rose-500 text-xs mt-1 font-semibold">
                Please check the form. Some required permissions or fields are invalid.
              </div>
            </div>

            <!-- Quick Presets -->
            <div class="pt-3 border-t border-gray-200/60 dark:border-gray-700/60">
              <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                <i class="fa-solid fa-wand-magic-sparkles mr-1 text-brand-500"></i> Quick Presets (1-Click Configuration):
              </span>
              <div class="flex flex-wrap items-center gap-2">
                <button
                  type="button"
                  @click="applyPreset('admin')"
                  class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-bold text-gray-700 hover:border-brand-500 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-brand-500 transition shadow-2xs"
                >
                  <i class="fa-solid fa-crown text-amber-500 text-xs"></i>
                  <span>Administrator (All)</span>
                </button>
                <button
                  type="button"
                  @click="applyPreset('agent')"
                  class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-bold text-gray-700 hover:border-brand-500 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-brand-500 transition shadow-2xs"
                >
                  <i class="fa-solid fa-truck-fast text-blue-500 text-xs"></i>
                  <span>Procurement Agent</span>
                </button>
                <button
                  type="button"
                  @click="applyPreset('accountant')"
                  class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-bold text-gray-700 hover:border-brand-500 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-brand-500 transition shadow-2xs"
                >
                  <i class="fa-solid fa-calculator text-teal-500 text-xs"></i>
                  <span>Accountant</span>
                </button>
                <button
                  type="button"
                  @click="applyPreset('finance')"
                  class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-bold text-gray-700 hover:border-brand-500 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-brand-500 transition shadow-2xs"
                >
                  <i class="fa-solid fa-coins text-emerald-500 text-xs"></i>
                  <span>Finance & Running Cost</span>
                </button>
                <button
                  type="button"
                  @click="applyPreset('support')"
                  class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-bold text-gray-700 hover:border-brand-500 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-brand-500 transition shadow-2xs"
                >
                  <i class="fa-solid fa-headset text-indigo-500 text-xs"></i>
                  <span>Customer Support</span>
                </button>
                <button
                  type="button"
                  @click="applyPreset('visa')"
                  class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-bold text-gray-700 hover:border-brand-500 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-brand-500 transition shadow-2xs"
                >
                  <i class="fa-solid fa-passport text-sky-500 text-xs"></i>
                  <span>Visa Agent</span>
                </button>
                <button
                  type="button"
                  @click="clearAllPermissions"
                  class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50/50 px-3 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-100 dark:border-rose-900/60 dark:bg-rose-950/30 dark:text-rose-400 transition"
                >
                  <i class="fa-solid fa-eraser text-xs"></i>
                  <span>Clear All</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Configuration Tabs Navigation -->
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-200 dark:border-gray-800 pb-3">
            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="activeModalTab = 'menus'"
                :class="[
                  'inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-bold transition-all',
                  activeModalTab === 'menus'
                    ? 'bg-brand-500 text-white shadow-xs'
                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700'
                ]"
              >
                <i class="fa-solid fa-compass text-xs"></i>
                <span>1. Sidebar Menus Visibility</span>
                <span class="ml-1 rounded-full bg-white/20 px-2 py-0.5 text-[11px] font-mono">
                  {{ selectedMenusCount }} / {{ totalMenusCount }}
                </span>
              </button>

              <button
                type="button"
                @click="activeModalTab = 'actions'"
                :class="[
                  'inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-bold transition-all',
                  activeModalTab === 'actions'
                    ? 'bg-brand-500 text-white shadow-xs'
                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700'
                ]"
              >
                <i class="fa-solid fa-key text-xs"></i>
                <span>2. Action & Capabilities</span>
                <span class="ml-1 rounded-full bg-white/20 px-2 py-0.5 text-[11px] font-mono">
                  {{ selectedActionsCount }} / {{ totalActionsCount }}
                </span>
              </button>
            </div>

            <!-- Permission Search Filter -->
            <div class="relative w-full sm:w-64">
              <i class="fa-solid fa-filter absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
              <input
                v-model="permissionFilter"
                type="text"
                placeholder="Filter permissions..."
                class="h-9 w-full rounded-xl border border-gray-200 bg-white pl-8 pr-3 text-xs font-medium text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              />
            </div>
          </div>

          <!-- TAB 1: SIDEBAR MENUS VISIBILITY -->
          <div v-show="activeModalTab === 'menus'" class="space-y-6">
            <div class="flex items-center justify-between bg-blue-50/60 border border-blue-100 dark:bg-blue-950/20 dark:border-blue-900/40 rounded-2xl p-3.5">
              <div class="flex items-center gap-2 text-xs font-semibold text-blue-900 dark:text-blue-300">
                <i class="fa-solid fa-circle-info text-sm"></i>
                <span>Check the sidebar navigation menus that users assigned to this role should see.</span>
              </div>
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  @click="selectAllMenus"
                  class="rounded-lg bg-white px-2.5 py-1 text-xs font-bold text-blue-700 shadow-2xs hover:bg-blue-50 dark:bg-gray-800 dark:text-blue-300 transition"
                >
                  Select All Menus
                </button>
                <button
                  type="button"
                  @click="deselectAllMenus"
                  class="rounded-lg bg-white px-2.5 py-1 text-xs font-bold text-gray-600 shadow-2xs hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 transition"
                >
                  Clear Menus
                </button>
              </div>
            </div>

            <!-- Grouped Menu Cards -->
            <div v-for="(menuItems, groupName) in menuDefinitions" :key="groupName" class="space-y-3">
              <div class="flex items-center justify-between border-b border-gray-100 pb-2 dark:border-gray-800">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-2">
                  <span class="h-2 w-2 rounded-full bg-brand-500"></span>
                  {{ groupName }} Menus
                </h4>
                <button
                  type="button"
                  @click="toggleMenuGroup(menuItems)"
                  class="text-[11px] font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400"
                >
                  {{ isGroupFullySelected(menuItems, 'key') ? 'Deselect Group' : 'Select Group' }}
                </button>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <label
                  v-for="item in filterItems(menuItems, 'name')"
                  :key="item.key"
                  :class="[
                    'relative flex items-start gap-3 rounded-2xl border p-3.5 cursor-pointer transition-all',
                    form.permissions.includes(item.key)
                      ? 'border-brand-500 bg-brand-50/40 dark:border-brand-500 dark:bg-brand-950/20 shadow-xs'
                      : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-800 dark:bg-gray-800/40 dark:hover:border-gray-700'
                  ]"
                >
                  <input
                    type="checkbox"
                    :value="item.key"
                    v-model="form.permissions"
                    class="mt-1 h-4 w-4 rounded-md border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900"
                  />
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-1">
                      <span class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                        <i :class="getFontAwesomeIcon(item.icon)" class="text-brand-500 text-xs"></i>
                        {{ item.name }}
                      </span>
                      <span class="text-[10px] font-mono text-gray-400 truncate">{{ item.path }}</span>
                    </div>
                    <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 line-clamp-2">
                      {{ item.description }}
                    </p>
                  </div>
                </label>
              </div>
            </div>
          </div>

          <!-- TAB 2: ACTION & FEATURE CAPABILITIES -->
          <div v-show="activeModalTab === 'actions'" class="space-y-6">
            <div class="flex items-center justify-between bg-emerald-50/60 border border-emerald-100 dark:bg-emerald-950/20 dark:border-emerald-900/40 rounded-2xl p-3.5">
              <div class="flex items-center gap-2 text-xs font-semibold text-emerald-900 dark:text-emerald-300">
                <i class="fa-solid fa-circle-check text-sm"></i>
                <span>Check the operations and features that users assigned to this role can perform.</span>
              </div>
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  @click="selectAllActions"
                  class="rounded-lg bg-white px-2.5 py-1 text-xs font-bold text-emerald-700 shadow-2xs hover:bg-emerald-50 dark:bg-gray-800 dark:text-emerald-300 transition"
                >
                  Select All Actions
                </button>
                <button
                  type="button"
                  @click="deselectAllActions"
                  class="rounded-lg bg-white px-2.5 py-1 text-xs font-bold text-gray-600 shadow-2xs hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 transition"
                >
                  Clear Actions
                </button>
              </div>
            </div>

            <!-- Modules List -->
            <div v-for="(actionItems, moduleName) in actionDefinitions" :key="moduleName" class="space-y-3">
              <div class="flex items-center justify-between border-b border-gray-100 pb-2 dark:border-gray-800">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-2">
                  <span class="flex h-5 w-5 items-center justify-center rounded-md bg-gray-100 dark:bg-gray-800 text-[10px]">
                    <i :class="getModuleIcon(moduleName)" class="text-brand-500"></i>
                  </span>
                  {{ moduleName }}
                </h4>
                <button
                  type="button"
                  @click="toggleActionGroup(actionItems)"
                  class="text-[11px] font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400"
                >
                  {{ isGroupFullySelected(actionItems, 'name') ? 'Deselect Module' : 'Select All in Module' }}
                </button>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <label
                  v-for="action in filterItems(actionItems, 'label')"
                  :key="action.name"
                  :class="[
                    'relative flex items-start gap-3 rounded-2xl border p-3.5 cursor-pointer transition-all',
                    form.permissions.includes(action.name)
                      ? 'border-emerald-500 bg-emerald-50/40 dark:border-emerald-500 dark:bg-emerald-950/20 shadow-xs'
                      : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-800 dark:bg-gray-800/40 dark:hover:border-gray-700'
                  ]"
                >
                  <input
                    type="checkbox"
                    :value="action.name"
                    v-model="form.permissions"
                    class="mt-1 h-4 w-4 rounded-md border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-900"
                  />
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-1">
                      <span class="text-xs font-bold text-gray-900 dark:text-white">
                        {{ action.label }}
                      </span>
                      <span class="text-[10px] font-mono text-gray-400 truncate">{{ action.name }}</span>
                    </div>
                    <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                      {{ action.description }}
                    </p>
                  </div>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Sticky Footer -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-100 bg-gray-50/80 px-6 py-4 dark:border-gray-800 dark:bg-gray-900/80 shrink-0">
          <div class="flex items-center gap-3 text-xs font-semibold text-gray-600 dark:text-gray-300">
            <span class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-2.5 py-1 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 font-bold">
              <i class="fa-solid fa-compass text-[11px]"></i>
              {{ selectedMenusCount }} Menus Enabled
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 font-bold">
              <i class="fa-solid fa-key text-[11px]"></i>
              {{ selectedActionsCount }} Actions Granted
            </span>
          </div>

          <div class="flex items-center gap-2 w-full sm:w-auto">
            <button
              type="button"
              @click="isModalOpen = false"
              class="w-full sm:w-auto rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition"
            >
              Cancel
            </button>
            <button
              type="button"
              @click="submitForm"
              :disabled="form.processing"
              class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-6 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-brand-600 active:scale-95 transition disabled:opacity-50"
            >
              <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
              <i v-else class="fa-solid fa-check text-xs"></i>
              <span>{{ isEditing ? 'Save Changes' : 'Create Role' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Inspect/Preview Role Modal -->
    <div
      v-if="isPreviewModalOpen"
      class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity"
    >
      <div class="relative w-full max-w-3xl rounded-3xl bg-white p-6 shadow-2xl dark:bg-gray-900 max-h-[85vh] flex flex-col border border-gray-200 dark:border-gray-800">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800 shrink-0">
          <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400 font-bold uppercase">
              {{ previewRole?.name?.substring(0, 2) }}
            </span>
            <div>
              <h3 class="text-base font-bold text-gray-900 dark:text-white capitalize">
                {{ previewRole?.name?.replace(/_/g, ' ') }} Access Profile
              </h3>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                Summary of active navigation menus and granted operations.
              </p>
            </div>
          </div>
          <button
            type="button"
            @click="isPreviewModalOpen = false"
            class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-400 hover:text-gray-700 dark:bg-gray-800"
          >
            <i class="fa-solid fa-xmark text-sm"></i>
          </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-5 custom-scrollbar text-xs">
          <!-- Menus section -->
          <div>
            <h4 class="font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2.5 flex items-center gap-2">
              <i class="fa-solid fa-compass text-blue-500"></i>
              Visible Sidebar Navigation Menus
            </h4>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="perm in getPreviewRoleMenus()"
                :key="perm"
                class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-2.5 py-1 font-semibold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200 dark:border-blue-800"
              >
                <i class="fa-solid fa-check text-[10px]"></i>
                {{ formatPermLabel(perm) }}
              </span>
              <span v-if="getPreviewRoleMenus().length === 0" class="text-gray-400 italic">No sidebar menus visible.</span>
            </div>
          </div>

          <!-- Actions section -->
          <div>
            <h4 class="font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2.5 flex items-center gap-2">
              <i class="fa-solid fa-key text-emerald-500"></i>
              Granted Action Capabilities
            </h4>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="perm in getPreviewRoleActions()"
                :key="perm"
                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800"
              >
                <i class="fa-solid fa-check text-[10px]"></i>
                {{ formatPermLabel(perm) }}
              </span>
              <span v-if="getPreviewRoleActions().length === 0" class="text-gray-400 italic">No action capabilities granted.</span>
            </div>
          </div>
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end shrink-0">
          <button
            type="button"
            @click="isPreviewModalOpen = false"
            class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
          >
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="isDeleteModalOpen"
      class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity"
    >
      <div class="relative w-full max-w-md rounded-3xl bg-white p-6 dark:bg-gray-900 shadow-2xl text-center border border-gray-200 dark:border-gray-800">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-50 text-rose-500 dark:bg-rose-950/40 mb-4">
          <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
        </div>

        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Delete Role</h3>
        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
          Are you sure you want to delete the <span class="font-bold text-gray-900 dark:text-white capitalize">{{ roleToDelete?.name?.replace(/_/g, ' ') }}</span> role? This will revoke all assigned menu and action permissions.
        </p>

        <div class="mt-6 flex items-center justify-center gap-3">
          <button
            type="button"
            @click="isDeleteModalOpen = false"
            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 transition"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="executeDelete"
            :disabled="deleteForm.processing"
            class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-rose-500 px-4 py-2.5 text-xs font-bold text-white hover:bg-rose-600 transition disabled:opacity-50"
          >
            <i v-if="deleteForm.processing" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
            <span>{{ deleteForm.processing ? 'Deleting...' : 'Yes, Delete Role' }}</span>
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  roles: {
    type: Array,
    default: () => [],
  },
  menuDefinitions: {
    type: Object,
    default: () => ({}),
  },
  actionDefinitions: {
    type: Object,
    default: () => ({}),
  },
  presets: {
    type: Object,
    default: () => ({}),
  },
})

// Search state
const searchQuery = ref('')
const permissionFilter = ref('')
const activeModalTab = ref('menus') // 'menus' | 'actions'

// Modals state
const isModalOpen = ref(false)
const isEditing = ref(false)
const editingId = ref(null)
const editingRoleOriginalName = ref('')

const isPreviewModalOpen = ref(false)
const previewRole = ref(null)

const isDeleteModalOpen = ref(false)
const roleToDelete = ref(null)
const deleteForm = useForm({})

// Form state
const form = useForm({
  name: '',
  permissions: [],
})

// Core system roles that cannot be deleted or renamed
const isCoreRole = (name) => {
  return ['admin', 'owner', 'super_admin'].includes(name)
}

const isSuperRole = (name) => {
  return ['admin', 'owner', 'super_admin'].includes(name)
}

// Flat list of all menu keys
const allMenuKeys = computed(() => {
  const keys = []
  Object.values(props.menuDefinitions).forEach((items) => {
    items.forEach((item) => keys.push(item.key))
  })
  return keys
})

// Flat list of all action names
const allActionNames = computed(() => {
  const names = []
  Object.values(props.actionDefinitions).forEach((items) => {
    items.forEach((item) => names.push(item.name))
  })
  return names
})

const totalMenusCount = computed(() => allMenuKeys.value.length)
const totalActionsCount = computed(() => allActionNames.value.length)

const totalAssignedUsers = computed(() => {
  return props.roles.reduce((acc, r) => acc + (r.users_count || 0), 0)
})

const filteredRoles = computed(() => {
  if (!searchQuery.value.trim()) return props.roles
  const q = searchQuery.value.toLowerCase()
  return props.roles.filter((r) => r.name.toLowerCase().includes(q))
})

const selectedMenusCount = computed(() => {
  return form.permissions.filter((p) => allMenuKeys.value.includes(p)).length
})

const selectedActionsCount = computed(() => {
  return form.permissions.filter((p) => allActionNames.value.includes(p)).length
})

const getRoleMenuCount = (role) => {
  if (isSuperRole(role.name)) return totalMenusCount.value
  const rolePerms = (role.permissions || []).map((p) => p.name)
  return rolePerms.filter((p) => allMenuKeys.value.includes(p)).length
}

const getRoleActionCount = (role) => {
  if (isSuperRole(role.name)) return totalActionsCount.value
  const rolePerms = (role.permissions || []).map((p) => p.name)
  return rolePerms.filter((p) => allActionNames.value.includes(p)).length
}

const formatSlug = (val) => {
  if (!val) return ''
  return val.trim().toLowerCase().replace(/\s+/g, '_')
}

// Map icon strings to font awesome
const getFontAwesomeIcon = (iconName) => {
  const map = {
    GridIcon: 'fa-solid fa-grip',
    TaskIcon: 'fa-solid fa-list-check',
    BoxCubeIcon: 'fa-solid fa-cube',
    DocsIcon: 'fa-solid fa-file-invoice-dollar',
    UserGroupIcon: 'fa-solid fa-users',
    ListIcon: 'fa-solid fa-list-ul',
    PlugInIcon: 'fa-solid fa-plug',
    PageIcon: 'fa-solid fa-file-lines',
    TableIcon: 'fa-solid fa-table-cells',
    PieChartIcon: 'fa-solid fa-chart-pie',
    BarChartIcon: 'fa-solid fa-chart-column',
    UserCircleIcon: 'fa-solid fa-circle-user',
  }
  return map[iconName] || 'fa-solid fa-compass'
}

const getModuleIcon = (moduleName) => {
  if (moduleName.includes('RFQ')) return 'fa-solid fa-boxes-stacked'
  if (moduleName.includes('User')) return 'fa-solid fa-users-gear'
  if (moduleName.includes('Invoices')) return 'fa-solid fa-receipt'
  if (moduleName.includes('Expenses')) return 'fa-solid fa-wallet'
  if (moduleName.includes('Tasks')) return 'fa-solid fa-list-check'
  return 'fa-solid fa-sliders'
}

// Filter items by search keyword in modal
const filterItems = (items, keyField) => {
  if (!permissionFilter.value.trim()) return items
  const q = permissionFilter.value.toLowerCase()
  return items.filter((item) => {
    const title = (item[keyField] || '').toLowerCase()
    const desc = (item.description || '').toLowerCase()
    const id = (item.key || item.name || '').toLowerCase()
    return title.includes(q) || desc.includes(q) || id.includes(q)
  })
}

// Group selection helpers
const isGroupFullySelected = (items, keyProp) => {
  return items.every((i) => form.permissions.includes(i[keyProp]))
}

const toggleMenuGroup = (items) => {
  const allSelected = isGroupFullySelected(items, 'key')
  if (allSelected) {
    const keysToRemove = items.map((i) => i.key)
    form.permissions = form.permissions.filter((p) => !keysToRemove.includes(p))
  } else {
    items.forEach((i) => {
      if (!form.permissions.includes(i.key)) form.permissions.push(i.key)
    })
  }
}

const toggleActionGroup = (items) => {
  const allSelected = isGroupFullySelected(items, 'name')
  if (allSelected) {
    const namesToRemove = items.map((i) => i.name)
    form.permissions = form.permissions.filter((p) => !namesToRemove.includes(p))
  } else {
    items.forEach((i) => {
      if (!form.permissions.includes(i.name)) form.permissions.push(i.name)
    })
  }
}

const selectAllMenus = () => {
  allMenuKeys.value.forEach((key) => {
    if (!form.permissions.includes(key)) form.permissions.push(key)
  })
}

const deselectAllMenus = () => {
  form.permissions = form.permissions.filter((p) => !allMenuKeys.value.includes(p))
}

const selectAllActions = () => {
  allActionNames.value.forEach((name) => {
    if (!form.permissions.includes(name)) form.permissions.push(name)
  })
}

const deselectAllActions = () => {
  form.permissions = form.permissions.filter((p) => !allActionNames.value.includes(p))
}

const clearAllPermissions = () => {
  form.permissions = []
}

// Apply quick presets
const applyPreset = (presetKey) => {
  const preset = props.presets[presetKey]
  if (!preset) return
  form.permissions = [...preset.permissions]
  if (!isEditing.value && (!form.name || form.name.trim() === '')) {
    form.name = presetKey.charAt(0).toUpperCase() + presetKey.slice(1).replace(/_/g, ' ')
  }
}

// Modal open methods
const openCreateModal = () => {
  isEditing.value = false
  editingId.value = null
  editingRoleOriginalName.value = ''
  form.reset()
  form.name = ''
  form.permissions = []
  form.clearErrors()
  permissionFilter.value = ''
  activeModalTab.value = 'menus'
  isModalOpen.value = true
}

const editRole = (role) => {
  isEditing.value = true
  editingId.value = role.id
  editingRoleOriginalName.value = role.name
  form.name = role.name
  // Extract permissions
  if (isSuperRole(role.name)) {
    form.permissions = [...allMenuKeys.value, ...allActionNames.value]
  } else {
    form.permissions = (role.permissions || []).map((p) => p.name)
  }
  form.clearErrors()
  permissionFilter.value = ''
  activeModalTab.value = 'menus'
  isModalOpen.value = true
}

const submitForm = () => {
  if (isEditing.value) {
    form.put(route('admin.roles.update', editingId.value), {
      onSuccess: () => {
        isModalOpen.value = false
      },
      preserveScroll: true,
    })
  } else {
    form.post(route('admin.roles.store'), {
      onSuccess: () => {
        isModalOpen.value = false
      },
      preserveScroll: true,
    })
  }
}

// Inspect modal helpers
const openPreviewModal = (role) => {
  previewRole.value = role
  isPreviewModalOpen.value = true
}

const getPreviewRoleMenus = () => {
  if (!previewRole.value) return []
  if (isSuperRole(previewRole.value.name)) return allMenuKeys.value
  const rolePerms = (previewRole.value.permissions || []).map((p) => p.name)
  return rolePerms.filter((p) => allMenuKeys.value.includes(p))
}

const getPreviewRoleActions = () => {
  if (!previewRole.value) return []
  if (isSuperRole(previewRole.value.name)) return allActionNames.value
  const rolePerms = (previewRole.value.permissions || []).map((p) => p.name)
  return rolePerms.filter((p) => allActionNames.value.includes(p))
}

const formatPermLabel = (perm) => {
  return perm.replace(/^(menu_)/, '').replace(/_/g, ' ')
}

// Delete helpers
const confirmDelete = (role) => {
  if (isCoreRole(role.name)) return
  if ((role.users_count || 0) > 0) return
  roleToDelete.value = role
  isDeleteModalOpen.value = true
}

const executeDelete = () => {
  if (roleToDelete.value) {
    deleteForm.delete(route('admin.roles.destroy', roleToDelete.value.id), {
      onSuccess: () => {
        isDeleteModalOpen.value = false
        roleToDelete.value = null
      },
      preserveScroll: true,
    })
  }
}
</script>
