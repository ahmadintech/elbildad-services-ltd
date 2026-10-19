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
        // Customizable Notification Settings for Admins
        Schema::create('agent_notification_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('admin_id')->constrained('users')->onDelete('cascade');
            $table->boolean('notify_on_login')->default(true);
            $table->boolean('notify_on_logout')->default(true);
            $table->boolean('notify_on_inactivity')->default(true);
            $table->unsignedInteger('inactivity_hours')->default(24); // e.g. 24 hours
            $table->boolean('exclude_weekends')->default(true); // Exclude Sat & Sun
            $table->boolean('sound_chime')->default(true);
            $table->json('monitored_agents')->nullable(); // null or ["all"] or array of user IDs
            $table->timestamps();

            $table->unique('admin_id');
        });

        // Audit Trail of Agent Logins & Logouts
        Schema::create('agent_activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('agent_id')->constrained('users')->onDelete('cascade');
            $table->enum('event', ['login', 'logout']);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['agent_id', 'event']);
            $table->index('occurred_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_activity_logs');
        Schema::dropIfExists('agent_notification_settings');
    }
};
