<?php

namespace App\Services\Zoho;

class ZohoTaxService
{
    protected ZohoClient $client;

    public function __construct(ZohoClient $client)
    {
        $this->client = $client;
    }

    public function listTaxes(): array
    {
        try {
            $response = $this->client->get('/settings/taxes');
            return $response['taxes'] ?? [];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to fetch Zoho taxes: " . $e->getMessage());
            return [];
        }
    }
}
