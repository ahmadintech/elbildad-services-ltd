<template>
  <div class="border border-gray-300 rounded-md shadow-sm dark:border-gray-700 bg-white dark:bg-gray-900">
    <div class="flex items-center gap-2 border-b border-gray-300 dark:border-gray-700 p-2 bg-gray-50 dark:bg-gray-800 rounded-t-md">
      <button type="button" @click="format('bold')" class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-700 rounded font-bold">B</button>
      <button type="button" @click="format('italic')" class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-700 rounded italic">I</button>
      <button type="button" @click="format('underline')" class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-700 rounded underline">U</button>
      <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></div>
      <button type="button" @click="format('insertUnorderedList')" class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-700 rounded">• List</button>
      <button type="button" @click="format('insertOrderedList')" class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-700 rounded">1. List</button>
    </div>
    <div 
      ref="editor" 
      class="p-4 min-h-[200px] max-h-[500px] overflow-y-auto focus:outline-none prose dark:prose-invert max-w-none"
      contenteditable="true"
      @input="updateContent"
      v-html="modelValue"
    ></div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue'])
const editor = ref(null)

const format = (command) => {
  document.execCommand(command, false, null)
  editor.value.focus()
  updateContent()
}

const updateContent = () => {
  emit('update:modelValue', editor.value.innerHTML)
}

onMounted(() => {
  if (editor.value && !editor.value.innerHTML && props.modelValue) {
    editor.value.innerHTML = props.modelValue
  }
})

watch(() => props.modelValue, (newVal) => {
  if (editor.value && editor.value.innerHTML !== newVal) {
    editor.value.innerHTML = newVal || ''
  }
})
</script>
