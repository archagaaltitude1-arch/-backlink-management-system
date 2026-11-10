<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('customer')->index();
            $table->string('status')->default('active')->index();
            $table->string('phone', 40)->nullable();
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->string('coupon_code')->nullable();
            $table->decimal('coupon_discount', 10, 2)->default(0);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedInteger('credit_cost')->default(1);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('coupon_code')->nullable();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedInteger('credit_cost')->default(1);
        });

        Schema::create('credit_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('credits');
            $table->decimal('price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('available_balance')->default(0);
            $table->unsignedInteger('reserved_balance')->default(0);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('credit_package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('transaction_id')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method');
            $table->string('gateway')->default('demo');
            $table->string('status')->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'created_at']);
        });

        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('transaction_type');
            $table->unsignedInteger('amount');
            $table->string('description');
            $table->timestamps();
            $table->index(['wallet_id', 'created_at']);
        });

        Schema::create('credit_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('reserved_credits');
            $table->string('status')->default('reserved');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->unique(['wallet_id', 'order_item_id']);
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('discount_type');
            $table->decimal('discount_value', 10, 2);
            $table->decimal('minimum_order_amount', 10, 2)->default(0);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('per_customer_limit')->default(1);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('discount_amount', 10, 2);
            $table->timestamps();
            $table->index(['coupon_id', 'customer_id']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->decimal('total_amount', 10, 2);
            $table->timestamp('issued_at');
            $table->timestamps();
        });

        Schema::create('billing_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->string('label', 80);
            $table->string('full_name');
            $table->string('company')->nullable();
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code', 30);
            $table->string('country', 2);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->string('label');
            $table->string('brand', 40);
            $table->string('last_four', 4);
            $table->string('token_reference')->unique();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('saved_websites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('website_id')->nullable();
            $table->string('website_name');
            $table->string('domain');
            $table->string('url');
            $table->unsignedSmallInteger('domain_rating')->nullable();
            $table->unsignedInteger('monthly_traffic')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamps();
            $table->unique(['customer_id', 'domain']);
        });

        Schema::create('customer_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('status')->default('planning');
            $table->text('target_url')->nullable();
            $table->text('published_url')->nullable();
            $table->string('verification_status')->default('pending');
            $table->text('report_summary')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_projects');
        Schema::dropIfExists('saved_websites');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('billing_addresses');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('credit_reservations');
        Schema::dropIfExists('credit_transactions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('credit_packages');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'coupon_code']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('credit_cost');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('credit_cost');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['coupon_code', 'coupon_discount']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['status']);
            $table->dropColumn(['role', 'status', 'phone']);
        });
    }
};
