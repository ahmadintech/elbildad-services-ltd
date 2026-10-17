<template>
  <FrontendLayout>
    <Head title="Visa Application - Elbildad Services" />

    <div class="pt-12 pb-12 px-4 max-w-3xl mx-auto">
      
      <!-- Validation errors -->
      <div v-if="Object.keys(form.errors).length > 0" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl">
        <p class="font-semibold text-red-700 mb-2 text-sm">Please fix the following errors:</p>
        <ul class="space-y-1">
          <li v-for="(error, field) in form.errors" :key="field" class="text-xs text-red-600 flex items-center gap-1.5">
            <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ error }}
          </li>
        </ul>
      </div>

      <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden relative">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-700 to-blue-500 px-8 py-8 text-white">
          <h1 class="text-2xl md:text-3xl font-bold mb-2">Chinese Business Visa</h1>
          <p class="opacity-90 text-sm">Apply for your Chinese business visa with ease. We guide you every step of the way.</p>
        </div>

        <!-- Progress Bar -->
        <div class="px-8 pt-6 pb-2">
          <div class="flex items-center justify-between relative">
            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-gray-100 rounded-full z-0"></div>
            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 h-1 bg-blue-500 rounded-full z-0 transition-all duration-300" :style="{ width: progressPercentage + '%' }"></div>
            
            <div v-for="(step, index) in steps" :key="index" class="relative z-10 flex flex-col items-center gap-2">
              <div :class="['w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm transition-colors duration-300', 
                currentStep > index ? 'bg-blue-600 text-white' : 
                currentStep === index ? 'bg-blue-500 text-white shadow-[0_0_0_4px_rgba(59,130,246,0.2)]' : 
                'bg-gray-200 text-gray-400']">
                <svg v-if="currentStep > index" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span v-else>{{ index + 1 }}</span>
              </div>
            </div>
          </div>
          <div class="flex justify-between mt-3 text-xs font-semibold text-gray-500">
            <span class="text-left w-1/4" :class="{'text-blue-600': currentStep >= 0}">Personal</span>
            <span class="text-center w-1/4" :class="{'text-blue-600': currentStep >= 1}">Passport</span>
            <span class="text-center w-1/4" :class="{'text-blue-600': currentStep >= 2}">Travel</span>
            <span class="text-right w-1/4" :class="{'text-blue-600': currentStep >= 3}">Visa & Docs</span>
          </div>
        </div>

        <form @submit.prevent="submitForm" class="px-8 py-6" enctype="multipart/form-data">
          
          <!-- Step 1: Personal Info -->
          <div v-show="currentStep === 0" class="space-y-5 animate-fadeIn">
            <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Personal Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">Full Name (as in passport) <span class="inline-block text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full ml-1">Required</span></label>
                <input v-model="form.full_name" type="text" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all" placeholder="e.g. Ahmed Ibrahim">
              </div>
              <div>
                <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">Date of Birth</label>
                <input v-model="form.dob" type="date" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all">
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">Nationality</label>
                <input v-model="form.nationality" type="text" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all" placeholder="e.g. Nigerian">
              </div>
              <div>
                <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">Gender</label>
                <select v-model="form.gender" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all">
                  <option value="">Select</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div>
                <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">Marital Status</label>
                <select v-model="form.marital_status" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all">
                  <option value="">Select</option>
                  <option value="Single">Single</option>
                  <option value="Married">Married</option>
                  <option value="Divorced">Divorced</option>
                  <option value="Widowed">Widowed</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">Occupation / Job Title <span class="inline-block text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full ml-1">Required</span></label>
                <input v-model="form.occupation" type="text" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all" placeholder="e.g. Managing Director">
              </div>
              <div>
                <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">Company Name <span class="inline-block text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full ml-1">Required</span></label>
                <input v-model="form.company_name" type="text" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all" placeholder="e.g. Acme Ltd">
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">Phone Number <span class="inline-block text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full ml-1">Required</span></label>
                <input v-model="form.phone_number" type="tel" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all" placeholder="e.g. 08012345678">
              </div>
              <div>
                <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">Email Address <span class="inline-block text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full ml-1">Required</span></label>
                <input v-model="form.email_address" type="email" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all" placeholder="you@example.com">
              </div>
            </div>

            <div>
              <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">Company / Residential Address</label>
              <textarea v-model="form.company_address" rows="2" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all" placeholder="Full address"></textarea>
            </div>
          </div>

          <!-- Step 2: Passport Info -->
          <div v-show="currentStep === 1" class="space-y-5 animate-fadeIn">
            <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Passport Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">Passport Number <span class="inline-block text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full ml-1">Required</span></label>
                <input v-model="form.passport_number" type="text" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all">
              </div>
              <div>
                <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">Country of Issuance</label>
                <input v-model="form.country_of_issuance" type="text" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all">
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">Issue Date</label>
                <input v-model="form.passport_issue_date" type="date" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all">
              </div>
              <div>
                <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">Expiry Date <span class="inline-block text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full ml-1">Required</span></label>
                <input v-model="form.passport_expiry_date" type="date" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all">
              </div>
            </div>

            <!-- File Uploads -->
            <div class="space-y-6 pt-2">
              <div>
                <label class="block text-[0.85rem] font-semibold text-slate-700 mb-2">Upload Passport Data Page (Bio page) <span class="inline-block text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full ml-1">Required</span></label>
                <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center cursor-pointer hover:bg-slate-50 transition-colors relative" @click="$refs.passportDataPageInput.click()">
                  <input ref="passportDataPageInput" type="file" @change="e => form.passport_data_page = e.target.files[0]" accept=".jpg,.jpeg,.png,.pdf" class="hidden">
                  <div v-if="form.passport_data_page" class="text-sm font-semibold text-blue-600">{{ form.passport_data_page.name }}</div>
                  <div v-else>
                    <svg class="mx-auto h-10 w-10 text-slate-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm text-slate-600">Click to <span class="text-blue-600 font-semibold">browse</span> files</p>
                    <p class="text-xs text-slate-400 mt-1">Formats: JPG, PNG, PDF</p>
                  </div>
                </div>
              </div>

              <div>
                <label class="block text-[0.85rem] font-semibold text-slate-700 mb-2">Upload Passport Photograph <span class="inline-block text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full ml-1">Required</span></label>
                <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center cursor-pointer hover:bg-slate-50 transition-colors relative" @click="$refs.passportPhotoInput.click()">
                  <input ref="passportPhotoInput" type="file" @change="e => form.passport_photo = e.target.files[0]" accept=".jpg,.jpeg,.png" class="hidden">
                  <div v-if="form.passport_photo" class="text-sm font-semibold text-blue-600">{{ form.passport_photo.name }}</div>
                  <div v-else>
                    <svg class="mx-auto h-10 w-10 text-slate-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm text-slate-600">Click to <span class="text-blue-600 font-semibold">browse</span> files</p>
                    <p class="text-xs text-slate-400 mt-1">White background, taken within the last 6 months (JPG, PNG)</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 3: Travel History -->
          <div v-show="currentStep === 2" class="space-y-5 animate-fadeIn">
            <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Travel History</h3>
            
            <div class="p-5 border border-slate-100 rounded-2xl bg-slate-50/50">
              <label class="block text-[0.85rem] font-semibold text-slate-700 mb-3">Have you ever visited China before? <span class="inline-block text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full ml-1">Required</span></label>
              <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="radio" v-model="form.visited_china_before" :value="true" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-600">
                  <span class="text-sm font-medium text-slate-700">Yes</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="radio" v-model="form.visited_china_before" :value="false" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-600">
                  <span class="text-sm font-medium text-slate-700">No</span>
                </label>
              </div>
            </div>

            <div v-if="form.visited_china_before" class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-5 border border-blue-100 bg-blue-50/30 rounded-2xl">
              <div>
                <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">Most recent visit date</label>
                <input v-model="form.most_recent_china_visit" type="date" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all">
              </div>
              <div>
                <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">Visits in last 5 years</label>
                <input v-model="form.visits_in_last_5_years" type="number" min="0" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all">
              </div>
              <div class="sm:col-span-2">
                <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">List of previous China visit dates</label>
                <input v-model="form.previous_china_visits_dates" type="text" placeholder="e.g. 2018, 2019" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all">
              </div>
            </div>

            <div class="p-5 border border-slate-100 rounded-2xl bg-slate-50/50">
              <label class="block text-[0.85rem] font-semibold text-slate-700 mb-3">Have you traveled to other countries before? <span class="inline-block text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full ml-1">Required</span></label>
              <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="radio" v-model="form.traveled_other_countries" :value="true" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-600">
                  <span class="text-sm font-medium text-slate-700">Yes</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="radio" v-model="form.traveled_other_countries" :value="false" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-600">
                  <span class="text-sm font-medium text-slate-700">No</span>
                </label>
              </div>
              <div v-if="form.traveled_other_countries" class="mt-4">
                <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">List countries visited (with years)</label>
                <input v-model="form.list_countries_visited" type="text" placeholder="e.g. USA 2020, UK 2021" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all">
              </div>
            </div>

            <div class="p-5 border border-slate-100 rounded-2xl bg-slate-50/50">
              <label class="block text-[0.85rem] font-semibold text-slate-700 mb-3">Have you attended trade fairs (e.g., Canton Fair)?</label>
              <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="radio" v-model="form.attended_trade_fairs" :value="true" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-600">
                  <span class="text-sm font-medium text-slate-700">Yes</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="radio" v-model="form.attended_trade_fairs" :value="false" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-600">
                  <span class="text-sm font-medium text-slate-700">No</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Step 4: Visa & Documents -->
          <div v-show="currentStep === 3" class="space-y-5 animate-fadeIn">
            <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Visa Details & Documents</h3>
            
            <div class="p-5 border border-slate-100 rounded-2xl bg-white shadow-sm">
              <label class="block text-[0.85rem] font-semibold text-slate-700 mb-3">Preferred Visa Type <span class="inline-block text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full ml-1">Required</span></label>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors" :class="{'border-blue-500 bg-blue-50/30': form.preferred_visa_type === 'Regular – 3 months Single Entry'}">
                  <input type="radio" v-model="form.preferred_visa_type" value="Regular – 3 months Single Entry" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-600">
                  <span class="text-sm font-medium text-slate-700">3 Months Single Entry</span>
                </label>
                <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors" :class="{'border-blue-500 bg-blue-50/30': form.preferred_visa_type === 'Regular – 1 Year Multiple Entry'}">
                  <input type="radio" v-model="form.preferred_visa_type" value="Regular – 1 Year Multiple Entry" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-600">
                  <span class="text-sm font-medium text-slate-700">1 Year Multiple Entry</span>
                </label>
                <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors" :class="{'border-blue-500 bg-blue-50/30': form.preferred_visa_type === 'Virgin / First-time Applicant'}">
                  <input type="radio" v-model="form.preferred_visa_type" value="Virgin / First-time Applicant" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-600">
                  <span class="text-sm font-medium text-slate-700">Virgin (First-time)</span>
                </label>
                <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors" :class="{'border-blue-500 bg-blue-50/30': form.preferred_visa_type === 'Virgin with Travel History (other countries)'}">
                  <input type="radio" v-model="form.preferred_visa_type" value="Virgin with Travel History (other countries)" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-600">
                  <span class="text-sm font-medium text-slate-700">Virgin (With Travel History)</span>
                </label>
              </div>
            </div>

            <div>
              <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">Purpose of Visit / Business Details <span class="inline-block text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full ml-1">Required</span></label>
              <textarea v-model="form.purpose_of_visit" rows="3" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all" placeholder="Explain the nature of your business in China..."></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">Intended Arrival Date</label>
                <input v-model="form.intended_arrival_date" type="date" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all">
              </div>
              <div>
                <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">Intended Departure Date</label>
                <input v-model="form.intended_departure_date" type="date" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all">
              </div>
            </div>

            <div>
              <label class="block text-[0.8rem] font-semibold text-slate-700 mb-1.5">Cities to Visit in China</label>
              <input v-model="form.cities_to_visit" type="text" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white text-sm focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 outline-none transition-all" placeholder="e.g. Guangzhou, Shenzhen, Yiwu">
            </div>

            <div>
              <label class="block text-[0.85rem] font-semibold text-slate-700 mb-2">Do you have previous Chinese visas? Upload them here</label>
              <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center cursor-pointer hover:bg-slate-50 transition-colors relative" @click="$refs.previousVisaPagesInput.click()">
                <input ref="previousVisaPagesInput" type="file" @change="e => form.previous_visa_pages = Array.from(e.target.files)" multiple accept=".jpg,.jpeg,.png,.pdf" class="hidden">
                <div v-if="form.previous_visa_pages.length > 0" class="text-sm font-semibold text-blue-600">{{ form.previous_visa_pages.length }} file(s) selected</div>
                <div v-else>
                  <svg class="mx-auto h-10 w-10 text-slate-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  <p class="text-sm text-slate-600">Click to <span class="text-blue-600 font-semibold">browse</span> files</p>
                  <p class="text-xs text-slate-400 mt-1">You can select multiple files</p>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-[0.85rem] font-semibold text-slate-700 mb-2">Invitation / Verification Letter (if applicable)</label>
              <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center cursor-pointer hover:bg-slate-50 transition-colors relative" @click="$refs.invitationLetterInput.click()">
                <input ref="invitationLetterInput" type="file" @change="e => form.invitation_letter = e.target.files[0]" accept=".jpg,.jpeg,.png,.pdf" class="hidden">
                <div v-if="form.invitation_letter" class="text-sm font-semibold text-blue-600">{{ form.invitation_letter.name }}</div>
                <div v-else>
                  <svg class="mx-auto h-10 w-10 text-slate-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  <p class="text-sm text-slate-600">Click to <span class="text-blue-600 font-semibold">browse</span> files</p>
                  <p class="text-xs text-slate-400 mt-1">Formats: JPG, PNG, PDF</p>
                </div>
              </div>
            </div>

            <div class="p-5 border border-amber-200 bg-amber-50 rounded-2xl">
              <label class="flex items-start gap-3 cursor-pointer">
                <input v-model="form.declaration_confirmed" type="checkbox" class="mt-1 w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-600">
                <span class="text-sm font-medium text-slate-800 leading-relaxed">
                  I confirm that all information provided is true and accurate. I consent to Elbildad Services processing my personal data for visa purposes.
                </span>
              </label>
            </div>
          </div>

          <!-- Navigation Buttons -->
          <div class="mt-8 flex justify-between items-center border-t border-slate-100 pt-6">
            <button type="button" @click="prevStep" v-show="currentStep > 0" class="px-6 py-3 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors">
              ← Back
            </button>
            <a href="/" v-show="currentStep === 0" class="inline-block px-6 py-3 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors no-underline">
              ← Back to Home
            </a>
            
            <button type="button" @click="nextStep" v-if="currentStep < steps.length - 1" class="px-8 py-3 rounded-xl bg-slate-800 text-white font-semibold text-sm hover:bg-slate-900 transition-colors shadow-lg shadow-slate-200">
              Continue →
            </button>
            
            <button type="submit" v-if="currentStep === steps.length - 1" :disabled="form.processing || !form.declaration_confirmed" class="px-8 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold text-sm hover:shadow-lg hover:shadow-blue-500/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
              <span v-if="form.processing" class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Submitting...
              </span>
              <span v-else>Submit Application</span>
            </button>
          </div>

        </form>
      </div>
      
    </div>
  </FrontendLayout>
