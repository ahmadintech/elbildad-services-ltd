<?php

namespace App\Services\Zoho;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Exceptions\Zoho\ZohoApiException;

class ZohoContactService
{
    protected ZohoClient $client;

    public function __construct(ZohoClient $client)
    {
        $this->client = $client;
    }

    public function syncCustomer(User $user): string
    {
        // Require name to sync
        if (!$user->name) {
            throw new ZohoApiException("User name is required to sync with Zoho.");
        }

        $nameParts = explode(' ', trim($user->name));
        $firstName = array_shift($nameParts);
        $lastName = count($nameParts) > 0 ? implode(' ', $nameParts) : $firstName;

        $data = [
            'contact_name' => $user->name,
            'company_name' => $user->name, // Mapped to COMPANY NAME
            'contact_type' => 'customer',
            'status' => 'active', // Ensure they are Active Customers
            'contact_persons' => [
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $user->email,
                    'phone' => $user->whatsapp_number, // Mapped to Work Phone
                    'is_primary_contact' => true,
                ]
            ],
            'billing_address' => [
                'address' => 'Nigeria',
            ]
        ];

        try {
            if ($user->zoho_contact_id) {
                // Update Contact
                $this->client->put("/contacts/{$user->zoho_contact_id}", $data);
                Log::info("Zoho Contact Updated: {$user->zoho_contact_id} for User {$user->id}");
                return $user->zoho_contact_id;
            } else {
                // Create Contact
                $response = $this->client->post('/contacts', $data);
                $zohoContactId = $response['contact']['contact_id'] ?? null;
                
                if ($zohoContactId) {
                    $user->updateQuietly(['zoho_contact_id' => $zohoContactId]);
                    Log::info("Zoho Contact Created: {$zohoContactId} for User {$user->id}");
                    return $zohoContactId;
                }
                
                Log::error("Contact creation response missing contact_id", ['response' => $response, 'user_id' => $user->id]);
                throw new ZohoApiException("Failed to retrieve contact_id from Zoho response.");
            }
        } catch (\Exception $e) {
            Log::error("Failed to sync customer {$user->id} to Zoho: " . $e->getMessage());
            throw $e;
        }
    }
}
