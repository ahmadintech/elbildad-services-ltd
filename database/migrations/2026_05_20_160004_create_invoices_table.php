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
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('rfq_id')->nullable()->constrained('rfqs')->onDelete('set null');
            $table->foreignUuid('estimate_id')->nullable()->constrained('estimates')->onDelete('set null');
            $table->foreignUuid('customer_id')->constrained('users')->onDelete('cascade');
            $table->string('zoho_invoice_id')->unique();
            $table->string('invoice_number');
            $table->string('status')->default('draft'); // InvoiceStatusEnum
            $table->decimal('total', 12, 2);
            $table->decimal('balance_due', 12, 2);
            $table->string('currency')->default('USD');
            $table->date('due_date')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
