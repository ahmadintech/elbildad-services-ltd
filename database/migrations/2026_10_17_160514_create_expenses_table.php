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
        Schema::create('expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('expense_number', 50)->unique();
            $table->enum('category', ['purchase', 'shipment', 'running_shipment', 'salary', 'operational'])->default('purchase');
            $table->string('title');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('NGN');
            
            // Shipment & RFQ linkage
            $table->foreignUuid('rfq_id')->nullable()->constrained('rfqs')->onDelete('set null');
            $table->string('item_number', 100)->nullable();
            $table->string('shipping_method', 50)->nullable(); // Air Freight, Sea Cargo, Express, etc.
            
            // Purchase / Vendor
            $table->string('vendor_name')->nullable();
            
            // Salary & Staff linkage
            $table->foreignUuid('agent_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('salary_period', 50)->nullable(); // e.g. "October 2026"
            
            // Operations & Running Shipment notes
            $table->text('note')->nullable();
            
            // Accounting metadata
            $table->date('expense_date');
            $table->string('payment_method', 50)->default('bank_transfer'); // bank_transfer, cash, pos, online
            $table->string('receipt_path')->nullable();
            $table->enum('status', ['paid', 'pending', 'approved'])->default('paid');
            $table->foreignUuid('recorded_by')->constrained('users')->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();

            $table->index('category');
            $table->index('expense_date');
            $table->index('rfq_id');
            $table->index('agent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
