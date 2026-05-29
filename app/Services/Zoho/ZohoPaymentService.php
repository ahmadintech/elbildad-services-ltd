<?php

namespace App\Services\Zoho;

use App\Models\Invoice;
use App\Models\Payment;
use App\Enums\InvoiceStatusEnum;
use Illuminate\Support\Facades\Log;
use App\Exceptions\Zoho\ZohoApiException;

class ZohoPaymentService
{
    protected ZohoClient $client;

    public function __construct(ZohoClient $client)
    {
        $this->client = $client;
    }

    public function createPayment(Invoice $invoice, array $data): array
    {
        $customer = $invoice->customer;
        if (!$customer || !$customer->zoho_contact_id) {
            throw new ZohoApiException("Customer must be synced with Zoho to record payments.");
        }

        $payload = [
            'customer_id' => $customer->zoho_contact_id,
            'invoice_id' => $invoice->zoho_invoice_id,
            'amount' => (float)$data['amount'],
            'date' => $data['paid_at'] ?? now()->format('Y-m-d'),
            'payment_mode' => $data['payment_mode'] ?? 'cash',
            'reference_number' => $data['reference_number'] ?? null,
        ];

        $response = $this->client->post('/customerpayments', $payload);
        $zohoPaymentId = $response['payment']['payment_id'] ?? null;

        if (!$zohoPaymentId) {
            throw new ZohoApiException("Failed to retrieve payment_id from Zoho response.");
        }

        // Update local Invoice balance
        $newBalance = max(0.0, (float)$invoice->balance_due - (float)$data['amount']);
        $newStatus = $newBalance <= 0.0 ? InvoiceStatusEnum::PAID : InvoiceStatusEnum::PARTIALLY_PAID;
        
        $invoice->update([
            'balance_due' => $newBalance,
            'status' => $newStatus,
        ]);

        $localPayment = Payment::create([
            'invoice_id' => $invoice->id,
            'customer_id' => $customer->id,
            'zoho_payment_id' => $zohoPaymentId,
            'amount' => (float)$data['amount'],
            'payment_mode' => $data['payment_mode'] ?? 'cash',
            'paid_at' => $data['paid_at'] ?? now()->format('Y-m-d'),
            'reference_number' => $data['reference_number'] ?? null,
        ]);

        return array_merge($response['payment'], ['local_id' => $localPayment->id]);
    }

    public function listPayments(string $zohoCustomerId): array
    {
        $response = $this->client->get('/customerpayments', ['customer_id' => $zohoCustomerId]);
        return $response['customerpayments'] ?? [];
    }

    public function deletePayment(string $zohoPaymentId): bool
    {
        $this->client->delete("/customerpayments/{$zohoPaymentId}");
        
        // Remove locally if found
        Payment::where('zoho_payment_id', $zohoPaymentId)->delete();

        return true;
    }
}
