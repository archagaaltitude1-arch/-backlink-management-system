<?php

namespace Tests\Feature\Customer;

use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistHeartTest extends TestCase
{
    use RefreshDatabase;

    public function test_heart_adds_a_listed_website_to_the_wishlist_and_can_remove_it(): void
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

        $this->get('/niches/technology')
            ->assertOk()
            ->assertSee('Add signaldesk-technology.example to wishlist', false)
            ->assertDontSee('title="Demo data"', false);

        $this->from('/niches/technology')
            ->post(route('wishlist.store'), ['domain' => 'signaldesk-technology.example'])
            ->assertRedirect('/niches/technology')
            ->assertSessionHas('wishlist_status', 'signaldesk-technology.example is on your wishlist.');

        $this->assertSame('signaldesk-technology.example', session('wishlist')['signaldesk-technology.example']['domain']);

        $this->get('/niches/technology')
            ->assertOk()
            ->assertSee('Remove signaldesk-technology.example from wishlist', false);

        $this->from('/niches/technology')
            ->delete(route('wishlist.destroy'), ['domain' => 'signaldesk-technology.example'])
            ->assertRedirect('/niches/technology');

        $this->assertArrayNotHasKey('signaldesk-technology.example', session('wishlist', []));
    }

    public function test_unknown_domain_is_not_added_to_the_wishlist(): void
    {
        config(['marketplace.demo' => false]);

        $this->from('/wishlist')
            ->post(route('wishlist.store'), ['domain' => 'missing.example'])
            ->assertRedirect('/wishlist')
            ->assertSessionHas('wishlist_error', 'That publisher is not on the list.');

        $this->assertSame([], session('wishlist', []));
    }
}
