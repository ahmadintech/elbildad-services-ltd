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
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'sourcing_company_id')) {
                $table->foreignUuid('sourcing_company_id')->nullable()->after('rfq_id')->constrained('sourcing_companies')->onDelete('set null');
            }
            if (!Schema::hasColumn('invoices', 'supplier_name')) {
                $table->string('supplier_name')->nullable()->after('sourcing_company_id');
            }
            if (!Schema::hasColumn('invoices', 'supplier_contact')) {
                $table->string('supplier_contact')->nullable()->after('supplier_name');
            }
            if (!Schema::hasColumn('invoices', 'supplier_location')) {
                $table->string('supplier_location')->nullable()->after('supplier_contact');
            }
            if (!Schema::hasColumn('invoices', 'supplier_url')) {
                $table->text('supplier_url')->nullable()->after('supplier_location');
            }
            if (!Schema::hasColumn('invoices', 'sourcing_notes')) {
                $table->text('sourcing_notes')->nullable()->after('supplier_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'sourcing_company_id')) {
                $table->dropForeign(['sourcing_company_id']);
                $table->dropColumn('sourcing_company_id');
            }
            $table->dropColumn([
                'supplier_name',
                'supplier_contact',
                'supplier_location',
                'supplier_url',
                'sourcing_notes',
            ]);
        });
    }
};
