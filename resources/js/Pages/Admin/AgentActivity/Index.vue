<template>
  <AdminLayout>
    <Head title="Agent Activity & Notification Settings" />

    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white/90">Agent Activity & Monitoring</h2>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
              <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
              {{ agents.length }} Active Agent{{ agents.length !== 1 ? 's' : '' }}
            </span>
          </div>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
            Real-time agent logins, RFQ processing performance, and customized inactivity alerts.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
          <!-- Notification Settings Button -->
          <button
            @click="openSettingsModal"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition-all"
          >
            <div class="relative flex h-5 w-5 items-center justify-center text-brand-500">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <span
                v-if="settingsForm.notify_on_login || settingsForm.notify_on_logout || settingsForm.notify_on_inactivity"
                class="absolute -top-0.5 -right-0.5 h-2 w-2 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-gray-800"
              ></span>
            </div>
            <span>Alert Settings</span>
            <span class="rounded bg-brand-50 px-1.5 py-0.5 text-[10px] font-bold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
              {{ settingsForm.inactivity_hours }}h Threshold
            </span>
          </button>

          <!-- Quick Test Alert Button -->
          <button
            @click="triggerTestAlert"
            :disabled="testingAlert"
            class="inline-flex items-center gap-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 px-3 py-2.5 text-xs font-semibold text-gray-700 dark:text-gray-300 transition-all disabled:opacity-50"
            title="Send a sample chime and notification to your admin bell"
          >
            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            {{ testingAlert ? 'Testing...' : 'Test Chime' }}
          </button>
        </div>
      </div>

      <!-- Main Tabs Switcher -->
      <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800">
        <nav class="flex space-x-6" aria-label="Tabs">
          <button
            @click="activeTab = 'agents'"
            :class="[
              activeTab === 'agents'
                ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-bold'
                : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 font-medium',
              'flex items-center gap-2 border-b-2 py-3 px-1 text-sm transition-all'
            ]"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>Agent Workload & Performance</span>
            <span class="rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-xs">
              {{ agents.length }}
            </span>
          </button>

          <button
            @click="activeTab = 'logs'"
            :class="[
              activeTab === 'logs'
                ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-bold'
                : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 font-medium',
              'flex items-center gap-2 border-b-2 py-3 px-1 text-sm transition-all'
            ]"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Login & Logout Audit Trail</span>
            <span class="rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 px-2 py-0.5 text-xs font-semibold">
              {{ recentLogs.length }}
            </span>
          </button>
        </nav>

        <div v-if="activeTab === 'logs'" class="text-xs text-gray-400 hidden sm:block">
          Auto-recorded upon agent login/logout events
        </div>
      </div>

      <!-- TAB 1: AGENT WORKLOAD & PERFORMANCE -->
      <div v-if="activeTab === 'agents'" class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 md:gap-6">
          <div
            v-for="stat in summary"
            :key="stat.title"
            class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] shadow-sm hover:shadow-md transition-shadow"
          >
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ stat.title }}</p>
                <p
                  class="text-2xl font-bold"
                  :class="{
                    'text-blue-600 dark:text-blue-400':    stat.color === 'blue',
                    'text-purple-600 dark:text-purple-400': stat.color === 'purple',
                    'text-indigo-600 dark:text-indigo-400': stat.color === 'indigo',
                    'text-green-600 dark:text-green-400':  stat.color === 'green',
                  }"
                >{{ stat.value }}</p>
              </div>
              <div
                class="flex h-11 w-11 items-center justify-center rounded-xl flex-shrink-0"
                :class="{
                  'bg-blue-50 dark:bg-blue-500/10':    stat.color === 'blue',
                  'bg-purple-50 dark:bg-purple-500/10': stat.color === 'purple',
                  'bg-indigo-50 dark:bg-indigo-500/10': stat.color === 'indigo',
                  'bg-green-50 dark:bg-green-500/10':  stat.color === 'green',
                }"
              >
                <svg v-if="stat.icon === 'agents'" class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <svg v-else-if="stat.icon === 'star'" class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                <svg v-else-if="stat.icon === 'rfq'" class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <svg v-else-if="stat.icon === 'check'" class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Status Overview -->
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5 shadow-sm">
          <h3 class="text-sm font-semibold text-gray-700 dark:text-white/80 mb-4 uppercase tracking-wider">Portal-Wide RFQ Status Breakdown</h3>
          <div class="flex flex-wrap gap-3">
            <div
              v-for="s in statusOverview"
              :key="s.label"
              class="flex items-center gap-2 rounded-xl border px-4 py-2.5"
              :class="{
                'border-green-200 bg-green-50 dark:border-green-500/20 dark:bg-green-500/5':       s.color === 'green',
                'border-purple-200 bg-purple-50 dark:border-purple-500/20 dark:bg-purple-500/5':   s.color === 'purple',
                'border-orange-200 bg-orange-50 dark:border-orange-500/20 dark:bg-orange-500/5':   s.color === 'orange',
                'border-red-200 bg-red-50 dark:border-red-500/20 dark:bg-red-500/5':               s.color === 'red',
                'border-sky-200 bg-sky-50 dark:border-sky-500/20 dark:bg-sky-500/5':               s.color === 'sky',
                'border-amber-200 bg-amber-50 dark:border-amber-500/20 dark:bg-amber-500/5':       s.color === 'amber',
                'border-emerald-200 bg-emerald-50 dark:border-emerald-500/20 dark:bg-emerald-500/5': s.color === 'emerald',
              }"
            >
              <span
                class="text-lg font-bold"
                :class="{
                  'text-green-600 dark:text-green-400':     s.color === 'green',
                  'text-purple-600 dark:text-purple-400':   s.color === 'purple',
                  'text-orange-600 dark:text-orange-400':   s.color === 'orange',
                  'text-red-600 dark:text-red-400':         s.color === 'red',
                  'text-sky-600 dark:text-sky-400':         s.color === 'sky',
                  'text-amber-600 dark:text-amber-400':     s.color === 'amber',
                  'text-emerald-600 dark:text-emerald-400': s.color === 'emerald',
                }"
              >{{ s.count }}</span>
              <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ s.label }}</span>
            </div>
          </div>
        </div>

        <!-- Agent Cards -->
        <div class="space-y-5">
          <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Agent Breakdown & Idle Tracking</h3>

          <div v-if="agents.length === 0" class="rounded-2xl border border-dashed border-gray-200 dark:border-gray-800 py-12 text-center text-gray-400">
            No agents found on the portal.
          </div>

          <div
            v-for="(agent, index) in agents"
            :key="agent.id"
            class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-sm overflow-hidden"
          >
            <!-- Agent Header -->
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div class="flex items-center gap-3">
                <span
                  class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-xs font-bold"
                  :class="{
                    'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400': index === 0,
                    'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-300':            index === 1,
                    'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400': index === 2,
                    'bg-gray-50 text-gray-400 dark:bg-gray-800':                                index > 2,
                  }"
                >#{{ index + 1 }}</span>

                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-500/20 text-brand-600 dark:text-brand-400 font-bold text-base">
                  {{ agent.name.charAt(0).toUpperCase() }}
                </div>

                <div>
                  <div class="flex items-center gap-2">
                    <p class="font-semibold text-gray-800 dark:text-white/90">{{ agent.name }}</p>
                    <span
                      class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                      :class="{
                        'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400': agent.role === 'super_agent',
                        'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400':         agent.role !== 'super_agent',
                      }"
                    >{{ agent.role === 'super_agent' ? '★ Super Agent' : 'Agent' }}</span>
                  </div>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ agent.email }} · Joined {{ agent.joined_at }}</p>
                </div>
              </div>

              <!-- Completion rate & expand button -->
              <div class="flex items-center gap-3 flex-shrink-0">
                <div class="text-right">
                  <p class="text-[10px] text-gray-400 uppercase tracking-wider">Completion</p>
                  <p
                    class="text-lg font-bold"
                    :class="{
                      'text-green-600 dark:text-green-400':   agent.completion_rate >= 75,
                      'text-orange-500':                       agent.completion_rate >= 40 && agent.completion_rate < 75,
                      'text-red-500':                          agent.completion_rate < 40,
                    }"
                  >{{ agent.completion_rate }}%</p>
                </div>
                <button
                  @click="toggleExpanded(agent.id)"
                  class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                  {{ expanded === agent.id ? 'Collapse' : 'View RFQs' }}
                  <svg
                    class="w-3.5 h-3.5 transition-transform duration-200"
                    :class="{ 'rotate-180': expanded === agent.id }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Stats + Login Row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 divide-x divide-y sm:divide-y-0 divide-gray-100 dark:divide-gray-800 border-b border-gray-100 dark:border-gray-800">
              <div class="p-3 text-center">
                <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-0.5">Total</p>
                <p class="text-lg font-bold text-gray-800 dark:text-white">{{ agent.total }}</p>
              </div>
              <div class="p-3 text-center">
                <p class="text-[10px] text-green-500 uppercase tracking-wider mb-0.5">Completed</p>
                <p class="text-lg font-bold text-green-600 dark:text-green-400">{{ agent.completed }}</p>
              </div>
              <div class="p-3 text-center">
                <p class="text-[10px] text-purple-500 uppercase tracking-wider mb-0.5">In Progress</p>
                <p class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ agent.in_progress }}</p>
              </div>
              <div class="p-3 text-center">
                <p class="text-[10px] text-orange-500 uppercase tracking-wider mb-0.5">Pending</p>
                <p class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ agent.pending }}</p>
              </div>
              <div class="p-3 text-center">
                <p class="text-[10px] text-sky-500 uppercase tracking-wider mb-0.5">Quoted</p>
                <p class="text-lg font-bold text-sky-600 dark:text-sky-400">{{ agent.quoted }}</p>
              </div>
              <div class="p-3 text-center">
                <p class="text-[10px] text-red-400 uppercase tracking-wider mb-0.5">Not Found</p>
                <p class="text-lg font-bold text-red-500 dark:text-red-400">{{ agent.not_found }}</p>
              </div>

              <!-- Last Login -->
              <div class="p-3 text-center col-span-2 sm:col-span-2 bg-gray-50/50 dark:bg-white/[0.01]">
                <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-0.5 flex items-center justify-center gap-1">
                  <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                  </svg>
                  Last Login
                </p>
                <p
                  class="text-sm font-semibold"
                  :class="{
                    'text-green-600 dark:text-green-400':   agent.days_since_login !== null && agent.days_since_login <= 1,
                    'text-orange-500':                       agent.days_since_login !== null && agent.days_since_login > 1 && agent.days_since_login <= 7,
                    'text-red-500':                          agent.days_since_login === null || agent.days_since_login > 7,
                  }"
                >{{ agent.last_login_human }}</p>
                <p v-if="agent.last_login_at" class="text-[10px] text-gray-400 mt-0.5">{{ agent.last_login_at }}</p>
                <p v-if="agent.avg_response_time" class="text-[10px] text-gray-400 mt-1">
                  Avg response: <strong class="text-gray-600 dark:text-gray-300">{{ agent.avg_response_time }}</strong>
                </p>
              </div>
            </div>

            <!-- Expanded: RFQ Detail Table -->
            <div v-if="expanded === agent.id" class="border-t border-gray-100 dark:border-gray-800">
              <div class="px-5 py-3 bg-gray-50/50 dark:bg-white/[0.01] flex items-center justify-between">
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                  Assigned RFQs ({{ agent.rfqs.length }})
                </p>
                <div class="flex items-center gap-3 text-[10px] text-gray-400">
                  <span class="flex items-center gap-1"><span class="inline-block w-2 h-2 rounded-full bg-red-400"></span> Critical (7d+)</span>
                  <span class="flex items-center gap-1"><span class="inline-block w-2 h-2 rounded-full bg-orange-400"></span> High (3d+)</span>
                  <span class="flex items-center gap-1"><span class="inline-block w-2 h-2 rounded-full bg-yellow-400"></span> Medium (1d+)</span>
                  <span class="flex items-center gap-1"><span class="inline-block w-2 h-2 rounded-full bg-green-400"></span> Recent</span>
                </div>
              </div>

              <div v-if="agent.rfqs.length === 0" class="px-5 py-8 text-center text-sm text-gray-400 dark:text-gray-500">
                No RFQs assigned to this agent yet.
              </div>

              <div v-else class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                  <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-transparent">
                      <th class="px-5 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Tracking ID</th>
                      <th class="px-5 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Product</th>
                      <th class="px-5 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                      <th class="px-5 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Date Assigned</th>
                      <th class="px-5 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Last Updated</th>
                      <th class="px-5 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Idle / Stall Time</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="rfq in agent.rfqs" :key="rfq.id" class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                      <td class="px-5 py-3">
                        <span class="font-mono text-xs text-gray-600 dark:text-gray-400">{{ rfq.tracking_token }}</span>
                      </td>
                      <td class="px-5 py-3">
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90 max-w-[220px] truncate" :title="rfq.product_name">{{ rfq.product_name }}</p>
                      </td>
                      <td class="px-5 py-3">
                        <span
                          class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                          :class="{
                            'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400':     rfq.status === 'completed',
                            'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400':         rfq.status === 'assigned',
                            'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400': rfq.status === 'sourcing',
                            'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-400': rfq.status === 'purchased',
                            'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400': rfq.status === 'shipped',
                            'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400':             rfq.status === 'not_found',
                            'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-400':             rfq.status === 'quoted',
                            'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400':     rfq.status === 'awaiting_payment',
                            'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400': rfq.status === 'payment_received',
                            'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300':            !['completed','assigned','sourcing','purchased','shipped','not_found','quoted','awaiting_payment','payment_received'].includes(rfq.status),
                          }"
                        >{{ rfq.status.replace(/_/g, ' ') }}</span>
                      </td>
                      <td class="px-5 py-3">
                        <p class="text-xs text-gray-700 dark:text-gray-300">{{ rfq.assigned_date }}</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ rfq.assigned_human }}</p>
                      </td>
                      <td class="px-5 py-3">
                        <p class="text-xs text-gray-700 dark:text-gray-300">{{ rfq.last_updated }}</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ rfq.last_updated_human }}</p>
                      </td>
                      <!-- Idle / Stall Time -->
                      <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                          <span
                            class="inline-block w-2 h-2 rounded-full flex-shrink-0"
                            :class="{
                              'bg-red-500':    rfq.risk === 'critical',
                              'bg-orange-400': rfq.risk === 'high',
                              'bg-yellow-400': rfq.risk === 'medium',
                              'bg-green-400':  rfq.risk === 'low',
                            }"
                          ></span>
                          <div>
                            <p
                              class="text-xs font-semibold"
                              :class="{
                                'text-red-600 dark:text-red-400':    rfq.risk === 'critical',
                                'text-orange-500 dark:text-orange-400': rfq.risk === 'high',
                                'text-yellow-600 dark:text-yellow-400': rfq.risk === 'medium',
                                'text-gray-500 dark:text-gray-400':  rfq.risk === 'low',
                              }"
                            >{{ rfq.idle_label }}</p>
                            <p v-if="rfq.risk === 'critical'" class="text-[10px] text-red-400 mt-0.5 font-medium">Needs urgent attention</p>
                            <p v-else-if="rfq.risk === 'high'" class="text-[10px] text-orange-400 mt-0.5">Follow up needed</p>
                            <p v-else-if="rfq.risk === 'medium'" class="text-[10px] text-yellow-500 mt-0.5">Check soon</p>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 2: LOGIN & LOGOUT AUDIT TRAIL -->
      <div v-else-if="activeTab === 'logs'" class="space-y-4">
        <!-- Filter Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white dark:bg-white/[0.03] p-4 rounded-2xl border border-gray-200 dark:border-gray-800">
          <div class="flex items-center gap-2">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Filter Event:</span>
            <button
              @click="logFilter = 'all'"
              :class="logFilter === 'all' ? 'bg-gray-800 text-white dark:bg-white dark:text-gray-900' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'"
              class="px-2.5 py-1 text-xs rounded-lg font-medium transition-colors"
            >
              All ({{ recentLogs.length }})
            </button>
            <button
              @click="logFilter = 'login'"
              :class="logFilter === 'login' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400'"
              class="px-2.5 py-1 text-xs rounded-lg font-medium transition-colors"
            >
              Logins
            </button>
            <button
              @click="logFilter = 'logout'"
              :class="logFilter === 'logout' ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'"
              class="px-2.5 py-1 text-xs rounded-lg font-medium transition-colors"
            >
              Logouts
            </button>
          </div>

          <div class="relative w-full sm:w-64">
            <input
              v-model="logSearch"
              type="text"
              placeholder="Search agent or IP..."
              class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 px-3 py-2 text-gray-800 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500"
            />
          </div>
        </div>

        <!-- Audit Trail Table -->
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-sm overflow-hidden">
          <div v-if="filteredLogs.length === 0" class="py-12 text-center text-sm text-gray-400">
            No authentication activity records found.
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
              <thead class="bg-gray-50/70 dark:bg-gray-800/40 text-[11px] font-semibold uppercase text-gray-400 tracking-wider">
                <tr>
                  <th class="px-5 py-3 text-left">Event</th>
                  <th class="px-5 py-3 text-left">Agent</th>
                  <th class="px-5 py-3 text-left">IP Address</th>
                  <th class="px-5 py-3 text-left">Browser / Device</th>
                  <th class="px-5 py-3 text-right">Timestamp</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                <tr
                  v-for="log in filteredLogs"
                  :key="log.id"
                  class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-colors"
                >
                  <!-- Event Badge -->
                  <td class="px-5 py-3.5 whitespace-nowrap">
                    <span
                      v-if="log.event === 'login'"
                      class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 dark:bg-emerald-500/20 px-2.5 py-1 text-xs font-semibold text-emerald-800 dark:text-emerald-300"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                      </svg>
                      Logged In
                    </span>
                    <span
                      v-else-if="log.event === 'logout'"
                      class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 dark:bg-slate-700/50 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:text-slate-300"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                      </svg>
                      Logged Out
                    </span>
                    <span v-else class="text-gray-500 capitalize">{{ log.event }}</span>
                  </td>

                  <!-- Agent -->
                  <td class="px-5 py-3.5 whitespace-nowrap">
                    <div class="font-medium text-gray-900 dark:text-white">{{ log.agent_name }}</div>
                    <div class="text-[11px] text-gray-400">{{ log.agent_email }}</div>
                  </td>

                  <!-- IP Address -->
                  <td class="px-5 py-3.5 whitespace-nowrap">
                    <span class="font-mono text-xs text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">
                      {{ log.ip_address || '127.0.0.1' }}
                    </span>
                  </td>

                  <!-- Browser / Device -->
                  <td class="px-5 py-3.5 max-w-xs truncate text-gray-500 dark:text-gray-400" :title="log.user_agent">
                    {{ log.user_agent ? log.user_agent.substring(0, 48) + '...' : 'Web Browser' }}
                  </td>

                  <!-- Timestamp -->
                  <td class="px-5 py-3.5 whitespace-nowrap text-right">
                    <div class="font-medium text-gray-800 dark:text-gray-200">{{ log.occurred_at_human }}</div>
                    <div class="text-[10px] text-gray-400">{{ log.occurred_at_formatted }}</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- NOTIFICATION CUSTOMIZATION MODAL -->
    <div
      v-if="showSettingsModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm transition-all animate-fadeIn"
      @click.self="closeSettingsModal"
    >
      <div class="relative w-full max-w-xl rounded-2xl bg-white dark:bg-gray-900 p-6 shadow-2xl border border-gray-100 dark:border-gray-800 max-h-[90vh] overflow-y-auto custom-scrollbar">
        <!-- Modal Header -->
        <div class="flex items-start justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-900 dark:text-white">Agent Notification Settings</h3>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                Customize real-time alerts when agents log in, sign out, or become inactive.
              </p>
            </div>
          </div>
          <button
            @click="closeSettingsModal"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1 rounded-lg"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Modal Body Form -->
        <form @submit.prevent="saveNotificationSettings" class="space-y-5 mt-5">
          <!-- Section 1: Authentication Events -->
          <div class="rounded-xl bg-gray-50 dark:bg-white/[0.02] p-4 border border-gray-100 dark:border-gray-800 space-y-4">
            <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Authentication Alerts
            </h4>

            <!-- Toggle: Notify on Login -->
            <div class="flex items-center justify-between">
              <div>
                <label class="text-sm font-semibold text-gray-800 dark:text-white/90">Notify on Agent Login</label>
                <p class="text-xs text-gray-500 dark:text-gray-400">Trigger notification whenever an agent signs into the portal.</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  v-model="settingsForm.notify_on_login"
                  class="sr-only peer"
                />
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500"></div>
              </label>
            </div>

            <!-- Toggle: Notify on Logout -->
            <div class="flex items-center justify-between border-t border-gray-200/60 dark:border-gray-800 pt-3">
              <div>
                <label class="text-sm font-semibold text-gray-800 dark:text-white/90">Notify on Agent Logout</label>
                <p class="text-xs text-gray-500 dark:text-gray-400">Trigger notification whenever an agent signs out of the portal.</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  v-model="settingsForm.notify_on_logout"
                  class="sr-only peer"
                />
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500"></div>
              </label>
            </div>
          </div>

          <!-- Section 2: Inactivity Detection -->
          <div class="rounded-xl bg-gray-50 dark:bg-white/[0.02] p-4 border border-gray-100 dark:border-gray-800 space-y-4">
            <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Inactivity & Absence Trigger
            </h4>

            <!-- Toggle: Notify on Inactivity -->
            <div class="flex items-center justify-between">
              <div>
                <label class="text-sm font-semibold text-gray-800 dark:text-white/90">Notify on Prolonged Inactivity</label>
                <p class="text-xs text-gray-500 dark:text-gray-400">Alert admin when an agent hasn't logged in within the set hours.</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  v-model="settingsForm.notify_on_inactivity"
                  class="sr-only peer"
                />
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500"></div>
              </label>
            </div>

            <!-- Inactivity Hours Input -->
            <div v-if="settingsForm.notify_on_inactivity" class="border-t border-gray-200/60 dark:border-gray-800 pt-3">
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                Inactivity Threshold (Hours)
              </label>
              <div class="flex items-center gap-3">
                <div class="relative w-36">
                  <input
                    type="number"
                    v-model.number="settingsForm.inactivity_hours"
                    min="1"
                    max="720"
                    required
                    class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-brand-500"
                  />
                  <span class="absolute right-3 top-2 text-xs text-gray-400">hours</span>
                </div>
                <div class="text-xs text-gray-500">
                  <span v-if="settingsForm.inactivity_hours === 24" class="font-medium text-emerald-600">Standard 24h (1 Business Day)</span>
                  <span v-else>{{ (settingsForm.inactivity_hours / 24).toFixed(1) }} business day(s)</span>
                </div>
              </div>
            </div>

            <!-- Exclude Weekends Toggle -->
            <div v-if="settingsForm.notify_on_inactivity" class="flex items-center justify-between border-t border-gray-200/60 dark:border-gray-800 pt-3">
              <div>
                <label class="text-sm font-semibold text-gray-800 dark:text-white/90">Exclude Weekends</label>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  Skip Saturdays and Sundays when calculating elapsed hours so agents aren't flagged over weekends.
                </p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  v-model="settingsForm.exclude_weekends"
                  class="sr-only peer"
                />
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500"></div>
              </label>
            </div>
          </div>

          <!-- Section 3: Audio & Monitored Agents -->
          <div class="rounded-xl bg-gray-50 dark:bg-white/[0.02] p-4 border border-gray-100 dark:border-gray-800 space-y-4">
            <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Sound Chime & Target Agents
            </h4>

            <!-- Sound Chime Toggle -->
            <div class="flex items-center justify-between">
              <div>
                <div class="flex items-center gap-2">
                  <label class="text-sm font-semibold text-gray-800 dark:text-white/90">Play Audio Chime</label>
                  <button
                    type="button"
                    @click="testAudioChime"
                    class="inline-flex items-center gap-1 rounded bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-400"
                  >
                    🔊 Hear Sound
                  </button>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Synthesizes a pleasant melodic chime when agent notification triggers.</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  v-model="settingsForm.sound_chime"
                  class="sr-only peer"
                />
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500"></div>
              </label>
            </div>

            <!-- Monitored Scope -->
            <div class="border-t border-gray-200/60 dark:border-gray-800 pt-3">
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Monitored Agents
              </label>
              <div class="space-y-2">
                <label class="flex items-center gap-2 text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                  <input
                    type="radio"
                    value="all"
                    v-model="scopeSelection"
                    class="text-brand-500 focus:ring-brand-500"
                  />
                  <span>All Agents (Global Monitoring)</span>
                </label>
                <label class="flex items-center gap-2 text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                  <input
                    type="radio"
                    value="specific"
                    v-model="scopeSelection"
                    class="text-brand-500 focus:ring-brand-500"
                  />
                  <span>Select Specific Agents Only</span>
                </label>
              </div>

              <!-- Specific Agent Checkboxes -->
              <div
                v-if="scopeSelection === 'specific'"
                class="mt-3 p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 max-h-40 overflow-y-auto custom-scrollbar space-y-1.5"
              >
                <label
                  v-for="ag in agentList"
                  :key="ag.id"
                  class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300 p-1 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded cursor-pointer"
                >
                  <input
                    type="checkbox"
                    :value="ag.id"
                    v-model="selectedAgentIds"
                    class="rounded text-brand-500 focus:ring-brand-500"
                  />
                  <span class="font-medium">{{ ag.name }}</span>
                  <span class="text-[10px] text-gray-400">({{ ag.email }})</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Modal Action Buttons -->
          <div class="flex items-center justify-between border-t border-gray-100 dark:border-gray-800 pt-4">
            <button
              type="button"
              @click="triggerTestAlert"
              :disabled="testingAlert"
              class="text-xs font-semibold text-gray-600 dark:text-gray-400 hover:text-brand-500 underline"
            >
              {{ testingAlert ? 'Sending test alert...' : 'Send Test Notification to Bell' }}
            </button>

            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="closeSettingsModal"
                class="rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="savingSettings"
                class="inline-flex items-center gap-1.5 rounded-xl bg-brand-500 hover:bg-brand-600 px-4 py-2 text-xs font-bold text-white shadow-sm disabled:opacity-50"
              >
                <svg v-if="savingSettings" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>{{ savingSettings ? 'Saving...' : 'Save Preferences' }}</span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, reactive, computed } from 'vue'
