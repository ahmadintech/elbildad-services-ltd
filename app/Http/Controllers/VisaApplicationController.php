<?php

namespace App\Http\Controllers;

use App\Models\VisaApplication;
use App\Mail\VisaApplicationReceivedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class VisaApplicationController extends Controller
{
    public function create()
    {
        return Inertia::render('Visa/Apply');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Section 1
            'full_name' => 'required|string|max:255',
            'dob' => 'nullable|date',
            'nationality' => 'nullable|string|max:255',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'marital_status' => 'nullable|string|in:Single,Married,Divorced,Widowed',
            'occupation' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string|max:255',
            'phone_number' => 'required|string|max:50',
            'email_address' => 'required|email|max:255',
            'residential_address' => 'nullable|string|max:255',
            // Section 2
            'passport_number' => 'required|string|max:255',
            'passport_issue_date' => 'nullable|date',
            'passport_expiry_date' => 'required|date',
            'country_of_issuance' => 'nullable|string|max:255',
            'passport_data_page' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'passport_photo' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            // Section 3
            'visited_china_before' => 'required|boolean',
            'most_recent_china_visit' => 'nullable|date',
            'visits_in_last_5_years' => 'nullable|integer',
            'previous_china_visits_dates' => 'nullable|string',
            'traveled_other_countries' => 'required|boolean',
            'list_countries_visited' => 'nullable|string',
            'attended_trade_fairs' => 'required|boolean',
            // Section 4
            'preferred_visa_type' => 'required|string',
            'purpose_of_visit' => 'required|string',
            'intended_arrival_date' => 'nullable|date',
            'intended_departure_date' => 'nullable|date',
            'cities_to_visit' => 'nullable|string',
            // Section 5
            'has_previous_chinese_visas' => 'required|boolean',
            'previous_visa_pages' => 'nullable|array',
            'previous_visa_pages.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048',
            'invitation_letter' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            // Declaration
            'declaration_confirmed' => 'required|accepted',
        ]);

        $data = $validated;
        unset($data['declaration_confirmed']);
        unset($data['passport_data_page']);
        unset($data['passport_photo']);
        unset($data['previous_visa_pages']);
        unset($data['invitation_letter']);

        // File uploads
        if ($request->hasFile('passport_data_page')) {
            $data['passport_data_page_path'] = $request->file('passport_data_page')->store('visa/passports', 'public');
        }
        if ($request->hasFile('passport_photo')) {
            $data['passport_photo_path'] = $request->file('passport_photo')->store('visa/photos', 'public');
        }
        if ($request->hasFile('invitation_letter')) {
            $data['invitation_letter_path'] = $request->file('invitation_letter')->store('visa/invitations', 'public');
        }
        
        $previousVisaPaths = [];
        if ($request->hasFile('previous_visa_pages')) {
            foreach ($request->file('previous_visa_pages') as $file) {
                $previousVisaPaths[] = $file->store('visa/previous', 'public');
            }
        }
        $data['previous_visa_pages_paths'] = $previousVisaPaths;

        $data['status'] = 'pending';

        $application = VisaApplication::create($data);

        // Send confirmation email to applicant
        if (!empty($data['email_address'])) {
            Mail::to($data['email_address'])->send(new VisaApplicationReceivedMail($application));
        }

        return redirect()->route('visa.success');
    }

    public function success()
    {
        return Inertia::render('Visa/Success');
    }

    public function updateStatus(Request $request, VisaApplication $visaApplication)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,assigned,processing,approved,rejected',
            'assigned_agent_id' => 'nullable|exists:users,id',
        ]);

        $visaApplication->update($validated);

        return back()->with('success', 'Application status updated successfully.');
    }
}
