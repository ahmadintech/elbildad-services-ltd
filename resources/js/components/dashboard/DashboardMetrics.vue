<template>
  <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4 md:gap-6">
    <div
      v-for="stat in stats"
      :key="stat.title"
      class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
    >
      <!-- Icon -->
      <div
        :class="[
          'flex items-center justify-center w-14 h-14 rounded-2xl mb-4',
          getColorClass(stat.color)
        ]"
      >
        <!-- RFQ Icon -->
        <svg v-if="stat.icon === 'rfq'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-7 h-7">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>

        <!-- Revenue Icon -->
        <svg v-if="stat.icon === 'revenue'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-7 h-7">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>

        <!-- Pending Icon -->
        <svg v-if="stat.icon === 'pending'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-7 h-7">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>

        <!-- Agents Icon -->
        <svg v-if="stat.icon === 'agents'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-7 h-7">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12l-6 0m0 0a6 6 0 1112 0m0 0a6 6 0 01-12 0" />
        </svg>

        <!-- Users Icon -->
        <svg v-if="stat.icon === 'users'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-7 h-7">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m4.646-7.354h.008v.008h-.008v-.008zM9 12a6 6 0 1112 0 6 6 0 01-12 0z" />
        </svg>
      </div>

      <!-- Title -->
      <p class="text-sm text-gray-500 dark:text-gray-400">{{ stat.title }}</p>

      <!-- Value -->
      <h3 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white/90">
        {{ stat.value }}
      </h3>

      <!-- Change Badge -->
      <div class="mt-3 flex items-center gap-2">
        <span
          :class="[
            'inline-flex items-center gap-0.5 rounded-full px-2.5 py-1 text-xs font-medium',
            parseFloat(stat.change) >= 0
              ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500'
              : 'bg-danger-50 text-danger-600 dark:bg-danger-500/15 dark:text-danger-500'
          ]"
        >
          <svg
            v-if="parseFloat(stat.change) >= 0"
            class="fill-current"
            width="12"
            height="12"
            viewBox="0 0 12 12"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M5.56462 1.62393C5.70193 1.47072 5.90135 1.37432 6.12329 1.37432C6.1236 1.37432 6.12391 1.37432 6.12422 1.37432C6.31631 1.37415 6.50845 1.44731 6.65505 1.59381L9.65514 4.5918C9.94814 4.88459 9.94831 5.35947 9.65552 5.65246C9.36273 5.94546 8.88785 5.94562 8.59486 5.65283L6.87329 3.93247L6.87329 10.125C6.87329 10.5392 6.53751 10.875 6.12329 10.875C5.70908 10.875 5.37329 10.5392 5.37329 10.125L5.37329 3.93578L3.65516 5.65282C3.36218 5.94562 2.8873 5.94547 2.5945 5.65248C2.3017 5.35949 2.30185 4.88462 2.59484 4.59182L5.56462 1.62393Z"
              fill=""
            />
          </svg>
          <svg
            v-else
            class="fill-current"
            width="12"
            height="12"
            viewBox="0 0 12 12"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M6.43538 10.3761C6.29807 10.5293 6.09865 10.6257 5.87671 10.6257C5.8764 10.6257 5.87609 10.6257 5.87578 10.6257C5.68369 10.6259 5.49155 10.5527 5.34495 10.4062L2.34486 7.4082C2.05186 7.1154 2.05169 6.6405 2.34448 6.3475C2.63727 6.0545 3.11215 6.0544 3.40514 6.3472L5.12671 8.0675L5.12671 1.875C5.12671 1.4608 5.46249 1.125 5.87671 1.125C6.29092 1.125 6.62671 1.4608 6.62671 1.875L6.62671 8.0642L8.34484 6.3472C8.63782 6.0544 9.1127 6.0545 9.4055 6.3475C9.6983 6.6405 9.69815 7.1154 9.40516 7.4082L6.43538 10.3761Z"
              fill=""
            />
          </svg>
          {{ stat.change }}
        </span>
        <span class="text-xs text-gray-500 dark:text-gray-400">vs last month</span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  stats: {
    type: Array,
    required: true
  }
})

const getColorClass = (color) => {
  const colorMap = {
    blue: 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400',
    green: 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400',
    orange: 'bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400',
    purple: 'bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400',
    red: 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400',
  }
  return colorMap[color] || colorMap.blue
}

const getIcon = (iconName) => {
  const icons = {
    rfq: {
      template: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>'
    },
    revenue: {
      template: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
    },
    pending: {
      template: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
    },
    agents: {
      template: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12l-6 0m0 0a6 6 0 1112 0m0 0a6 6 0 01-12 0" /></svg>'
    },
    users: {
      template: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m4.646-7.354h.008v.008h-.008v-.008zM9 12a6 6 0 1112 0 6 6 0 01-12 0z" /></svg>'
    }
  }
  return icons[iconName] || icons.rfq
}
</script>