import { playChimeSound } from '@/utils/soundAlert'

const props = defineProps({
  agents: {
    type: Array,
    default: () => [],
  },
  summary: {
    type: Array,
    default: () => [],
  },
  statusOverview: {
    type: Array,
    default: () => [],
  },
  totalRfqs: {
    type: Number,
    default: 0,
  },
  notificationSettings: {
    type: Object,
    default: () => ({}),
  },
  recentLogs: {
    type: Array,
    default: () => [],
  },
  agentList: {
    type: Array,
    default: () => [],
  },
})

// Tab State
const activeTab = ref('agents')
const expanded = ref(null)

const toggleExpanded = (id) => {
  expanded.value = expanded.value === id ? null : id
}

// Log Filters
const logFilter = ref('all')
const logSearch = ref('')

const filteredLogs = computed(() => {
  let list = props.recentLogs || []

  if (logFilter.value !== 'all') {
    list = list.filter((l) => l.event === logFilter.value)
  }

  if (logSearch.value.trim()) {
    const q = logSearch.value.toLowerCase()
    list = list.filter((l) =>
      (l.agent_name && l.agent_name.toLowerCase().includes(q)) ||
      (l.agent_email && l.agent_email.toLowerCase().includes(q)) ||
      (l.ip_address && l.ip_address.toLowerCase().includes(q))
    )
  }

  return list
})

