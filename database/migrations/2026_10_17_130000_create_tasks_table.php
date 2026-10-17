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
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('general'); // talk, rfq_followup, meeting, supplier_contact, general
            $table->string('status')->default('pending'); // pending, in_progress, done, stuck
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete(); // Creator
            $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // Assigned agent
            $table->foreignUuid('rfq_id')->nullable()->constrained('rfqs')->nullOnDelete(); // Linked RFQ (optional)

            $table->string('contact_name')->nullable(); // e.g. client or supplier name to talk with
            $table->string('contact_phone')->nullable(); // phone/whatsapp number to call

            $table->timestamp('due_at')->nullable(); // Scheduled date & time
            $table->timestamp('reminder_at')->nullable(); // Alert trigger timestamp
            $table->timestamp('reminder_sent_at')->nullable(); // When reminder was delivered/acknowledged
            
            $table->timestamp('completed_at')->nullable();
            $table->foreignUuid('completed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->integer('duration_minutes')->nullable()->default(0); // Total timing spent in minutes
            $table->timestamp('timing_started_at')->nullable(); // If live timer is running
            $table->text('notes')->nullable(); // Result/notes after call or completion

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
