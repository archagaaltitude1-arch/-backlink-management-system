<?php

namespace Tests\Feature\Customer;

use App\Support\PublicPackages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ServicePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_services_overview_is_available(): void
    {
        $this->get('/services')
            ->assertOk()
            ->assertSee('Link Building Services for Every Goal and Budget', false)
            ->assertSee('Price from', false);
    }

    public function test_each_service_page_shows_its_heading(): void
    {
        foreach (config('backlink_services.menu') as $item) {
            $slug = $item['slug'];
            $service = config('backlink_services.'.$slug);
            $response = $this->get('/services/'.$slug)->assertOk();
            $response->assertSee(Str::before($service['h1'], $service['h1_highlight']), false);
            $response->assertSee($service['h1_highlight'], false);
            $response->assertSee(Str::after($service['h1'], $service['h1_highlight']), false);
        }
    }

    public function test_each_pricing_card_lists_at_least_six_points(): void
    {
        foreach (config('backlink_services.menu') as $item) {
            $slug = $item['slug'];

            if ($slug === 'niche-edit') {
                continue;
            }

            $response = $this->get('/services/'.$slug)->assertOk();

            foreach (config('backlink_services.'.$slug.'.tiers') as $tier) {
                $features = $tier['features'];
                $this->assertGreaterThanOrEqual(6, count($features), $slug.' '.$tier['name']);

                foreach ($features as $feature) {
                    $response->assertSee($feature, false);
                }
            }
        }
    }

    public function test_niche_edit_service_lists_websites_instead_of_packages(): void
    {
        $this->get('/services/niche-edit')
            ->assertOk()
            ->assertSee('Buy Niche Edits Backlinks', false)
            ->assertSee('Create project', false)
            ->assertSee('Website URL', false)
            ->assertSee('Link insertion', false)
            ->assertSee('Browse websites', false)
            ->assertDontSee('Niche Edit Packages', false);
    }

    public function test_each_service_package_opens_its_own_product_page(): void
    {
        $this->get('/services/text-link')
            ->assertOk()
            ->assertSee('/packages/starter-text-link', false)
            ->assertSee('/packages/pro-text-link', false)
            ->assertSee('/packages/professional-text-link', false)
            ->assertSee('/packages/enterprise-text-link', false);

        $this->get('/services/contextual-link')
            ->assertOk()
            ->assertSee('/packages/starter-contextual-link', false)
            ->assertSee('/packages/pro-contextual-link', false)
            ->assertSee('/packages/professional-contextual-link', false)
            ->assertSee('/packages/enterprise-contextual-link', false);

        $this->get('/services/guest-post')
            ->assertOk()
            ->assertSee('/packages/starter-guest-post', false)
            ->assertSee('/packages/pro-guest-post', false)
            ->assertSee('/packages/professional-guest-post', false);

        $menu = $this->get('/')->assertOk();

        foreach (['starter', 'pro', 'professional', 'enterprise'] as $level) {
            foreach (['text-link', 'contextual-link', 'guest-post'] as $service) {
                $menu->assertSee('/packages/'.$level.'-'.$service, false);
            }
        }

        foreach (PublicPackages::serviceProducts() as $product) {
            $prefix = trim((string) ($product['price_prefix'] ?? ''));
            $price = ($prefix === '' ? '' : $prefix.' ').'$'.number_format((float) $product['amount'], 2);

            $this->get('/packages/'.$product['slug'])
                ->assertOk()
                ->assertSee($product['label'], false)
                ->assertSee($price, false)
                ->assertSee('Add to cart', false)
                ->assertSee('Publishing rules', false);
        }

        $this->from('/packages/pro-contextual-link')
            ->post('/cart', [
                'package_slug' => 'pro-contextual-link',
                'quantity' => 1,
                'order_type' => 'placement',
            ])
            ->assertRedirect('/packages/pro-contextual-link');

        $line = array_values(session('shopping_cart'))[0];
        $this->assertSame('Pro Contextual Link Package', $line['display_name']);
        $this->assertSame(99.0, (float) $line['price']);
        $this->assertSame('placement', $line['order_type']);
    }

    public function test_unknown_service_is_not_found(): void
    {
        $this->get('/services/unknown')->assertNotFound();
    }

    public function test_navbar_links_to_every_service_page(): void
    {
        $response = $this->get('/services')->assertOk();

        foreach (config('backlink_services.menu') as $item) {
            $response->assertSee('/services/'.$item['slug'], false);
        }
    }
}
