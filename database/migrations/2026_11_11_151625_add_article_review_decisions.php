<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tie each admin review, rejection, and approval to a blog.
     */
    public function up(): void
    {
        DB::table('article_reviews')
            ->whereNotIn('article_id', function ($query): void {
                $query->select('id')->from('articles');
            })
            ->delete();

        Schema::table('article_reviews', function (Blueprint $table): void {
            $table->timestamp('reviewed_at')->nullable()->after('feedback');
            $table->index('review_status');
            $table->foreign('article_id')->references('id')->on('articles')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('article_reviews', function (Blueprint $table): void {
            $table->dropForeign(['article_id']);
            $table->dropIndex(['review_status']);
            $table->dropColumn('reviewed_at');
        });
    }
};
