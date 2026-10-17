@extends('layouts.frontend')

@section('title', 'RFQ Commitment Fee Checkout')

@section('content')
<div class="py-12 bg-gray-50 min-h-screen pt-32 pb-24">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-10 text-center text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-10 -mr-10 opacity-10">
                    <svg width="150" height="150" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4l3 3"></path></svg>
                </div>
                
                <h1 class="text-3xl font-extrabold tracking-tight mb-2">Complete Your RFQ Request</h1>
                <p class="text-blue-100 max-w-xl mx-auto text-sm sm:text-base">Please pay the commitment fee to finalize your request for "{{ $rfq->product_name }}"</p>
            </div>

            <div class="p-8 sm:p-12">
                <!-- Order Details -->
                <div class="mb-10 bg-gray-50 rounded-xl p-6 border border-gray-100 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-800 border-b border-gray-200 pb-3 mb-4">Transaction Summary</h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 font-medium">Tracking Token:</span>
                            <span class="font-mono font-bold text-gray-800 bg-gray-200 px-2 py-1 rounded">{{ $rfq->tracking_token }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 font-medium">Transaction Ref:</span>
                            <span class="font-mono font-semibold text-gray-700">{{ $transaction->reference }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 font-medium">Customer:</span>
                            <span class="font-semibold text-gray-800">{{ $rfq->customer->name }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 font-medium">Email:</span>
                            <span class="font-semibold text-gray-800">{{ $rfq->customer->email }}</span>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4 mt-2 flex justify-between items-center">
                            <span class="text-base font-bold text-gray-800">Total Commitment Fee</span>
                            <span class="text-2xl font-black text-indigo-600">NGN {{ number_format($transaction->amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Info Alert -->


                @if(session('info'))
                    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded mb-6" role="alert">
                        <p class="font-bold">Notice</p>
                        <p>{{ session('info') }}</p>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-6" role="alert">
                        <p class="font-bold">Error</p>
                        <p>{{ session('error') }}</p>
                    </div>
                @endif
                
                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded mb-6" role="alert">
                        <p class="font-bold">Success</p>
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                <div id="payment-status-message" class="hidden rounded-lg p-4 mb-6"></div>

                <!-- Actions -->
                @if(isset($dva_error))
                    <div class="rounded-xl p-5 mb-6 text-sm font-medium bg-red-50 text-red-700 border border-red-200">
                        <div class="flex items-center space-x-2 mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="font-bold">Virtual Account Error</span>
                        </div>
                        <p class="mb-4 text-red-600">{{ $dva_error }}</p>
                        <p class="text-sm text-red-500 mb-4">Please refresh the page to try generating the account again.</p>
                        <button onclick="window.location.reload()" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none transition-colors">
                            Try Again
                        </button>
                    </div>
                @else
                    @php
                        $metadata = $transaction->metadata ?? [];
                        $accountNo = $metadata['virtual_account_number'] ?? null;
                        // If Zainpay doesn't return bankName, default to Zainpay Virtual Account
                        $bankName = $metadata['bank_name'] ?? null;
                        if (!$bankName || $bankName === 'Zainpay Bank') {
                            $bankName = 'Guaranty Trust Bank';
                        }
                    @endphp

                    @if($accountNo)
                        <div class="bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-100 rounded-2xl p-6 md:p-8 mb-6 shadow-sm relative overflow-hidden">
                            <!-- Background accent -->
                            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-blue-100 rounded-full opacity-50 blur-xl"></div>
                            
                            <div class="relative z-10 text-center">
                                <p class="text-sm md:text-base text-slate-600 font-medium mb-2">Please transfer exactly</p>
                                <h2 class="text-3xl md:text-4xl font-extrabold text-blue-700 mb-6">&#8358;{{ number_format($transaction->amount, 2) }}</h2>
                                
                                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 mb-4">
                                    <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-1">Account Number</p>
                                    <div class="flex items-center justify-center space-x-3">
                                        <span class="text-2xl md:text-3xl font-bold text-slate-800 tracking-widest select-all" id="accountNumber">{{ $accountNo }}</span>
                                        <button onclick="copyAccountNo()" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Copy Account Number">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
                                    <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-1">Bank Name</p>
                                    <span class="text-lg md:text-xl font-semibold text-slate-700">{{ $bankName }}</span>
                                </div>
                                
                                <div class="mt-5 flex items-center justify-center space-x-2 text-amber-600 bg-amber-50 py-2 px-3 rounded-lg w-fit mx-auto">
                                    <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span class="text-xs font-semibold">Account expires in 1 hour</span>
                                </div>
                            </div>
                        </div>

                        <p class="text-sm md:text-base text-slate-500 text-center mb-6 px-4">
                            After transferring the exact amount, please wait a few seconds. Your payment will be verified automatically.
                        </p>

                        {{-- Auto-verification status panel --}}
                        <div id="auto-verify-panel" class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-5 text-center">
                            <div class="flex items-center justify-center space-x-2 text-blue-600 mb-1">
                                <svg id="spinner-icon" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span id="auto-verify-text" class="text-sm font-semibold">Waiting for payment confirmation…</span>
                            </div>
                            <p class="text-xs text-blue-500 hidden">We check automatically every 30 seconds</p>
                        </div>
                        
                        <div class="flex flex-col space-y-3 mt-4">
                            <form action="{{ route('rfq.verify-dva', $rfq->tracking_token) }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit" class="w-full flex justify-center items-center py-4 px-6 rounded-xl shadow-md text-base font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all transform hover:-translate-y-0.5">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    I Have Made The Transfer
                                </button>
                            </form>
                            
                            {{-- Cancel button — sends DELETE to remove unpaid RFQ from system --}}
                            <form action="{{ route('rfq.cancel', $rfq->tracking_token) }}" method="POST" class="w-full" onsubmit="return confirm('Are you sure you want to cancel? Your RFQ will be deleted.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full flex justify-center items-center py-3 px-6 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-red-50 hover:text-red-600 transition-colors">
                                    Cancel &amp; Discard This RFQ
                                </button>
                            </form>
                        </div>

                        {{-- Full-screen success animation overlay (hidden until payment confirmed) --}}
                        <div id="payment-success-overlay" class="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-white" style="display:none!important;">
                            <div class="text-center px-6">
                                <div class="mx-auto mb-6 w-28 h-28 rounded-full bg-green-100 flex items-center justify-center animate-bounce">
                                    <svg class="w-16 h-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <h2 class="text-3xl font-extrabold text-gray-900 mb-3">Payment Confirmed!</h2>
                                <p class="text-lg text-gray-600 mb-2">₦{{ number_format($transaction->amount, 2) }} received successfully.</p>
                                <p class="text-sm text-gray-500 mb-8">A confirmation email has been sent to you. Redirecting to tracking…</p>
                                <div class="flex items-center justify-center space-x-2">
                                    <svg class="animate-spin h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm font-semibold text-green-600">Taking you to your tracking page…</span>
                                </div>
                            </div>
                        </div>

                        <script>
                            function copyAccountNo() {
                                const accountNo = document.getElementById('accountNumber').innerText;
                                navigator.clipboard.writeText(accountNo).then(() => {
                                    alert('Account number copied to clipboard!');
                                }).catch(err => {
                                    console.error('Failed to copy text: ', err);
                                });
                            }

                            // Auto-poll payment status every 3 seconds
                            (function startPolling() {
                                const statusUrl  = '{{ route("rfq.payment-status", $rfq->tracking_token) }}';
                                const panel      = document.getElementById('auto-verify-panel');
                                const statusText = document.getElementById('auto-verify-text');
                                const spinner    = document.getElementById('spinner-icon');
                                const overlay    = document.getElementById('payment-success-overlay');

                                function showSuccess(trackUrl) {
                                    // Show full-screen celebration overlay
                                    overlay.style.cssText = 'display:flex!important;position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999;background:white;flex-direction:column;align-items:center;justify-content:center;';
                                    
                                    // Update the small panel too
                                    panel.classList.remove('bg-blue-50', 'border-blue-200');
                                    panel.classList.add('bg-green-50', 'border-green-200');
                                    statusText.classList.remove('text-blue-600');
                                    statusText.classList.add('text-green-700');
                                    statusText.textContent = 'Payment confirmed!';
                                    
                                    // Redirect after 2.5 seconds
                                    setTimeout(() => window.location.href = trackUrl, 2500);
                                }

                                function poll() {
                                    fetch(statusUrl, { headers: { 'Accept': 'application/json' } })
                                        .then(r => r.json())
                                        .then(data => {
                                            if (data.paid) {
                                                showSuccess(data.track_url);
                                            } else {
                                                setTimeout(poll, 30000);
                                            }
                                        })
                                        .catch(() => {
                                            setTimeout(poll, 5000);
                                        });
                                }

                                setTimeout(poll, 30000);
                            })();
                        </script>
                    @else
                        <div class="flex flex-col items-center justify-center p-8 bg-slate-50 rounded-2xl border border-slate-100 mb-6 mt-8">
                            <svg class="animate-spin h-10 w-10 text-blue-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-sm font-medium text-slate-600">Generating secure Virtual Account...</p>
                            <p class="text-xs text-slate-400 mt-2">Please wait while we connect to Zainpay</p>
                        </div>
                        <script>
                            setTimeout(() => window.location.reload(), 4000);
                        </script>
                    @endif
                @endif

                <div class="mt-6 flex items-center justify-center space-x-2 text-sm text-gray-500">
                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Secured by Zainpay</span>
                </div>
            </div>
        </div>
        
        <!-- Trust Indicators -->
        <div class="mt-8 flex justify-center items-center gap-6 text-gray-400">
            <div class="flex items-center text-xs font-semibold">
                <svg class="w-4 h-4 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                256-bit SSL
            </div>
            <div class="flex items-center text-xs font-semibold">
                <span class="mr-1">Powered by</span>
                <span class="font-bold text-gray-600">Zainpay</span>
            </div>
        </div>
    </div>
</div>
@endsection
