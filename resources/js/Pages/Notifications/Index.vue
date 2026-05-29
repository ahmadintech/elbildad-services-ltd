<template>
  <AdminLayout>
    <Head title="Notifications" />
    <div class="space-y-6">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">All Notifications</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">View all your recent alerts and activities.</p>
        </div>
        <div v-if="hasUnread">
          <Link
            method="post"
            :href="route('notifications.mark-all-as-read')"
            as="button"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-50 px-4 py-2 text-sm font-medium text-brand-600 hover:bg-brand-100 transition-colors"
          >
            Mark all as read
          </Link>
        </div>
      </div>

      <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="p-0">
          <ul class="divide-y divide-gray-100 dark:divide-gray-800">
            <li v-for="notification in notifications.data" :key="notification.id" :class="{'bg-gray-50/50 dark:bg-white/[0.02]': !notification.read_at}">
              <div class="flex gap-4 p-5 hover:bg-gray-50 dark:hover:bg-white/[0.05] transition-colors">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 22C13.1 22 14 21.1 14 20H10C10 21.1 10.9 22 12 22ZM18 16V11C18 7.93 16.36 5.36 13.5 4.68V4C13.5 3.17 12.83 2.5 12 2.5C11.17 2.5 10.5 3.17 10.5 4V4.68C7.63 5.36 6 7.92 6 11V16L4 18V19H20V18L18 16Z" fill="currentColor"/>
                  </svg>
                </div>
                <div class="flex-1">
                  <div class="flex items-start justify-between gap-2">
                    <div>
                      <p class="font-medium text-gray-800 dark:text-white/90" :class="{'font-bold': !notification.read_at}">
                        {{ notification.message }}
                      </p>
                      <div class="mt-1 flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                        <span class="font-medium text-brand-500">{{ notification.type }}</span>
                        <span class="h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                        <span>{{ notification.time }}</span>
                      </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                      <Link
                        v-if="!notification.read_at && !$page.props.auth.user.roles.includes('admin') && !$page.props.auth.user.roles.includes('owner')"
                        method="post"
                        :href="route('notifications.mark-as-read', notification.id)"
                        as="button"
                        class="text-xs font-medium text-gray-500 hover:text-brand-500"
                      >
                        Mark as read
                      </Link>
                      <a
                        v-if="notification.action_url && notification.action_url !== '#'"
                        :href="notification.action_url"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                      >
                        View Details
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </li>
            <li v-if="notifications.data.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
              You don't have any notifications yet.
            </li>
          </ul>
        </div>
        
        <!-- Pagination -->
        <div v-if="notifications.links && notifications.links.length > 3" class="flex items-center justify-between border-t border-gray-100 bg-white px-5 py-4 dark:border-gray-800 dark:bg-transparent">
          <div class="flex flex-1 justify-between sm:hidden">
            <Link :href="notifications.prev_page_url" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" :class="{ 'opacity-50 pointer-events-none': !notifications.prev_page_url }">Previous</Link>
            <Link :href="notifications.next_page_url" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" :class="{ 'opacity-50 pointer-events-none': !notifications.next_page_url }">Next</Link>
          </div>
          <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700 dark:text-gray-400">
                Showing
                <span class="font-medium">{{ notifications.from || 0 }}</span>
                to
                <span class="font-medium">{{ notifications.to || 0 }}</span>
                of
                <span class="font-medium">{{ notifications.total }}</span>
                results
              </p>
            </div>
            <div>
              <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                <template v-for="(link, key) in notifications.links" :key="key">
                  <Link
                    :href="link.url || '#'"
                    v-html="link.label"
                    class="relative inline-flex items-center px-4 py-2 text-sm font-semibold border"
                    :class="[
                      link.active 
                        ? 'z-10 bg-brand-50 border-brand-500 text-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600'
                        : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-gray-800',
                      !link.url ? 'opacity-50 cursor-not-allowed text-gray-400' : '',
                      key === 0 ? 'rounded-l-md' : '',
                      key === notifications.links.length - 1 ? 'rounded-r-md' : ''
                    ]"
                  />
                </template>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  notifications: Object
})

const page = usePage()
const hasUnread = computed(() => {
  if (page.props.auth.user.roles.includes('admin') || page.props.auth.user.roles.includes('owner')) {
    return false; // Admin views all system activity, not necessarily tracking "read" state globally in the same way
  }
  return props.notifications.data.some(n => !n.read_at)
})
</script>
