<template>
  <Head title="Sign Up" />
  <div class="relative p-6 bg-white z-1 dark:bg-gray-900 sm:p-0">
    <div class="relative flex flex-col justify-center w-full h-screen lg:flex-row dark:bg-gray-900">
      <div class="flex flex-col flex-1 w-full lg:w-1/2">
        <div class="w-full max-w-md pt-10 mx-auto px-4">
          <a href="/" class="inline-flex items-center text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
            <svg class="stroke-current" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
              <path d="M12.7083 5L7.5 10.2083L12.7083 15.4167" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            Back to home
          </a>
        </div>
        <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto px-4">
          <div>
            <div class="mb-5 sm:mb-8 text-center lg:text-left">
              <h1 class="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">Sign Up</h1>
              <p class="text-sm text-gray-500 dark:text-gray-400">Create an account to start requesting quotes</p>
            </div>
            <form @submit.prevent="submit">
              <div class="space-y-4">
                <div>
                  <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Full Name<span class="text-error-500">*</span></label>
                  <input v-model="form.name" type="text" id="name" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:text-white" />
                  <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                </div>
                <div>
                  <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email Address<span class="text-error-500">*</span></label>
                  <input v-model="form.email" type="email" id="email" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:text-white" />
                  <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                </div>
                <div>
                  <label for="whatsapp_number" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">WhatsApp Number</label>
                  <input v-model="form.whatsapp_number" type="text" id="whatsapp_number" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:text-white" />
                </div>
                <div>
                  <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Password<span class="text-error-500">*</span></label>
                  <input v-model="form.password" type="password" id="password" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:text-white" />
                  <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
                </div>
                <div>
                  <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Confirm Password<span class="text-error-500">*</span></label>
                  <input v-model="form.password_confirmation" type="password" id="password_confirmation" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:text-white" />
                </div>
                <button type="submit" :disabled="form.processing" class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 hover:bg-brand-600 disabled:opacity-50">
                  {{ form.processing ? 'Creating account...' : 'Sign Up' }}
                </button>
              </div>
            </form>
            <div class="mt-6 text-center">
              <p class="text-sm text-gray-500">Already have an account? <Link href="/signin" class="text-brand-500 font-medium hover:text-brand-600">Sign In</Link></p>
            </div>
          </div>
        </div>
      </div>
      <div class="relative items-center hidden w-full h-full lg:w-1/2 bg-gray-900 dark:bg-white/5 lg:flex justify-center p-12">
        <div class="text-center max-w-sm">
           <img src="/images/logo/elbildad-logo.png" alt="Logo" class="mx-auto mb-6 h-20 w-auto" />
           <h2 class="text-2xl font-bold text-white mb-4">Join Elbildad Services</h2>
           <p class="text-gray-400">Your trusted and reliable sourcing agency</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm, Head } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  email: '',
  whatsapp_number: '',
  password: '',
  password_confirmation: ''
})

const submit = () => {
  form.post('/register', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>
