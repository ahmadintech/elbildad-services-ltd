<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Rfq;
use App\Jobs\ProcessRfqWithAI;
use App\Enums\RfqStatusEnum;
use App\Mail\PaymentConfirmedEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ZainpayWebhookController extends Controller
{
    /**
     * Handle Zainpay Webhook (DVA Deposit / Repush)
     */
    public function handle(Request $request)
    {
        $payload    = $request->getContent();
        $signature  = $request->header('Zainpay-Signature');
        $secretKey  = config('services.zainpay.secret_key');

        // Always log the raw incoming payload so we can debug any format issues
        Log::error('Zainpay Webhook: Incoming request [ALWAYS LOG]', [
            'headers'   => $request->headers->all(),
            'body'      => $payload,
            'signature' => $signature,
        ]);

        // Verify signature when present. If no secret key is configured, skip.
        if ($signature && $secretKey) {
            $expectedSignature = hash_hmac('sha256', $payload, $secretKey);
            if (!hash_equals($expectedSignature, $signature)) {
                Log::warning('Zainpay Webhook: Invalid signature', [
                    'expected' => $expectedSignature,
                    'received' => $signature,
                ]);
                return response()->json(['error' => 'Invalid signature'], 401);
            }
        }

        $data = json_decode($payload, true);

        if (!is_array($data)) {
            Log::error('Zainpay Webhook: Could not parse JSON payload', ['raw' => $payload]);
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        Log::error('Zainpay Webhook: Parsed payload', ['data' => $data]);

        // Zainpay DVA webhooks can nest data inside a 'data' key
        $inner  = $data['data'] ?? $data;
        $txnRef = $inner['txnRef'] ?? $inner['transactionReference'] ?? $inner['transactionRef'] ?? $data['txnRef'] ?? $data['transactionReference'] ?? null;
        $status = strtolower($inner['status'] ?? $data['status'] ?? '');
        $code   = (string) ($inner['code'] ?? $data['code'] ?? '');

        Log::error('Zainpay Webhook: Extracted values', [
            'txnRef' => $txnRef,
            'status' => $status,
            'code'   => $code,
        ]);

        if (!$txnRef) {
            Log::warning('Zainpay Webhook: No txnRef found in payload');
            return response()->json(['status' => 'ok', 'note' => 'No txnRef']); // Return 200 so Zainpay does not keep retrying
        }

        $transaction = Transaction::where('reference', $txnRef)->first();

        if (!$transaction) {
            Log::warning('Zainpay Webhook: Transaction not found', ['txnRef' => $txnRef]);
            return response()->json(['status' => 'ok', 'note' => 'Transaction not found']);
        }

        $isSuccess = ($code === '00') || in_array($status, ['success', 'successful', 'completed', 'paid']);

        Log::info('Zainpay Webhook: Processing transaction', [
            'txnRef'         => $txnRef,
            'status'         => $status,
            'isSuccess'      => $isSuccess,
            'current_status' => $transaction->status,
        ]);

        if ($isSuccess && $transaction->status !== 'successful') {
            $transaction->update([
                'status'   => 'successful',
                'metadata' => array_merge($transaction->metadata ?? [], ['webhook_data' => $data]),
            ]);

            Log::error('Zainpay Webhook: Transaction marked successful', ['txnRef' => $txnRef]);

            // Trigger RFQ processing if this is an RFQ commitment fee payment
            if ($transaction->rfq_id) {
                $rfq = Rfq::find($transaction->rfq_id);
                // status is cast to RfqStatusEnum enum object — compare enum case, not string
                if ($rfq && $rfq->status === RfqStatusEnum::COMMITMENT_FEE_PENDING) {
                    // Move to PENDING so admin can see payment confirmed and pipeline can start
                    $rfq->update(['status' => RfqStatusEnum::PENDING->value]);
                    ProcessRfqWithAI::dispatch($rfq);
                    Log::error('Zainpay Webhook: RFQ moved to PENDING and AI dispatched', ['rfq_id' => $rfq->id]);


                    // Send IMMEDIATE (non-queued) payment confirmation email to customer
                    if ($rfq->customer && $rfq->customer->email) {
                        try {
                            Mail::to($rfq->customer->email)
                                ->send(new PaymentConfirmedEmail($rfq->load('customer'), $transaction));
                            Log::error('Zainpay Webhook: Payment confirmation email sent', ['email' => $rfq->customer->email]);
                        } catch (\Exception $e) {
                            Log::error('Zainpay Webhook: Failed to send email', ['error' => $e->getMessage()]);
                        }
                    }
                }
            }
        } elseif (in_array($status, ['failed', 'cancelled', 'expired']) && !in_array($transaction->status, ['successful'])) {
            $transaction->update([
                'status'   => 'failed',
                'metadata' => array_merge($transaction->metadata ?? [], ['webhook_data' => $data]),
            ]);
            Log::warning('Zainpay Webhook: Transaction marked failed', ['txnRef' => $txnRef, 'status' => $status]);
        } else {
            Log::info('Zainpay Webhook: Transaction already processed, skipping', [
                'txnRef'         => $txnRef,
                'current_status' => $transaction->status,
            ]);
        }

        return response()->json(['status' => 'success']);
    }
}
