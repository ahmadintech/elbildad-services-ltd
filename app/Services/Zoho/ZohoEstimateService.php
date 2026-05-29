<?php

namespace App\Services\Zoho;

use App\Models\User;
use App\Models\Rfq;
use App\Models\Estimate;
use App\Enums\EstimateStatusEnum;
use Illuminate\Support\Facades\Log;
use App\Exceptions\Zoho\ZohoApiException;

class ZohoEstimateService
{
    protected ZohoClient $client;
    protected ZohoContactService $contactService;

    public function __construct(ZohoClient $client, ZohoContactService $contactService)
    {
        $this->client = $client;
        $this->contactService = $contactService;
    }

    public function createEstimate(Rfq $rfq, array $lineItems): array
    {
        $customer = $rfq->customer;
        
        if (!$customer) {
            throw new ZohoApiException("RFQ has no associated customer.");
        }

        if (!$customer->zoho_contact_id) {
            $this->contactService->syncCustomer($customer);
            // Refresh model from DB to get populated zoho_contact_id
            $customer->refresh();
        }
        
        if (!$customer->zoho_contact_id) {
            throw new ZohoApiException("Failed to obtain valid Zoho Contact ID for customer {$customer->id}.");
        }

        // Prepare line items for Zoho
        $zohoLineItems = [];
        $total = 0.0;
        foreach ($lineItems as $item) {
            $lineItem = [
                'name' => $item['name'],
                'rate' => (float)$item['rate'],
                'quantity' => (float)$item['quantity'],
                'description' => $item['description'] ?? '',
            ];
            // Only include item_id if it is a non-empty, non-null, non-false value
            if (isset($item['zoho_item_id']) && $item['zoho_item_id']) {
                $lineItem['item_id'] = $item['zoho_item_id'];
            }
            $zohoLineItems[] = $lineItem;
            $total += (float)$item['rate'] * (float)$item['quantity'];
        }

        Log::debug('Zoho Estimate Payload', ['payload' => [
            'customer_id' => (string)$customer->zoho_contact_id,
            'line_items' => $zohoLineItems
        ]]);

        $payload = [
            'customer_id' => (string)$customer->zoho_contact_id,
            'line_items' => $zohoLineItems,
            'notes' => 'Generated automatically from RFQ #' . $rfq->product_name,
            'valid_date' => now()->addDays(30)->format('Y-m-d'),
        ];

        $response = $this->client->post('/estimates', $payload);
        $zohoEstimateId = $response['estimate']['estimate_id'] ?? null;

        if (!$zohoEstimateId) {
            throw new ZohoApiException("Failed to retrieve estimate_id from Zoho response.");
        }

        // Save local record
        $localEstimate = Estimate::create([
            'rfq_id' => $rfq->id,
            'customer_id' => $customer->id,
            'zoho_estimate_id' => $zohoEstimateId,
            'status' => EstimateStatusEnum::DRAFT,
            'total' => $total,
            'currency' => 'USD',
            'valid_date' => now()->addDays(30),
            'notes' => $payload['notes'],
        ]);

        return array_merge($response['estimate'], ['local_id' => $localEstimate->id]);
    }

    public function createManualEstimate(User $customer, array $lineItems, array $options = []): array
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
            // Only include item_id if it is a non-empty, non-null, non-false value
            if (isset($item['zoho_item_id']) && $item['zoho_item_id']) {
                $lineItem['item_id'] = $item['zoho_item_id'];
            }
            $zohoLineItems[] = $lineItem;
            $total += (float)$item['rate'] * (float)$item['quantity'];
        }

        Log::debug('Zoho Manual Estimate Payload', ['payload' => [
            'customer_id' => (string)$customer->zoho_contact_id,
            'line_items' => $zohoLineItems
        ]]);

        $payload = [
            'customer_id' => (string)$customer->zoho_contact_id,
            'line_items' => $zohoLineItems,
            'notes' => $options['notes'] ?? 'Manual Estimate',
            'valid_date' => $options['valid_date'] ?? now()->addDays(30)->format('Y-m-d'),
        ];

        $response = $this->client->post('/estimates', $payload);
        $zohoEstimateId = $response['estimate']['estimate_id'] ?? null;

        if (!$zohoEstimateId) {
            throw new ZohoApiException("Failed to retrieve estimate_id from Zoho response.");
        }

        $localEstimate = Estimate::create([
            'rfq_id' => null,
            'customer_id' => $customer->id,
            'zoho_estimate_id' => $zohoEstimateId,
            'status' => EstimateStatusEnum::DRAFT,
            'total' => $total,
            'currency' => 'USD',
            'valid_date' => $payload['valid_date'],
            'notes' => $payload['notes'],
        ]);

        return array_merge($response['estimate'], ['local_id' => $localEstimate->id]);
    }

    public function getEstimatePdf(string $zohoEstimateId): string
    {
        return $this->client->getRaw("/estimates/{$zohoEstimateId}", ['accept' => 'pdf'], ['Accept' => 'application/pdf']);
    }

    public function sendEstimate(string $zohoEstimateId): bool
    {
        $this->client->post("/estimates/{$zohoEstimateId}/status/sent");
        
        // Update local status
        Estimate::where('zoho_estimate_id', $zohoEstimateId)->update([
            'status' => EstimateStatusEnum::SENT
        ]);

        return true;
    }

    public function acceptEstimate(string $zohoEstimateId): bool
    {
        $this->client->post("/estimates/{$zohoEstimateId}/status/accepted");
        
        // Update local status
        Estimate::where('zoho_estimate_id', $zohoEstimateId)->update([
            'status' => EstimateStatusEnum::ACCEPTED
        ]);

        return true;
    }

    public function declineEstimate(string $zohoEstimateId): bool
    {
        $this->client->post("/estimates/{$zohoEstimateId}/status/declined");
        
        // Update local status
        Estimate::where('zoho_estimate_id', $zohoEstimateId)->update([
            'status' => EstimateStatusEnum::DECLINED
        ]);

        return true;
    }

    public function listEstimates(array $filters = []): array
    {
        $response = $this->client->get('/estimates', $filters);
        return $response['estimates'] ?? [];
    }

    public function getEstimate(string $zohoEstimateId): array
    {
        $response = $this->client->get("/estimates/{$zohoEstimateId}");
        return $response['estimate'] ?? [];
    }
}
