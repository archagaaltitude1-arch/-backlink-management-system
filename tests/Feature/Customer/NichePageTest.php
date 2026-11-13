<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NichePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['marketplace.demo' => true]);
    }

    public function test_agriculture_niche_lists_sample_publishers(): void
    {
        $this->get('/niches/agriculture-farming')
            ->assertOk()
            ->assertSee('Agriculture & Farming Guest Posting Sites')
            ->assertSee('farmpulse.example');
    }

    public function test_minimum_dr_hides_lower_ratings(): void
    {
        $this->get('/niches/agriculture-farming?dr_min=40')
            ->assertOk()
            ->assertDontSee('taniharian.example')
            ->assertSee('farmpulse.example');
    }

    public function test_price_sort_puts_the_lowest_price_first(): void
    {
        $this->get('/niches/agriculture-farming?sort=price_asc')
            ->assertOk()
            ->assertSeeInOrder(['taniharian.example', 'farmpulse.example']);
    }

    public function test_unknown_niche_is_not_found(): void
    {
        $this->get('/niches/unknown')->assertNotFound();
    }
}
