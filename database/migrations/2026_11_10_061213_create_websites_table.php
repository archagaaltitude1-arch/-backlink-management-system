<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->string('website_name');
            $table->string('domain')->unique();
            $table->string('url');
            $table->string('niche', 80)->index();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('domain_rating');
            $table->unsignedSmallInteger('domain_authority');
            $table->unsignedSmallInteger('trust_flow');
            $table->unsignedSmallInteger('page_authority');
            $table->unsignedInteger('monthly_traffic');
            $table->decimal('spam_score', 4, 1)->default(0);
            $table->unsignedInteger('backlinks')->default(0);
            $table->unsignedInteger('referring_domains')->default(0);
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('credit_cost')->default(0);
            $table->string('status')->default('active')->index();
            $table->timestamps();

            $table->index(['status', 'domain_rating']);
            $table->index(['status', 'monthly_traffic']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};
