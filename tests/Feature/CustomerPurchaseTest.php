<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\CreditPackage;
use App\Models\CreditReservation;
use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CustomerPurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_order_and_complete_demo_payment(): void
    {
        Notification::fake();
        $customer = User::factory()->create();

        $this->actingAs($customer)->get(route('orders.cart'))->assertOk();
        $this->post(route('orders.cart.items.store'), [
            'product_key' => 'guest_post',
            'quantity' => 1,
            'unit_price' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('cart_items', ['item_name' => 'Guest Post Placement', 'unit_price' => 120]);

        $this->post(route('orders.store'), [
            'items' => [[
                'target_url' => 'https://customer.example.test/landing',
                'anchor_text' => 'customer growth tools',
                'article_topic' => 'A practical guide to growth',
                'requirements' => 'Include a concise introduction.',
            ]],
        ])->assertRedirect();

        $order = Order::query()->where('customer_id', $customer->id)->firstOrFail();
        $this->assertMatchesRegularExpression('/^BM-\d{8}-[A-Z0-9]{6}$/', $order->order_number);
        $this->assertSame('120.00', $order->total_amount);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'target_url' => 'https://customer.example.test/landing',
            'anchor_text' => 'customer growth tools',
            'article_topic' => 'A practical guide to growth',
            'requirements' => 'Include a concise introduction.',
        ]);

        $this->post(route('orders.payment.process', $order), [
            'payment_method' => 'demo_card',
            'demo_result' => 'success',
        ])->assertRedirect(route('orders.show', $order));

        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'successful', 'gateway' => 'demo']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid', 'order_status' => 'processing']);
        $this->assertDatabaseHas('invoices', ['order_id' => $order->id, 'customer_id' => $customer->id]);
        Notification::assertSentTo($customer, OrderPlacedNotification::class);
    }

    public function test_coupon_discount_is_calculated_by_the_server(): void
    {
        $customer = User::factory()->create();
        Coupon::create([
            'code' => 'SAVE10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'minimum_order_amount' => 50,
            'usage_limit' => 10,
            'per_customer_limit' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($customer)->get(route('orders.cart'));
        $this->post(route('orders.cart.items.store'), ['product_key' => 'guest_post', 'quantity' => 1]);
        $this->post(route('orders.cart.coupon'), ['coupon_code' => 'save10'])->assertRedirect();
        $this->post(route('orders.store'), [
            'items' => [[
                'target_url' => 'https://customer.example.test/page',
                'anchor_text' => 'growth platform',
                'article_topic' => 'Growth platforms explained',
                'requirements' => '',
            ]],
        ])->assertRedirect();

        $order = Order::query()->where('customer_id', $customer->id)->firstOrFail();
        $this->assertSame('108.00', $order->total_amount);
        $this->assertSame('12.00', $order->discount_amount);
        $this->assertDatabaseHas('coupon_usages', ['coupon_id' => Coupon::where('code', 'SAVE10')->value('id'), 'customer_id' => $customer->id]);
    }

    public function test_declined_payment_does_not_issue_an_invoice_or_consume_the_coupon(): void
    {
        $customer = User::factory()->create();
        Coupon::create([
            'code' => 'ONCE10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'minimum_order_amount' => 0,
            'per_customer_limit' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($customer)->get(route('orders.cart'));
        $this->post(route('orders.cart.items.store'), ['product_key' => 'link_insertion', 'quantity' => 1]);
        $this->post(route('orders.cart.coupon'), ['coupon_code' => 'ONCE10']);
        $this->post(route('orders.store'), ['items' => [[
            'target_url' => 'https://customer.example.test/page',
            'anchor_text' => 'resources',
            'article_topic' => 'Useful resources',
        ]]]);
        $order = Order::query()->where('customer_id', $customer->id)->firstOrFail();

        $this->post(route('orders.payment.process', $order), [
            'payment_method' => 'demo_card',
            'demo_result' => 'declined',
        ])->assertRedirect(route('orders.show', $order));

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'failed']);
        $this->assertDatabaseMissing('invoices', ['order_id' => $order->id]);
        $this->assertDatabaseMissing('coupon_usages', ['order_id' => $order->id]);
    }

    public function test_only_fulfillment_staff_can_settle_reserved_credits(): void
    {
        $customer = User::factory()->create();
        $staff = User::factory()->create(['account_type' => 'seo']);
        $order = Order::create([
            'order_number' => 'BM-20261110-PAID01',
            'customer_id' => $customer->id,
            'session_id' => 'customer-session',
            'total_amount' => 80,
            'payment_status' => 'paid',
            'order_status' => 'processing',
        ]);
        $orderItem = $order->items()->create([
            'item_name' => 'Niche Edit',
            'unit_price' => 80,
            'quantity' => 1,
            'credit_cost' => 25,
            'target_url' => 'https://customer.example.test/page',
            'anchor_text' => 'growth platform',
            'article_topic' => 'Growth platforms explained',
        ]);
        $wallet = Wallet::create(['customer_id' => $customer->id, 'available_balance' => 75, 'reserved_balance' => 25]);
        $reservation = CreditReservation::create([
            'wallet_id' => $wallet->id,
            'order_item_id' => $orderItem->id,
            'reserved_credits' => 25,
            'status' => 'reserved',
        ]);

        $this->actingAs($customer)->postJson(route('api.credits.settle', $orderItem), ['published' => true])->assertForbidden();
        $this->actingAs($staff)->postJson(route('api.credits.settle', $orderItem), ['published' => true])->assertOk();

        $this->assertDatabaseHas('credit_reservations', ['id' => $reservation->id, 'status' => 'captured']);
        $this->assertDatabaseHas('wallets', ['id' => $wallet->id, 'available_balance' => 75, 'reserved_balance' => 0]);
        $this->assertDatabaseHas('credit_transactions', ['wallet_id' => $wallet->id, 'transaction_type' => 'deduction', 'amount' => 25]);
    }

    public function test_successful_credit_package_purchase_updates_wallet_and_payment_history(): void
    {
        $customer = User::factory()->create();
        $package = CreditPackage::create([
            'name' => 'Starter Credits',
            'credits' => 100,
            'price' => 29,
            'is_active' => true,
        ]);

        $this->actingAs($customer)
            ->postJson(route('api.wallet.purchase', $package))
            ->assertCreated()
            ->assertJsonPath('data.wallet.available_balance', 100);

        $this->assertDatabaseHas('payments', [
            'customer_id' => $customer->id,
            'credit_package_id' => $package->id,
            'status' => 'successful',
            'gateway' => 'demo',
        ]);
        $this->assertDatabaseHas('credit_transactions', [
            'transaction_type' => 'purchase',
            'amount' => 100,
        ]);
    }

    public function test_credit_reservation_deducts_available_balance_once(): void
    {
        $customer = User::factory()->create();
        $order = Order::create([
            'order_number' => 'BM-20261110-RESV01',
            'customer_id' => $customer->id,
            'session_id' => 'reservation-session',
            'total_amount' => 80,
            'payment_status' => 'paid',
        ]);
        $orderItem = $order->items()->create([
            'item_name' => 'Niche Edit',
            'unit_price' => 80,
            'quantity' => 1,
            'credit_cost' => 25,
            'target_url' => 'https://customer.example.test/page',
            'anchor_text' => 'growth platform',
            'article_topic' => 'Growth platforms explained',
        ]);
        Wallet::create(['customer_id' => $customer->id, 'available_balance' => 75, 'reserved_balance' => 0]);

        $this->actingAs($customer)->postJson(route('api.credits.reserve'), ['order_item_id' => $orderItem->id])->assertCreated();
        $this->postJson(route('api.credits.reserve'), ['order_item_id' => $orderItem->id])->assertCreated();

        $this->assertDatabaseHas('wallets', ['customer_id' => $customer->id, 'available_balance' => 50, 'reserved_balance' => 25]);
        $this->assertDatabaseCount('credit_reservations', 1);
    }

    public function test_customer_cannot_pay_another_customers_order(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $order = Order::create([
            'order_number' => 'BM-20261110-OTHER1',
            'customer_id' => $otherCustomer->id,
            'session_id' => 'other-customer-session',
            'total_amount' => 60,
        ]);

        $this->actingAs($customer)
            ->post(route('orders.payment.process', $order), ['payment_method' => 'demo_card', 'demo_result' => 'success'])
            ->assertNotFound();

        $this->assertDatabaseMissing('payments', ['order_id' => $order->id]);
    }
}
