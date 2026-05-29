<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            // Separate supplier arrays from each AI provider
            $table->json('claude_suppliers')->nullable()->after('ai_suppliers');
            $table->json('qwen_suppliers')->nullable()->after('claude_suppliers');
            // Single best-pick supplier selected by comparing both AI responses
            $table->json('best_supplier')->nullable()->after('qwen_suppliers');
        });
    }

    public function down(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            $table->dropColumn(['claude_suppliers', 'qwen_suppliers', 'best_supplier']);
        });
    }
};
