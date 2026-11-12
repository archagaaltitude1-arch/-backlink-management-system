<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_the_admin_login_from_order_management(): void
    {
        $this->get(route('admin.orders'))->assertRedirect(route('login'));
    }

    public function test_customers_cannot_open_order_management(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.orders'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_admin_sees_an_empty_order_list_when_no_orders_exist(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.orders'))
            ->assertSee('No orders have been placed yet.');
    }

    public function test_admin_sees_stored_orders_on_the_dashboard_and_order_page(): void
    {
        $customer = $this->customer();
        $order = $this->order($customer, 'BM-ADMIN-0001', 'paid', 'completed');
        $order->items()->create([
            'item_name' => 'Guest Post Placement',
            'unit_price' => 120,
            'quantity' => 1,
            'target_url' => 'https://morgandigital.example/seo-services',
            'anchor_text' => 'SEO services',
            'article_topic' => 'Search growth',
        ]);

        $this->actingAs($this->admin());

        $this->get(route('admin.dashboard'))
            ->assertSee('BM-ADMIN-0001')
            ->assertSee('Morgan Digital')
            ->assertSee('Needs SEO staff');

        $this->get(route('admin.orders'))
            ->assertSee('BM-ADMIN-0001')
            ->assertSee('Morgan Digital')
            ->assertSee('morgan@example.test')
            ->assertSee('Guest Post Placement')
            ->assertSee('Needs SEO staff')
            ->assertSee('Assign to SEO staff')
            ->assertSee('Completed');
    }

    public function test_admin_order_search_hides_orders_that_do_not_match(): void
    {
        $customer = $this->customer();
        $this->order($customer, 'BM-ADMIN-0001', 'paid', 'completed');
        $this->order($customer, 'BM-ADMIN-0002', 'pending', 'pending');

        $this->actingAs($this->admin())
            ->get(route('admin.orders', ['q' => 'BM-ADMIN-0001']))
            ->assertSee('BM-ADMIN-0001')
            ->assertDontSee('BM-ADMIN-0002');
    }

    public function test_admin_sees_the_line_items_stored_for_an_order(): void
    {
        $customer = $this->customer();
        $order = $this->order($customer, 'BM-ADMIN-0001', 'pending', 'pending');
        $order->items()->create([
            'item_name' => 'Niche Edit',
            'unit_price' => 80,
            'quantity' => 2,
            'target_url' => 'https://morgandigital.example/marketing',
            'anchor_text' => 'digital marketing team',
            'article_topic' => 'Marketing workflows',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $order))
            ->assertSee('Niche Edit')
            ->assertSee('https://morgandigital.example/marketing')
            ->assertSee('$160.00')
            ->assertSee('No payment has been recorded for this order.');
    }

    private function admin(): User
    {
        $role = Role::query()->firstOrCreate(['role_name' => 'Admin'], ['permissions' => []]);

        return User::factory()->create([
            'role_id' => $role->id,
            'status' => 'active',
            'account_type' => 'admin',
        ]);
    }

    private function customer(): User
    {
        $role = Role::query()->firstOrCreate(['role_name' => 'Customer'], ['permissions' => []]);

        return User::factory()->create([
            'name' => 'Morgan Digital',
            'email' => 'morgan@example.test',
            'role_id' => $role->id,
            'account_type' => 'customer',
        ]);
    }

    private function order(User $customer, string $orderNumber, string $paymentStatus, string $orderStatus): Order
    {
        return Order::query()->create([
            'order_number' => $orderNumber,
            'customer_id' => $customer->id,
            'session_id' => 'admin-order-test',
            'total_amount' => 120,
            'payment_status' => $paymentStatus,
            'order_status' => $orderStatus,
        ]);
    }
}
