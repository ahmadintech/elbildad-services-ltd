<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('expenses')) {
            // Update MySQL enum to include 'running_cost' alongside 'running_shipment'
            DB::statement("ALTER TABLE `expenses` MODIFY COLUMN `category` ENUM('purchase', 'shipment', 'running_shipment', 'running_cost', 'salary', 'operational') NOT NULL DEFAULT 'purchase'");

            // Update existing running_shipment records to running_cost
            DB::table('expenses')
                ->where('category', 'running_shipment')
                ->update(['category' => 'running_cost']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('expenses')) {
            DB::table('expenses')
                ->where('category', 'running_cost')
                ->update(['category' => 'running_shipment']);

            DB::statement("ALTER TABLE `expenses` MODIFY COLUMN `category` ENUM('purchase', 'shipment', 'running_shipment', 'salary', 'operational') NOT NULL DEFAULT 'purchase'");
        }
    }
};
