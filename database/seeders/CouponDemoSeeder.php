<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\CouponEmailLog;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class CouponDemoSeeder extends Seeder
{
    /**
     * Sample coupons for the checkout popup.
     *
     * CHECKOUT10 is the code offered on checkout for any package, including the $39 Starter Text Link package.
     */
    public function run(): void
    {
        $expires = now()->addMonths(3);

        $this->coupon('CHECKOUT10', [
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'applicable_packages' => null,
            'purchase_type' => 'both',
            'minimum_order_amount' => 0,
            'usage_limit' => 200,
            'per_customer_limit' => 1,
            'expires_at' => $expires,
            'is_active' => true,
        ]);

        $this->coupon('STARTER5', [
            'discount_type' => 'fixed',
            'discount_value' => 5,
            'applicable_packages' => ['starter'],
            'purchase_type' => 'package',
            'minimum_order_amount' => 0,
            'usage_limit' => 100,
            'per_customer_limit' => 1,
            'expires_at' => $expires,
            'is_active' => true,
        ]);

        $this->coupon('PRO15', [
            'discount_type' => 'percentage',
            'discount_value' => 15,
            'applicable_packages' => ['pro', 'professional'],
            'purchase_type' => 'package',
            'minimum_order_amount' => 0,
            'usage_limit' => 100,
            'per_customer_limit' => 1,
            'expires_at' => $expires,
            'is_active' => true,
        ]);

        $this->coupon('ENTERPRISE50', [
            'discount_type' => 'fixed',
            'discount_value' => 50,
            'maximum_discount_amount' => 50,
            'applicable_packages' => ['enterprise'],
            'purchase_type' => 'package',
            'minimum_order_amount' => 100,
            'usage_limit' => 20,
            'per_customer_limit' => 1,
            'expires_at' => $expires,
            'is_active' => true,
        ]);

        $once = $this->coupon('ONCE-STARTER', [
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'applicable_packages' => ['starter'],
            'purchase_type' => 'package',
            'minimum_order_amount' => 0,
            'usage_limit' => 1,
            'per_customer_limit' => 1,
            'expires_at' => $expires,
            'is_active' => true,
        ]);

        $this->coupon('EXPIRED20', [
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'applicable_packages' => null,
            'purchase_type' => 'both',
            'minimum_order_amount' => 0,
            'usage_limit' => 10,
            'per_customer_limit' => 1,
            'starts_at' => now()->subMonths(2),
            'expires_at' => now()->subDay(),
            'is_active' => true,
        ]);

        $this->coupon('PAUSED5', [
            'discount_type' => 'fixed',
            'discount_value' => 5,
            'applicable_packages' => null,
            'purchase_type' => 'both',
            'minimum_order_amount' => 0,
            'usage_limit' => 10,
            'per_customer_limit' => 1,
            'expires_at' => $expires,
            'is_active' => false,
        ]);

        $redeemed = $this->coupon('REDEEMED10', [
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'applicable_packages' => ['starter'],
            'purchase_type' => 'package',
            'minimum_order_amount' => 0,
            'usage_limit' => 1,
            'per_customer_limit' => 1,
            'expires_at' => $expires,
            'is_active' => true,
        ]);

        $customer = User::query()->where('email', 'noah.williams@example.com')->first();

        if ($customer === null) {
            return;
        }

        if ($once->customer_id === null && ! $once->usages()->exists()) {
            $once->update(['customer_id' => $customer->id]);
        }

        CouponEmailLog::query()->updateOrCreate(
            ['coupon_id' => $once->id, 'customer_id' => $customer->id],
            ['email' => $customer->email, 'sent_at' => now()->subDays(2)],
        );

        $order = Order::query()->updateOrCreate(
            ['order_number' => 'BM-COUPON-DEMO'],
            [
                'customer_id' => $customer->id,
                'session_id' => 'coupon-demo',
                'total_amount' => 44.10,
                'discount_amount' => 4.90,
                'coupon_code' => $redeemed->code,
                'payment_status' => 'paid',
                'order_status' => 'completed',
            ],
        );

        $order->items()->updateOrCreate(
            ['item_name' => 'Starter Backlink Package'],
            [
                'unit_price' => 49,
                'quantity' => 1,
                'target_url' => 'https://example.com/starter',
                'anchor_text' => 'starter campaign',
                'article_topic' => 'Starter backlinks',
                'requirements' => 'Sample redeemed coupon order.',
            ],
        );

        CouponUsage::query()->updateOrCreate(
            ['coupon_id' => $redeemed->id, 'customer_id' => $customer->id, 'order_id' => $order->id],
            ['discount_amount' => 4.90],
        );

        $redeemed->update(['customer_id' => $customer->id]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function coupon(string $code, array $attributes): Coupon
    {
        return Coupon::query()->updateOrCreate(['code' => $code], $attributes);
    }
}
