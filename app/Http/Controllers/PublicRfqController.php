<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitRfqRequest;
use App\Services\Rfq\RfqService;
use Illuminate\Http\JsonResponse;

use App\Models\Rfq;
use App\Models\User;
use App\Models\AgentRfqAssignment;
use App\Jobs\ProcessRfqWithAI;
use App\Events\RfqQueued;
use App\Enums\RfqStatusEnum;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeUserEmail;
use App\Mail\RfqReceivedEmail;

class PublicRfqController extends Controller
{
    public function submit(SubmitRfqRequest $request)
    {
        $data = $request->validated();
        
        // If a user is logged in but they are not a customer (e.g. an admin testing the form), 
        // we should not use their account. We want to find/create the actual customer.
        $user = auth()->user();
        if ($user && !$user->hasRole('customer')) {
            $user = null;
        }

        if (!$user) {
            // Try to find by WhatsApp first (including soft deleted)
            $user = User::withTrashed()->where('whatsapp_number', $data['whatsapp_number'])->first();
            
            // If not found by WhatsApp, try by email (if provided)
            if (!$user && !empty($data['email'])) {
                $user = User::withTrashed()->where('email', $data['email'])->first();
            }

            if ($user && $user->trashed()) {
                $user->restore();
            }

            if (!$user) {
                $randomPassword = Str::random(10);
                $user = User::create([
                    'name' => $data['full_name'],
                    'whatsapp_number' => $data['whatsapp_number'],
                    'email' => $data['email'] ?? null,
                    'password' => bcrypt($randomPassword),
                ]);
                
                $user->assignRole('customer');
                Log::info("WhatsApp Stub: Account created for {$user->whatsapp_number}. Password: {$randomPassword}");

                if ($user->email) {
                    Mail::to($user->email)->send(new WelcomeUserEmail($user, $randomPassword));
                }
            }
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('rfq_images', 'public');
        }

        $rfq = Rfq::create([
            'customer_id' => $user->id,
            'product_name' => $data['product_name'],
            'specifications' => $data['specifications'],
            'additional_requirements' => $data['additional_requirements'] ?? null,
            'quantity' => $data['quantity'],
            'delivery_method' => $data['delivery_method'],
            'target_price' => $data['target_price'] ?? null,
            'location' => $data['location'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'status' => RfqStatusEnum::PENDING->value,
            'tracking_token' => Str::uuid()->toString(),
            'image_path' => $imagePath,
        ]);

        ProcessRfqWithAI::dispatch($rfq);

        if ($user->email) {
            Mail::to($user->email)->send(new RfqReceivedEmail($rfq));
        }

        return redirect()->route('rfq.track', $rfq->tracking_token)
            ->with('success', 'RFQ submitted successfully! Your tracking token is: ' . $rfq->tracking_token);
    }
}
