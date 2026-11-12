<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('google_subject')->nullable()->unique();
        });

        Schema::table('coupons', function (Blueprint $table): void {
            $table->foreignId('customer_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('reward_order_id')->nullable()->unique()->constrained('orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table): void {
            $table->dropUnique(['reward_order_id']);
            $table->dropConstrainedForeignId('reward_order_id');
            $table->dropConstrainedForeignId('customer_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['google_subject']);
            $table->dropColumn('google_subject');
        });
    }
};
