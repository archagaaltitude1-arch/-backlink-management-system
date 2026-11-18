<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table): void {
            $table->timestamp('saved_for_later_at')->nullable()->after('expiry_notified_at');
        });

        $now = now();

        DB::table('discount_campaigns')->insertOrIgnore([
            'type' => 'order_reward',
            'is_enabled' => true,
            'discount_type' => 'percentage',
            'discount_value' => 2,
            'minimum_order_amount' => 0,
            'combines_with_package_discount' => true,
            'qualifying_orders' => null,
            'recurring' => false,
            'inactivity_days' => null,
            'expiry_days' => 14,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('discount_campaigns')->where('type', 'order_reward')->delete();

        Schema::table('coupons', function (Blueprint $table): void {
            $table->dropColumn('saved_for_later_at');
        });
    }
};
