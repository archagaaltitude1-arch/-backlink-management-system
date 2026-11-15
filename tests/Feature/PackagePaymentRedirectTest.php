<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class PackagePaymentRedirectTest extends TestCase
{
    use RefreshDatabase;

    #[TestWith(['Pro Text Link Package', 'pro-text-link', 'Text link', 40])]
    #[TestWith(['Enterprise Contextual Link Package', 'enterprise-contextual-link', 'Contextual', 60])]
    #[TestWith(['Professional Guest Post Package', 'professional-guest-post', 'Guest post', 70])]
    #[TestWith(['Starter Guest Post Package', 'starter-guest-post', 'Guest post', 50])]
    public function test_paid_package_opens_websites_for_that_service(string $name, string $slug, string $type, int $dr): void
    {
        Notification::fake();
        $customer = User::factory()->create();
        $order = $this->packageOrder($customer, $name);

        $url = route('publishers.index', [
            'type' => $type,
            'dr_min' => $dr,
            'package' => $slug,
            'confirmed' => '1',
        ]);

        $this->actingAs($customer)->post(route('orders.payment.process', $order), [
            'payment_method' => 'demo_card',
            'demo_result' => 'success',
        ])->assertRedirect($url);

        $this->get($url)
            ->assertOk()
            ->assertSee('Payment confirmed. These websites match your '.$name.'.', false)
            ->assertSee('value="'.$type.'" selected', false)
            ->assertSee('value="'.$dr.'" selected', false)
            ->assertSee($type.' websites');
    }

    public function test_paid_display_package_opens_the_publisher_chart(): void
    {
        Notification::fake();
        $customer = User::factory()->create();
        $order = $this->packageOrder($customer, 'Growth Backlink Package');
        $url = route('publishers.index', [
            'package' => 'display-growth',
            'confirmed' => '1',
        ]);

        $this->actingAs($customer)->post(route('orders.payment.process', $order), [
            'payment_method' => 'demo_card',
            'demo_result' => 'success',
        ])->assertRedirect($url);

        $this->get($url)
            ->assertOk()
            ->assertSee('Payment confirmed. These websites match your Growth Backlink Package.', false);
    }

    public function test_declined_package_payment_stays_on_the_order(): void
    {
        $customer = User::factory()->create();
        $order = $this->packageOrder($customer, 'Starter Text Link Package');

        $this->actingAs($customer)->post(route('orders.payment.process', $order), [
            'payment_method' => 'demo_card',
            'demo_result' => 'declined',
        ])->assertRedirect(route('orders.show', $order));

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'failed']);
    }

    public function test_paid_publisher_order_stays_on_the_order(): void
    {
        Notification::fake();
        $customer = User::factory()->create();
        $order = $this->packageOrder($customer, 'Guest Post Placement');

        $this->actingAs($customer)->post(route('orders.payment.process', $order), [
            'payment_method' => 'demo_card',
            'demo_result' => 'success',
        ])->assertRedirect(route('orders.show', $order));
    }

    private function packageOrder(User $customer, string $name): Order
    {
        $order = $customer->orders()->create([
            'order_number' => 'BM-PKG-'.strtoupper(substr(md5($name), 0, 8)),
            'session_id' => 'package-payment',
            'total_amount' => 69,
            'discount_amount' => 0,
        ]);
        $order->items()->create([
            'item_name' => $name,
            'unit_price' => 69,
            'quantity' => 1,
            'target_url' => 'https://customer.example.test/landing',
            'anchor_text' => 'customer brand',
            'article_topic' => 'Package placement',
        ]);

        return $order;
    }
}
