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
        Schema::table('services', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active')->after('credit_cost');
        });

        Schema::table('niches', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active')->after('description');
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->unsignedInteger('backlink_quantity')->nullable()->after('credit_amount');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('backlink_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('niches', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['backlink_quantity', 'status']);
        });
    }
};
