<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReactPublicCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_still_render_blade_for_a_normal_browser_request(): void
    {
        $this->get('/services')
            ->assertOk()
            ->assertSee('Link Building Services for Every Goal and Budget', false);
    }

    public function test_react_reads_the_existing_service_page_data(): void
    {
        $this->withHeader('X-Backlink-Spa', '1')
            ->getJson('/services/guest-post')
            ->assertOk()
            ->assertJsonPath('view', 'customer.services.show')
            ->assertJsonPath('data.page.slug', 'guest-post')
            ->assertJsonPath('data.page.package_tabs.0.quote.final', fn (mixed $price): bool => is_numeric($price) && (float) $price > 0);
    }

    public function test_website_selection_rejects_a_short_list_and_accepts_five_domains(): void
    {
        $selection = route('packages.websites', 'display-starter');
        $short = $this->withHeader('X-Backlink-Spa', '1')
            ->from($selection)
            ->postJson(route('shopping.store'), [
                'package_slug' => 'display-starter',
                'quantity' => 1,
                'order_type' => 'package',
                'websites' => ['farmpulse.example'],
            ]);

        $short->assertOk()
            ->assertJsonPath('flash.shopping_error', 'Choose 5 websites for this package.');
        $this->assertNull(session('shopping_cart'));

        $websites = [
            'farmpulse.example',
            'agrarblatt.example',
            'greenfieldsjournal.example',
            'cosechadiaria.example',
            'campoforte.example',
        ];

        $this->withHeader('X-Backlink-Spa', '1')
            ->from($selection)
            ->postJson(route('shopping.store'), [
                'package_slug' => 'display-starter',
                'quantity' => 1,
                'order_type' => 'package',
                'websites' => $websites,
            ])
            ->assertOk()
            ->assertJsonPath('flash.shopping_status', 'Starter Backlink Package was added to your cart.');

        $line = array_values(session('shopping_cart'))[0];
        $this->assertSame($websites, $line['websites']);
    }

    public function test_tier_website_selection_continues_to_checkout_only_with_the_full_set(): void
    {
        $websites = [
            'farmpulse.example',
            'agrarblatt.example',
            'greenfieldsjournal.example',
            'cosechadiaria.example',
            'campoforte.example',
        ];

        $this->withHeader('X-Backlink-Spa', '1')
            ->postJson(route('shopping.store'), [
                'package_slug' => 'starter-guest-post',
                'order_type' => 'package',
                'backlinks' => 5,
                'website_selection' => true,
                'websites' => ['farmpulse.example'],
            ])
            ->assertOk()
            ->assertJsonPath('flash.shopping_error', 'Choose exactly 5 websites for this package.');

        $this->withHeader('X-Backlink-Spa', '1')
            ->postJson(route('shopping.store'), [
                'package_slug' => 'starter-guest-post',
                'order_type' => 'package',
                'backlinks' => 5,
                'website_selection' => true,
                'websites' => $websites,
            ])
            ->assertOk()
            ->assertJsonPath('redirect', route('shopping.checkout', [], false));

        $line = array_values(session('shopping_cart'))[0];
        $this->assertSame($websites, $line['websites']);
        $this->assertSame(5, $line['backlinks']);
    }

    public function test_checkout_popup_offers_one_coupon_and_apply_keeps_it_on_the_order(): void
    {
        $customer = User::factory()->create();
        $customer->orders()->create([
            'order_number' => 'BM-PAID-'.$customer->id,
            'session_id' => 'paid-'.$customer->id,
            'total_amount' => 20,
            'payment_status' => 'paid',
            'order_status' => 'completed',
        ]);
        Coupon::query()->create([
            'code' => 'CHECKOUT10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'purchase_type' => 'both',
            'usage_limit' => 200,
            'expires_at' => now()->addMonth(),
        ]);
        $this->actingAs($customer);
        session(['shopping_cart' => [
            'line-1' => [
                'line' => 'line-1',
                'domain' => 'farmpulse.example',
                'display_name' => 'Farm Pulse',
                'price' => 100,
                'quantity' => 1,
                'order_label' => 'Guest post',
            ],
        ]]);

        $this->get(route('shopping.checkout'))
            ->assertOk()
            ->assertSee('CHECKOUT10', false)
            ->assertSee('Not now', false)
            ->assertSee('Apply this coupon before you place the order, or leave it for later.', false);

        $this->withHeaders([
            'X-Backlink-Spa' => '1',
            'Accept' => 'text/html, application/json',
        ])->get('/cart/checkout')
            ->assertOk()
            ->assertJsonPath('data.couponOffer.code', 'CHECKOUT10')
            ->assertJsonPath('data.coupon', null);

        $this->withHeaders([
            'X-Backlink-Spa' => '1',
            'Accept' => 'text/html, application/json',
        ])->from('/cart/checkout')->post('/cart/coupon', ['coupon_code' => 'CHECKOUT10'])
            ->assertOk()
            ->assertJsonPath('redirect', '/cart/checkout')
            ->assertJsonPath('errors', []);

        $this->assertSame('CHECKOUT10', session('shopping_coupon'));

        $this->withHeaders([
            'X-Backlink-Spa' => '1',
            'Accept' => 'text/html, application/json',
        ])->get('/cart/checkout')
            ->assertOk()
            ->assertJsonPath('data.coupon', 'CHECKOUT10')
            ->assertJsonPath('data.couponOffer', null);
    }

    public function test_checkout_popup_uses_the_customers_own_coupon_instead_of_the_public_code(): void
    {
        $customer = User::factory()->create();
        $customer->orders()->create([
            'order_number' => 'BM-PAID-'.$customer->id,
            'session_id' => 'paid-'.$customer->id,
            'total_amount' => 20,
            'payment_status' => 'paid',
            'order_status' => 'completed',
        ]);
        Coupon::query()->create([
            'code' => 'CHECKOUT10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'purchase_type' => 'both',
            'expires_at' => now()->addMonth(),
        ]);
        Coupon::query()->create([
            'code' => 'ORDER-NEW',
            'discount_type' => 'percentage',
            'discount_value' => 2,
            'purchase_type' => 'both',
            'customer_id' => $customer->id,
            'expires_at' => now()->addMonth(),
        ]);
        $this->actingAs($customer);
        session(['shopping_cart' => [
            'line-1' => [
                'line' => 'line-1',
                'domain' => 'farmpulse.example',
                'display_name' => 'Farm Pulse',
                'price' => 80,
                'quantity' => 1,
            ],
        ]]);

        $this->withHeaders([
            'X-Backlink-Spa' => '1',
            'Accept' => 'text/html, application/json',
        ])->get('/cart/checkout')
            ->assertOk()
            ->assertJsonPath('data.couponOffer', null)
            ->assertJsonPath('data.couponChoices.0.code', 'ORDER-NEW');
    }
}
