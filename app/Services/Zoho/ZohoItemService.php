<?php

namespace App\Services\Zoho;

use Illuminate\Support\Facades\Log;

class ZohoItemService
{
    protected ZohoClient $client;

    public function __construct(ZohoClient $client)
    {
        $this->client = $client;
    }

    public function createItem(array $data): array
    {
        $payload = [
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'rate' => (float)$data['rate'],
            'unit' => $data['unit'] ?? 'pcs',
            'product_type' => $data['item_type'] ?? 'goods',
        ];
        if (!empty($data['sku'])) {
            $payload['sku'] = $data['sku'];
        }

        $response = $this->client->post('/items', $payload);
        return $response['item'] ?? [];
    }

    public function listItems(): array
    {
        $response = $this->client->get('/items');
        return $response['items'] ?? [];
    }

    public function updateItem(string $zohoItemId, array $data): array
    {
        $payload = [
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'rate' => (float)$data['rate'],
            'unit' => $data['unit'] ?? 'pcs',
            'product_type' => $data['item_type'] ?? 'goods',
        ];
        if (!empty($data['sku'])) {
            $payload['sku'] = $data['sku'];
        }

        $response = $this->client->put("/items/{$zohoItemId}", $payload);
        return $response['item'] ?? [];
    }

    public function deleteItem(string $zohoItemId): bool
    {
        $this->client->delete("/items/{$zohoItemId}");
        return true;
    }

    public function markActive(string $zohoItemId): bool
    {
        $this->client->post("/items/{$zohoItemId}/active");
        return true;
    }

    public function markInactive(string $zohoItemId): bool
    {
        $this->client->post("/items/{$zohoItemId}/inactive");
        return true;
    }

    public function uploadImage(string $zohoItemId, string $localImagePath): bool
    {
        $absolutePath = storage_path('app/public/' . $localImagePath);
        if (!file_exists($absolutePath)) {
            return false;
        }

        $url = rtrim(config('services.zoho.base_url'), '/') . "/items/{$zohoItemId}/image";
        
        $token = app(\App\Services\Zoho\ZohoTokenService::class)->getAccessToken();
        
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => "Zoho-oauthtoken {$token}"
        ])
        ->attach('image', file_get_contents($absolutePath), basename($absolutePath))
        ->post($url . '?organization_id=' . config('services.zoho.organization_id'));

        if ($response->failed()) {
            \Illuminate\Support\Facades\Log::error("Failed to upload image to Zoho Item {$zohoItemId}: " . $response->body());
            return false;
        }

        return true;
    }
}
