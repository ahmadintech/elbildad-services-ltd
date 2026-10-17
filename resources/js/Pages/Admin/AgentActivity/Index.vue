<template>
  <AdminLayout>
    <Head title="Agent Activity" />

    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Agent Activity</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">Performance, login activity, and RFQ processing overview for all agents.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-400 self-start sm:self-auto">
          <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
          {{ agents.length }} Agent{{ agents.length !== 1 ? 's' : '' }} on Portal
        </span>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 md:gap-6">
        <div v-for="stat in summary" :key="stat.title"
          class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] shadow-sm">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ stat.title }}</p>
              <p class="text-2xl font-bold" :class="{
                'text-blue-600 dark:text-blue-400':    stat.color === 'blue',
                'text-purple-600 dark:text-purple-400': stat.color === 'purple',
                'text-indigo-600 dark:text-indigo-400': stat.color === 'indigo',
                'text-green-600 dark:text-green-400':  stat.color === 'green',
              }">{{ stat.value }}</p>
            </div>
            <div class="flex h-11 w-11 items-center justify-center rounded-xl flex-shrink-0" :class="{
              'bg-blue-50 dark:bg-blue-500/10':    stat.color === 'blue',
              'bg-purple-50 dark:bg-purple-500/10': stat.color === 'purple',
              'bg-indigo-50 dark:bg-indigo-500/10': stat.color === 'indigo',
              'bg-green-50 dark:bg-green-500/10':  stat.color === 'green',
            }">
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
          <div v-for="s in statusOverview" :key="s.label"
            class="flex items-center gap-2 rounded-xl border px-4 py-2.5" :class="{
              'border-green-200 bg-green-50 dark:border-green-500/20 dark:bg-green-500/5':       s.color === 'green',
              'border-purple-200 bg-purple-50 dark:border-purple-500/20 dark:bg-purple-500/5':   s.color === 'purple',
              'border-orange-200 bg-orange-50 dark:border-orange-500/20 dark:bg-orange-500/5':   s.color === 'orange',
              'border-red-200 bg-red-50 dark:border-red-500/20 dark:bg-red-500/5':               s.color === 'red',
              'border-sky-200 bg-sky-50 dark:border-sky-500/20 dark:bg-sky-500/5':               s.color === 'sky',
              'border-amber-200 bg-amber-50 dark:border-amber-500/20 dark:bg-amber-500/5':       s.color === 'amber',
              'border-emerald-200 bg-emerald-50 dark:border-emerald-500/20 dark:bg-emerald-500/5': s.color === 'emerald',
            }">
            <span class="text-lg font-bold" :class="{
              'text-green-600 dark:text-green-400':     s.color === 'green',
              'text-purple-600 dark:text-purple-400':   s.color === 'purple',
              'text-orange-600 dark:text-orange-400':   s.color === 'orange',
              'text-red-600 dark:text-red-400':         s.color === 'red',
              'text-sky-600 dark:text-sky-400':         s.color === 'sky',
              'text-amber-600 dark:text-amber-400':     s.color === 'amber',
              'text-emerald-600 dark:text-emerald-400': s.color === 'emerald',
            }">{{ s.count }}</span>
            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ s.label }}</span>
          </div>
        </div>
      </div>

      <!-- Agent Cards — each fully expanded with login + RFQ table -->
      <div class="space-y-5">
        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Agent Details & Activity</h3>

        <div v-if="agents.length === 0" class="rounded-2xl border border-dashed border-gray-200 dark:border-gray-800 py-12 text-center text-gray-400">
          No agents found on the portal.
        </div>

        <div v-for="(agent, index) in agents" :key="agent.id"
          class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-sm overflow-hidden">

          <!-- Agent Header -->
          <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
              <!-- Rank badge -->
              <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-xs font-bold" :class="{
                'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400': index === 0,
                'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-300':            index === 1,
                'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400': index === 2,
                'bg-gray-50 text-gray-400 dark:bg-gray-800':                                index > 2,
              }">#{{ index + 1 }}</span>
              <!-- Avatar -->
              <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-500/20 text-brand-600 dark:text-brand-400 font-bold text-base">
                {{ agent.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <p class="font-semibold text-gray-800 dark:text-white/90">{{ agent.name }}</p>
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide" :class="{
                    'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400': agent.role === 'super_agent',
                    'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400':         agent.role !== 'super_agent',
                  }">{{ agent.role === 'super_agent' ? '★ Super Agent' : 'Agent' }}</span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ agent.email }} · Joined {{ agent.joined_at }}</p>
              </div>
            </div>
            <!-- Completion rate pill -->
            <div class="flex items-center gap-3 flex-shrink-0">
              <div class="text-right">
                <p class="text-[10px] text-gray-400 uppercase tracking-wider">Completion</p>
                <p class="text-lg font-bold" :class="{
                  'text-green-600 dark:text-green-400':   agent.completion_rate >= 75,
                  'text-orange-500':                       agent.completion_rate >= 40 && agent.completion_rate < 75,
                  'text-red-500':                          agent.completion_rate < 40,
                }">{{ agent.completion_rate }}%</p>
              </div>
              <button @click="toggleExpanded(agent.id)"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                {{ expanded === agent.id ? 'Collapse' : 'View RFQs' }}
                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': expanded === agent.id }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
              <p class="text-sm font-semibold" :class="{
                'text-green-600 dark:text-green-400':   agent.days_since_login !== null && agent.days_since_login <= 1,
                'text-orange-500':                       agent.days_since_login !== null && agent.days_since_login > 1 && agent.days_since_login <= 7,
                'text-red-500':                          agent.days_since_login === null || agent.days_since_login > 7,
              }">{{ agent.last_login_human }}</p>
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
                      <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize" :class="{
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
                      }">{{ rfq.status.replace(/_/g, ' ') }}</span>
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
                        <span class="inline-block w-2 h-2 rounded-full flex-shrink-0" :class="{
                          'bg-red-500':    rfq.risk === 'critical',
                          'bg-orange-400': rfq.risk === 'high',
                          'bg-yellow-400': rfq.risk === 'medium',
                          'bg-green-400':  rfq.risk === 'low',
                        }"></span>
                        <div>
                          <p class="text-xs font-semibold" :class="{
                            'text-red-600 dark:text-red-400':    rfq.risk === 'critical',
                            'text-orange-500 dark:text-orange-400': rfq.risk === 'high',
                            'text-yellow-600 dark:text-yellow-400': rfq.risk === 'medium',
                            'text-gray-500 dark:text-gray-400':  rfq.risk === 'low',
                          }">{{ rfq.idle_label }}</p>
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
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps({
  agents: Array,
  summary: Array,
  statusOverview: Array,
  totalRfqs: Number,
})

const expanded = ref(null)

const toggleExpanded = (id) => {
  expanded.value = expanded.value === id ? null : id
}
</script>
