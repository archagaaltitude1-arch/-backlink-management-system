<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class PackageWebsiteSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_packages_page_opens_website_selection_for_starter(): void
    {
        $selection = route('packages.websites', 'display-starter');

        $this->get(route('packages.index'))
            ->assertOk()
            ->assertSee('Choose Backlinks', false)
            ->assertSee($selection, false);

        $this->get(route('packages.show', 'display-starter'))
            ->assertOk()
            ->assertSee('Choose Backlinks', false)
            ->assertSee($selection, false)
            ->assertDontSee('Add to cart', false);

        $this->get($selection)
            ->assertOk()
            ->assertSee('Select Backlink Websites', false)
            ->assertSee('Starter – 5 Backlinks', false)
            ->assertSee('Choose 5 websites', false)
            ->assertSee('name="websites[]"', false);
    }

    #[TestWith(['display-professional', 'Professional – 25 Backlinks', 'Choose 25 websites'])]
    #[TestWith(['pro-text-link', 'Pro – 10 Backlinks', 'Choose 10 websites'])]
    #[TestWith(['enterprise-guest-post', 'Enterprise – Custom Backlinks', 'Choose the websites'])]
    public function test_package_selection_names_the_chosen_tier(string $slug, string $choice, string $instruction): void
    {
        $this->get(route('packages.show', $slug))
            ->assertOk()
            ->assertSee('Choose Backlinks', false)
            ->assertDontSee('Add to cart', false);

        $this->get(route('packages.websites', $slug))
            ->assertOk()
            ->assertSee('Select Backlink Websites', false)
            ->assertSee($choice, false)
            ->assertSee($instruction, false);
    }

    public function test_two_starter_packages_ask_for_ten_websites(): void
    {
        $this->get(route('packages.websites', ['slug' => 'display-starter', 'quantity' => 2]))
            ->assertOk()
            ->assertSee('Starter – 5 Backlinks', false)
            ->assertSee('Choose 10 websites', false);
    }

    public function test_starter_package_enters_the_cart_after_five_websites_are_chosen(): void
    {
        $websites = [
            'farmpulse.example',
            'agrarblatt.example',
            'greenfieldsjournal.example',
            'cosechadiaria.example',
            'campoforte.example',
        ];
        $selection = route('packages.websites', 'display-starter');

        $this->from($selection)
            ->post(route('shopping.store'), [
                'package_slug' => 'display-starter',
                'quantity' => 1,
                'order_type' => 'package',
                'websites' => $websites,
            ])
            ->assertRedirect($selection)
            ->assertSessionHas('shopping_status');

        $line = array_values(session('shopping_cart'))[0];

        $this->assertSame('Starter Backlink Package', $line['display_name']);
        $this->assertSame($websites, $line['websites']);
    }

    public function test_package_without_websites_returns_to_selection(): void
    {
        $this->post(route('shopping.store'), [
            'package_slug' => 'display-starter',
            'quantity' => 1,
        ])->assertRedirect(route('packages.websites', ['slug' => 'display-starter', 'quantity' => 1]))
            ->assertSessionHas('shopping_error', 'Choose the websites for this package before adding it to your cart.');

        $this->assertNull(session('shopping_cart'));
    }

    public function test_package_with_too_few_websites_stays_off_the_cart(): void
    {
        $this->post(route('shopping.store'), [
            'package_slug' => 'display-starter',
            'quantity' => 1,
            'websites' => ['farmpulse.example'],
        ])->assertRedirect(route('packages.websites', ['slug' => 'display-starter', 'quantity' => 1]))
            ->assertSessionHas('shopping_error', 'Choose 5 websites for this package.');

        $this->assertNull(session('shopping_cart'));
    }

    public function test_unknown_website_is_rejected(): void
    {
        $this->post(route('shopping.store'), [
            'package_slug' => 'display-starter',
            'quantity' => 1,
            'websites' => [
                'farmpulse.example',
                'agrarblatt.example',
                'greenfieldsjournal.example',
                'cosechadiaria.example',
                'not-listed.example',
            ],
        ])->assertRedirect(route('packages.websites', ['slug' => 'display-starter', 'quantity' => 1]))
            ->assertSessionHas('shopping_error', 'That website is not on the list.');

        $this->assertNull(session('shopping_cart'));
    }

    public function test_unknown_package_selection_page_is_not_found(): void
    {
        $this->get('/packages/missing-package/websites')->assertNotFound();
    }
}
