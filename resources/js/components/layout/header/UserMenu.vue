<template>
  <div class="relative" ref="dropdownRef">
    <button
      class="flex items-center text-gray-700 dark:text-gray-400"
      @click.prevent="toggleDropdown"
    >
      <span class="mr-3 overflow-hidden rounded-full h-11 w-11 bg-brand-50 dark:bg-brand-500/10 flex items-center justify-center shrink-0">
        <img v-if="user?.avatar" :src="'/storage/' + user.avatar" alt="Avatar" class="w-full h-full object-cover" />
        <span v-else class="text-xs font-bold text-brand-500 dark:text-brand-400">{{ userInitials }}</span>
      </span>

      <span class="block mr-1 font-medium text-theme-sm">{{ user.name }}</span>

      <ChevronDownIcon :class="{ 'rotate-180': dropdownOpen }" />
    </button>

    <!-- Dropdown Start -->
    <div
      v-if="dropdownOpen"
      class="absolute right-0 mt-[17px] flex w-[260px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark"
    >
      <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-800 mb-2">
        <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">
          {{ user.name }}
        </span>
        <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400 truncate">
          {{ user.email || user.whatsapp_number }}
        </span>
      </div>

      <ul class="flex flex-col gap-1 pb-3">
        <li v-for="item in menuItems" :key="item.href">
          <Link
            :href="item.href"
            class="flex items-center gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
          >
            <component
              :is="item.icon"
              class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300"
            />
            {{ item.text }}
          </Link>
        </li>
      </ul>
      <button
        @click="isLogoutModalOpen = true"
        class="flex items-center w-full gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-error-50 hover:text-error-600 dark:text-gray-400 dark:hover:bg-error-500/10 dark:hover:text-error-500 transition-colors text-left"
      >
        <LogoutIcon
          class="text-gray-500 group-hover:text-error-600 dark:group-hover:text-error-500"
        />
        Sign out
      </button>
    </div>
    <!-- Dropdown End -->

    <!-- Logout Confirmation Modal -->
    <div v-if="isLogoutModalOpen" class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity">
      <div class="relative w-full max-w-md rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8 shadow-xl text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-error-50 dark:bg-error-500/10 mb-6">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-error-500">
            <path d="M12 8V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 16.0195V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>

        <h3 class="mb-2 text-xl font-bold text-gray-800 dark:text-white/90">Confirm Sign Out</h3>
        <p class="mb-8 text-sm text-gray-500 dark:text-gray-400">
          Are you sure you want to sign out? You will need to log in again to access your dashboard.
        </p>

        <div class="flex items-center justify-center gap-3">
          <button @click="isLogoutModalOpen = false" type="button" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 sm:w-auto transition-colors">
            Cancel
          </button>
          <form action="/logout" method="POST" class="w-full sm:w-auto">
            <input type="hidden" name="_token" :value="csrfToken">
            <button type="submit" class="w-full rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-error-600 transition-colors">
              Yes, Sign Out
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ChevronDownIcon, LogoutIcon, SettingsIcon, UserCircleIcon } from '@/icons'
import { onMounted, onUnmounted, ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth.user)

const userInitials = computed(() => {
  if (!user.value?.name) return 'U'
  return user.value.name.split(' ').map(n => n[0]).join('').toUpperCase()
})

const dropdownOpen = ref(false)
const isLogoutModalOpen = ref(false)
const dropdownRef = ref<HTMLElement | null>(null)
const csrfToken = (document.head.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;

const menuItems = [
  { href: '/profile', icon: UserCircleIcon, text: 'Edit profile' },
  { href: '/profile', icon: SettingsIcon, text: 'Account settings' },
]

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value
}

const closeDropdown = () => {
  dropdownOpen.value = false
}

const handleClickOutside = (event: Event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    closeDropdown()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
