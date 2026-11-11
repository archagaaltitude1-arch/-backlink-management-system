<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_the_admin_login_from_payment_management(): void
    {
        $this->get(route('admin.payments'))->assertRedirect(route('login'));
    }

    public function test_customers_cannot_open_payment_management(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.payments'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_admin_sees_an_empty_payment_list_when_no_payments_exist(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.payments'))
            ->assertSee('No payments have been recorded yet.');
    }

    public function test_admin_sees_stored_payments_on_the_dashboard_and_payment_page(): void
    {
        $customer = $this->customer();
        $order = Order::query()->create([
            'order_number' => 'BM-PAY-0001',
            'customer_id' => $customer->id,
            'session_id' => 'admin-payment-test',
            'total_amount' => 80,
            'payment_status' => 'paid',
            'order_status' => 'processing',
        ]);
        Payment::query()->create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'transaction_id' => 'DEMO-ADMIN-0001',
            'amount' => 80,
            'payment_method' => 'Demo Visa ending 4242',
            'gateway' => 'demo',
            'status' => 'successful',
            'paid_at' => now(),
        ]);

        $this->actingAs($this->admin());

        $this->get(route('admin.dashboard'))
            ->assertSee('DEMO-ADMIN-0001')
            ->assertSee('$80.00');

        $this->get(route('admin.payments'))
            ->assertSee('DEMO-ADMIN-0001')
            ->assertSee('Morgan Digital')
            ->assertSee('BM-PAY-0001')
            ->assertSee('Demo Visa ending 4242')
            ->assertSee('$80.00')
            ->assertSee('Successful');
    }

    public function test_admin_payment_status_filter_hides_other_payments(): void
    {
        $customer = $this->customer();
        $this->payment($customer, 'DEMO-ADMIN-0001', 'successful');
        $this->payment($customer, 'DEMO-ADMIN-0002', 'failed');

        $this->actingAs($this->admin())
            ->get(route('admin.payments', ['status' => 'successful']))
            ->assertSee('DEMO-ADMIN-0001')
            ->assertDontSee('DEMO-ADMIN-0002');
    }

    public function test_admin_sees_the_order_linked_to_a_payment(): void
    {
        $customer = $this->customer();
        $order = Order::query()->create([
            'order_number' => 'BM-PAY-0001',
            'customer_id' => $customer->id,
            'session_id' => 'admin-payment-test',
            'total_amount' => 80,
            'payment_status' => 'paid',
            'order_status' => 'processing',
        ]);
        $order->items()->create([
            'item_name' => 'Link Insertion',
            'unit_price' => 80,
            'quantity' => 1,
            'target_url' => 'https://morgandigital.example/resources',
            'anchor_text' => 'marketing resources',
            'article_topic' => 'Growth resources',
        ]);
        $payment = Payment::query()->create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'transaction_id' => 'DEMO-ADMIN-0001',
            'amount' => 80,
            'payment_method' => 'Demo card',
            'gateway' => 'demo',
            'status' => 'successful',
            'paid_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.payments.show', $payment))
            ->assertSee('DEMO-ADMIN-0001')
            ->assertSee('BM-PAY-0001')
            ->assertSee('Link Insertion')
            ->assertSee('$80.00');
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

    private function payment(User $customer, string $transactionId, string $status): Payment
    {
        return Payment::query()->create([
            'customer_id' => $customer->id,
            'transaction_id' => $transactionId,
            'amount' => 40,
            'payment_method' => 'Demo card',
            'gateway' => 'demo',
            'status' => $status,
        ]);
    }
}
