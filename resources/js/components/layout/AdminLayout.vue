<template>
  <div class="min-h-screen xl:flex">
    <app-sidebar />
    <Backdrop />
    <div
      class="flex-1 transition-all duration-300 ease-in-out"
      :class="[isExpanded || isHovered ? 'lg:ml-[290px]' : 'lg:ml-[90px]']"
    >
      <app-header />
      <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <slot></slot>
      </div>

      <!-- Global Task & Talk Reminders with Desktop Alerts & Melodic Chime -->
      <GlobalTaskReminder />

      <!-- Footer -->
      <footer class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6 pt-0">
        <p class="text-sm text-center text-gray-500 dark:text-gray-400">
          Designed and Developed by 
          <a 
            href="#" 
            class="text-brand-500 hover:text-brand-600 transition-colors duration-200 font-medium"
          >
            Elbildad Services Ltd
          </a>
        </p>
      </footer>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useSidebar } from '@/composables/useSidebar'
import AppHeader from './AppHeader.vue'
import AppSidebar from './AppSidebar.vue'
import Backdrop from './Backdrop.vue'
import GlobalTaskReminder from '@/components/tasks/GlobalTaskReminder.vue'
import { watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

const { isExpanded, isHovered } = useSidebar()
const page = usePage()

watch(
  () => page.props.flash,
  (flash: any) => {
    if (flash?.success) {
      toast.success(flash.success, {
        position: 'top-right',
        autoClose: 3000,
      })
    }
    if (flash?.error) {
      toast.error(flash.error, {
        position: 'top-right',
        autoClose: 5000,
      })
    }
  },
  { deep: true, immediate: true }
)
</script>
