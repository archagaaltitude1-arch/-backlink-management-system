<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['marketplace.demo' => true]);
    }

    public function test_site_lists_show_niche_specifications_above_the_chart(): void
    {
        $premium = $this->get(route('sites.show', 'premium-guest-posting-sites'));

        $premium->assertOk()
            ->assertDontSee('>Preview</h2>', false)
            ->assertSee('Search by niche', false)
            ->assertSee('Search by website or niche', false)
            ->assertSee('Semrush traffic', false)
            ->assertSee('Sports/Gaming allowed?', false)
            ->assertSee('No. of referring domains', false)
            ->assertSee('data-live-chart', false);

        $this->get(route('sites.show', 'link-insertions-niche-edits'))
            ->assertOk()
            ->assertDontSee('>Preview</h2>', false)
            ->assertSee('Search by niche', false)
            ->assertSee('Semrush traffic', false)
            ->assertSee('Foreign lang. allowed?', false);

        $this->get(route('sites.show', 'favorite-websites'))
            ->assertOk()
            ->assertDontSee('>Preview</h2>', false)
            ->assertSee('Open favorite websites');
    }
}
