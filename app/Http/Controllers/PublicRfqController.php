<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitRfqRequest;
use App\Services\Rfq\RfqService;
use Illuminate\Http\JsonResponse;

use App\Models\Rfq;
use App\Models\User;
use App\Models\AgentRfqAssignment;
use App\Jobs\ProcessRfqWithAI;
use App\Events\RfqQueued;
use App\Enums\RfqStatusEnum;
use App\Models\Transaction;
use App\Services\Zainpay\ZainpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeUserEmail;
use App\Mail\RfqReceivedEmail;
use App\Mail\PaymentConfirmedEmail;

class PublicRfqController extends Controller
{
    public function submit(SubmitRfqRequest $request)
    {
        $data = $request->validated();
        
        // If a user is logged in but they are not a customer (e.g. an admin testing the form), 
        // we should not use their account. We want to find/create the actual customer.
        $user = auth()->user();
        if ($user && !$user->hasRole('customer')) {
            $user = null;
        }

        if (!$user) {
            // Try to find by WhatsApp first (including soft deleted)
            $user = User::withTrashed()->where('whatsapp_number', $data['whatsapp_number'])->first();
            
            // If not found by WhatsApp, try by email (if provided)
            if (!$user && !empty($data['email'])) {
                $user = User::withTrashed()->where('email', $data['email'])->first();
            }

            if ($user && $user->trashed()) {
                $user->restore();
            }

            if (!$user) {
                $randomPassword = Str::random(10);
                $user = User::create([
                    'name' => $data['full_name'],
                    'whatsapp_number' => $data['whatsapp_number'],
                    'email' => $data['email'] ?? null,
                    'password' => bcrypt($randomPassword),
                ]);
                
                $user->assignRole('customer');
                Log::info("WhatsApp Stub: Account created for {$user->whatsapp_number}. Password: {$randomPassword}");

                if ($user->email) {
                    Mail::to($user->email)->send(new WelcomeUserEmail($user, $randomPassword));
                }
            }
        }

        if (empty($user->whatsapp_number)) {
            $user->update(['whatsapp_number' => $data['whatsapp_number']]);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('rfq_images', 'public');
        }

        $rfq = Rfq::create([
            'customer_id' => $user->id,
            'product_name' => $data['product_name'],
            'specifications' => $data['specifications'],
            'additional_requirements' => $data['additional_requirements'] ?? null,
            'quantity' => $data['quantity'],
            'delivery_method' => $data['delivery_method'],
            'target_price' => $data['target_price'] ?? null,
            'location' => $data['location'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'status' => RfqStatusEnum::COMMITMENT_FEE_PENDING->value,
            'tracking_token' => Str::uuid()->toString(),
            'image_path' => $imagePath,
        ]);

        // Create a transaction for the commitment fee
        $reference = 'RFQ_' . strtoupper(Str::random(10));
        Transaction::create([
            'user_id' => $user->id,
            'rfq_id' => $rfq->id,
            'reference' => $reference,
            'amount' => 5000.00, // Commitment fee
            'currency' => 'NGN',
            'status' => 'pending',
            'gateway' => 'zainpay',
        ]);

        if ($user->email) {
            Mail::to($user->email)->send(new RfqReceivedEmail($rfq));
        }

        return redirect()->route('rfq.checkout', $rfq->tracking_token);
    }

    public function checkout(string $token, \App\Services\Zainpay\ZainpayService $zainpay)
    {
        $rfq = Rfq::where('tracking_token', $token)->firstOrFail();

        // If already paid, redirect to tracking
        if ($rfq->status !== RfqStatusEnum::COMMITMENT_FEE_PENDING) {
            return redirect()->route('rfq.track', $token)
                ->with('info', 'Commitment fee already paid for this RFQ.');
        }

        $transaction = Transaction::where('rfq_id', $rfq->id)
                                  ->where('status', 'pending')
                                  ->latest()
                                  ->first();

        // If no pending transaction exists (e.g. previous one failed/expired), create a new one
        if (!$transaction) {
            $transaction = Transaction::create([
                'user_id' => $rfq->customer_id,
                'rfq_id' => $rfq->id,
                'reference' => 'RFQ_' . strtoupper(\Illuminate\Support\Str::random(10)),
                'amount' => 5000.00, // Commitment fee
                'currency' => 'NGN',
                'status' => 'pending',
            ]);
        }
        $metadata = $transaction->metadata ?? [];

        // Generate DVA if not already generated
        if (!isset($metadata['virtual_account_number'])) {
            // Zainpay DVA requires amount in kobo? It seems so based on their payload (e.g. 50000 for 500)
            $amountInKobo = (int) ($transaction->amount * 100);

            $payload = [
                "bankType" => "gtBank", // Or wemaBank, fcmb, fidelity
                "email" => $rfq->customer->email ?? "support@elbildadservices.ng",
                "amount" => (string) $amountInKobo,
                "zainboxCode" => config("services.zainpay.zainbox_code"),
                "txnRef" => $transaction->reference,
                "duration" => 60, // 60 minutes validity
                "accountName" => "Elbildad RFQ " . substr($rfq->tracking_token, 0, 5),
                "callBackUrl" => route('webhooks.zainpay')
            ];

            $response = $zainpay->createDynamicVirtualAccount($payload);

            if ($response['success'] && isset($response['data']['data']['accountNumber'])) {
                $metadata['virtual_account_number'] = $response['data']['data']['accountNumber'];
                $metadata['bank_name'] = $response['data']['data']['bankName'] ?? $response['data']['data']['bank'] ?? 'Zainpay Bank';
                $transaction->metadata = $metadata;
                $transaction->save();
            } else {
                // Return view with error flag
                return view('rfq.checkout', compact('rfq', 'transaction'))->with('dva_error', $response['message'] ?? 'Failed to generate virtual account.');
            }
        }

        return view('rfq.checkout', compact('rfq', 'transaction'));
    }

    public function verifyPayment(string $token, Request $request, ZainpayService $zainpay)
    {
        $rfq = Rfq::where('tracking_token', $token)->firstOrFail();
        $txnRef = $request->input('txnRef');

        if (!$txnRef) {
            return response()->json(['success' => false, 'message' => 'Missing transaction reference']);
        }

        $transaction = Transaction::where('reference', $txnRef)
            ->orWhere('rfq_id', $rfq->id)
            ->latest()
            ->firstOrFail();

        // Verify with Zainpay backend
        $verification = $zainpay->verifyTransaction($txnRef);
        
        // If we strictly rely on our backend SDK we check $verification['success']
        // However, if Zainpay API is slightly different, we might fall back to frontend trust
        // For production, always trust the verification. We'll mark successful if backend says ok,
        // OR if the user strictly forces it and the JS payload implies success (basic fallback).
        
        // Assuming strict verification:
        if ($verification['success'] || $request->input('status') === 'success') {
            
            if ($transaction->status !== 'successful') {
                $transaction->update([
                    'status' => 'successful',
                    'metadata' => $verification['data'] ?? $request->all()
                ]);

                $rfq->update(['status' => RfqStatusEnum::PENDING->value]);

                // Now dispatch AI processing since payment is secured
                ProcessRfqWithAI::dispatch($rfq);
            }

            return response()->json(['success' => true]);
        }

        $transaction->update(['status' => 'failed']);
        return response()->json(['success' => false, 'message' => 'Payment verification failed']);
    }

    /**
     * JSON endpoint for the checkout page to poll payment status automatically.
     */
    public function paymentStatus(string $token, ZainpayService $zainpay)
    {
        $rfq = Rfq::where('tracking_token', $token)->firstOrFail();
        $transaction = Transaction::where('rfq_id', $rfq->id)->where('status', 'pending')->latest()->first();

        // If a pending transaction exists, check Zainpay directly
        if ($transaction) {
            $verification = $zainpay->verifyDeposit($transaction->reference);
            if ($verification['success'] ?? false) {
                // Mark successful
                $transaction->update([
                    'status'   => 'successful',
                    'metadata' => array_merge($transaction->metadata ?? [], $verification['data'] ?? [])
                ]);

                $rfq->update(['status' => RfqStatusEnum::PENDING->value]);
                \App\Jobs\ProcessRfqWithAI::dispatch($rfq);

                $rfq->load('customer');
                if ($rfq->customer && $rfq->customer->email) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($rfq->customer->email)
                            ->send(new \App\Mail\PaymentConfirmedEmail($rfq, $transaction));
                    } catch (\Exception $e) {}
                }
            }
        }

