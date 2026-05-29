<template>
  <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Top Categories</h3>
    </div>

    <div class="divide-y divide-gray-100 dark:divide-gray-700">
      <div
        v-for="(category, index) in categories"
        :key="index"
        class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors"
      >
        <div class="mb-2 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div
              :class="[
                'h-3 w-3 rounded-full',
                getCategoryColor(index)
              ]"
            ></div>
            <span class="font-medium text-gray-900 dark:text-white/90">{{ category.name }}</span>
          </div>
          <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-500/15 dark:text-blue-400">
            {{ category.count }} RFQs
          </span>
        </div>

        <!-- Progress Bar -->
        <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
          <div
            :style="{ width: getPercentage(category.count) + '%' }"
            :class="getCategoryColor(index)"
            class="h-full transition-all duration-300"
          ></div>
        </div>
      </div>
    </div>

    <div v-if="categories.length === 0" class="px-6 py-12 text-center">
      <p class="text-gray-500 dark:text-gray-400">No categories available</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  categories: {
    type: Array,
    required: true
  }
})

const maxCount = computed(() => {
  if (props.categories.length === 0) return 1
  return Math.max(...props.categories.map(c => c.count))
})

const getCategoryColor = (index) => {
  const colors = [
    'bg-blue-500',
    'bg-purple-500',
    'bg-pink-500',
    'bg-orange-500',
    'bg-green-500',
    'bg-red-500'
  ]
  return colors[index % colors.length]
}

const getPercentage = (count) => {
  return maxCount.value > 0 ? (count / maxCount.value) * 100 : 0
}
</script>
