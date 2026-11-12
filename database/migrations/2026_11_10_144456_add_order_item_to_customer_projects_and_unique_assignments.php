<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Link each published backlink to one order item, and allow one staff assignment per item.
     */
    public function up(): void
    {
        Schema::table('customer_projects', function (Blueprint $table): void {
            $table->foreignId('order_item_id')->nullable()->after('order_id')->constrained('order_items')->nullOnDelete();
        });

        Schema::table('order_assignments', function (Blueprint $table): void {
            $table->unique('order_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_assignments', function (Blueprint $table): void {
            $table->dropUnique(['order_item_id']);
        });

        Schema::table('customer_projects', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('order_item_id');
        });
    }
};
