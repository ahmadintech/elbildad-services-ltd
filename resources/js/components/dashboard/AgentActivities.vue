<template>
  <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="mb-5 flex items-center justify-between">
      <div>
        <h3 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
          Agent Activities
        </h3>
        <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-400">
          Recent activity, login tracking, and response metrics
        </p>
      </div>

      <Link
        href="/admin/agent-activity"
        class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-bold text-gray-700 hover:bg-gray-50 hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 shadow-xs transition"
      >
        <span>Full Activity Logs</span>
        <i class="fa-solid fa-arrow-right text-[10px]"></i>
      </Link>
    </div>

    <div v-if="activities.length === 0" class="py-10 text-center text-sm text-gray-400">
      <i class="fa-solid fa-user-clock text-3xl mb-2 text-gray-300 dark:text-gray-600 block"></i>
      No recent agent activity recorded.
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="(agent, index) in activities"
        :key="index"
        class="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-xs transition hover:border-gray-300 hover:shadow-sm dark:border-gray-800 dark:bg-gray-900/60 dark:hover:border-gray-700"
      >
        <div class="flex items-center gap-3.5">
          <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 font-bold text-base dark:bg-brand-500/15 dark:text-brand-400 shadow-xs">
            {{ agent.name.charAt(0) }}
          </div>
          <div>
            <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ agent.name }}</h4>
            <div class="mt-1 flex items-center gap-3 text-xs text-gray-600 dark:text-gray-400">
              <span class="inline-flex items-center gap-1.5">
                <i class="fa-regular fa-clock text-gray-400 text-xs"></i>
                <span>Last Login: <strong class="font-semibold text-gray-700 dark:text-gray-300">{{ agent.last_login_at }}</strong></span>
              </span>
            </div>
          </div>
        </div>

        <div class="flex flex-col items-end gap-1.5 text-right">
          <span class="inline-flex items-center gap-1 text-xs font-semibold text-gray-700 dark:text-gray-300">
            <span>Avg Response:</span>
            <span class="rounded bg-brand-50 px-2 py-0.5 font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
              {{ agent.avg_response_time }}
            </span>
          </span>
          <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 dark:text-emerald-400">
            <i class="fa-solid fa-circle-check text-[11px]"></i>
            Completed RFQs: <strong class="font-bold">{{ agent.completed_rfqs }}</strong>
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  activities: {
    type: Array,
    required: true
  }
})
</script>
