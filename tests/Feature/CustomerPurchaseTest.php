<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\CreditPackage;
use App\Models\CreditReservation;
use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\CheckoutNotification;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CustomerPurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cart_is_kept_when_the_customer_signs_in(): void
    {
        $customer = User::factory()->create();

        $response = $this->post(route('orders.cart.items.store'), [
            'product_key' => 'guest_post',
            'quantity' => 1,
        ])->assertRedirect(route('auth.login'));

        $sessionCookie = config('session.cookie');
        $this->withCookie($sessionCookie, $response->getCookie($sessionCookie)->getValue());
        $response = $this->post(route('auth.login'), [
            'email' => $customer->email,
            'password' => 'password',
        ])->assertRedirect(route('orders.checkout'));

        $this->withCookie($sessionCookie, $response->getCookie($sessionCookie)->getValue());
        $this->get(route('orders.checkout'))->assertOk()->assertSee('Guest Post Placement');
    }

    public function test_checkout_coupon_and_payment_precede_publishing_details_and_order_confirmation(): void
    {
        Notification::fake();
        $customer = User::factory()->create();
        $admin = User::factory()->create(['account_type' => 'admin']);

        $this->actingAs($customer)->post(route('orders.cart.items.store'), [
            'product_key' => 'guest_post',
            'quantity' => 1,
        ])->assertRedirect();

        $this->from(route('orders.checkout'))->post(route('orders.checkout.coupon'))->assertRedirect();
        $coupon = Coupon::where('customer_id', $customer->id)->sole();
        Notification::assertSentTo($customer, CheckoutNotification::class, fn ($notice) => $notice->subject->is($coupon));

        $this->post(route('orders.cart.coupon'), ['coupon_code' => $coupon->code])->assertRedirect();
        $this->post(route('orders.checkout.payment'))->assertRedirect();
        $order = $customer->orders()->sole();
        $this->assertSame('108.00', $order->total_amount);

        $details = ['items' => [[
            'target_url' => 'https://customer.example.test/landing',
            'anchor_text' => 'growth tools',
            'article_topic' => 'A guide to growth',
        ]]];
        $this->post(route('orders.details', $order), $details)->assertConflict();

        $this->post(route('orders.payment.process', $order), [
            'payment_method' => 'demo_card',
            'demo_result' => 'success',
        ])->assertRedirect(route('orders.show', $order));

        $this->assertSame('awaiting_details', $order->fresh()->order_status);
        $this->assertNotNull($order->invoice);
        $this->get(route('orders.show', $order))->assertOk()->assertSee('Submit for review');
        Notification::assertNotSentTo($admin, CheckoutNotification::class);

        $this->post(route('orders.details', $order), $details)->assertRedirect();
        $this->assertSame('awaiting_approval', $order->fresh()->order_status);
        $this->assertSame('growth tools', $order->items()->sole()->anchor_text);
        Notification::assertSentTo($customer, CheckoutNotification::class, fn ($notice) => $notice->subject->is($order));
        Notification::assertSentTo($admin, CheckoutNotification::class, fn ($notice) => $notice->forAdmin && $notice->subject->is($order));
        $this->post(route('orders.details', $order), $details)->assertConflict();
    }

    public function test_customer_can_create_order_and_complete_demo_payment(): void
    {
        Notification::fake();
        $customer = User::factory()->create();

        $this->actingAs($customer)->get(route('orders.cart'))->assertOk()->assertSee('aria-label="My account"', false);
        $this->post(route('orders.cart.items.store'), [
            'product_key' => 'guest_post',
            'quantity' => 1,
            'unit_price' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('cart_items', ['item_name' => 'Guest Post Placement', 'unit_price' => 120]);

        $this->get(route('orders.checkout'))->assertOk()->assertSee('aria-label="My account"', false);

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
        foreach (['orders.payment', 'orders.index', 'orders.show'] as $routeName) {
            $url = $routeName === 'orders.index' ? route($routeName) : route($routeName, $order);
            $this->get($url)->assertOk()->assertSee('aria-label="My account"', false)->assertSee($order->order_number);
        }

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
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid', 'order_status' => 'awaiting_approval']);
        $this->assertDatabaseHas('invoices', ['order_id' => $order->id, 'customer_id' => $customer->id]);
        $coupon = $order->rewardCoupon()->firstOrFail();
        Notification::assertSentTo($customer, OrderPlacedNotification::class, function (OrderPlacedNotification $notice) use ($customer, $coupon): bool {
            return str_contains(implode(' ', $notice->toMail($customer)->introLines), $coupon->code);
        });
        $this->get(route('customer.invoices.download', $order->invoice))->assertOk()->assertDownload();

        $this->post(route('orders.payment.process', $order), [
            'payment_method' => 'demo_card',
            'demo_result' => 'success',
        ])->assertRedirect();
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('coupons', 1);

        $this->post(route('orders.cart.items.store'), ['product_key' => 'guest_post', 'quantity' => 1])->assertRedirect();
        $this->postJson(route('api.cart.coupon'), ['coupon_code' => $coupon->code])
            ->assertOk()->assertJsonPath('data.discount', 12);
        $this->post(route('orders.store'), [
            'items' => [[
                'target_url' => 'https://customer.example.test/second',
                'anchor_text' => 'next campaign',
                'article_topic' => 'Planning a second campaign',
            ]],
        ])->assertRedirect();
        $nextOrder = $customer->orders()->where('id', '!=', $order->id)->firstOrFail();
        $this->postJson(route('api.payments.orders.store', $nextOrder), [
            'payment_method' => 'demo_card',
            'demo_result' => 'success',
        ])->assertOk()->assertJsonPath('data.payment.amount', '108.00');

        $this->post(route('orders.cart.items.store'), ['product_key' => 'guest_post', 'quantity' => 1])->assertRedirect();
        $this->postJson(route('api.cart.coupon'), ['coupon_code' => $coupon->code])->assertUnprocessable();
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
