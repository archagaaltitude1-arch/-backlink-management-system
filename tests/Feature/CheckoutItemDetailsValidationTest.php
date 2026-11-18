<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutItemDetailsValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_shopping_checkout_rejects_anchor_text_outside_fifteen_to_twenty_characters(): void
    {
        $customer = User::factory()->create();
        $this->actingAs($customer);
        $line = $this->addStarterPackage();

        $this->from(route('shopping.checkout'))
            ->post(route('shopping.checkout.store'), $this->payload($line, [
                'anchor_text' => 'short',
            ]))
            ->assertRedirect(route('shopping.checkout'))
            ->assertSessionHasErrors('items.0.anchor_text');

        $this->from(route('shopping.checkout'))
            ->post(route('shopping.checkout.store'), $this->payload($line, [
                'anchor_text' => 'this anchor is way too long',
            ]))
            ->assertRedirect(route('shopping.checkout'))
            ->assertSessionHasErrors('items.0.anchor_text');
    }

    public function test_shopping_checkout_rejects_more_than_five_keywords(): void
    {
        $customer = User::factory()->create();
        $this->actingAs($customer);
        $line = $this->addStarterPackage();

        $this->from(route('shopping.checkout'))
            ->post(route('shopping.checkout.store'), $this->payload($line, [
                'keywords' => 'one, two, three, four, five, six',
            ]))
            ->assertRedirect(route('shopping.checkout'))
            ->assertSessionHasErrors('items.0.keywords');
    }

    public function test_shopping_checkout_accepts_valid_anchor_and_keywords(): void
    {
        $customer = User::factory()->create();
        $this->actingAs($customer);
        $line = $this->addStarterPackage();

        $this->post(route('shopping.checkout.store'), $this->payload($line, [
            'anchor_text' => 'starter campaign',
            'keywords' => 'seo, backlinks, niche, guest, posts',
        ]))->assertRedirect();

        $this->assertDatabaseHas('order_items', [
            'anchor_text' => 'starter campaign',
            'keywords' => 'seo, backlinks, niche, guest, posts',
        ]);
    }

    private function addStarterPackage(): string
    {
        $this->post(route('shopping.store'), [
            'package_slug' => 'display-starter',
            'quantity' => 1,
            'order_type' => 'package',
            'websites' => [
                'farmpulse.example',
                'agrarblatt.example',
                'greenfieldsjournal.example',
                'cosechadiaria.example',
                'campoforte.example',
            ],
        ])->assertRedirect();

        return (string) array_key_first(session('shopping_cart'));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(string $line, array $overrides = []): array
    {
        return [
            'items' => [[
                'line' => $line,
                'target_url' => 'https://customer.example.test/page',
                'anchor_text' => 'starter campaign',
                'article_topic' => 'Starter backlinks',
                'keywords' => 'seo, growth',
                ...$overrides,
            ]],
        ];
    }
}
