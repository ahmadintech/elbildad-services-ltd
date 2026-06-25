<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request a Quote — Elbildad Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f1f5f9; }

        .input-field {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.75rem;
            background: #fff;
            font-size: 0.875rem;
            color: #1e293b;
            transition: all 0.2s ease;
            outline: none;
        }
        .input-field:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
        }
        .input-field::placeholder { color: #94a3b8; }

        .label { display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 0.4rem; letter-spacing: 0.01em; }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
            padding: 1rem;
            border-radius: 0.875rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .btn-submit:hover { box-shadow: 0 8px 25px rgba(37,99,235,0.4); transform: translateY(-1px); }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

        .error-msg { font-size: 0.75rem; color: #ef4444; margin-top: 0.3rem; }

        .section-divider {
            border: none;
            border-top: 1.5px dashed #e2e8f0;
            margin: 1.5rem 0;
        }

        /* Loader spinner inside button */
        .spinner {
            display: inline-block;
            width: 18px; height: 18px;
            border: 2.5px solid rgba(255,255,255,0.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            vertical-align: middle;
            margin-right: 8px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Floating label badge */
        .badge { display: inline-block; font-size: 0.68rem; background: #dbeafe; color: #1d4ed8; font-weight: 600; padding: 0.15rem 0.5rem; border-radius: 999px; margin-left: 6px; vertical-align: middle; }
        .badge-opt { background: #f1f5f9; color: #64748b; }
    </style>
</head>
<body class="min-h-screen py-12 px-4">

    {{-- Top nav --}}
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur border-b border-gray-100 h-16 flex items-center px-6">
        <a href="{{ url('/') }}" class="flex items-center gap-2">
            <img src="/images/logo/elbildad-logo.png" alt="Elbildad" class="h-9 w-auto object-contain">
            <div class="leading-none">
                <span class="block text-xl font-bold text-slate-800 tracking-tight">Elbildad</span>
                <span class="block text-[10px] font-semibold text-blue-600 uppercase tracking-widest">Services</span>
            </div>
        </a>
        <div class="ml-auto">
            <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">Sign In</a>
        </div>
    </header>

    <div class="pt-24 max-w-2xl mx-auto">

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl">
                <p class="font-semibold text-red-700 mb-2 text-sm">Please fix the following errors:</p>
                <ul class="space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="text-xs text-red-600 flex items-center gap-1.5">
                            <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">

            {{-- Header --}}
            <div class="bg-gradient-to-r from-blue-700 to-blue-500 px-8 py-8 text-white">
                <h1 class="text-2xl md:text-3xl font-bold mb-1">Submit Your Sourcing Request</h1>
                <p class="opacity-85 text-sm">Get factory prices from China to Nigeria — we handle everything.</p>
            </div>

            {{-- Commitment fee notice --}}
            <div class="mx-8 mt-6 p-4 bg-blue-50 border border-blue-200 rounded-2xl flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-semibold text-blue-800 text-sm">Commitment Fee: ₦5,000</p>
                    <p class="text-blue-700 text-xs mt-0.5 leading-relaxed">
                        To ensure we dedicate our team's time to serious requests only. Fully refundable if we can't source within 48 hours, otherwise deducted from your service charge.
                    </p>
                </div>
            </div>

            {{-- Form --}}
            <form id="rfq-form" method="POST" action="{{ route('rfq.submit') }}" enctype="multipart/form-data" class="px-8 py-6 space-y-5" novalidate>
                @csrf

                {{-- Personal info --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label" for="full_name">Full Name <span class="badge">Required</span></label>
                        <input type="text" id="full_name" name="full_name" class="input-field @error('full_name') border-red-400 @enderror"
                               placeholder="e.g. Ahmed Ibrahim" value="{{ old('full_name', auth()->user()->name ?? '') }}" required>
                        @error('full_name') <p class="error-msg">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label" for="company_name">Company Name <span class="badge badge-opt">Optional</span></label>
                        <input type="text" id="company_name" name="company_name" class="input-field"
                               placeholder="e.g. Acme Ltd" value="{{ old('company_name') }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label" for="whatsapp_number">WhatsApp Number <span class="badge">Required</span></label>
                        <input type="tel" id="whatsapp_number" name="whatsapp_number" class="input-field @error('whatsapp_number') border-red-400 @enderror"
                               placeholder="e.g. 08031234567" value="{{ old('whatsapp_number', auth()->user()->whatsapp_number ?? '') }}" 
                               pattern="[0-9]{11}" minlength="11" maxlength="11" title="Please enter exactly 11 digits" required>
                        @error('whatsapp_number') <p class="error-msg">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label" for="email">Email Address <span class="badge">Required</span></label>
                        <input type="email" id="email" name="email" class="input-field @error('email') border-red-400 @enderror"
                               placeholder="you@example.com" value="{{ old('email', auth()->user()->email ?? '') }}" required>
                        @error('email') <p class="error-msg">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="label" for="location">Location in Nigeria <span class="badge badge-opt">Optional</span></label>
                    <input type="text" id="location" name="location" class="input-field"
                           placeholder="e.g. Kano, Lagos, Abuja" value="{{ old('location') }}">
                </div>

                <hr class="section-divider">

                {{-- Product details --}}
                <div>
                    <label class="label" for="product_name">Product Name / Description <span class="badge">Required</span></label>
                    <input type="text" id="product_name" name="product_name" class="input-field @error('product_name') border-red-400 @enderror"
                           placeholder="e.g. Industrial Generator, 45KVA" value="{{ old('product_name') }}" required>
                    @error('product_name') <p class="error-msg">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="specifications">Detailed Specifications <span class="badge">Required</span></label>
                    <textarea id="specifications" name="specifications" rows="4" class="input-field @error('specifications') border-red-400 @enderror"
                              placeholder="Size, colour, power, certifications, brand preferences, etc." required>{{ old('specifications') }}</textarea>
                    @error('specifications') <p class="error-msg">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label">Product Image <span class="badge badge-opt">Optional</span></label>
                    <div id="dropzone" class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center cursor-pointer hover:bg-gray-50 transition-colors @error('image') border-red-400 @enderror">
                        <input type="file" id="image" name="image" class="hidden" accept="image/*">
                        
                        <div id="preview-container" class="hidden">
                            <img id="image-preview" src="" class="max-h-32 mx-auto rounded-lg mb-2 shadow-sm border border-gray-200 object-cover">
                            <p id="file-name" class="text-sm text-gray-700 font-medium truncate max-w-xs mx-auto"></p>
                            <button type="button" id="remove-image" class="text-xs font-semibold text-red-500 hover:text-red-700 mt-2 bg-red-50 px-2 py-1 rounded">Remove Image</button>
                        </div>
                        
                        <div id="upload-placeholder">
                            <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm text-gray-600">Drag and drop an image, or <span class="text-blue-600 font-semibold">browse</span></p>
                            <p class="text-xs text-gray-400 mt-1">Max size: 4MB. Formats: JPG, PNG, GIF, WEBP</p>
                        </div>
                    </div>
                    @error('image') <p class="error-msg">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label" for="quantity">Quantity Needed <span class="badge">Required</span></label>
                        <select id="quantity" name="quantity" class="input-field @error('quantity') border-red-400 @enderror" required>
                            <option value="">Select quantity</option>
                            <option value="1-10"   {{ old('quantity') === '1-10'   ? 'selected' : '' }}>1 – 10 units</option>
                            <option value="11-50"  {{ old('quantity') === '11-50'  ? 'selected' : '' }}>11 – 50 units</option>
                            <option value="51-100" {{ old('quantity') === '51-100' ? 'selected' : '' }}>51 – 100 units</option>
                            <option value="100+"   {{ old('quantity') === '100+'   ? 'selected' : '' }}>100+ units</option>
                        </select>
                        @error('quantity') <p class="error-msg">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label" for="delivery_method">Preferred Delivery Method <span class="badge">Required</span></label>
                        <select id="delivery_method" name="delivery_method" class="input-field @error('delivery_method') border-red-400 @enderror" required>
                            <option value="">Select method</option>
                            <option value="air"     {{ old('delivery_method') === 'air'     ? 'selected' : '' }}>✈️ Air Freight</option>
                            <option value="sea"     {{ old('delivery_method') === 'sea'     ? 'selected' : '' }}>🚢 Sea Freight</option>
                            <option value="discuss" {{ old('delivery_method') === 'discuss' ? 'selected' : '' }}>💬 Discuss with us</option>
                        </select>
                        @error('delivery_method') <p class="error-msg">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="label" for="target_price">Target Price / Budget <span class="badge badge-opt">Optional</span></label>
                    <input type="text" id="target_price" name="target_price" class="input-field"
                           placeholder="e.g. ₦500,000 or $300 per unit" value="{{ old('target_price') }}">
                </div>

                <div>
                    <label class="label" for="additional_requirements">Additional Requirements <span class="badge badge-opt">Optional</span></label>
                    <textarea id="additional_requirements" name="additional_requirements" rows="3" class="input-field"
                              placeholder="Quality standards, samples needed, custom branding, etc.">{{ old('additional_requirements') }}</textarea>
                </div>

                <hr class="section-divider">

                {{-- Submit --}}
                <div>
                    <button type="submit" id="submit-btn" class="btn-submit">
                        <span id="btn-label">Submit Request</span>
                    </button>
                    <p class="text-center text-slate-400 text-xs mt-3">By submitting, you agree to our terms regarding the commitment fee.</p>
                </div>

            </form>

            {{-- WhatsApp alternative --}}
            <div class="mx-8 mb-8 p-4 bg-gray-50 rounded-2xl text-center">
                <p class="text-slate-500 text-sm mb-2">Prefer to chat directly?</p>
                <a href="https://wa.me/2348032775756" target="_blank"
                   class="inline-flex items-center gap-2 text-emerald-600 font-semibold hover:text-emerald-700 text-sm transition-colors">
                    <svg viewBox="0 0 24 24" class="w-5 h-5 fill-current"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.558 4.116 1.535 5.846L.057 23.625a.75.75 0 00.92.92l5.78-1.477A11.955 11.955 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75c-1.99 0-3.865-.553-5.463-1.515l-.392-.232-4.054 1.036 1.054-3.953-.255-.406A9.712 9.712 0 012.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/></svg>
                    Submit via WhatsApp
                </a>
            </div>
        </div>

        <p class="text-center text-slate-400 text-xs mt-6">
            © {{ date('Y') }} Elbildad Services Ltd · <a href="{{ url('/') }}" class="hover:text-slate-600">Home</a>
        </p>
    </div>

    <script>
        document.getElementById('rfq-form').addEventListener('submit', function(e) {
            const btn = document.getElementById('submit-btn');
            const label = document.getElementById('btn-label');
            btn.disabled = true;
            label.innerHTML = '<span class="spinner"></span> Submitting...';
        });

        // Image drag & drop logic
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('image');
        const previewContainer = document.getElementById('preview-container');
        const uploadPlaceholder = document.getElementById('upload-placeholder');
        const imagePreview = document.getElementById('image-preview');
        const fileNameDisplay = document.getElementById('file-name');
        const removeImageBtn = document.getElementById('remove-image');

        dropzone.addEventListener('click', (e) => {
            if (e.target !== removeImageBtn) {
                fileInput.click();
            }
        });

        ['dragover', 'dragenter'].forEach(evt => {
            dropzone.addEventListener(evt, e => {
                e.preventDefault();
                dropzone.classList.add('bg-blue-50', 'border-blue-400');
            });
        });

        ['dragleave', 'dragend', 'drop'].forEach(evt => {
            dropzone.addEventListener(evt, e => {
                e.preventDefault();
                dropzone.classList.remove('bg-blue-50', 'border-blue-400');
            });
        });

        dropzone.addEventListener('drop', e => {
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                handleFileSelection();
            }
        });

        fileInput.addEventListener('change', handleFileSelection);

        removeImageBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            fileInput.value = '';
            previewContainer.classList.add('hidden');
            uploadPlaceholder.classList.remove('hidden');
        });

        function handleFileSelection() {
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                
                // Validate size (4MB)
                if (file.size > 4 * 1024 * 1024) {
                    alert('File is too large. Maximum size is 4MB.');
                    fileInput.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    fileNameDisplay.textContent = file.name;
                    uploadPlaceholder.classList.add('hidden');
                    previewContainer.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>