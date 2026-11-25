<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestCheckoutResumeTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_a_selected_publisher_opens_the_url_and_anchor_checkout(): void
    {
        $customer = User::factory()->create();

        $this->withSession([
            'shopping_cart' => $this->publisherCart(),
            'url.intended' => route('customer.dashboard'),
        ])->post('/login', [
            'email' => $customer->email,
            'password' => 'password',
        ])->assertRedirect(route('shopping.checkout'));

        $this->get(route('shopping.checkout'))
            ->assertOk()
            ->assertSee('Target URL', false)
            ->assertSee('Anchor text', false)
            ->assertSee('Farm Pulse', false);
    }

    public function test_registration_with_a_selected_publisher_opens_checkout(): void
    {
        $this->withSession([
            'shopping_cart' => $this->publisherCart(),
        ])->post('/register', [
            'name' => 'New Customer',
            'email' => 'new-customer@example.test',
            'password' => 'password-1',
            'password_confirmation' => 'password-1',
        ])->assertRedirect(route('shopping.checkout'));
    }

    public function test_login_without_a_publisher_still_opens_the_dashboard(): void
    {
        $customer = User::factory()->create();

        $this->post('/login', [
            'email' => $customer->email,
            'password' => 'password',
        ])->assertRedirect(route('customer.dashboard'));
    }

    public function test_login_keeps_a_specific_page_when_one_was_requested(): void
    {
        $customer = User::factory()->create();

        $this->withSession([
            'shopping_cart' => $this->publisherCart(),
            'url.intended' => route('customer.profile'),
        ])->post('/login', [
            'email' => $customer->email,
            'password' => 'password',
        ])->assertRedirect(route('customer.profile'));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function publisherCart(): array
    {
        return [
            'line-1' => [
                'line' => 'line-1',
                'domain' => 'farmpulse.example',
                'display_name' => 'Farm Pulse',
                'price' => 100,
                'quantity' => 1,
                'order_label' => 'Guest post',
            ],
        ];
    }
}
