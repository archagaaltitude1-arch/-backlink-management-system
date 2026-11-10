<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->renameColumn('role', 'account_type');
            if (! Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken();
            }
            if (! Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable();
            }
        });

        foreach (['customer' => 'Customer', 'admin' => 'Admin', 'staff' => 'Staff', 'seo' => 'SEO'] as $accountType => $roleName) {
            DB::table('roles')->insertOrIgnore(['role_name' => $roleName, 'permissions' => '[]', 'created_at' => now(), 'updated_at' => now()]);
            DB::table('users')->where('account_type', $accountType)->whereNull('role_id')->update([
                'role_id' => DB::table('roles')->where('role_name', $roleName)->value('id'),
            ]);
        }

        if (! Schema::hasTable('seo_metrics')) {
            Schema::create('seo_metrics', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('website_id')->unique()->constrained()->cascadeOnDelete();
                foreach (['DR', 'DA', 'TF', 'CF', 'spam_score'] as $metric) {
                    $table->decimal($metric, 5, 2)->default(0);
                }
                $table->unsignedBigInteger('traffic')->default(0);
                $table->unsignedBigInteger('backlinks')->default(0);
                $table->timestamps();
            });

        }

        if (! Schema::hasTable('website_niches')) {
            Schema::create('website_niches', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('website_id')->constrained()->cascadeOnDelete();
                $table->foreignId('niche_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['website_id', 'niche_id']);
            });

        }

        Schema::table('websites', function (Blueprint $table): void {
            if (! Schema::hasColumn('websites', 'niche')) {
                $table->string('niche', 80)->default('General')->index();
                $table->text('description')->nullable();
                foreach (['domain_rating', 'domain_authority', 'trust_flow', 'page_authority'] as $metric) {
                    $table->decimal($metric, 5, 2)->default(0);
                }
                $table->unsignedBigInteger('monthly_traffic')->default(0);
                $table->decimal('spam_score', 5, 2)->default(0);
                $table->unsignedBigInteger('backlinks')->default(0);
                $table->unsignedBigInteger('referring_domains')->default(0);
            }
        });

        DB::table('websites')->orderBy('id')->chunkById(100, function ($websites): void {
            foreach ($websites as $website) {
                $metrics = DB::table('seo_metrics')->where('website_id', $website->id)->first();
                if ($metrics !== null) {
                    DB::table('websites')->where('id', $website->id)->update([
                        'domain_rating' => $metrics->DR, 'domain_authority' => $metrics->DA,
                        'trust_flow' => $metrics->TF, 'page_authority' => $metrics->CF,
                        'monthly_traffic' => $metrics->traffic, 'spam_score' => $metrics->spam_score,
                        'backlinks' => $metrics->backlinks,
                    ]);
                } else {
                    DB::table('seo_metrics')->insert([
                        'website_id' => $website->id, 'DR' => $website->domain_rating,
                        'DA' => $website->domain_authority, 'TF' => $website->trust_flow,
                        'CF' => $website->page_authority, 'traffic' => $website->monthly_traffic,
                        'spam_score' => $website->spam_score, 'backlinks' => $website->backlinks,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
                $existingNiche = DB::table('website_niches')->join('niches', 'niches.id', '=', 'website_niches.niche_id')
                    ->where('website_id', $website->id)->orderBy('niches.id')->value('niche_name');
                if ($existingNiche !== null) {
                    DB::table('websites')->where('id', $website->id)->update(['niche' => $existingNiche]);

                    continue;
                }
                DB::table('niches')->insertOrIgnore(['niche_name' => $website->niche, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);
                DB::table('website_niches')->insert([
                    'website_id' => $website->id,
                    'niche_id' => DB::table('niches')->where('niche_name', $website->niche)->value('id'),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        });
    }

    /**
     * Restore the legacy role name while preserving imported catalogue and role data.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->renameColumn('account_type', 'role');
        });
    }
};
