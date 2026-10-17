<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisaApplication extends AbstractModel
{
    use SoftDeletes;

    protected $fillable = [
        'full_name', 'dob', 'nationality', 'gender', 'marital_status',
        'occupation', 'company_name', 'company_address', 'phone_number',
        'email_address', 'residential_address',
        'passport_number', 'passport_issue_date', 'passport_expiry_date',
        'country_of_issuance', 'passport_data_page_path', 'passport_photo_path',
        'visited_china_before', 'most_recent_china_visit', 'visits_in_last_5_years',
        'previous_china_visits_dates', 'traveled_other_countries', 'list_countries_visited',
        'attended_trade_fairs', 'preferred_visa_type', 'purpose_of_visit',
        'intended_arrival_date', 'intended_departure_date', 'cities_to_visit',
        'has_previous_chinese_visas', 'previous_visa_pages_paths', 'invitation_letter_path',
        'status', 'assigned_agent_id',
    ];

    protected $casts = [
        'dob' => 'date',
        'passport_issue_date' => 'date',
        'passport_expiry_date' => 'date',
        'most_recent_china_visit' => 'date',
        'intended_arrival_date' => 'date',
        'intended_departure_date' => 'date',
        'visited_china_before' => 'boolean',
        'traveled_other_countries' => 'boolean',
        'attended_trade_fairs' => 'boolean',
        'has_previous_chinese_visas' => 'boolean',
        'previous_visa_pages_paths' => 'array',
    ];

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }
}
