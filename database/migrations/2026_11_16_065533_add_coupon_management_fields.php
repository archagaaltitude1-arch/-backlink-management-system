<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table): void {
            $table->decimal('maximum_discount_amount', 10, 2)->nullable()->after('discount_value');
            $table->timestamp('starts_at')->nullable()->after('minimum_order_amount');
            $table->json('applicable_packages')->nullable()->after('starts_at');
            $table->string('purchase_type')->default('both')->after('applicable_packages');
        });

        Schema::create('coupon_email_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->string('email');
            $table->timestamp('sent_at');
            $table->timestamps();
            $table->index(['coupon_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_email_logs');

        Schema::table('coupons', function (Blueprint $table): void {
            $table->dropColumn([
                'maximum_discount_amount',
                'starts_at',
                'applicable_packages',
                'purchase_type',
            ]);
        });
    }
};
