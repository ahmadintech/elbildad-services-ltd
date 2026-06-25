<template>
  <div class="border border-gray-300 rounded-md shadow-sm dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden text-gray-900 dark:text-white">
    <QuillEditor
      v-model:content="content"
      contentType="html"
      :toolbar="toolbarOptions"
      theme="snow"
      class="min-h-[250px] custom-quill-editor"
      @update:content="updateContent"
    />
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue'])
const content = ref('')

const toolbarOptions = [
  ['bold', 'italic', 'underline', 'strike'],
  ['blockquote', 'code-block'],
  [{ 'header': 1 }, { 'header': 2 }],
  [{ 'list': 'ordered'}, { 'list': 'bullet' }],
  [{ 'script': 'sub'}, { 'script': 'super' }],
  [{ 'indent': '-1'}, { 'indent': '+1' }],
  [{ 'direction': 'rtl' }],
  [{ 'size': ['small', false, 'large', 'huge'] }],
  [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
  [{ 'color': [] }, { 'background': [] }],
  [{ 'font': [] }],
  [{ 'align': [] }],
  ['clean'],
  ['link']
]

onMounted(() => {
  content.value = props.modelValue || ''
})

watch(() => props.modelValue, (newVal) => {
  if (content.value !== newVal) {
    content.value = newVal || ''
  }
})

const updateContent = (val) => {
  emit('update:modelValue', val)
}
</script>

<style>
/* Adjust toolbar for dark mode */
.dark .ql-toolbar.ql-snow {
  background-color: #1f2937;
  border-color: #374151;
}
.dark .ql-container.ql-snow {
  border-color: #374151;
  background-color: #111827;
}
.dark .ql-snow .ql-stroke {
  stroke: #d1d5db;
}
.dark .ql-snow .ql-fill, .dark .ql-snow .ql-stroke.ql-fill {
  fill: #d1d5db;
}
.dark .ql-snow .ql-picker {
  color: #d1d5db;
}
</style>
