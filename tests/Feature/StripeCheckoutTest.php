<?php

namespace Tests\Feature;

use App\Models\CreditPackage;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StripeCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.stripe.demo' => false,
            'services.stripe.secret' => 'sk_test_fake',
            'services.stripe.webhook_secret' => 'whsec_test_fake',
        ]);
        Http::preventStrayRequests();
        Http::fake(['https://api.stripe.com/v1/checkout/sessions' => Http::response([
            'id' => 'cs_test_order',
            'url' => 'https://checkout.stripe.com/c/pay/cs_test_order',
        ])]);
    }

    public function test_paid_stripe_order_is_confirmed_only_once_after_a_valid_webhook(): void
    {
        Notification::fake();
        $customer = User::factory()->create();
        $order = $customer->orders()->create([
            'order_number' => 'BM-STRIPE-ORDER', 'session_id' => 'stripe-order',
            'total_amount' => 108, 'discount_amount' => 12,
        ]);

        $this->actingAs($customer)->post(route('orders.payment.process', $order), [
            'payment_method' => 'demo_card', 'demo_result' => 'success', 'amount' => 1,
        ])->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_order');

        $payment = $order->payments()->firstOrFail();
        $this->post(route('orders.payment.process', $order))->assertRedirect();
        $this->assertDatabaseCount('payments', 1);
        Http::assertSent(fn ($request) => $request['line_items'][0]['price_data']['unit_amount'] === 10800
            && $request->hasHeader('Idempotency-Key', $payment->transaction_id));
        $this->get(route('orders.show', [$order, 'stripe' => 'returned']))->assertOk();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'pending']);
        $this->assertDatabaseCount('invoices', 0);

        $this->webhook($payment, 10800, false)->assertBadRequest();
        $this->webhook($payment, 1)->assertUnprocessable();
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'pending']);
        $this->webhook($payment, 10800)->assertNoContent();
        $this->webhook($payment, 10800)->assertNoContent();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid', 'order_status' => 'awaiting_approval']);
        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseCount('coupons', 1);
        Notification::assertSentToTimes($customer, OrderPlacedNotification::class, 1);
    }

    public function test_stripe_credits_are_added_once_after_confirmation(): void
    {
        $customer = User::factory()->create();
        $package = CreditPackage::create(['name' => 'Starter credits', 'credits' => 100, 'price' => 29, 'is_active' => true]);

        $this->actingAs($customer)->postJson(route('api.wallet.purchase', $package))
            ->assertOk()->assertJsonPath('data.checkout_url', 'https://checkout.stripe.com/c/pay/cs_test_order');
        $payment = $customer->payments()->firstOrFail();
        $this->assertDatabaseCount('credit_transactions', 0);

        $this->webhook($payment, 2900)->assertNoContent();
        $this->webhook($payment, 2900)->assertNoContent();

        $this->assertDatabaseHas('wallets', ['customer_id' => $customer->id, 'available_balance' => 100]);
        $this->assertDatabaseCount('credit_transactions', 1);
    }

    public function test_expired_checkout_can_be_retried_with_a_new_payment_reference(): void
    {
        $customer = User::factory()->create();
        $order = $customer->orders()->create([
            'order_number' => 'BM-STRIPE-RETRY', 'session_id' => 'stripe-retry', 'total_amount' => 100,
        ]);

        $this->actingAs($customer)->post(route('orders.payment.process', $order))->assertRedirect();
        $expired = $order->payments()->firstOrFail();
        $this->webhook($expired, 10000, event: 'checkout.session.expired')->assertNoContent();
        $this->assertDatabaseHas('payments', ['id' => $expired->id, 'status' => 'failed']);

        $this->post(route('orders.payment.process', $order))->assertRedirect();
        $retry = $order->payments()->where('status', 'pending')->firstOrFail();
        $this->assertNotSame($expired->transaction_id, $retry->transaction_id);
        $this->webhook($expired, 10000, event: 'checkout.session.expired')->assertNoContent();
        $this->assertDatabaseHas('payments', ['id' => $retry->id, 'status' => 'pending']);
    }

    private function webhook(Payment $payment, int $amount, bool $valid = true, string $event = 'checkout.session.completed'): TestResponse
    {
        $body = json_encode(['type' => $event, 'data' => ['object' => [
            'id' => 'cs_test_order', 'client_reference_id' => $payment->transaction_id,
            'mode' => 'payment', 'status' => 'complete', 'payment_status' => 'paid',
            'currency' => 'usd', 'amount_total' => $amount,
        ]]], JSON_THROW_ON_ERROR);
        $timestamp = now()->timestamp;
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, $valid ? 'whsec_test_fake' : 'wrong-secret');

        return $this->call('POST', route('stripe.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
        ], $body);
    }
}
