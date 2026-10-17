<template>
  <AdminLayout>
    <Head title="Tasks & Schedule" />

    <div class="space-y-7">
      <!-- Top Title & Controls Bar -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">
            Tasks & Schedule
          </h1>
          <p class="mt-1.5 text-base text-gray-600 dark:text-gray-300">
            Schedule client & supplier talks, manage daily pipeline tasks, track timing, and receive notifications.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <!-- Live Alerts Active Toggler Button -->
          <button
            type="button"
            @click="setupNotifications"
            :class="[
              'inline-flex items-center gap-3 rounded-xl border px-3.5 py-2 text-sm font-semibold transition-all shadow-sm',
              notificationGranted
                ? 'border-emerald-300 bg-emerald-50/80 text-emerald-800 dark:border-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300'
                : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200'
            ]"
            title="Toggle Live Alerts"
          >
            <!-- Sliding Toggle Switch Track & Knob -->
            <span
              :class="[
                'relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out',
                notificationGranted ? 'bg-emerald-600' : 'bg-gray-300 dark:bg-gray-600'
              ]"
            >
              <span
                :class="[
                  'pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out',
                  notificationGranted ? 'translate-x-4' : 'translate-x-0'
                ]"
              />
            </span>
            <span class="font-bold flex items-center gap-1.5">
              <i :class="['fa-solid', notificationGranted ? 'fa-bell text-emerald-600 dark:text-emerald-400' : 'fa-bell-slash text-gray-400']"></i>
              <span>Live Alerts Active</span>
            </span>
          </button>

          <!-- Audio Test Button -->
          <button
            type="button"
            @click="testChime"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 shadow-sm transition"
            title="Play chime sound test"
          >
            <i class="fa-solid fa-volume-high text-brand-500"></i>
            <span>Test Sound</span>
          </button>

          <!-- Schedule Talk Button -->
          <button
            type="button"
            @click="openCreateModal('talk')"
            class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-bold text-white shadow-md hover:bg-purple-700 active:scale-95 transition"
          >
            <i class="fa-solid fa-phone-volume"></i>
            <span>Schedule Talk</span>
          </button>

          <!-- New Task Button -->
          <button
            type="button"
            @click="openCreateModal('general')"
            class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-600 active:scale-95 transition"
          >
            <i class="fa-solid fa-plus"></i>
            <span>New Task</span>
          </button>
        </div>
      </div>

      <!-- monday.com Metric KPI Cards Row -->
      <div class="grid grid-cols-2 gap-3.5 sm:grid-cols-3 lg:grid-cols-6">
        <!-- Total -->
        <div class="rounded-2xl border border-gray-200 bg-white p-4.5 dark:border-gray-800 dark:bg-gray-900/90 shadow-sm hover:shadow transition">
          <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
            <span>Total Tasks</span>
            <i class="fa-solid fa-layer-group text-gray-400 text-sm"></i>
          </div>
          <p class="mt-2 text-3xl font-black text-gray-900 dark:text-white">{{ stats.total }}</p>
          <div class="mt-1 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ stats.completion_rate }}% completed</div>
        </div>

        <!-- Scheduled Talks -->
        <div class="rounded-2xl border border-purple-200 bg-purple-50/60 p-4.5 dark:border-purple-800/60 dark:bg-purple-950/25 shadow-sm hover:shadow transition">
          <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-purple-700 dark:text-purple-300">
            <span>Scheduled Talks</span>
            <i class="fa-solid fa-phone text-purple-600 dark:text-purple-400 text-sm"></i>
          </div>
          <p class="mt-2 text-3xl font-black text-purple-700 dark:text-purple-300">{{ stats.talks }}</p>
          <div class="mt-1 text-xs font-semibold text-purple-700/80 dark:text-purple-300/80">Calls & Meetings</div>
        </div>

        <!-- Working On It (In Progress) -->
        <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-4.5 dark:border-amber-800/60 dark:bg-amber-950/25 shadow-sm hover:shadow transition">
          <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">
            <span>Working On It</span>
            <i class="fa-solid fa-arrows-rotate text-amber-600 dark:text-amber-400 text-sm animate-spin-slow"></i>
          </div>
          <p class="mt-2 text-3xl font-black text-amber-700 dark:text-amber-300">{{ stats.in_progress }}</p>
          <div class="mt-1 text-xs font-semibold text-amber-700/80 dark:text-amber-300/80">Active Pipeline</div>
        </div>

        <!-- Pending / Scheduled -->
        <div class="rounded-2xl border border-blue-200 bg-blue-50/60 p-4.5 dark:border-blue-800/60 dark:bg-blue-950/25 shadow-sm hover:shadow transition">
          <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-blue-700 dark:text-blue-300">
            <span>Pending Queue</span>
            <i class="fa-regular fa-clock text-blue-600 dark:text-blue-400 text-sm"></i>
          </div>
          <p class="mt-2 text-3xl font-black text-blue-700 dark:text-blue-300">{{ stats.pending }}</p>
          <div class="mt-1 text-xs font-semibold text-blue-700/80 dark:text-blue-300/80">Upcoming Queue</div>
        </div>

        <!-- Done -->
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4.5 dark:border-emerald-800/60 dark:bg-emerald-950/25 shadow-sm hover:shadow transition">
          <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">
            <span>Done</span>
            <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-sm"></i>
          </div>
          <p class="mt-2 text-3xl font-black text-emerald-700 dark:text-emerald-300">{{ stats.done }}</p>
          <div class="mt-1 text-xs font-semibold text-emerald-700/80 dark:text-emerald-300/80">Successfully Closed</div>
        </div>

        <!-- Overdue -->
        <div class="rounded-2xl border border-rose-200 bg-rose-50/60 p-4.5 dark:border-rose-800/60 dark:bg-rose-950/25 shadow-sm hover:shadow transition">
          <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-rose-700 dark:text-rose-300">
            <span>Overdue</span>
            <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-sm"></i>
          </div>
          <p class="mt-2 text-3xl font-black text-rose-700 dark:text-rose-300">{{ stats.overdue }}</p>
          <div class="mt-1 text-xs font-semibold text-rose-700/80 dark:text-rose-300/80">Needs Attention</div>
        </div>
      </div>

      <!-- Filters & Search Toolbar -->
      <div class="rounded-2xl border border-gray-200 bg-white p-4.5 dark:border-gray-800 dark:bg-gray-900/90 shadow-sm">
        <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6">
          <!-- Search -->
          <div class="lg:col-span-2">
            <div class="relative">
              <input
                v-model="filterForm.search"
                @input="applyFiltersDebounced"
                type="text"
                placeholder="Search tasks, clients, phone..."
                class="h-11 w-full rounded-xl border border-gray-300 bg-gray-50/60 pl-11 pr-4 text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
              />
              <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-gray-400 text-sm"></i>
            </div>
          </div>

          <!-- Time filter -->
          <div>
            <select
              v-model="filterForm.time_filter"
              @change="applyFilters"
              class="h-11 w-full rounded-xl border border-gray-300 bg-gray-50/60 px-3.5 text-sm font-medium text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            >
              <option value="">All Dates</option>
              <option value="today">Today's Schedule</option>
              <option value="upcoming">Upcoming</option>
              <option value="overdue">Overdue</option>
            </select>
          </div>

          <!-- Type filter -->
          <div>
            <select
              v-model="filterForm.type"
              @change="applyFilters"
              class="h-11 w-full rounded-xl border border-gray-300 bg-gray-50/60 px-3.5 text-sm font-medium text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            >
              <option value="all">All Types</option>
              <option value="talk">Talk / Call</option>
              <option value="rfq_followup">RFQ Follow-up</option>
              <option value="supplier_contact">Supplier Inquiry</option>
              <option value="meeting">Meeting</option>
              <option value="general">General Task</option>
            </select>
          </div>

          <!-- Status filter -->
          <div>
            <select
              v-model="filterForm.status"
              @change="applyFilters"
              class="h-11 w-full rounded-xl border border-gray-300 bg-gray-50/60 px-3.5 text-sm font-medium text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            >
              <option value="all">All Statuses</option>
              <option value="pending">Scheduled / Pending</option>
              <option value="in_progress">Working On It</option>
              <option value="done">Done</option>
              <option value="stuck">Stuck</option>
            </select>
          </div>

          <!-- Agent filter (Visible to Admins) -->
          <div v-if="isAdmin">
            <select
              v-model="filterForm.agent_id"
              @change="applyFilters"
              class="h-11 w-full rounded-xl border border-brand-300 bg-brand-50/40 px-3.5 text-sm font-bold text-brand-700 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-brand-300"
            >
              <option value="all">All Agents</option>
              <option v-for="agent in agents" :key="agent.id" :value="agent.id">
                {{ agent.name }}
              </option>
            </select>
          </div>
          <div v-else>
            <select
              v-model="filterForm.priority"
              @change="applyFilters"
              class="h-11 w-full rounded-xl border border-gray-300 bg-gray-50/60 px-3.5 text-sm font-medium text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            >
              <option value="all">All Priorities</option>
              <option value="urgent">Urgent</option>
              <option value="high">High</option>
              <option value="medium">Medium</option>
              <option value="low">Low</option>
            </select>
          </div>
        </div>
      </div>

      <!-- monday.com Style Table Board & Responsive Views -->
      <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden dark:border-gray-800 dark:bg-gray-900/90">
        <!-- Table Toolbar -->
        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gray-50/70 dark:bg-gray-800/40">
          <div class="flex items-center gap-3">
            <div class="h-3.5 w-3.5 rounded bg-brand-500"></div>
            <h2 class="text-base font-extrabold text-gray-950 dark:text-white tracking-wide uppercase">
              Task Board & Scheduled Talks
            </h2>
            <span class="rounded-full bg-gray-200 px-2.5 py-0.5 text-xs font-bold text-gray-800 dark:bg-gray-700 dark:text-gray-200">
              {{ tasks.length }}
            </span>
          </div>

          <div class="flex items-center gap-3">
            <div class="hidden sm:block text-xs font-medium text-gray-500 dark:text-gray-400">
              <i class="fa-solid fa-hand-pointer mr-1 text-brand-500"></i>
              Click status or priority to toggle
            </div>

            <!-- View Mode Toggler (Table vs Cards) -->
            <div class="inline-flex rounded-xl border border-gray-200 bg-white p-1 dark:border-gray-700 dark:bg-gray-800 shadow-xs">
              <button
                type="button"
                @click="viewMode = 'table'"
                :class="[
                  'inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition',
                  viewMode === 'table'
                    ? 'bg-brand-500 text-white shadow-xs'
                    : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'
                ]"
              >
                <i class="fa-solid fa-table-list"></i>
                <span>Table</span>
              </button>
              <button
                type="button"
                @click="viewMode = 'board'"
                :class="[
                  'inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition',
                  viewMode === 'board'
                    ? 'bg-brand-500 text-white shadow-xs'
                    : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'
                ]"
              >
                <i class="fa-solid fa-table-columns"></i>
                <span>Cards</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="tasks.length === 0" class="py-20 text-center">
          <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800">
            <i class="fa-solid fa-clipboard-list text-2xl"></i>
          </div>
          <h3 class="mt-4 text-base font-bold text-gray-900 dark:text-white">No tasks found</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Schedule a talk or create an action item using the buttons above.
          </p>
        </div>

        <!-- Standard Table View -->
        <div v-else-if="viewMode === 'table'" class="overflow-x-auto custom-scrollbar">
          <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50/90 dark:bg-gray-800/80 text-gray-600 dark:text-gray-300 uppercase tracking-wider text-xs font-bold border-b border-gray-200 dark:border-gray-800">
              <tr>
                <th class="w-12 px-4 py-3.5 text-center">Done</th>
                <th class="min-w-[280px] px-5 py-3.5">Task / Talk Details</th>
                <th class="min-w-[210px] px-4 py-3.5">Contact & Phone</th>
                <th class="min-w-[160px] px-4 py-3.5">Assigned Agent</th>
                <th class="w-[140px] px-4 py-3.5 text-center">Status</th>
                <th class="w-[120px] px-4 py-3.5 text-center">Priority</th>
                <th class="min-w-[170px] px-4 py-3.5">Schedule / Timing</th>
                <th class="min-w-[130px] px-4 py-3.5 text-center">Live Timer</th>
                <th class="w-20 px-4 py-3.5 text-right pr-5">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-transparent">
              <tr
                v-for="task in tasks"
                :key="task.id"
                :class="[
                  'transition-colors hover:bg-gray-50/80 dark:hover:bg-gray-800/40',
                  task.status === 'done' ? 'bg-gray-50/40 opacity-75' : ''
                ]"
              >
                <!-- Done Checkbox -->
                <td class="px-4 py-4 text-center align-middle">
                  <button
                    type="button"
                    @click="toggleDone(task)"
                    :class="[
                      'flex h-6 w-6 items-center justify-center rounded-lg border-2 transition-all mx-auto',
                      task.status === 'done'
                        ? 'border-emerald-500 bg-emerald-500 text-white shadow-xs'
                        : 'border-gray-300 hover:border-emerald-500 dark:border-gray-600'
                    ]"
                    title="Toggle Done"
                  >
                    <i v-if="task.status === 'done'" class="fa-solid fa-check text-xs"></i>
                  </button>
                </td>

                <!-- Task Details (Type tag + RFQ + Title + Description) -->
                <td class="px-5 py-4 align-top">
                  <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2">
                      <span :class="getTypeBadgeClass(task.type)" class="inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-xs font-semibold shadow-xs">
                        <i :class="getTypeIcon(task.type)"></i>
                        {{ getTypeLabel(task.type) }}
                      </span>
                      <div v-if="task.rfq" class="inline-flex items-center gap-1 text-xs text-brand-600 dark:text-brand-400 font-semibold bg-brand-50 dark:bg-brand-950/40 px-2 py-0.5 rounded-md">
                        <i class="fa-solid fa-link text-[10px]"></i>
                        <span>RFQ:</span>
                        <span class="truncate max-w-[140px]">{{ task.rfq.product_name }}</span>
                      </div>
                    </div>
                    <p :class="['text-sm font-bold text-gray-950 dark:text-white leading-snug', task.status === 'done' ? 'line-through text-gray-400 dark:text-gray-500' : '']">
                      {{ task.title }}
                    </p>
                    <p v-if="task.description" class="text-xs text-gray-500 dark:text-gray-400 font-medium line-clamp-2">
                      {{ task.description }}
                    </p>
                  </div>
                </td>

                <!-- Contact & Phone (Cleanly separated badges) -->
                <td class="px-4 py-4 align-middle">
                  <div v-if="task.contact_name || task.contact_phone" class="space-y-1.5">
                    <p class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                      <i class="fa-solid fa-user text-gray-400 text-xs"></i>
                      <span>{{ task.contact_name || 'Client' }}</span>
                    </p>
                    <div v-if="task.contact_phone" class="flex flex-wrap items-center gap-1.5">
                      <a
                        :href="`tel:${task.contact_phone}`"
                        class="inline-flex items-center gap-1 rounded-md bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 px-2 py-0.5 text-xs font-mono font-semibold text-gray-800 dark:text-gray-200 transition"
                        title="Click to Call"
                      >
                        <i class="fa-solid fa-phone text-[9px] text-gray-500"></i>
                        <span>{{ task.contact_phone }}</span>
                      </a>
                      <a
                        :href="`https://wa.me/${cleanPhone(task.contact_phone)}`"
                        target="_blank"
                        class="inline-flex items-center gap-1 rounded-md bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 dark:border-emerald-800 dark:bg-emerald-950/40 px-2 py-0.5 text-xs font-bold text-emerald-700 dark:text-emerald-300 transition"
                        title="Open WhatsApp"
                      >
                        <i class="fa-brands fa-whatsapp text-emerald-600 dark:text-emerald-400"></i>
                        <span>WhatsApp</span>
                      </a>
                    </div>
                  </div>
                  <span v-else class="text-gray-400 text-xs font-medium">—</span>
                </td>

                <!-- Assigned Agent -->
                <td class="px-4 py-4 align-middle">
                  <div v-if="task.assigned_agent" class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-500 text-white font-extrabold text-xs shadow-xs">
                      {{ task.assigned_agent.name.charAt(0) }}
                    </div>
                    <div class="min-w-0">
                      <p class="text-xs font-bold text-gray-900 dark:text-white truncate max-w-[130px]" :title="task.assigned_agent.name">
                        {{ task.assigned_agent.name }}
                      </p>
                    </div>
                  </div>
                  <span v-else class="text-gray-400 text-xs font-medium">Unassigned</span>
                </td>

                <!-- monday.com Style Status Cell -->
                <td class="px-4 py-4 text-center align-middle">
                  <div class="relative inline-block text-left group">
                    <button
                      type="button"
                      :style="{ backgroundColor: getStatusColor(task.status) }"
                      class="w-32 rounded-lg py-1.5 px-3 text-center text-xs font-extrabold uppercase tracking-wide text-white shadow-xs transition hover:opacity-90 active:scale-95"
                    >
                      {{ getStatusLabel(task.status) }}
                    </button>
                    <!-- Quick Dropdown -->
                    <div class="absolute left-1/2 -translate-x-1/2 top-full mt-1.5 hidden group-hover:block z-30 w-36 rounded-xl border border-gray-200 bg-white p-1.5 shadow-2xl dark:border-gray-700 dark:bg-gray-800">
                      <button
                        v-for="st in ['pending', 'in_progress', 'done', 'stuck']"
                        :key="st"
                        type="button"
                        @click="changeStatus(task, st)"
                        :style="{ backgroundColor: getStatusColor(st) }"
                        class="mb-1 last:mb-0 w-full rounded-lg py-1.5 px-2.5 text-center text-xs font-bold text-white transition hover:opacity-95"
                      >
                        {{ getStatusLabel(st) }}
                      </button>
                    </div>
                  </div>
                </td>

                <!-- Priority Cell -->
                <td class="px-4 py-4 text-center align-middle">
                  <div class="relative inline-block text-left group">
                    <button
                      type="button"
                      :class="getPriorityClass(task.priority)"
                      class="w-24 rounded-lg py-1.5 px-2 text-center text-xs font-bold uppercase tracking-wider transition hover:opacity-85 shadow-xs"
                    >
                      {{ capitalize(task.priority) }}
                    </button>
                    <!-- Quick Dropdown -->
                    <div class="absolute left-1/2 -translate-x-1/2 top-full mt-1.5 hidden group-hover:block z-30 w-28 rounded-xl border border-gray-200 bg-white p-1.5 shadow-xl dark:border-gray-700 dark:bg-gray-800">
                      <button
                        v-for="pr in ['low', 'medium', 'high', 'urgent']"
                        :key="pr"
                        type="button"
                        @click="changePriority(task, pr)"
                        :class="getPriorityClass(pr)"
                        class="mb-1 last:mb-0 w-full rounded-md py-1 text-xs font-bold"
                      >
                        {{ capitalize(pr) }}
                      </button>
                    </div>
                  </div>
                </td>

                <!-- Schedule / Timing -->
                <td class="px-4 py-4 align-middle">
                  <div class="space-y-1">
                    <div class="flex items-center gap-1.5">
                      <span
                        v-if="task.is_overdue"
                        class="inline-flex items-center gap-1 rounded bg-rose-50 px-1.5 py-0.5 text-[10px] font-bold text-rose-700 dark:bg-rose-950/50 dark:text-rose-400 border border-rose-200 dark:border-rose-800 shrink-0"
                      >
                        <i class="fa-solid fa-triangle-exclamation text-[9px]"></i>
                        Overdue
                      </span>
                      <span class="text-xs font-bold text-gray-800 dark:text-gray-200">
                        {{ task.due_label }}
                      </span>
                    </div>
                    <div v-if="task.reminder_label" class="flex items-center gap-1 text-[11px] font-semibold text-purple-600 dark:text-purple-400">
                      <i class="fa-solid fa-bell text-[9px]"></i>
                      <span>Alert: {{ task.reminder_label }}</span>
                    </div>
                  </div>
                </td>

                <!-- Live Timer (Digital Stopwatch Widget) -->
                <td class="px-4 py-4 text-center align-middle">
                  <div class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50/80 px-2.5 py-1.5 shadow-xs dark:border-gray-700 dark:bg-gray-800">
                    <button
                      type="button"
                      @click="toggleTimer(task)"
                      :class="[
                        'flex h-6 w-6 items-center justify-center rounded-lg text-xs font-bold transition shadow-xs',
                        task.is_timing
                          ? 'bg-amber-500 text-white animate-pulse'
                          : 'bg-emerald-600 text-white hover:bg-emerald-700'
                      ]"
                      :title="task.is_timing ? 'Pause Timer' : 'Start Timer'"
                    >
                      <i :class="task.is_timing ? 'fa-solid fa-pause text-[10px]' : 'fa-solid fa-play text-[9px] ml-0.5'"></i>
                    </button>
                    <span class="font-mono text-xs font-bold text-gray-800 dark:text-gray-200">
                      {{ formatMinutes(task.duration_minutes) }}
                    </span>
                  </div>
                </td>

                <!-- Actions -->
                <td class="px-4 py-4 text-right align-middle pr-5">
                  <div class="flex items-center justify-end gap-1.5">
                    <button
                      type="button"
                      @click="openEditModal(task)"
                      class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-white transition"
                      title="Edit Task"
                    >
                      <i class="fa-solid fa-pen-to-square text-xs"></i>
                    </button>
                    <button
                      type="button"
                      @click="deleteTask(task)"
                      class="rounded-lg p-2 text-gray-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40 transition"
                      title="Delete Task"
                    >
                      <i class="fa-solid fa-trash-can text-xs"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Responsive Cards View (Alternative View Mode) -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-5">
          <div
            v-for="task in tasks"
            :key="task.id"
            :class="[
              'rounded-xl border border-gray-200 bg-white p-4 shadow-xs hover:shadow-sm dark:border-gray-800 dark:bg-gray-900/60 transition flex flex-col justify-between',
              task.status === 'done' ? 'opacity-80' : ''
            ]"
          >
            <div>
              <!-- Card Top: Type badge, Due date, Overdue alert -->
              <div class="flex items-center justify-between gap-2 mb-2">
                <span :class="getTypeBadgeClass(task.type)" class="inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-xs font-bold shadow-xs">
                  <i :class="getTypeIcon(task.type)"></i>
                  {{ getTypeLabel(task.type) }}
                </span>
                <span
                  v-if="task.is_overdue"
                  class="inline-flex items-center gap-1 rounded bg-rose-50 px-1.5 py-0.5 text-[10px] font-bold text-rose-700 dark:bg-rose-950/50 dark:text-rose-400 border border-rose-200"
                >
                  <i class="fa-solid fa-triangle-exclamation text-[9px]"></i>
                  Overdue
                </span>
              </div>

              <!-- Title & RFQ -->
              <h3 :class="['text-sm font-bold text-gray-900 dark:text-white', task.status === 'done' ? 'line-through text-gray-400' : '']">
                {{ task.title }}
              </h3>
              <p v-if="task.description" class="mt-1 text-xs text-gray-500 line-clamp-2">
                {{ task.description }}
              </p>

              <!-- Customer Contact Details & Actions -->
              <div v-if="task.contact_name || task.contact_phone" class="mt-3 rounded-lg bg-gray-50 p-2.5 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800 space-y-1.5">
                <p class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                  <i class="fa-solid fa-user text-gray-400 text-[10px]"></i>
                  <span>{{ task.contact_name || 'Client Contact' }}</span>
                </p>
                <div v-if="task.contact_phone" class="flex flex-wrap items-center gap-1.5">
                  <a
                    :href="`tel:${task.contact_phone}`"
                    class="inline-flex items-center gap-1 rounded bg-white hover:bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs font-mono font-medium text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-600"
                  >
                    <i class="fa-solid fa-phone text-[9px] text-gray-400"></i>
                    {{ task.contact_phone }}
                  </a>
                  <a
                    :href="`https://wa.me/${cleanPhone(task.contact_phone)}`"
                    target="_blank"
                    class="inline-flex items-center gap-1 rounded bg-emerald-50 hover:bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-700 border border-emerald-200"
                  >
                    <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                    WhatsApp
                  </a>
                </div>
              </div>

              <!-- Schedule & Chime Alert -->
              <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                <span class="font-medium flex items-center gap-1">
                  <i class="fa-regular fa-clock text-gray-400 text-[10px]"></i>
                  {{ task.due_label }}
                </span>
                <span v-if="task.reminder_label" class="font-semibold text-purple-600 flex items-center gap-1">
                  <i class="fa-solid fa-bell text-[10px]"></i>
                  {{ task.reminder_label }}
                </span>
              </div>
            </div>

            <!-- Card Footer: Status, Priority, Timer, and Actions -->
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between gap-2">
              <div class="flex items-center gap-2">
                <span
                  :style="{ backgroundColor: getStatusColor(task.status) }"
                  class="rounded-md px-2.5 py-1 text-xs font-extrabold uppercase text-white shadow-xs"
                >
                  {{ getStatusLabel(task.status) }}
                </span>
                <span
                  :class="getPriorityClass(task.priority)"
                  class="rounded-md px-2 py-1 text-xs font-bold uppercase"
                >
                  {{ capitalize(task.priority) }}
                </span>
              </div>

              <div class="flex items-center gap-2">
                <!-- Timer -->
                <button
                  type="button"
                  @click="toggleTimer(task)"
                  :class="[
                    'flex h-7 w-7 items-center justify-center rounded-lg text-xs font-bold transition shadow-xs',
                    task.is_timing ? 'bg-amber-500 text-white animate-pulse' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200'
                  ]"
                  :title="task.is_timing ? 'Pause Timer' : 'Start Timer'"
                >
                  <i :class="task.is_timing ? 'fa-solid fa-pause text-[10px]' : 'fa-solid fa-play text-[9px] ml-0.5'"></i>
                </button>

                <!-- Edit -->
                <button
                  type="button"
                  @click="openEditModal(task)"
                  class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                >
                  <i class="fa-solid fa-pen-to-square text-xs"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Task Create / Edit Modal (Floating firmly above top navbar with z-[999999]) -->
      <div v-if="showModal" class="fixed inset-0 z-[999999] flex items-center justify-center p-4 sm:p-6 bg-gray-950/75 backdrop-blur-sm overflow-y-auto">
        <div class="relative my-auto w-full max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 sm:p-7 shadow-2xl dark:border-gray-700 dark:bg-gray-900 max-h-[90vh] overflow-y-auto">
          <!-- Modal Header -->
          <div class="flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-800">
            <div>
              <h3 class="text-xl font-extrabold text-gray-950 dark:text-white">
                {{ isEditing ? 'Edit Task / Talk' : 'Schedule Talk or Task' }}
              </h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Set talk timing, customer contact, and live chime reminders
              </p>
            </div>
            <button @click="closeModal" class="rounded-xl p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-white transition">
              <i class="fa-solid fa-xmark text-lg"></i>
            </button>
          </div>

          <form @submit.prevent="saveTask" class="mt-6 space-y-5">
            <!-- Action Type Selection (Reduced font size and subtle weight) -->
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                Action Type
              </label>
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <button
                  type="button"
                  v-for="tp in types"
                  :key="tp.value"
                  @click="taskForm.type = tp.value"
                  :class="[
                    'flex items-center justify-center gap-1.5 rounded-lg border py-2 px-2.5 text-center text-xs transition',
                    taskForm.type === tp.value
                      ? 'border-brand-500 bg-brand-50 text-brand-700 font-semibold dark:border-brand-400 dark:bg-brand-500/20 dark:text-brand-300 ring-1 ring-brand-500/20 shadow-xs'
                      : 'border-gray-200 bg-white font-normal text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800/80 dark:text-gray-300'
                  ]"
                >
                  <i :class="[tp.icon, 'text-xs', taskForm.type === tp.value ? 'text-brand-600 dark:text-brand-400' : 'text-gray-400']"></i>
                  <span>{{ tp.label }}</span>
                </button>
              </div>
            </div>

            <!-- Title -->
            <div>
              <label class="block text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5">
                Title / Subject <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="taskForm.title"
                required
                type="text"
                placeholder="e.g., Call client regarding China air freight quote"
                class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-xs"
              />
            </div>

            <!-- Contact Name & Phone (For talks/calls) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5">
                  <i class="fa-solid fa-user mr-1 text-gray-400"></i>
                  Contact / Client Name
                </label>
                <input
                  v-model="taskForm.contact_name"
                  type="text"
                  placeholder="e.g., Alhaji Usman"
                  class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-xs"
                />
              </div>
              <div>
                <label class="block text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5">
                  <i class="fa-solid fa-phone mr-1 text-gray-400"></i>
                  Phone / WhatsApp
                </label>
                <input
                  v-model="taskForm.contact_phone"
                  type="text"
                  placeholder="e.g., +234 801 234 5678"
                  class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-xs"
                />
              </div>
            </div>

            <!-- Assigned Agent & Related RFQ -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div v-if="isAdmin">
                <label class="block text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5">
                  <i class="fa-solid fa-user-gear mr-1 text-gray-400"></i>
                  Assign to Agent
                </label>
                <select
                  v-model="taskForm.assigned_to"
                  class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm font-semibold text-gray-900 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-xs"
                >
                  <option v-for="agent in agents" :key="agent.id" :value="agent.id">
                    {{ agent.name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5">
                  <i class="fa-solid fa-box mr-1 text-gray-400"></i>
                  Linked RFQ (Optional)
                </label>
                <select
                  v-model="taskForm.rfq_id"
                  class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm font-semibold text-gray-900 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-xs"
                >
                  <option :value="null">None</option>
                  <option v-for="rfq in rfqs" :key="rfq.id" :value="rfq.id">
                    {{ rfq.product_name }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Due Date & Reminder Alert -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5">
                  <i class="fa-regular fa-calendar-days mr-1 text-gray-400"></i>
                  Scheduled Due Time
                </label>
                <input
                  v-model="taskForm.due_at"
                  type="datetime-local"
                  class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm font-semibold text-gray-900 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-xs"
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5">
                  <i class="fa-solid fa-bell mr-1 text-purple-600"></i>
                  Live Chime Alert Time
                </label>
                <input
                  v-model="taskForm.reminder_at"
                  type="datetime-local"
                  class="h-12 w-full rounded-xl border border-purple-300 bg-purple-50/20 px-4 text-sm font-semibold text-gray-900 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-purple-800 dark:bg-gray-800 dark:text-white shadow-xs"
                />
              </div>
            </div>

            <!-- Priority & Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5">Priority</label>
                <select
                  v-model="taskForm.priority"
                  class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm font-semibold text-gray-900 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-xs"
                >
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                  <option value="urgent">Urgent</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5">Status</label>
                <select
                  v-model="taskForm.status"
                  class="h-12 w-full rounded-xl border border-gray-300 px-4 text-sm font-semibold text-gray-900 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-xs"
                >
                  <option value="pending">Scheduled / Pending</option>
                  <option value="in_progress">Working On It</option>
                  <option value="done">Done</option>
                  <option value="stuck">Stuck</option>
                </select>
              </div>
            </div>

            <!-- Notes -->
            <div>
              <label class="block text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5">
                Description / Discussion Notes
              </label>
              <textarea
                v-model="taskForm.description"
                rows="3"
                placeholder="Details of what to discuss or instructions for the agent..."
                class="w-full rounded-xl border border-gray-300 p-4 text-sm font-medium text-gray-900 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-xs"
              ></textarea>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3.5 pt-4 border-t border-gray-200 dark:border-gray-800">
              <button
                type="button"
                @click="closeModal"
                class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 transition"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="rounded-xl bg-brand-500 px-7 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-600 active:scale-95 transition"
              >
                {{ isEditing ? 'Save Changes' : 'Schedule' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import { playChimeSound, requestNotificationPermission, sendBrowserNotification } from '@/utils/soundAlert'
import { toast } from 'vue3-toastify'

const props = defineProps({
  tasks: Array,
  stats: Object,
  agents: Array,
  rfqs: Array,
  isAdmin: Boolean,
  filters: Object,
})

const page = usePage()
const notificationGranted = ref(false)
const viewMode = ref('table')

// Filter state
const filterForm = ref({
  search: props.filters?.search || '',
  status: props.filters?.status || 'all',
  priority: props.filters?.priority || 'all',
  type: props.filters?.type || 'all',
  agent_id: props.filters?.agent_id || 'all',
  time_filter: props.filters?.time_filter || '',
})

// Modal & Form State
const showModal = ref(false)
const isEditing = ref(false)
const editingTaskId = ref(null)

const types = [
  { value: 'talk', label: 'Talk / Call', icon: 'fa-solid fa-phone-volume text-purple-600 dark:text-purple-400' },
  { value: 'rfq_followup', label: 'RFQ Follow-up', icon: 'fa-solid fa-boxes-packing text-blue-600 dark:text-blue-400' },
  { value: 'supplier_contact', label: 'Supplier', icon: 'fa-solid fa-handshake text-amber-600 dark:text-amber-400' },
  { value: 'general', label: 'Task', icon: 'fa-solid fa-list-check text-emerald-600 dark:text-emerald-400' },
]

const taskForm = useForm({
  title: '',
  description: '',
  type: 'talk',
  priority: 'medium',
  status: 'pending',
  assigned_to: page.props.auth.user?.id || '',
  rfq_id: null,
  contact_name: '',
  contact_phone: '',
  due_at: '',
  reminder_at: '',
  duration_minutes: 0,
  notes: '',
})

// monday.com Style Colors & Badges
const getStatusColor = (status) => {
  switch (status) {
    case 'done': return '#00C875' // monday.com green
    case 'in_progress': return '#FDAB3D' // monday.com orange
    case 'stuck': return '#E2445C' // monday.com red
    case 'pending': return '#579BFC' // monday.com blue
    default: return '#C4C4C4'
  }
}

const getStatusLabel = (status) => {
  switch (status) {
    case 'done': return 'Done'
    case 'in_progress': return 'Working on it'
    case 'stuck': return 'Stuck'
    case 'pending': return 'Scheduled'
    default: return status
  }
}

const getPriorityClass = (priority) => {
  switch (priority) {
    case 'urgent': return 'bg-rose-600 text-white shadow-xs'
    case 'high': return 'bg-purple-600 text-white shadow-xs'
    case 'medium': return 'bg-blue-600 text-white shadow-xs'
    case 'low': return 'bg-gray-500 text-white shadow-xs'
    default: return 'bg-gray-400 text-white'
  }
}

const getTypeBadgeClass = (type) => {
  switch (type) {
    case 'talk': return 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300 border border-purple-200 dark:border-purple-800'
    case 'rfq_followup': return 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border border-blue-200 dark:border-blue-800'
    case 'supplier_contact': return 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-200 dark:border-amber-800'
    case 'meeting': return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800'
    default: return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700'
  }
}

const getTypeIcon = (type) => {
  switch (type) {
    case 'talk': return 'fa-solid fa-phone'
    case 'rfq_followup': return 'fa-solid fa-boxes-packing'
    case 'supplier_contact': return 'fa-solid fa-handshake'
    case 'meeting': return 'fa-solid fa-users'
    default: return 'fa-solid fa-list-check'
  }
}

const getTypeLabel = (type) => {
  switch (type) {
    case 'talk': return 'Talk / Call'
    case 'rfq_followup': return 'RFQ Follow-up'
    case 'supplier_contact': return 'Supplier'
    case 'meeting': return 'Meeting'
    default: return 'Task'
  }
}

const capitalize = (str) => {
  if (!str) return ''
  return str.charAt(0).toUpperCase() + str.slice(1)
}

const cleanPhone = (phone) => {
  if (!phone) return ''
  return phone.replace(/[^0-9]/g, '')
}

const formatMinutes = (mins) => {
  if (!mins || mins === 0) return '0m'
  const h = Math.floor(mins / 60)
  const m = mins % 60
  if (h > 0) return `${h}h ${m}m`
  return `${m}m`
}

// Quick Actions
const toggleDone = (task) => {
  const newStatus = task.status === 'done' ? 'pending' : 'done'
  changeStatus(task, newStatus)
}

const changeStatus = (task, newStatus) => {
  router.patch(`/tasks/${task.id}/status`, { status: newStatus }, {
    preserveScroll: true,
  })
}

const changePriority = (task, newPriority) => {
  router.put(`/tasks/${task.id}`, {
    ...task,
    priority: newPriority,
    assigned_to: task.assigned_agent?.id || null,
    rfq_id: task.rfq?.id || null,
  }, {
    preserveScroll: true,
  })
}

const toggleTimer = (task) => {
  router.post(`/tasks/${task.id}/toggle-timer`, {}, {
    preserveScroll: true,
  })
}

const deleteTask = (task) => {
  if (confirm(`Are you sure you want to delete "${task.title}"?`)) {
    router.delete(`/tasks/${task.id}`, {
      preserveScroll: true,
    })
  }
}

// Filter Navigation
let debounceTimeout = null
const applyFiltersDebounced = () => {
  clearTimeout(debounceTimeout)
  debounceTimeout = setTimeout(() => {
    applyFilters()
  }, 300)
}

const applyFilters = () => {
  router.get('/tasks', filterForm.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

// Modal Handlers
const openCreateModal = (presetType = 'general') => {
  isEditing.value = false
  editingTaskId.value = null
  taskForm.reset()
  taskForm.type = presetType
  taskForm.assigned_to = page.props.auth.user?.id || ''
  taskForm.status = 'pending'
  taskForm.priority = presetType === 'talk' ? 'high' : 'medium'
  showModal.value = true
}

const openEditModal = (task) => {
  isEditing.value = true
  editingTaskId.value = task.id
  taskForm.title = task.title
  taskForm.description = task.description || ''
  taskForm.type = task.type
  taskForm.priority = task.priority
  taskForm.status = task.status
  taskForm.assigned_to = task.assigned_agent?.id || ''
  taskForm.rfq_id = task.rfq?.id || null
  taskForm.contact_name = task.contact_name || ''
  taskForm.contact_phone = task.contact_phone || ''
  taskForm.due_at = task.due_at ? task.due_at.slice(0, 16) : ''
  taskForm.reminder_at = task.reminder_at ? task.reminder_at.slice(0, 16) : ''
  taskForm.duration_minutes = task.duration_minutes || 0
  taskForm.notes = task.notes || ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const saveTask = () => {
  if (isEditing.value && editingTaskId.value) {
    taskForm.put(`/tasks/${editingTaskId.value}`, {
      onSuccess: () => closeModal(),
    })
  } else {
    taskForm.post('/tasks', {
      onSuccess: () => closeModal(),
    })
  }
}

// Live Alerts & Sound
const setupNotifications = async () => {
  // Always trigger sound directly from user click to unlock AudioContext
  playChimeSound()

  const perm = await requestNotificationPermission()
  notificationGranted.value = perm === 'granted'

  if (perm === 'granted') {
    toast.success('Live Alerts & Chime Sound Active!')
    sendBrowserNotification('Notifications & Sound Active', {
      body: 'You will receive melodic chime alerts for upcoming talks and tasks.',
      url: '/tasks',
    })
  } else if (perm === 'denied') {
    toast.warning(
      'Browser notifications are currently blocked for this site. Click the settings icon in your URL bar to allow Notifications, or try http://localhost:8000',
      { autoClose: 9000 }
    )
  } else if (perm === 'unsupported') {
    toast.info('Desktop notifications are unsupported on this connection, but audio chimes and in-app banners are active!')
  }
}

const testChime = () => {
  playChimeSound()
  toast.info('Played chime test! If you did not hear it, please check your device volume.', { autoClose: 4000 })
  sendBrowserNotification('Melodic Chime Test', {
    body: 'Elbildad live audio alert synthesizer is working perfectly.',
    url: '/tasks',
  })
}

onMounted(() => {
  if ('Notification' in window && Notification.permission === 'granted') {
    notificationGranted.value = true
  }
})
</script>

<style scoped>
@keyframes bounceOnce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}
.animate-bounce-once {
  animation: bounceOnce 0.6s ease-in-out;
}
.animate-spin-slow {
  animation: spin 3s linear infinite;
}
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
