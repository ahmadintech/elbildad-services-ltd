<?php

namespace App\Services\Zoho;

use App\Models\User;
use App\Models\Rfq;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Enums\InvoiceStatusEnum;
use Illuminate\Support\Facades\Log;
use App\Exceptions\Zoho\ZohoApiException;

class ZohoInvoiceService
{
    protected ZohoClient $client;
    protected ZohoContactService $contactService;

    public function __construct(ZohoClient $client, ZohoContactService $contactService)
    {
        $this->client = $client;
        $this->contactService = $contactService;
    }

    public function createInvoice(Estimate $estimate): array
    {
        $customer = $estimate->customer;
        if (!$customer || !$customer->zoho_contact_id) {
            throw new ZohoApiException("Customer must have a valid Zoho Contact ID to generate an invoice.");
        }

        // Fetch estimate from Zoho to get its line items
        $zohoEstimate = $this->client->get("/estimates/{$estimate->zoho_estimate_id}");
        $rawLineItems = $zohoEstimate['estimate']['line_items'] ?? [];

        if (empty($rawLineItems)) {
            throw new ZohoApiException("No line items found on the Zoho Estimate.");
        }

        // Format line items to remove read-only fields that cause Zoho API errors
        $formattedItems = [];
        foreach ($rawLineItems as $item) {
            $formattedItem = [
                'name' => $item['name'] ?? '',
                'rate' => $item['rate'] ?? 0,
                'quantity' => $item['quantity'] ?? 0,
                'description' => $item['description'] ?? '',
            ];
            if (!empty($item['item_id'])) {
                $formattedItem['item_id'] = $item['item_id'];
            }
            $formattedItems[] = $formattedItem;
        }

        $payload = [
            'customer_id' => $customer->zoho_contact_id,
            'estimate_id' => $estimate->zoho_estimate_id,
            'date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'line_items' => $formattedItems,
        ];

        $response = $this->client->post('/invoices?estimate_id=' . $estimate->zoho_estimate_id, $payload);
        $zohoInvoiceId = $response['invoice']['invoice_id'] ?? null;

        if (!$zohoInvoiceId) {
            throw new ZohoApiException("Failed to retrieve invoice_id from Zoho response.");
        }

        $localInvoice = Invoice::create([
            'rfq_id' => $estimate->rfq_id,
            'estimate_id' => $estimate->id,
            'customer_id' => $customer->id,
            'zoho_invoice_id' => $zohoInvoiceId,
            'invoice_number' => $response['invoice']['invoice_number'] ?? ('INV-' . rand(1000, 9999)),
            'status' => InvoiceStatusEnum::DRAFT,
            'total' => $estimate->total,
            'balance_due' => $estimate->total,
            'currency' => $estimate->currency,
            'due_date' => now()->addDays(14),
            'issued_at' => now(),
        ]);

        return array_merge($response['invoice'], ['local_id' => $localInvoice->id]);
    }

    public function createInvoiceFromRfq(Rfq $rfq, array $lineItems): array
    {
        $customer = $rfq->customer;
        if (!$customer) {
            throw new ZohoApiException("RFQ has no associated customer.");
        }

        if (!$customer->zoho_contact_id) {
            $this->contactService->syncCustomer($customer);
            $customer->refresh();
        }
        
        if (!$customer->zoho_contact_id) {
            throw new ZohoApiException("Failed to obtain valid Zoho Contact ID for customer {$customer->id}.");
        }

        $zohoLineItems = [];
        $total = 0.0;
        foreach ($lineItems as $item) {
            $lineItem = [
                'name' => $item['name'],
                'rate' => (float)$item['rate'],
                'quantity' => (float)$item['quantity'],
                'description' => $item['description'] ?? '',
            ];
            if (!empty($item['zoho_item_id'])) {
                $lineItem['item_id'] = $item['zoho_item_id'];
            }
            $zohoLineItems[] = $lineItem;
            $total += (float)$item['rate'] * (float)$item['quantity'];
        }

        $payload = [
            'customer_id' => (string)$customer->zoho_contact_id,
            'line_items' => $zohoLineItems,
            'notes' => 'Generated directly from RFQ #' . $rfq->product_name,
            'date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
        ];

        Log::debug("Creating invoice from RFQ with payload", ['customer_id' => $customer->zoho_contact_id, 'customer_local_id' => $customer->id]);
        $response = $this->client->post('/invoices', $payload);
        $zohoInvoiceId = $response['invoice']['invoice_id'] ?? null;

        if (!$zohoInvoiceId) {
            throw new ZohoApiException("Failed to retrieve invoice_id from Zoho response.");
        }

        $localInvoice = Invoice::create([
            'rfq_id' => $rfq->id,
            'estimate_id' => null,
            'customer_id' => $customer->id,
            'zoho_invoice_id' => $zohoInvoiceId,
            'invoice_number' => $response['invoice']['invoice_number'] ?? ('INV-' . rand(1000, 9999)),
            'status' => InvoiceStatusEnum::DRAFT,
            'total' => $total,
            'balance_due' => $total,
            'currency' => 'USD',
            'due_date' => now()->addDays(14),
            'issued_at' => now(),
        ]);

        return array_merge($response['invoice'], ['local_id' => $localInvoice->id]);
    }

