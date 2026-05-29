<?php

namespace App\Observers;

use App\Models\User;
use App\Services\Zoho\ZohoContactService;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    protected ZohoContactService $contactService;

    public function __construct(ZohoContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        try {
            $this->contactService->syncCustomer($user);
        } catch (\Exception $e) {
            Log::error("Observer failed to sync created user {$user->id} to Zoho: " . $e->getMessage());
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Avoid infinite loop by only syncing when important contact details change
        if ($user->wasChanged(['name', 'email', 'whatsapp_number'])) {
            try {
                $this->contactService->syncCustomer($user);
            } catch (\Exception $e) {
                Log::error("Observer failed to sync updated user {$user->id} to Zoho: " . $e->getMessage());
            }
        }
    }
}
