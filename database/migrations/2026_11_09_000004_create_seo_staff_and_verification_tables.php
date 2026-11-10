<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * SEO Staff and Backlink Verification tables:
     * - staff_profiles
     * - order_assignments
     * - article_reviews
     * - publications
     * - backlink_verifications
     * - backlink_reports
     * - notifications
     */
    public function up(): void
    {
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('designation')->default('SEO Specialist');
            $table->string('contact_number')->nullable();
            $table->timestamps();
        });

        Schema::create('order_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_item_id');
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('assigned'); // assigned, in_progress, completed, reassigned
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('article_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_id');
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->string('review_status')->default('pending'); // pending, approved, rejected, corrections_requested
            $table->text('feedback')->nullable();
            $table->timestamps();
        });

        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_item_id');
            $table->unsignedBigInteger('article_id')->nullable();
            $table->unsignedBigInteger('website_id');
            $table->string('published_url');
            $table->string('status')->default('published'); // published, verification_pending, live
            $table->timestamps();
        });

        Schema::create('backlink_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')->constrained('publications')->cascadeOnDelete();
            $table->string('target_url');
            $table->string('anchor_text');
            $table->string('verification_status')->default('pending'); // pending, verified, failed
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('backlink_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verification_id')->constrained('backlink_verifications')->cascadeOnDelete();
            $table->text('report_details')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('backlink_reports');
        Schema::dropIfExists('backlink_verifications');
        Schema::dropIfExists('publications');
        Schema::dropIfExists('article_reviews');
        Schema::dropIfExists('order_assignments');
        Schema::dropIfExists('staff_profiles');
    }
};
