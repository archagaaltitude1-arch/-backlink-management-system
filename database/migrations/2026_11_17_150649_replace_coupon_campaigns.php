<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->unique();
            $table->boolean('is_enabled')->default(true);
            $table->string('discount_type')->default('percentage');
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->decimal('minimum_order_amount', 10, 2)->default(0);
            $table->boolean('combines_with_package_discount')->default(true);
            $table->unsignedInteger('qualifying_orders')->nullable();
            $table->boolean('recurring')->default(false);
            $table->unsignedInteger('inactivity_days')->nullable();
            $table->unsignedInteger('expiry_days')->nullable();
            $table->timestamps();
        });

        Schema::create('first_order_redemptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('discount_amount', 10, 2);
            $table->timestamps();
        });

        Schema::table('coupons', function (Blueprint $table): void {
            $table->string('type')->default('promotional')->after('code');
            $table->unsignedInteger('milestone')->nullable()->after('customer_id');
            $table->timestamp('expiry_notified_at')->nullable()->after('expires_at');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('package_discount_amount', 10, 2)->default(0)->after('discount_amount');
            $table->string('offer_type')->nullable()->after('coupon_code');
        });

        $now = now();

        DB::table('discount_campaigns')->insert([
            [
                'type' => 'first_order',
                'is_enabled' => true,
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'minimum_order_amount' => 0,
                'combines_with_package_discount' => true,
                'qualifying_orders' => null,
                'recurring' => false,
                'inactivity_days' => null,
                'expiry_days' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'loyalty',
                'is_enabled' => true,
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'minimum_order_amount' => 0,
                'combines_with_package_discount' => true,
                'qualifying_orders' => 3,
                'recurring' => true,
                'inactivity_days' => null,
                'expiry_days' => 30,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'comeback',
                'is_enabled' => true,
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'minimum_order_amount' => 0,
                'combines_with_package_discount' => true,
                'qualifying_orders' => null,
                'recurring' => false,
                'inactivity_days' => 30,
                'expiry_days' => 14,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['package_discount_amount', 'offer_type']);
        });

        Schema::table('coupons', function (Blueprint $table): void {
            $table->dropColumn(['type', 'milestone', 'expiry_notified_at']);
        });

        Schema::dropIfExists('first_order_redemptions');
        Schema::dropIfExists('discount_campaigns');
    }
};
