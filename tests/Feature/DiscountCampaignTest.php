<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\DiscountCampaign;
use App\Models\Order;
use App\Models\User;
use App\Notifications\CouponNotice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DiscountCampaignTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_order_discount_is_automatic_and_only_recorded_after_payment(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('10% off on your first order', false);

        $this->post(route('orders.cart.items.store'), ['product_key' => 'guest_post', 'quantity' => 1]);
        $this->post(route('orders.store'), ['items' => [[
            'target_url' => 'https://customer.example.test/page',
            'anchor_text' => 'starter campaign',
            'article_topic' => 'First campaign',
        ]]])->assertRedirect();

        $order = Order::query()->where('customer_id', $customer->id)->firstOrFail();
        $this->assertSame('108.00', $order->total_amount);
        $this->assertSame('12.00', $order->discount_amount);
        $this->assertSame('first_order', $order->offer_type);
        $this->assertDatabaseCount('first_order_redemptions', 0);

        $this->post(route('orders.payment.process', $order), [
            'payment_method' => 'demo_card',
            'demo_result' => 'declined',
        ])->assertRedirect();
        $this->assertDatabaseCount('first_order_redemptions', 0);

        $this->post(route('orders.payment.process', $order), [
            'payment_method' => 'demo_card',
            'demo_result' => 'success',
        ])->assertRedirect();
        $this->assertDatabaseHas('first_order_redemptions', ['customer_id' => $customer->id, 'order_id' => $order->id]);

        $this->get(route('customer.coupons'))
            ->assertOk()
            ->assertSee('First-order discount used', false)
            ->assertSee('Available coupons', false)
            ->assertSee('Used coupons', false)
            ->assertSee('Expired coupons', false);
    }

    public function test_loyalty_and_comeback_coupons_are_issued_once_and_shown_on_the_dashboard(): void
    {
        Notification::fake();
        $customer = User::factory()->create(['account_type' => 'customer']);

        foreach ([1, 2, 3] as $number) {
            $customer->orders()->create([
                'order_number' => 'BM-LOYAL-'.$number,
                'session_id' => 'loyal-'.$number,
                'total_amount' => 40,
                'payment_status' => 'paid',
                'order_status' => 'completed',
            ]);
        }

        Artisan::call('discounts:issue');
        Artisan::call('discounts:issue');

        $loyalty = Coupon::query()->where('customer_id', $customer->id)->where('type', 'loyalty')->get();
        $this->assertCount(1, $loyalty);
        $this->assertSame(3, $loyalty->first()->milestone);
        Notification::assertSentToTimes($customer, CouponNotice::class, 1);

        $customer->orders()->update(['updated_at' => now()->subDays(40)]);

        Artisan::call('discounts:issue');
        Artisan::call('discounts:issue');

        $this->assertSame(1, Coupon::query()->where('customer_id', $customer->id)->where('type', 'comeback')->count());

        $this->actingAs($customer)->get(route('customer.coupons'))
            ->assertOk()
            ->assertSee($loyalty->first()->code, false)
            ->assertSee('Copy code', false);
    }

    public function test_admin_can_disable_the_first_order_campaign(): void
    {
        $admin = User::factory()->create(['account_type' => 'admin']);
        $campaign = DiscountCampaign::findType('first_order');

        $this->actingAs($admin)->get(route('admin.coupons'))
            ->assertOk()
            ->assertSee('First-order discount', false)
            ->assertSee('Loyalty reward', false)
            ->assertSee('Comeback coupon', false)
            ->assertSee('Order reward', false)
            ->assertSee('Save-for-later deadline (days)', false);

        $this->put(route('admin.coupons.campaigns.update', $campaign), [
            'is_enabled' => '0',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'minimum_order_amount' => 0,
            'combines_with_package_discount' => '0',
        ])->assertRedirect();

        $this->assertFalse($campaign->fresh()->is_enabled);

        $customer = User::factory()->create();
        $this->actingAs($customer)->post(route('orders.cart.items.store'), ['product_key' => 'guest_post', 'quantity' => 1]);
        $this->post(route('orders.store'), ['items' => [[
            'target_url' => 'https://customer.example.test/page',
            'anchor_text' => 'starter campaign',
            'article_topic' => 'Full price campaign',
        ]]]);

        $this->assertSame('120.00', Order::query()->where('customer_id', $customer->id)->firstOrFail()->total_amount);
    }

    public function test_each_paid_order_gives_a_two_percent_coupon_for_a_later_package_or_backlink(): void
    {
        Notification::fake();
        $customer = User::factory()->create();
        $order = $customer->orders()->create([
            'order_number' => 'BM-REWARD-0001',
            'session_id' => 'order-reward',
            'total_amount' => 80,
            'payment_status' => 'pending',
            'order_status' => 'pending',
        ]);
        $order->items()->create([
            'item_name' => 'Publish Guest Post on farmpulse.example',
            'unit_price' => 80,
            'quantity' => 1,
            'target_url' => 'https://customer.example.test/page',
            'anchor_text' => 'starter campaign',
            'article_topic' => 'Individual backlink',
        ]);

        $this->actingAs($customer)->get(route('orders.payment', $order))
            ->assertOk()
            ->assertSee('Available coupons', false)
            ->assertSee('Use coupon', false)
            ->assertDontSee('Your 2% coupon', false)
            ->assertDontSee('data-reward-coupon', false)
            ->assertDontSee('data-coupon-popup', false)
            ->assertDontSee('Save for later', false)
            ->assertDontSee('individual backlink', false)
            ->assertSee('Pay with dummy gateway', false);

        $coupon = Coupon::query()->where('reward_order_id', $order->id)->firstOrFail();
        $this->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Available coupons', false)
            ->assertSee('href="'.route('customer.coupons').'"', false)
            ->assertSee($coupon->code, false)
            ->assertSee('Deadline '.$coupon->expires_at->format('M j, Y'), false)
            ->assertSee('Use on next purchase', false);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('order_reward', $coupon->type);
        $this->assertSame('2.00', $coupon->discount_value);
        $this->assertSame('both', $coupon->purchase_type);
        $this->assertNull($coupon->saved_for_later_at);
        $this->assertTrue($coupon->expires_at->greaterThan(now()->addDays(13)));
        Notification::assertSentTo($customer, CouponNotice::class);

        $order->update([
            'coupon_code' => $coupon->code,
            'offer_type' => 'order_reward',
            'discount_amount' => 1.60,
            'total_amount' => 78.40,
        ]);
        $this->get(route('orders.payment', $order))
            ->assertOk()
            ->assertDontSee('Apply', false)
            ->assertDontSee('Save for later', false)
            ->assertSee('Coupon '.$coupon->code.' is applied', false)
            ->assertSee('78.40', false);
        $this->assertSame($coupon->code, $order->fresh()->coupon_code);
        $this->assertSame('78.40', $order->fresh()->total_amount);

        $this->post(route('orders.payment.coupon', $order), ['coupon_code' => $coupon->code])
            ->assertRedirect();
        $order->refresh();
        $this->assertSame('1.60', $order->discount_amount);
        $this->assertSame('78.40', $order->total_amount);
        $this->assertDatabaseCount('coupon_usages', 0);

        $this->delete(route('orders.payment.coupon.destroy', $order))->assertRedirect();
        $this->assertSame('72.00', $order->fresh()->total_amount);
        $this->assertSame('first_order', $order->fresh()->offer_type);

        $this->post(route('orders.payment.process', $order), [
            'payment_method' => 'demo_card',
            'demo_result' => 'declined',
        ])->assertRedirect(route('orders.show', $order));
        $this->assertDatabaseCount('coupon_usages', 0);

        $this->get(route('orders.thanks', $order))->assertRedirect(route('orders.payment', $order));

        $this->from(route('orders.payment', $order))
            ->post(route('customer.coupons.save', $coupon))
            ->assertRedirect(route('orders.payment', $order))
            ->assertSessionHas('saved_coupon_popup.code', $coupon->code);
        $coupon->refresh();
        $this->assertNotNull($coupon->saved_for_later_at);

        $this->get(route('orders.payment', $order))
            ->assertOk()
            ->assertSee('Available coupons', false)
            ->assertSee($coupon->code, false)
            ->assertSee('Use coupon', false)
            ->assertDontSee('You must select this coupon before you make a payment.', false)
            ->assertDontSee('Save for later', false);

        $this->get(route('customer.coupons'))
            ->assertOk()
            ->assertSee('Saved for later. Use it before '.$coupon->expires_at->format('M j, Y'), false)
            ->assertDontSee('data-reward-apply', false);

        $this->actingAs(User::factory()->create())
            ->post(route('customer.coupons.save', $coupon))
            ->assertNotFound();

        $this->actingAs($customer);
        session(['shopping_cart' => [
            'pub-1' => [
                'line' => 'pub-1',
                'domain' => 'farmpulse.example',
                'display_name' => 'Farm Pulse',
                'price' => 100,
                'quantity' => 1,
                'order_label' => 'Guest post',
            ],
        ]]);

        $this->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Saved for later. Use it before '.$coupon->expires_at->format('M j, Y'), false);

        $this->get(route('shopping.checkout'))
            ->assertOk()
            ->assertSee('individual backlink', false)
            ->assertSee($coupon->code, false)
            ->assertSee('Apply this coupon before you place the order, or leave it for later.', false)
            ->assertSee('Not now', false)
            ->assertSee('data-saved-coupon-select', false)
            ->assertDontSee('data-reward-apply', false)
            ->assertDontSee('Save for later</button>', false)
            ->assertDontSee('Deadline '.$coupon->expires_at->format('M j, Y'), false);

        $this->from(route('customer.dashboard'))
            ->post(route('customer.coupons.apply', $coupon))
            ->assertRedirect(route('shopping.checkout'))
            ->assertSessionHas('shopping_coupon', $coupon->code);
        $this->get(route('shopping.checkout'))
            ->assertOk()
            ->assertSee('Coupon '.$coupon->code.' is applied', false)
            ->assertSee('2.00', false)
            ->assertSee('98.00', false);

        $this->post(route('shopping.coupon'), ['coupon_code' => $coupon->code])->assertRedirect();
        $this->get(route('shopping.checkout'))
            ->assertOk()
            ->assertSee('2.00', false)
            ->assertSee('98.00', false);

        $coupon->update(['expires_at' => now()->subDay()]);
        $this->from(route('shopping.checkout'))
            ->post(route('shopping.coupon'), ['coupon_code' => $coupon->code])
            ->assertSessionHasErrors('coupon_code');
    }

    public function test_each_applied_coupon_lowers_the_amount_due_and_moves_onto_the_order(): void
    {
        Notification::fake();
        $customer = User::factory()->create();
        $order = $customer->orders()->create([
            'order_number' => 'BM-REWARD-0002',
            'session_id' => 'order-reward-stack',
            'total_amount' => 80,
            'payment_status' => 'pending',
            'order_status' => 'pending',
        ]);
        $order->items()->create([
            'item_name' => 'Publish Guest Post on farmpulse.example',
            'unit_price' => 80,
            'quantity' => 1,
            'target_url' => 'https://customer.example.test/page',
            'anchor_text' => 'starter campaign',
            'article_topic' => 'Individual backlink',
        ]);

        $this->actingAs($customer)->get(route('orders.payment', $order))->assertOk();
        $first = Coupon::query()->where('reward_order_id', $order->id)->firstOrFail();
        $second = $first->replicate(['reward_order_id']);
        $second->code = 'ORDER-SECOND1';
        $second->save();

        $this->post(route('orders.payment.coupon', $order), ['coupon_code' => $first->code])->assertRedirect();
        $order->refresh();
        $this->assertSame('1.60', $order->discount_amount);
        $this->assertSame('78.40', $order->total_amount);

        $this->post(route('orders.payment.coupon', $order), ['coupon_code' => $second->code])->assertRedirect();
        $order->refresh();
        $this->assertSame('3.20', $order->discount_amount);
        $this->assertSame('76.80', $order->total_amount);
        $this->assertSame([$first->code, $second->code], $order->applied_coupon_codes);

        $this->get(route('orders.payment', $order))
            ->assertOk()
            ->assertSee('On this order', false)
            ->assertSee('Coupon '.$first->code.' is applied', false)
            ->assertSee('Coupon '.$second->code.' is applied', false)
            ->assertSee('76.80', false)
            ->assertDontSee('Use coupon', false);

        $this->delete(route('orders.payment.coupon.destroy', $order), ['coupon_code' => $second->code])->assertRedirect();
        $order->refresh();
        $this->assertSame('1.60', $order->discount_amount);
        $this->assertSame('78.40', $order->total_amount);
        $this->assertSame([$first->code], $order->applied_coupon_codes);
    }
}
