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

    #[TestWith(['Pro Text Link Package'])]
    #[TestWith(['Enterprise Contextual Link Package'])]
    #[TestWith(['Professional Guest Post Package'])]
    #[TestWith(['Starter Guest Post Package'])]
    #[TestWith(['Growth Backlink Package'])]
    public function test_paid_package_opens_a_thank_you_page_with_an_invoice(string $name): void
    {
        Notification::fake();
        $customer = User::factory()->create();
        $order = $this->packageOrder($customer, $name);

        $this->actingAs($customer)->post(route('orders.payment.process', $order), [
            'payment_method' => 'demo_card',
            'demo_result' => 'success',
        ])->assertRedirect(route('orders.thanks', $order));

        $this->get(route('orders.thanks', $order))
            ->assertOk()
            ->assertSee('Thank you', false)
            ->assertSee('Download invoice', false)
            ->assertSee('Shop more', false)
            ->assertSee('Shop more to gain more discounts', false)
            ->assertSee('Shop 2 more orders to earn 20% off.', false)
            ->assertDontSee('Select Backlink Websites', false);

        $this->get(route('orders.payment', $order))
            ->assertRedirect(route('orders.thanks', $order));

        $this->get(route('customer.invoices.download', $order->invoice()->firstOrFail()))
            ->assertOk()
            ->assertDownload();

        $this->actingAs(User::factory()->create())
            ->get(route('orders.thanks', $order))
            ->assertNotFound();
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
        $this->get(route('orders.thanks', $order))->assertRedirect(route('orders.payment', $order));
    }

    public function test_paid_publisher_order_opens_the_thank_you_page(): void
    {
        Notification::fake();
        $customer = User::factory()->create();
        $order = $this->packageOrder($customer, 'Guest Post Placement');

        $this->actingAs($customer)->post(route('orders.payment.process', $order), [
            'payment_method' => 'demo_card',
            'demo_result' => 'success',
        ])->assertRedirect(route('orders.thanks', $order));
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