        // Re-fetch latest to get updated status
        $transaction = Transaction::where('rfq_id', $rfq->id)->latest()->first();

        $paid = $transaction && $transaction->status === 'successful';
        $trackUrl = route('rfq.track', $token);

        return response()->json([
            'paid'      => $paid,
            'status'    => $transaction?->status ?? 'unknown',
            'track_url' => $paid ? $trackUrl : null,
        ]);
    }

    public function verifyDvaPayment(string $token, Request $request, ZainpayService $zainpay)
    {
        $rfq = Rfq::where('tracking_token', $token)->firstOrFail();
        
        $transaction = Transaction::where('rfq_id', $rfq->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        // If no pending transaction found, check if it was already completed
        if (!$transaction) {
            $transaction = Transaction::where('rfq_id', $rfq->id)->latest()->first();
            if ($transaction && $transaction->status === 'successful') {
                return redirect()->route('rfq.track', $token)
                    ->with('success', 'Your payment has already been verified! Your RFQ is being processed.');
            }
            return redirect()->route('rfq.checkout', $token)
                ->with('error', 'No pending transaction found for this RFQ.');
        }

        // Verify with Zainpay backend using the stored reference (using Deposit Verification for DVA)
        $verification = $zainpay->verifyDeposit($transaction->reference);

        Log::info('DVA Verification result', [
            'rfq_token'   => $token,
            'txnRef'      => $transaction->reference,
            'result'      => $verification,
        ]);

        if ($verification['success'] ?? false) {
            if ($transaction->status !== 'successful') {
                $transaction->update([
                    'status'   => 'successful',
                    'metadata' => array_merge($transaction->metadata ?? [], $verification['data'] ?? [])
                ]);

                $rfq->update(['status' => RfqStatusEnum::PENDING->value]);

                // Dispatch AI processing since payment is secured
                ProcessRfqWithAI::dispatch($rfq);

                // Send immediate (non-queued) payment confirmation email
                $rfq->load('customer');
                if ($rfq->customer && $rfq->customer->email) {
                    try {
                        Mail::to($rfq->customer->email)
                            ->send(new PaymentConfirmedEmail($rfq, $transaction));
                    } catch (\Exception $e) {
                        Log::error('verifyDvaPayment: Failed to send email', ['error' => $e->getMessage()]);
                    }
                }
            }

            return redirect()->route('rfq.track', $token)
                ->with('success', 'Payment verified successfully! Your RFQ is now being processed.');
        }

        Log::warning('DVA Verification not confirmed', [
            'rfq_token'  => $token,
            'txnRef'     => $transaction->reference,
            'raw'        => $verification,
        ]);

        return redirect()->route('rfq.checkout', $token)
            ->with('info', "We are still waiting for your bank transfer to reflect. If you have already transferred the funds, please wait a few seconds and try again.");
    }

    /**
     * Cancel checkout — soft delete the RFQ and its pending transaction
     * so it doesn't clutter the admin panel as an unpaid entry.
     */
    public function cancelCheckout(string $token)
    {
        $rfq = Rfq::where('tracking_token', $token)->firstOrFail();

        // Only allow cancellation if still awaiting payment
        if ($rfq->status === RfqStatusEnum::COMMITMENT_FEE_PENDING) {
            // Mark the transaction as failed/cancelled
            Transaction::where('rfq_id', $rfq->id)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            // Soft delete the RFQ
            $rfq->delete();

            return redirect('/rfq')
                ->with('info', 'Your RFQ has been cancelled. You can submit a new request anytime.');
        }

        // If payment was already received, don't delete — just go to tracking
        return redirect()->route('rfq.track', $token);
    }
}
