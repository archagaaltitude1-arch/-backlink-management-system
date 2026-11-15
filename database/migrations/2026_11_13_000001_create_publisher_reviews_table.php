<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('publisher_reviews')) {
            return;
        }

        Schema::create('publisher_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('website_id')->nullable();
            $table->string('demo_domain')->nullable();
            $table->unsignedTinyInteger('rating');
            $table->string('title')->nullable();
            $table->text('comment');
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->unique(['user_id', 'website_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publisher_reviews');
    }
};
