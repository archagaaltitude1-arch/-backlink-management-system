<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\CouponNotice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AdminCouponManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_percentage_and_fixed_coupons_without_emailing_customers(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['account_type' => 'admin']);
        User::factory()->create(['account_type' => 'customer']);

        $this->actingAs($admin)->get(route('admin.coupons'))
            ->assertOk()
            ->assertSee('Coupon Management');

        $this->post(route('admin.coupons.store'), $this->couponPayload([
            'code' => 'backlink10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'applicable_packages' => ['starter'],
            'minimum_order_amount' => 50,
            'expires_at' => now()->addMonth()->format('Y-m-d\TH:i'),
        ]))->assertRedirect();

        $this->assertDatabaseHas('coupons', [
            'code' => 'BACKLINK10',
            'discount_type' => 'percentage',
            'purchase_type' => 'both',
            'is_active' => true,
        ]);

        $this->post(route('admin.coupons.store'), $this->couponPayload([
            'code' => '',
            'generate_code' => '1',
            'discount_type' => 'fixed',
            'discount_value' => 10,
            'applicable_packages' => ['all'],
            'maximum_discount_amount' => 10,
        ]))->assertRedirect();

        $fixed = Coupon::query()->where('discount_type', 'fixed')->firstOrFail();
        $this->assertSame(10.0, (float) $fixed->discount_value);
        $this->assertNull($fixed->applicable_packages);
        Notification::assertNothingSent();

        $this->post(route('admin.coupons.store'), $this->couponPayload([
            'code' => 'BACKLINK10',
        ]))->assertSessionHasErrors('code');
    }

    public function test_admin_adds_a_coupon_to_the_customer_dashboard_without_email(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['account_type' => 'admin']);
        $chosen = User::factory()->create(['account_type' => 'customer', 'name' => 'Mina Customer']);
        $other = User::factory()->create(['account_type' => 'customer', 'name' => 'Other Customer']);
        $coupon = $this->makeCoupon([
            'code' => 'BACKLINK10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'applicable_packages' => ['starter', 'pro'],
            'minimum_order_amount' => 50,
            'expires_at' => now()->addDays(14),
        ]);

        $this->actingAs($admin)->post(route('admin.coupons.send', $coupon), [
            'audience' => 'selected',
            'customers' => [$chosen->id],
        ])->assertRedirect()->assertSessionHas('success');

        Notification::assertSentTo($chosen, CouponNotice::class);
        Notification::assertNotSentTo($other, CouponNotice::class);
        $this->assertDatabaseMissing('coupon_email_logs', ['coupon_id' => $coupon->id]);
        $this->assertSame($chosen->id, $coupon->fresh()->customer_id);

        $this->get(route('admin.coupons.show', $coupon))
            ->assertOk()
            ->assertSee($chosen->email)
            ->assertSee('This coupon has not been redeemed.');
    }

    public function test_checkout_accepts_a_valid_coupon_and_rejects_invalid_ones_before_payment(): void
    {
        $customer = User::factory()->create();
        $percentage = $this->makeCoupon([
            'code' => 'BACKLINK10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'applicable_packages' => ['starter'],
            'minimum_order_amount' => 0,
        ]);
        $fixed = $this->makeCoupon([
            'code' => 'SAVE10',
            'discount_type' => 'fixed',
            'discount_value' => 10,
            'applicable_packages' => null,
        ]);
        $this->makeCoupon([
            'code' => 'OLD10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'expires_at' => now()->subDay(),
        ]);
        $this->makeCoupon([
            'code' => 'PROONLY',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'applicable_packages' => ['professional'],
        ]);
        $limited = $this->makeCoupon([
            'code' => 'ONCE',
            'discount_type' => 'fixed',
            'discount_value' => 5,
            'usage_limit' => 1,
        ]);
        $personal = $this->makeCoupon([
            'code' => 'MINE',
            'discount_type' => 'fixed',
            'discount_value' => 5,
            'per_customer_limit' => 1,
        ]);
        $this->makeCoupon([
            'code' => 'BIGORDER',
            'discount_type' => 'fixed',
            'discount_value' => 5,
            'minimum_order_amount' => 100,
        ]);
        $capped = $this->makeCoupon([
            'code' => 'HALF',
            'discount_type' => 'percentage',
            'discount_value' => 50,
            'maximum_discount_amount' => 5,
        ]);
        $huge = $this->makeCoupon([
            'code' => 'HUGE',
            'discount_type' => 'fixed',
            'discount_value' => 100,
        ]);

        $other = User::factory()->create();
        $spent = Order::query()->create([
            'order_number' => 'BM-LIMIT-1',
            'customer_id' => $other->id,
            'session_id' => 'limit',
            'total_amount' => 44,
            'discount_amount' => 5,
            'coupon_code' => 'ONCE',
        ]);
        CouponUsage::query()->create([
            'coupon_id' => $limited->id,
            'customer_id' => $other->id,
            'order_id' => $spent->id,
            'discount_amount' => 5,
        ]);
        $mine = Order::query()->create([
            'order_number' => 'BM-LIMIT-2',
            'customer_id' => $customer->id,
            'session_id' => 'mine',
            'total_amount' => 44,
            'discount_amount' => 5,
            'coupon_code' => 'MINE',
        ]);
        CouponUsage::query()->create([
            'coupon_id' => $personal->id,
            'customer_id' => $customer->id,
            'order_id' => $mine->id,
            'discount_amount' => 5,
        ]);

        $this->actingAs($customer);
        $line = $this->addStarterPackage();

        $this->from(route('shopping.checkout'))
            ->post(route('shopping.coupon'), ['coupon_code' => 'missing'])
            ->assertSessionHas('shopping_error', 'Invalid coupon code.');
        $this->from(route('shopping.checkout'))
            ->post(route('shopping.coupon'), ['coupon_code' => 'old10'])
            ->assertSessionHas('shopping_error', 'Coupon has expired.');
        $this->from(route('shopping.checkout'))
            ->post(route('shopping.coupon'), ['coupon_code' => 'PROONLY'])
            ->assertSessionHas('shopping_error', 'Coupon is not applicable to this package.');
        $this->from(route('shopping.checkout'))
            ->post(route('shopping.coupon'), ['coupon_code' => 'ONCE'])
            ->assertSessionHas('shopping_error', 'Coupon usage limit reached.');
        $this->from(route('shopping.checkout'))
            ->post(route('shopping.coupon'), ['coupon_code' => 'MINE'])
            ->assertSessionHas('shopping_error', 'You have already used this coupon.');
        $this->from(route('shopping.checkout'))
            ->post(route('shopping.coupon'), ['coupon_code' => 'BIGORDER'])
            ->assertSessionHas('shopping_error', 'Minimum order amount not reached.');

        $this->from(route('shopping.checkout'))
            ->post(route('shopping.coupon'), ['coupon_code' => 'backlink10'])
            ->assertSessionHas('shopping_status');
        $this->get(route('shopping.checkout'))
            ->assertOk()
            ->assertSee('$49.00', false)
            ->assertSee('-$4.90', false)
            ->assertSee('$44.10', false)
            ->assertSee('BACKLINK10');
        $this->assertDatabaseCount('coupon_usages', 2);

        $this->delete(route('shopping.coupon.destroy'))->assertRedirect();
        $this->assertNull(session('shopping_coupon'));

        $this->from(route('shopping.checkout'))
            ->post(route('shopping.coupon'), ['coupon_code' => 'HALF'])
            ->assertSessionHas('shopping_status');
        $this->get(route('shopping.checkout'))->assertSee('-$5.00', false)->assertSee('$44.00', false);

        $this->delete(route('shopping.coupon.destroy'));
        $this->post(route('shopping.coupon'), ['coupon_code' => 'HUGE']);
        $this->get(route('shopping.checkout'))->assertSee('$0.00', false);

        $this->delete(route('shopping.coupon.destroy'));
        $this->post(route('shopping.coupon'), ['coupon_code' => $fixed->code]);
        $this->post(route('shopping.checkout.store'), $this->checkoutPayload($line))->assertRedirect();

        $order = Order::query()->where('customer_id', $customer->id)->where('coupon_code', 'SAVE10')->firstOrFail();
        $this->assertSame('39.00', $order->total_amount);
        $this->assertSame('10.00', $order->discount_amount);
        $this->assertDatabaseMissing('coupon_usages', ['coupon_id' => $fixed->id]);
        $this->assertDatabaseMissing('coupon_usages', ['coupon_id' => $percentage->id]);
        $this->assertSame(5.0, $capped->discountAmount(49));
        $this->assertSame(49.0, $huge->discountAmount(49));
    }

    public function test_successful_payment_records_one_redemption_and_a_decline_does_not(): void
    {
        $admin = User::factory()->create(['account_type' => 'admin']);
        $customer = User::factory()->create(['name' => 'Paid Customer']);
        $coupon = $this->makeCoupon([
            'code' => 'BACKLINK10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'applicable_packages' => ['starter'],
        ]);
        $this->actingAs($customer);
        $line = $this->addStarterPackage();
        $this->post(route('shopping.coupon'), ['coupon_code' => 'BACKLINK10']);
        $this->post(route('shopping.checkout.store'), $this->checkoutPayload($line))->assertRedirect();
        $failed = Order::query()->where('coupon_code', 'BACKLINK10')->firstOrFail();

        $this->post(route('orders.payment.process', $failed), [
            'payment_method' => 'demo_card',
            'demo_result' => 'declined',
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', ['id' => $failed->id, 'payment_status' => 'failed']);
        $this->assertDatabaseMissing('coupon_usages', ['coupon_id' => $coupon->id]);

        $this->post(route('orders.payment.process', $failed), [
            'payment_method' => 'demo_card',
            'demo_result' => 'success',
        ])->assertRedirect();
        $this->post(route('orders.payment.process', $failed), [
            'payment_method' => 'demo_card',
            'demo_result' => 'success',
        ])->assertRedirect();

        $this->assertSame(1, CouponUsage::query()->where('coupon_id', $coupon->id)->count());
        $this->assertDatabaseHas('coupon_usages', [
            'coupon_id' => $coupon->id,
            'customer_id' => $customer->id,
            'order_id' => $failed->id,
            'discount_amount' => 4.9,
        ]);

        $this->actingAs($admin)->get(route('admin.coupons.show', $coupon))
            ->assertOk()
            ->assertSee('Paid Customer')
            ->assertSee('$4.90', false);
        $this->get(route('admin.coupons', ['view' => 'expired']))->assertOk()->assertDontSee('BACKLINK10');
        $this->post(route('admin.coupons.activate', $coupon))->assertRedirect();
        $this->assertFalse($coupon->fresh()->is_active);
    }

    public function test_a_repeated_payment_webhook_redeems_the_coupon_once(): void
    {
        config([
            'services.stripe.demo' => false,
            'services.stripe.secret' => 'sk_test_fake',
            'services.stripe.webhook_secret' => 'whsec_test_fake',
        ]);
        Http::preventStrayRequests();
        Http::fake(['https://api.stripe.com/v1/checkout/sessions' => Http::response([
            'id' => 'cs_test_coupon',
            'url' => 'https://checkout.stripe.com/c/pay/cs_test_coupon',
        ])]);

        $customer = User::factory()->create();
        $coupon = $this->makeCoupon([
            'code' => 'BACKLINK10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'applicable_packages' => ['starter'],
            'usage_limit' => 1,
        ]);
        $this->actingAs($customer);
        $line = $this->addStarterPackage();
        $this->post(route('shopping.coupon'), ['coupon_code' => 'BACKLINK10']);
        $this->post(route('shopping.checkout.store'), $this->checkoutPayload($line));
        $order = Order::query()->where('coupon_code', 'BACKLINK10')->firstOrFail();

        $this->post(route('orders.payment.process', $order))->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_coupon');
        $this->assertDatabaseMissing('coupon_usages', ['coupon_id' => $coupon->id]);

        $payment = Payment::query()->where('order_id', $order->id)->firstOrFail();
        $this->webhook($payment, 4410)->assertNoContent();
        $this->webhook($payment, 4410)->assertNoContent();

        $this->assertSame(1, CouponUsage::query()->where('coupon_id', $coupon->id)->count());
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid', 'total_amount' => 44.10]);
    }

    public function test_a_second_customer_cannot_pass_the_usage_limit_after_the_first_payment(): void
    {
        $coupon = $this->makeCoupon([
            'code' => 'BACKLINK10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'usage_limit' => 1,
        ]);
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->actingAs($first);
        $firstLine = $this->addStarterPackage();
        $this->post(route('shopping.coupon'), ['coupon_code' => 'BACKLINK10']);
        $this->post(route('shopping.checkout.store'), $this->checkoutPayload($firstLine));

        $this->actingAs($second);
        $secondLine = $this->addStarterPackage();
        $this->post(route('shopping.coupon'), ['coupon_code' => 'BACKLINK10']);
        $this->post(route('shopping.checkout.store'), $this->checkoutPayload($secondLine));

        $firstOrder = Order::query()->where('customer_id', $first->id)->firstOrFail();
        $secondOrder = Order::query()->where('customer_id', $second->id)->firstOrFail();

        $this->actingAs($first)->post(route('orders.payment.process', $firstOrder), [
            'payment_method' => 'demo_card',
            'demo_result' => 'success',
        ])->assertRedirect();

        $this->actingAs($second)->post(route('orders.payment.process', $secondOrder), [
            'payment_method' => 'demo_card',
            'demo_result' => 'success',
        ])->assertSessionHasErrors('coupon_code');

        $this->assertSame(1, CouponUsage::query()->where('coupon_id', $coupon->id)->count());
        $this->assertDatabaseHas('orders', ['id' => $secondOrder->id, 'payment_status' => 'pending']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function couponPayload(array $overrides): array
    {
        return array_merge([
            'code' => 'BACKLINK10',
            'generate_code' => '0',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'maximum_discount_amount' => '',
            'applicable_packages' => ['all'],
            'purchase_type' => 'both',
            'minimum_order_amount' => 0,
            'starts_at' => '',
            'expires_at' => '',
            'usage_limit' => '',
            'per_customer_limit' => 1,
            'is_active' => '1',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeCoupon(array $overrides): Coupon
    {
        return Coupon::query()->create(array_merge([
            'code' => 'SAVE10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'minimum_order_amount' => 0,
            'purchase_type' => 'both',
            'per_customer_limit' => 1,
            'is_active' => true,
        ], $overrides));
    }

    private function pendingOrder(User $customer, string $number, float $amount, string $itemName): Order
    {
        $order = $customer->orders()->create([
            'order_number' => $number,
            'session_id' => $number,
            'total_amount' => $amount,
            'payment_status' => 'pending',
            'order_status' => 'pending',
        ]);
        $order->items()->create([
            'item_name' => $itemName,
            'unit_price' => $amount,
            'quantity' => 1,
            'target_url' => 'https://example.com/order',
            'anchor_text' => 'campaign',
            'article_topic' => 'Campaign',
        ]);

        return $order;
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
     * @return array<string, mixed>
     */
    private function checkoutPayload(string $line): array
    {
        return ['items' => [[
            'line' => $line,
            'target_url' => 'https://customer.example.test/page',
            'anchor_text' => 'starter campaign',
            'article_topic' => 'Starter backlinks',
        ]]];
    }

    private function webhook(Payment $payment, int $amount): TestResponse
    {
        $body = json_encode(['type' => 'checkout.session.completed', 'data' => ['object' => [
            'id' => 'cs_test_coupon',
            'client_reference_id' => $payment->transaction_id,
            'mode' => 'payment',
            'status' => 'complete',
            'payment_status' => 'paid',
            'currency' => 'usd',
            'amount_total' => $amount,
        ]]], JSON_THROW_ON_ERROR);
        $timestamp = now()->timestamp;
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'whsec_test_fake');

        return $this->call('POST', route('stripe.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
        ], $body);
    }
}
