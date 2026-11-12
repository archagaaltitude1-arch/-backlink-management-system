<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table): void {
                $table->id();
                $table->string('role_name', 100)->unique();
                $table->json('permissions')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table): void {
                $table->id();
                $table->string('permission_name', 150)->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('services')) {
            Schema::create('services', function (Blueprint $table): void {
                $table->id();
                $table->string('service_name', 150);
                $table->text('description')->nullable();
                $table->decimal('base_price', 10, 2)->default(0);
                $table->unsignedInteger('credit_cost')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('niches')) {
            Schema::create('niches', function (Blueprint $table): void {
                $table->id();
                $table->string('niche_name', 150)->unique();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('packages')) {
            Schema::create('packages', function (Blueprint $table): void {
                $table->id();
                $table->string('package_name', 150);
                $table->decimal('price', 10, 2)->default(0);
                $table->unsignedInteger('credit_amount')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('credit_pricing')) {
            Schema::create('credit_pricing', function (Blueprint $table): void {
                $table->id();
                $table->unsignedInteger('credit_amount');
                $table->decimal('price', 10, 2);
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('niche_specifications')) {
            Schema::create('niche_specifications', function (Blueprint $table): void {
                $table->id();
                $table->string('specification_name', 150);
                $table->enum('field_type', ['number', 'text', 'boolean', 'select'])->default('number');
                $table->decimal('min_value', 12, 2)->nullable();
                $table->decimal('max_value', 12, 2)->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

    }

    /**
     * Preserve catalogue data because these tables may predate Laravel migrations.
     */
    public function down(): void {}
};
