<?php

use App\Services\PermissionRegistry;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        PermissionRegistry::syncAll();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op to preserve permission definitions
    }
};
