<template>
  <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <div class="flex items-center gap-2.5">
          <h3 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
            Agent Tasks & Scheduled Talks
          </h3>
          <span class="inline-flex items-center gap-1.5 rounded-full bg-purple-100 px-3 py-1 text-xs font-bold text-purple-700 dark:bg-purple-950/60 dark:text-purple-300">
            <span class="h-1.5 w-1.5 rounded-full bg-purple-600 animate-pulse"></span>
            Team Schedule
          </span>
        </div>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Live overview of all agent scheduled talks, follow-ups, and pipeline tasks
        </p>
      </div>

      <div class="flex items-center gap-2">
        <Link
          href="/tasks"
          class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-purple-700 active:scale-95 transition"
        >
          <span>Open Full Board</span>
          <i class="fa-solid fa-arrow-right text-xs"></i>
        </Link>
      </div>
    </div>

    <!-- Quick Stats Pills -->
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mb-5">
      <div class="rounded-xl border border-gray-200 bg-gray-50/80 p-3.5 dark:border-gray-800 dark:bg-gray-800/40 shadow-xs">
        <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
          <span>Total Tasks</span>
          <i class="fa-solid fa-layer-group text-gray-400"></i>
        </div>
        <p class="mt-1.5 text-2xl font-black text-gray-900 dark:text-white">{{ stats?.total || 0 }}</p>
      </div>

      <div class="rounded-xl border border-purple-200 bg-purple-50/70 p-3.5 dark:border-purple-900/40 dark:bg-purple-950/30 shadow-xs">
        <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-purple-700 dark:text-purple-300">
          <span>Scheduled Talks</span>
          <i class="fa-solid fa-phone text-purple-600 dark:text-purple-400"></i>
        </div>
        <p class="mt-1.5 text-2xl font-black text-purple-700 dark:text-purple-300">{{ stats?.talks || 0 }}</p>
      </div>

      <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-3.5 dark:border-amber-900/40 dark:bg-amber-950/30 shadow-xs">
        <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">
          <span>In Progress</span>
          <i class="fa-solid fa-arrows-rotate text-amber-600 dark:text-amber-400"></i>
        </div>
        <p class="mt-1.5 text-2xl font-black text-amber-700 dark:text-amber-300">{{ stats?.in_progress || 0 }}</p>
      </div>

      <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-3.5 dark:border-emerald-900/40 dark:bg-emerald-950/30 shadow-xs">
        <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">
          <span>Done</span>
          <i class="fa-solid fa-check text-emerald-600 dark:text-emerald-400"></i>
        </div>
        <p class="mt-1.5 text-2xl font-black text-emerald-700 dark:text-emerald-300">{{ stats?.done || 0 }}</p>
      </div>
    </div>

    <!-- Tasks List -->
    <div v-if="tasks.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
      <i class="fa-regular fa-calendar-check text-4xl text-gray-300 dark:text-gray-600 mb-2"></i>
      <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No tasks or talks scheduled yet.</p>
      <Link href="/tasks" class="mt-2 inline-flex items-center gap-1.5 text-sm font-bold text-brand-500 hover:text-brand-600 hover:underline">
        <i class="fa-solid fa-plus text-xs"></i>
        <span>Schedule first task</span>
      </Link>
    </div>

    <div v-else class="space-y-3 max-h-[420px] overflow-y-auto custom-scrollbar pr-1">
      <div
        v-for="task in tasks"
        :key="task.id"
        class="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-3.5 shadow-xs transition hover:border-gray-300 hover:shadow-sm dark:border-gray-800 dark:bg-gray-900/60 dark:hover:border-gray-700"
      >
        <div class="flex items-center gap-3.5 min-w-0">
          <!-- Type Icon Badge (FontAwesome, No Emojis) -->
          <div
            :class="[
              'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl shadow-xs',
              task.type === 'talk'
                ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300'
                : task.type === 'rfq'
                ? 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300'
                : task.type === 'supplier'
                ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300'
                : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
            ]"
          >
            <i v-if="task.type === 'talk'" class="fa-solid fa-phone-volume text-sm"></i>
            <i v-else-if="task.type === 'rfq'" class="fa-solid fa-boxes-stacked text-sm"></i>
            <i v-else-if="task.type === 'supplier'" class="fa-solid fa-handshake text-sm"></i>
            <i v-else class="fa-solid fa-list-check text-sm"></i>
          </div>

          <div class="min-w-0">
            <div class="flex items-center gap-2">
              <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">
                {{ task.title }}
              </h4>
              <span
                v-if="task.is_overdue"
                class="inline-flex items-center gap-1 rounded bg-rose-50 px-2 py-0.5 text-xs font-bold text-rose-600 dark:bg-rose-950/40 dark:text-rose-400"
              >
                <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                Overdue
              </span>
            </div>

            <div class="mt-1 flex flex-wrap items-center gap-2.5 text-xs text-gray-600 dark:text-gray-400">
              <span class="flex items-center gap-1 font-semibold text-gray-800 dark:text-gray-200">
                <i class="fa-solid fa-user text-gray-400 text-[10px]"></i>
                {{ task.assigned_agent_name }}
              </span>
              <span v-if="task.contact_name" class="text-gray-500">
                <i class="fa-solid fa-building-user text-gray-400 text-[10px] mr-1"></i>
                {{ task.contact_name }}
              </span>
              <span class="text-gray-500">
                <i class="fa-solid fa-clock text-gray-400 text-[10px] mr-1"></i>
                {{ task.due_label }}
              </span>
            </div>
          </div>
        </div>

        <!-- monday.com Status Pill -->
        <div class="shrink-0 ml-3">
          <span
            :style="{ backgroundColor: getStatusColor(task.status) }"
            class="inline-block rounded-lg px-3 py-1.5 text-xs font-bold text-white shadow-xs uppercase tracking-wide"
          >
            {{ getStatusLabel(task.status) }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  tasks: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({})
  }
})

const getStatusColor = (status) => {
  switch (status) {
    case 'done': return '#00C875'
    case 'in_progress': return '#FDAB3D'
    case 'stuck': return '#E2445C'
    case 'pending': return '#579BFC'
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
</script>
