<template>
  <!-- Global Floating Live Reminder Banner -->
  <teleport to="body">
    <div
      v-if="activeReminder"
      class="fixed top-5 right-5 z-[9999999] w-full max-w-md animate-bounce-once"
    >
      <div class="rounded-2xl border-2 border-purple-400 bg-white p-5 shadow-2xl dark:border-purple-600 dark:bg-gray-900 ring-4 ring-purple-500/20">
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-start gap-3">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-purple-600 text-white shadow-md animate-pulse">
              <i v-if="activeReminder.type === 'talk'" class="fa-solid fa-phone-volume text-lg"></i>
              <i v-else-if="activeReminder.type === 'supplier_contact'" class="fa-solid fa-handshake text-lg"></i>
              <i v-else-if="activeReminder.type === 'rfq_followup'" class="fa-solid fa-boxes-packing text-lg"></i>
              <i v-else class="fa-solid fa-bell text-lg"></i>
            </div>
            <div>
              <div class="flex items-center gap-2">
                <span class="rounded bg-purple-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-purple-700 dark:bg-purple-950 dark:text-purple-300">
                  {{ activeReminder.type === 'talk' ? 'Scheduled Talk Due' : 'Live Task Reminder' }}
                </span>
                <span class="text-xs text-gray-400">Right Now</span>
              </div>
              <h4 class="mt-1 text-sm font-bold text-gray-900 dark:text-white leading-tight">
                {{ activeReminder.title }}
              </h4>
              <p v-if="activeReminder.contact_name" class="mt-1 text-xs font-semibold text-purple-700 dark:text-purple-300">
                <i class="fa-solid fa-user mr-1 text-purple-400"></i>
                Contact: {{ activeReminder.contact_name }}
                <span v-if="activeReminder.contact_phone" class="text-gray-500 dark:text-gray-400">
                  ({{ activeReminder.contact_phone }})
                </span>
              </p>
            </div>
          </div>

          <button
            type="button"
            @click="dismissReminder"
            class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 transition"
            title="Dismiss"
          >
            <i class="fa-solid fa-xmark text-sm"></i>
          </button>
        </div>

        <!-- Action Buttons -->
        <div class="mt-4 flex flex-wrap items-center gap-2 pt-3 border-t border-gray-100 dark:border-gray-800">
          <!-- Call Button if phone exists -->
          <a
            v-if="activeReminder.contact_phone"
            :href="`tel:${activeReminder.contact_phone}`"
            class="inline-flex items-center gap-1.5 rounded-xl bg-purple-600 px-3 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-purple-700 transition"
          >
            <i class="fa-solid fa-phone text-[10px]"></i>
            <span>Call Now</span>
          </a>

          <!-- WhatsApp Button if phone exists -->
          <a
            v-if="activeReminder.contact_phone"
            :href="`https://wa.me/${cleanPhone(activeReminder.contact_phone)}`"
            target="_blank"
            class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-emerald-700 transition"
          >
            <i class="fa-brands fa-whatsapp text-[11px]"></i>
            <span>WhatsApp</span>
          </a>

          <!-- Open Board -->
          <Link
            href="/tasks"
            @click="dismissReminder"
            class="inline-flex items-center gap-1 rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-bold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition"
          >
            <span>Open Board</span>
            <i class="fa-solid fa-arrow-right text-[10px]"></i>
          </Link>

          <!-- Snooze 10 mins -->
          <button
            type="button"
            @click="snoozeReminder"
            class="rounded-xl border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition"
          >
            Snooze 10m
          </button>

          <!-- Mark as Done -->
          <button
            type="button"
            @click="completeReminder"
            class="rounded-xl bg-emerald-50 px-2.5 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 transition"
          >
            Mark Done
          </button>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { playChimeSound, sendBrowserNotification } from '@/utils/soundAlert'

const activeReminder = ref(null)
const page = usePage()
let pollerInterval = null

const cleanPhone = (phone) => {
  if (!phone) return ''
  return phone.replace(/[^0-9]/g, '')
}

// Global poller for live task & talk reminders (works on ANY page)
const checkDueReminders = async () => {
  try {
    const user = page.props.auth?.user
    if (!user) return // Don't poll if unauthenticated

    const res = await fetch('/tasks/due-reminders')
    if (!res.ok) return
    const data = await res.json()

    if (data.reminders && data.reminders.length > 0) {
      const first = data.reminders[0]
      // Only chime if new reminder or not already showing this one
      if (!activeReminder.value || activeReminder.value.id !== first.id) {
        activeReminder.value = first

        // Play melodic chime and dispatch desktop notification
        sendBrowserNotification(`Reminder: ${first.title}`, {
          body: first.contact_name
            ? `Talk with ${first.contact_name} is scheduled now`
            : 'Scheduled task is due now',
          url: '/tasks',
        })
      }
    }
  } catch (e) {
    // Silently continue
  }
}

const getCsrfToken = () => {
  return page.props.csrf_token || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
}

const dismissReminder = async () => {
  if (!activeReminder.value) return
  const id = activeReminder.value.id
  activeReminder.value = null
  try {
    await fetch(`/tasks/${id}/acknowledge-reminder`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
    })
  } catch (e) {
    // ignore
  }
}

const snoozeReminder = async () => {
  if (!activeReminder.value) return
  const id = activeReminder.value.id
  activeReminder.value = null
  try {
    await fetch(`/tasks/${id}/acknowledge-reminder`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
      body: JSON.stringify({ snooze: true }),
    })
  } catch (e) {
    // ignore
  }
}

const completeReminder = async () => {
  if (!activeReminder.value) return
  const id = activeReminder.value.id
  activeReminder.value = null
  router.patch(`/tasks/${id}/status`, { status: 'done' }, {
    preserveScroll: true,
  })
}

onMounted(() => {
  // Check immediately, then poll every 30 seconds
  checkDueReminders()
  pollerInterval = setInterval(checkDueReminders, 30000)
})

onUnmounted(() => {
  if (pollerInterval) clearInterval(pollerInterval)
})
</script>

<style scoped>
@keyframes bounceOnce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}
.animate-bounce-once {
  animation: bounceOnce 0.6s ease-in-out;
}
</style>
