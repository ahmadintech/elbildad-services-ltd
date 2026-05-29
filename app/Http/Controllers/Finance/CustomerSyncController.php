<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Zoho\ZohoContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class CustomerSyncController extends Controller
{
    protected ZohoContactService $contactService;

    public function __construct(ZohoContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    public function sync(User $user): RedirectResponse
    {
        $this->contactService->syncCustomer($user);

        return Redirect::back()->with('success', "Customer '{$user->name}' was synced to Zoho Contacts successfully.");
    }
}