// Settings Modal & Form
const showSettingsModal = ref(false)
const savingSettings = ref(false)
const testingAlert = ref(false)

const settingsForm = reactive({
  notify_on_login: props.notificationSettings?.notify_on_login ?? true,
  notify_on_logout: props.notificationSettings?.notify_on_logout ?? true,
  notify_on_inactivity: props.notificationSettings?.notify_on_inactivity ?? true,
  inactivity_hours: props.notificationSettings?.inactivity_hours ?? 24,
  exclude_weekends: props.notificationSettings?.exclude_weekends ?? true,
  sound_chime: props.notificationSettings?.sound_chime ?? true,
})

const scopeSelection = ref(
  !props.notificationSettings?.monitored_agents ||
  props.notificationSettings?.monitored_agents.includes('all')
    ? 'all'
    : 'specific'
)

const selectedAgentIds = ref(
  props.notificationSettings?.monitored_agents &&
  !props.notificationSettings?.monitored_agents.includes('all')
    ? [...props.notificationSettings.monitored_agents]
    : []
)

const openSettingsModal = () => {
  showSettingsModal.value = true
}

const closeSettingsModal = () => {
  showSettingsModal.value = false
}

const testAudioChime = () => {
  playChimeSound()
}

const saveNotificationSettings = () => {
  savingSettings.value = true

  const monitored = scopeSelection.value === 'all' ? ['all'] : selectedAgentIds.value

  router.post(
    '/admin/agent-activity/notification-settings',
    {
      ...settingsForm,
      monitored_agents: monitored,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        savingSettings.value = false
        closeSettingsModal()
      },
      onError: () => {
        savingSettings.value = false
      },
    }
  )
}

const triggerTestAlert = () => {
  testingAlert.value = true
  playChimeSound()

  router.post(
    '/admin/agent-activity/test-notification',
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        testingAlert.value = false
      },
      onError: () => {
        testingAlert.value = false
      },
    }
  )
}
</script>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.98);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.animate-fadeIn {
  animation: fadeIn 0.15s ease-out forwards;
}
</style>