    public function createManualInvoice(User $customer, array $lineItems, array $options = []): array
    {
        if (!$customer->zoho_contact_id) {
            $this->contactService->syncCustomer($customer);
            $customer->refresh();
        }
        
        if (!$customer->zoho_contact_id) {
            throw new ZohoApiException("Failed to obtain valid Zoho Contact ID for customer {$customer->id}.");
        }

        $zohoLineItems = [];
        $total = 0.0;
        foreach ($lineItems as $item) {
            $lineItem = [
                'name' => $item['name'],
                'rate' => (float)$item['rate'],
                'quantity' => (float)$item['quantity'],
                'description' => $item['description'] ?? '',
            ];
            if (!empty($item['zoho_item_id'])) {
                $lineItem['item_id'] = $item['zoho_item_id'];
            }
            $zohoLineItems[] = $lineItem;
            $total += (float)$item['rate'] * (float)$item['quantity'];
        }

        $payload = [
            'customer_id' => (string)$customer->zoho_contact_id,
            'line_items' => $zohoLineItems,
            'notes' => $options['notes'] ?? 'Manual Invoice',
            'date' => $options['date'] ?? now()->format('Y-m-d'),
            'due_date' => $options['due_date'] ?? now()->addDays(14)->format('Y-m-d'),
        ];

        Log::debug("Creating manual invoice with payload", ['customer_id' => $customer->zoho_contact_id, 'customer_local_id' => $customer->id]);
        $response = $this->client->post('/invoices', $payload);
        $zohoInvoiceId = $response['invoice']['invoice_id'] ?? null;

        if (!$zohoInvoiceId) {
            throw new ZohoApiException("Failed to retrieve invoice_id from Zoho response.");
        }

        $localInvoice = Invoice::create([
            'rfq_id' => null,
            'estimate_id' => null,
            'customer_id' => $customer->id,
            'zoho_invoice_id' => $zohoInvoiceId,
            'invoice_number' => $response['invoice']['invoice_number'] ?? ('INV-' . rand(1000, 9999)),
            'status' => InvoiceStatusEnum::DRAFT,
            'total' => $total,
            'balance_due' => $total,
            'currency' => 'USD',
            'due_date' => $payload['due_date'],
            'issued_at' => now(),
        ]);

        return array_merge($response['invoice'], ['local_id' => $localInvoice->id]);
    }

    public function getInvoicePdf(string $zohoInvoiceId): string
    {
        return $this->client->getRaw("/invoices/{$zohoInvoiceId}", ['accept' => 'pdf'], ['Accept' => 'application/pdf']);
    }

    public function sendInvoice(string $zohoInvoiceId): bool
    {
        $this->client->post("/invoices/{$zohoInvoiceId}/status/sent");
        
        Invoice::where('zoho_invoice_id', $zohoInvoiceId)->update([
            'status' => InvoiceStatusEnum::SENT
        ]);

        return true;
    }

    public function voidInvoice(string $zohoInvoiceId): bool
    {
        $this->client->post("/invoices/{$zohoInvoiceId}/status/void");
        
        Invoice::where('zoho_invoice_id', $zohoInvoiceId)->update([
            'status' => InvoiceStatusEnum::VOID,
            'balance_due' => 0.0
        ]);

        return true;
    }

    public function markAsSent(string $zohoInvoiceId): bool
    {
        return $this->sendInvoice($zohoInvoiceId);
    }

    public function listInvoices(array $filters = []): array
    {
        $response = $this->client->get('/invoices', $filters);
        return $response['invoices'] ?? [];
    }

    public function getInvoice(string $zohoInvoiceId): array
    {
        $response = $this->client->get("/invoices/{$zohoInvoiceId}");
        return $response['invoice'] ?? [];
    }

    public function recordPayment(string $zohoInvoiceId, float $amount, string $date, string $paymentMode): array
    {
        $localInvoice = Invoice::where('zoho_invoice_id', $zohoInvoiceId)->first();
        if (!$localInvoice) {
            throw new ZohoApiException("Local invoice not found for Zoho ID: " . $zohoInvoiceId);
        }

        $customer = $localInvoice->customer;
        if (!$customer || !$customer->zoho_contact_id) {
            throw new ZohoApiException("Customer missing valid Zoho Contact ID for payment.");
        }

        $payload = [
            'customer_id' => $customer->zoho_contact_id,
            'payment_mode' => $paymentMode,
            'amount' => $amount,
            'date' => $date,
            'invoices' => [
                [
                    'invoice_id' => $zohoInvoiceId,
                    'amount_applied' => $amount
                ]
            ]
        ];

        $response = $this->client->post('/customerpayments', $payload);
        
        $newBalance = max(0.0, (float)$localInvoice->balance_due - $amount);
        $newStatus = $newBalance <= 0.0 ? InvoiceStatusEnum::PAID : InvoiceStatusEnum::PARTIALLY_PAID;
        
        $localInvoice->update([
            'balance_due' => $newBalance,
            'status' => $newStatus,
        ]);

        $enumValue = match (strtolower($paymentMode)) {
            'cash' => \App\Enums\PaymentModeEnum::CASH,
            'bank remittance', 'bank transfer' => \App\Enums\PaymentModeEnum::BANK_TRANSFER,
            'credit card', 'card' => \App\Enums\PaymentModeEnum::CARD,
            'usdt' => \App\Enums\PaymentModeEnum::USDT,
            default => \App\Enums\PaymentModeEnum::OTHER,
        };

        \App\Models\Payment::create([
            'invoice_id' => $localInvoice->id,
            'customer_id' => $localInvoice->customer_id,
            'zoho_payment_id' => $response['payment']['payment_id'] ?? ('PAY-' . rand(1000, 9999)),
            'amount' => $amount,
            'payment_mode' => $enumValue,
            'paid_at' => $date,
            'reference_number' => $response['payment']['reference_number'] ?? null,
        ]);

        return $response['payment'] ?? [];
    }
}
