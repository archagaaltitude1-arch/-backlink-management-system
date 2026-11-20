<?php

namespace Tests\Feature;

use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RefreshPublisherBacklinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_refresh_stores_rank_traffic_and_spam_for_the_niche_filters(): void
    {
        config([
            'services.dataforseo.login' => 'login',
            'services.dataforseo.password' => 'secret',
        ]);
        $website = Website::query()->create([
            'website_name' => 'Live Metrics',
            'domain' => 'livemetrics.example',
            'url' => 'https://livemetrics.example',
            'niche' => 'Technology',
            'domain_rating' => 10,
            'domain_authority' => 10,
            'trust_flow' => 10,
            'page_authority' => 10,
            'monthly_traffic' => 100,
            'spam_score' => 20,
            'price' => 80,
            'status' => 'active',
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'https://api.dataforseo.com/v3/backlinks/summary/live' => Http::response([
                'tasks' => [[
                    'status_code' => 20000,
                    'result' => [[
                        'backlinks' => 333179,
                        'referring_domains' => 5204,
                        'rank' => 405,
                        'target_spam_score' => 4,
                    ]],
                ]],
            ]),
            'https://api.dataforseo.com/v3/dataforseo_labs/google/domain_rank_overview/live' => Http::response([
                'tasks' => [[
                    'status_code' => 20000,
                    'result' => [[
                        'items' => [[
                            'metrics' => ['organic' => ['etv' => 16789.435]],
                        ]],
                    ]],
                ]],
            ]),
        ]);

        $this->artisan('publishers:refresh-backlinks', ['domain' => 'livemetrics.example'])->assertSuccessful();

        $website->refresh();
        $this->assertSame(41, $website->domain_rating);
        $this->assertSame(16789, $website->monthly_traffic);
        $this->assertSame(333179, $website->backlinks);
        $this->assertSame(5204, $website->referring_domains);
        $this->assertSame('4.0', $website->spam_score);
        $this->assertNotNull($website->backlinks_refreshed_at);
    }

    public function test_failed_dataforseo_response_leaves_stored_metrics_unchanged(): void
    {
        config([
            'services.dataforseo.login' => 'login',
            'services.dataforseo.password' => 'secret',
        ]);
        $website = Website::query()->create([
            'website_name' => 'Kept Metrics',
            'domain' => 'keptmetrics.example',
            'url' => 'https://keptmetrics.example',
            'niche' => 'Technology',
            'domain_rating' => 44,
            'domain_authority' => 30,
            'trust_flow' => 20,
            'page_authority' => 18,
            'monthly_traffic' => 18000,
            'spam_score' => 6,
            'price' => 90,
            'status' => 'active',
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'https://api.dataforseo.com/*' => Http::response([
                'tasks' => [['status_code' => 40501, 'result' => null]],
            ]),
        ]);

        $this->artisan('publishers:refresh-backlinks', ['domain' => 'keptmetrics.example'])->assertFailed();

        $website->refresh();
        $this->assertSame(44, $website->domain_rating);
        $this->assertSame(18000, $website->monthly_traffic);
        $this->assertNull($website->backlinks_refreshed_at);
    }

    public function test_niche_filter_row_keeps_refreshed_metrics(): void
    {
        config(['marketplace.demo' => false]);
        $website = Website::query()->create([
            'website_name' => 'Filter Metrics',
            'domain' => 'filtermetrics.example',
            'url' => 'https://filtermetrics.example',
            'niche' => 'Technology',
            'domain_rating' => 41,
            'domain_authority' => 30,
            'trust_flow' => 20,
            'page_authority' => 18,
            'monthly_traffic' => 16789,
            'spam_score' => 4,
            'backlinks' => 333179,
            'referring_domains' => 5204,
            'price' => 80,
            'status' => 'active',
        ]);
        $website->backlinks_refreshed_at = now();
        $website->save();

        $this->get(route('publishers.index'))
            ->assertOk()
            ->assertSee('data-dr="41"', false)
            ->assertSee('data-traffic="16789"', false)
            ->assertSee('data-spam="4"', false)
            ->assertSee('data-backlinks="333179"', false)
            ->assertSee('data-referring="5204"', false);
    }
}
