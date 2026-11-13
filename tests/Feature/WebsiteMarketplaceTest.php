<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Website;
use Database\Seeders\DemoCustomerWorkflowSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seed_provides_exactly_forty_publisher_websites(): void
    {
        $this->seed(DemoCustomerWorkflowSeeder::class);

        $this->assertDatabaseCount('websites', 40);
        $featuredWebsite = Website::query()->orderByDesc('domain_rating')->firstOrFail();
        $this->get(route('customer.websites'))
            ->assertOk()
            ->assertSee('Browse websites')
            ->assertSee($featuredWebsite->website_name)
            ->assertSee('Publisher marketplace');
    }

    public function test_marketplace_filters_by_search_niche_and_minimum_domain_rating(): void
    {
        $this->seed(DemoCustomerWorkflowSeeder::class);

        $this->get(route('customer.websites', [
            'search' => 'Clear Byte',
            'niche' => 'Technology',
            'min_dr' => 40,
        ]))
            ->assertOk()
            ->assertSee('clear-byte.example')
            ->assertDontSee('Metric Mint');
    }

    public function test_publisher_cart_item_uses_database_price_and_credits(): void
    {
        $this->seed(DemoCustomerWorkflowSeeder::class);
        $customer = User::where('email', 'customer@example.test')->firstOrFail();
        $website = Website::where('domain', 'clear-byte.example')->firstOrFail();

        $this->actingAs($customer)->post(route('orders.cart.items.store'), [
            'product_key' => 'guest_post',
            'website_id' => $website->id,
            'quantity' => 1,
            'unit_price' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'item_name' => 'Clear Byte — Guest Post Placement',
            'website_id' => $website->id,
            'unit_price' => $website->price,
            'credit_cost' => $website->credit_cost,
        ]);
    }
}
