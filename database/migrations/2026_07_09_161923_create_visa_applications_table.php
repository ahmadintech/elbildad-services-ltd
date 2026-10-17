<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visa_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Section 1: Personal Information
            $table->string('full_name');
            $table->date('dob')->nullable();
            $table->string('nationality')->nullable();
            $table->string('gender')->nullable(); // Male, Female, Other
            $table->string('marital_status')->nullable(); // Single, Married, Divorced, Widowed
            $table->string('occupation');
            $table->string('company_name');
            $table->string('company_address')->nullable();
            $table->string('phone_number');
            $table->string('email_address');
            $table->string('residential_address')->nullable();

            // Section 2: Passport Information
            $table->string('passport_number');
            $table->date('passport_issue_date')->nullable();
            $table->date('passport_expiry_date');
            $table->string('country_of_issuance')->nullable();
            $table->string('passport_data_page_path');
            $table->string('passport_photo_path');

            // Section 3: Travel History
            $table->boolean('visited_china_before')->default(false);
            $table->date('most_recent_china_visit')->nullable();
            $table->integer('visits_in_last_5_years')->nullable()->default(0);
            $table->text('previous_china_visits_dates')->nullable();
            $table->boolean('traveled_other_countries')->default(false);
            $table->text('list_countries_visited')->nullable();
            $table->boolean('attended_trade_fairs')->default(false);

            // Section 4: Visa Details
            $table->string('preferred_visa_type'); 
            // Regular – 3 months Single Entry, Regular – 1 Year Multiple Entry, Virgin / First-time Applicant, Virgin with Travel History (other countries)
            $table->string('purpose_of_visit');
            $table->date('intended_arrival_date')->nullable();
            $table->date('intended_departure_date')->nullable();
            $table->string('cities_to_visit')->nullable();

            // Section 5: Previous Visas & Documents
            $table->boolean('has_previous_chinese_visas')->default(false);
            $table->text('previous_visa_pages_paths')->nullable(); // Can be JSON or comma-separated
            $table->string('invitation_letter_path')->nullable();

            // Internal
            $table->string('status')->default('pending'); // pending, processing, approved, rejected
            $table->foreignUuid('assigned_agent_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa_applications');
    }
};
