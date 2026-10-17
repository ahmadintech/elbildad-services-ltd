<?php

namespace App\Services\Zainpay;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZainpayService
{
    protected string $baseUrl;
    protected string $privateKey;

    public function __construct()
    {
        $this->baseUrl = config('services.zainpay.base_url', 'https://api.zainpay.ng');
        $this->privateKey = config('services.zainpay.private_key');
    }

    /**
     * Verify a transaction using the transaction reference.
     */
    public function verifyTransaction(string $txnRef): array
    {
        try {
            // Zainpay verification endpoint (using common v2 format)
            $response = Http::withToken($this->privateKey)
                ->get("{$this->baseUrl}/zainbox/card/payment/verify/v2/{$txnRef}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('Zainpay verification failed', [
                'txnRef' => $txnRef,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'Verification request failed'
            ];
        } catch (\Exception $e) {
            Log::error('Zainpay verification error', [
                'txnRef' => $txnRef,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify a virtual account deposit/transfer using the transaction reference.
     */
    public function verifyDeposit(string $txnRef): array
    {
        try {
            // Try v2 deposit verification first
            $response = Http::withToken($this->privateKey)
                ->get("{$this->baseUrl}/virtual-account/wallet/deposit/verify/v2/{$txnRef}");

            // Fallback to non-v2 on 404/405
            if ($response->status() === 404 || $response->status() === 405) {
                $response = Http::withToken($this->privateKey)
                    ->get("{$this->baseUrl}/virtual-account/wallet/deposit/verify/{$txnRef}");
            }

            $body = $response->body();
            $data = $response->json() ?? [];

            // Log the full raw response so we can debug what Zainpay returns
            Log::info('Zainpay Deposit verification raw response', [
                'txnRef'  => $txnRef,
                'status'  => $response->status(),
                'headers' => $response->headers(),
                'body'    => $body,
            ]);

            if ($response->successful()) {
                // Zainpay commonly returns code "00" for success
                $code          = $data['code'] ?? $data['data']['code'] ?? '';
                $paymentStatus = strtolower($data['status'] ?? $data['data']['status'] ?? '');

                $isSuccess = ($code === '00')
                    || in_array($paymentStatus, ['success', 'successful', 'completed']);

                return [
                    'success' => $isSuccess,
                    'data'    => $data,
                ];
            }

            Log::error('Zainpay Deposit verification failed', [
                'txnRef' => $txnRef,
                'status' => $response->status(),
                'body'   => $body,
            ]);

            // Try to extract a clean message from Zainpay JSON error
            $message = 'Deposit verification request failed.';
            if (isset($data['description'])) {
                $message .= ' Reason: ' . $data['description'];
            } elseif (isset($data['message'])) {
                $message .= ' Reason: ' . $data['message'];
            } elseif (isset($data['error'])) {
                $message .= ' Reason: ' . $data['error'];
            } else {
                $message .= ' (Status: ' . $response->status() . ')';
            }

            return [
                'success' => false,
                'message' => $message,
            ];
        } catch (\Exception $e) {
            Log::error('Zainpay Deposit verification error', [
                'txnRef' => $txnRef,
                'error'  => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'System error during verification.'
            ];
        }
    }

    /**
     * Create a Dynamic Virtual Account for checkout
     */
    public function createDynamicVirtualAccount(array $payload): array
    {
        try {
            // Using the endpoint from Zainpay Docs
            $response = Http::withToken($this->privateKey)
                ->post("{$this->baseUrl}/virtual-account/dynamic/create/request", $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('Zainpay DVA creation failed', [
                'payload' => $payload,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'Virtual account creation failed: ' . $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Zainpay DVA creation error', [
                'payload' => $payload,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
