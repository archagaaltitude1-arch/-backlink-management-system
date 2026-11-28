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
        Schema::table('orders', function (Blueprint $table): void {
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
        });

        Schema::table('order_assignments', function (Blueprint $table): void {
            $table->timestamp('in_progress_at')->nullable();
        });

        Schema::table('customer_projects', function (Blueprint $table): void {
            $table->timestamp('published_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_projects', function (Blueprint $table): void {
            $table->dropColumn('published_at');
        });

        Schema::table('order_assignments', function (Blueprint $table): void {
            $table->dropColumn('in_progress_at');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['accepted_at', 'completed_at']);
        });
    }
};
