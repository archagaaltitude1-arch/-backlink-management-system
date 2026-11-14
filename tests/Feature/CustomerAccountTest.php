<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_and_authenticates_a_customer(): void
    {
        $response = $this->post(route('auth.register.store'), [
            'name' => 'Taylor Customer',
            'email' => 'taylor@example.test',
            'password' => 'CustomerPass123!',
            'password_confirmation' => 'CustomerPass123!',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'taylor@example.test',
            'account_type' => 'customer',
            'status' => 'active',
        ]);
    }

    public function test_guest_is_redirected_to_login_from_the_customer_dashboard(): void
    {
        $this->get(route('customer.dashboard'))
            ->assertRedirect(route('auth.login'));
    }

    public function test_guest_root_shows_the_public_home_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Real, Traffic-Driven Websites', false);
    }

    public function test_guest_login_page_hides_customer_navigation(): void
    {
        $this->get(route('auth.login'))
            ->assertOk()
            ->assertDontSee('Dashboard')
            ->assertDontSee('Open navigation');
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $owner = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $order = Order::create([
            'order_number' => 'BM-20261110-OWNER1',
            'customer_id' => $owner->id,
            'session_id' => 'owner-session',
            'total_amount' => 100,
        ]);

        $this->actingAs($otherCustomer)
            ->get(route('orders.show', $order))
            ->assertNotFound();
    }

    public function test_customer_account_pages_render_for_an_authenticated_customer(): void
    {
        $customer = User::factory()->create();
        $this->actingAs($customer);

        foreach ([
            'customer.dashboard',
            'customer.services',
            'customer.websites',
            'customer.profile',
            'customer.wallet',
            'customer.payment-history',
            'customer.payment-methods',
            'customer.invoices',
            'customer.addresses',
            'customer.saved-websites',
            'customer.notifications',
            'customer.projects',
            'customer.backlinks',
            'customer.reports',
            'orders.cart',
            'orders.index',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk()->assertSee('aria-label="My account"', false);
        }
    }
}
