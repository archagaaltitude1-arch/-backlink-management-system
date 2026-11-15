<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryChartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['marketplace.demo' => true]);
    }

    public function test_country_icon_opens_only_that_countrys_chart(): void
    {
        $page = $this->get(route('publishers.index', ['country' => 'AR']));

        $page->assertOk()
            ->assertSee('Argentina publisher websites')
            ->assertSee('agromercado.example')
            ->assertDontSee('farmpulse.example')
            ->assertSee('data-live-chart', false)
            ->assertSee('href="'.route('publishers.index', ['country' => 'US']).'#all-publishers"', false);
    }

    public function test_country_without_stored_sites_still_shows_its_chart(): void
    {
        $page = $this->get(route('publishers.index', ['country' => 'BD']));

        $page->assertOk()
            ->assertSee('Bangladesh publisher websites')
            ->assertSee('signaldesk-bd.example')
            ->assertSee('fieldnote-bd.example')
            ->assertDontSee('farmpulse.example')
            ->assertSee('data-live-chart', false);

        $this->get(route('publishers.index', ['country' => 'ZZ']))
            ->assertOk()
            ->assertSee('List of posting websites (All publishers)')
            ->assertSee('farmpulse.example');
    }
}
