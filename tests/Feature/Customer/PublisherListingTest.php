<?php

namespace Tests\Feature\Customer;

use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublisherListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_publishers_show_sample_filter_values_and_traffic_charts(): void
    {
        config(['marketplace.demo' => false]);

        Website::query()->create([
            'website_name' => 'Ledger Filters',
            'domain' => 'ledgerfilters.example',
            'url' => 'https://ledgerfilters.example',
            'niche' => 'Technology',
            'domain_rating' => 44,
            'domain_authority' => 38,
            'trust_flow' => 20,
            'page_authority' => 22,
            'monthly_traffic' => 18000,
            'price' => 120,
            'status' => 'active',
        ]);

        $publishers = $this->get(route('publishers.index'));

        $publishers->assertOk()
            ->assertSee('ledgerfilters.example')
            ->assertSee('data-live-chart', false)
            ->assertSee('No. of referring domains', false)
            ->assertSee('Spam Score', false)
            ->assertSee('>TF<', false)
            ->assertDontSee('Specifications')
            ->assertSee('sample figures', false)
            ->assertSee('Guest post')
            ->assertSee('Buy now')
            ->assertSee('href="'.route('publish.show', 'signaldesk-technology.example').'"', false)
            ->assertDontSee('farmpulse.example');

        $this->assertMatchesRegularExpression(
            '/<option value="(?:English|Spanish|German|French|Portuguese|Dutch|Italian)">/',
            $publishers->getContent()
        );
        $this->assertMatchesRegularExpression(
            '/<option value="(?:US|UK|DE|FR|IN|AU|BR|ES|NL)"\s*(?:selected)?\s*>/',
            $publishers->getContent()
        );

        $this->get('/niches/technology')
            ->assertOk()
            ->assertSee('ledgerfilters.example')
            ->assertSee('signaldesk-technology.example')
            ->assertSee('lanternlab-technology.example')
            ->assertSee('Buy now')
            ->assertSee('href="'.route('publish.show', 'signaldesk-technology.example').'"', false)
            ->assertSee('data-live-chart', false)
            ->assertSee('No. of referring domains', false)
            ->assertSee('Spam Score', false)
            ->assertSee('>TF<', false)
            ->assertDontSee('Specifications')
            ->assertSee('sample figures', false);
    }
}