</template>

<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue'
import { useForm, Head } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const currentStep = ref(0)
const steps = [0, 1, 2, 3]

const progressPercentage = computed(() => {
  return (currentStep.value / (steps.length - 1)) * 100
})

const nextStep = () => {
  if (currentStep.value < steps.length - 1) {
    currentStep.value++
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const prevStep = () => {
  if (currentStep.value > 0) {
    currentStep.value--
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const form = useForm({
  full_name: '', dob: '', nationality: '', gender: '', marital_status: '',
  occupation: '', company_name: '', company_address: '', phone_number: '', email_address: '',
  passport_number: '', passport_issue_date: '', passport_expiry_date: '', country_of_issuance: '',
  passport_data_page: null, passport_photo: null,
  visited_china_before: false, most_recent_china_visit: '', visits_in_last_5_years: 0,
  previous_china_visits_dates: '', traveled_other_countries: false, list_countries_visited: '',
  attended_trade_fairs: false,
  preferred_visa_type: '', purpose_of_visit: '', intended_arrival_date: '', intended_departure_date: '',
  cities_to_visit: '',
  has_previous_chinese_visas: false, previous_visa_pages: [], invitation_letter: null,
  declaration_confirmed: false,
})

const submitForm = () => {
  form.post(route('visa.store'))
}
</script>

<style scoped>
.animate-fadeIn {
  animation: fadeIn 0.4s ease-in-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
@media (max-width: 767px) {
  .visa-mobile-only { display: block !important; }
}
</style>
