<?php

namespace Tests\Feature\Customer;

use App\Models\OrderItem;
use App\Models\User;
use App\Support\PublicPackages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageTierSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_packages_page_offers_a_live_package_selection(): void
    {
        $this->get(route('packages.index'))
            ->assertOk()
            ->assertSee('id="select"', false)
            ->assertSee('data-package-service="text-link"', false)
            ->assertSee('data-package-service="contextual-link"', false)
            ->assertSee('data-package-service="guest-post"', false)
            ->assertSee('data-tier-tab="starter"', false)
            ->assertSee('Continue to Checkout', false);
    }

    public function test_service_pages_use_package_tabs_instead_of_separate_cards(): void
    {
        foreach (['guest-post', 'contextual-link', 'text-link'] as $service) {
            $this->get(route('services.show', $service))
                ->assertOk()
                ->assertSee('data-tier-tab="starter"', false)
                ->assertSee('data-tier-tab="professional"', false)
                ->assertSee('data-tier-tab="pro"', false)
                ->assertSee('data-tier-tab="enterprise"', false)
                ->assertSee('Continue to Checkout', false)
                ->assertDontSee('class="sp-plan', false);
        }
    }

    public function test_starter_quantity_is_priced_from_the_package_rate_with_a_five_percent_discount(): void
    {
        $quote = PublicPackages::quote('starter-text-link', 7);

        $this->assertSame(7.8, $quote['unit']);
        $this->assertSame(54.6, $quote['subtotal']);
        $this->assertSame(2.73, $quote['discount_amount']);
        $this->assertSame(51.87, $quote['final']);

        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->from(route('services.show', 'text-link'))
            ->post(route('shopping.store'), [
                'package_slug' => 'starter-text-link',
                'order_type' => 'package',
                'backlinks' => 7,
            ])
            ->assertRedirect(route('packages.show', 'starter-text-link'));

        $line = array_values(session('shopping_cart'))[0];

        $this->assertSame(51.87, $line['price']);
        $this->assertSame(7, $line['backlinks']);

        $this->actingAs($customer)
            ->get(route('packages.show', 'starter-text-link'))
            ->assertOk()
            ->assertSee('Home', false)
            ->assertSee('Packages', false)
            ->assertSee('Starter Text Link Package', false)
            ->assertSee('Choose Backlinks', false)
            ->assertSee('choose 7 websites', false);

        $this->actingAs($customer)
            ->get(route('packages.websites', 'starter-text-link'))
            ->assertOk()
            ->assertSee('Choose 7 websites', false)
            ->assertSee('0/7', false)
            ->assertDontSee('Choose 5 websites', false);
    }

    public function test_selected_quantity_opens_that_packages_website_chooser(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->post(route('shopping.store'), [
                'package_slug' => 'starter-guest-post',
                'order_type' => 'package',
                'backlinks' => 6,
            ])
            ->assertRedirect(route('packages.show', 'starter-guest-post'));

        $this->actingAs($customer)
            ->get(route('packages.show', 'starter-guest-post'))
            ->assertOk()
            ->assertSee('Home', false)
            ->assertSee('Packages', false)
            ->assertSee('Starter Guest Post Package', false)
            ->assertSee('Choose Backlinks', false)
            ->assertSee('name="backlinks" value="6"', false);

        $this->actingAs($customer)
            ->get(route('packages.websites', ['slug' => 'starter-guest-post', 'backlinks' => 6]))
            ->assertOk()
            ->assertSee('Choose 6 websites', false)
            ->assertSee('0/6', false)
            ->assertSee('data-limit="6"', false)
            ->assertDontSee('Choose 10 websites', false);

        $this->actingAs($customer)
            ->post(route('shopping.store'), [
                'package_slug' => 'professional-contextual-link',
                'order_type' => 'package',
                'backlinks' => 12,
            ])
            ->assertRedirect(route('packages.show', 'professional-contextual-link'));

        $this->actingAs($customer)
            ->get(route('packages.websites', 'professional-contextual-link'))
            ->assertOk()
            ->assertSee('Choose 12 websites', false)
            ->assertSee('0/12', false)
            ->assertDontSee('Choose 25 websites', false);

        $this->actingAs($customer)
            ->post(route('shopping.store'), [
                'package_slug' => 'enterprise-text-link',
                'order_type' => 'package',
                'backlinks' => 30,
            ])
            ->assertRedirect(route('packages.show', 'enterprise-text-link'));

        $this->actingAs($customer)
            ->get(route('packages.websites', ['slug' => 'enterprise-text-link', 'backlinks' => 30]))
            ->assertOk()
            ->assertSee('Choose 30 websites', false)
            ->assertSee('0/30', false);
    }

    public function test_website_chooser_rejects_a_different_count_than_the_one_selected(): void
    {
        $customer = User::factory()->create();
        $page = route('packages.websites', ['slug' => 'starter-text-link', 'backlinks' => 6]);
        $websites = [
            'farmpulse.example',
            'agrarblatt.example',
            'greenfieldsjournal.example',
            'cosechadiaria.example',
            'campoforte.example',
        ];

        $this->actingAs($customer)
            ->post(route('shopping.store'), [
                'package_slug' => 'starter-text-link',
                'order_type' => 'package',
                'backlinks' => 6,
            ])
            ->assertRedirect(route('packages.show', 'starter-text-link'));

        $this->actingAs($customer)
            ->from($page)
            ->post(route('shopping.store'), [
                'package_slug' => 'starter-text-link',
                'order_type' => 'package',
                'backlinks' => 6,
                'website_selection' => 1,
                'websites' => $websites,
            ])
            ->assertRedirect($page)
            ->assertSessionHas('shopping_error', 'Choose exactly 6 websites for this package.');

        $websites[] = 'agromercado.example';

        $this->actingAs($customer)
            ->post(route('shopping.store'), [
                'package_slug' => 'starter-text-link',
                'order_type' => 'package',
                'backlinks' => 6,
                'website_selection' => 1,
                'websites' => $websites,
            ])
            ->assertRedirect(route('shopping.checkout'));

        $line = array_values(session('shopping_cart'))[0];

        $this->assertSame(6, $line['backlinks']);
        $this->assertSame($websites, $line['websites']);
    }

    public function test_package_quantity_outside_the_band_is_rejected(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->from(route('services.show', 'text-link'))
            ->post(route('shopping.store'), [
                'package_slug' => 'starter-text-link',
                'order_type' => 'package',
                'backlinks' => 12,
            ])
            ->assertRedirect(route('services.show', 'text-link'))
            ->assertSessionHasErrors('backlinks');

        $this->assertNull(session('shopping_cart'));
    }

    public function test_paid_order_accepts_only_the_purchased_website_count(): void
    {
        $customer = User::factory()->create();
        $order = $customer->orders()->create([
            'order_number' => 'BM-TIER-0001',
            'session_id' => 'tier-selection',
            'total_amount' => 51.87,
            'payment_status' => 'paid',
            'order_status' => 'awaiting_approval',
        ]);
        $order->items()->create([
            'item_name' => 'Starter Text Link Package',
            'unit_price' => 7.41,
            'quantity' => 1,
            'backlinks' => 7,
            'target_url' => 'https://customer.example.test/page',
            'anchor_text' => 'starter campaign',
            'article_topic' => 'Starter links',
        ]);

        $page = route('packages.websites', [
            'slug' => 'starter-text-link',
            'backlinks' => 7,
            'order' => $order->id,
        ]);

        $this->actingAs($customer)
            ->get($page)
            ->assertOk()
            ->assertSee('0/7', false)
            ->assertSee('Confirm websites', false);

        $websites = [
            'farmpulse.example',
            'agrarblatt.example',
            'greenfieldsjournal.example',
            'cosechadiaria.example',
            'campoforte.example',
        ];

        $this->actingAs($customer)
            ->from($page)
            ->post(route('packages.websites.assign', 'starter-text-link'), [
                'order' => $order->id,
                'backlinks' => 7,
                'websites' => $websites,
            ])
            ->assertRedirect($page)
            ->assertSessionHas('shopping_error', 'Choose exactly 7 websites for this order.');

        $this->assertNull(OrderItem::query()->firstOrFail()->selected_websites);

        $websites[] = 'agromercado.example';
        $websites[] = 'terraviva.example';

        $this->actingAs($customer)
            ->post(route('packages.websites.assign', 'starter-text-link'), [
                'order' => $order->id,
                'backlinks' => 7,
                'websites' => $websites,
            ])
            ->assertRedirect(route('orders.show', $order));

        $this->assertSame($websites, OrderItem::query()->firstOrFail()->selected_websites);
    }
}
