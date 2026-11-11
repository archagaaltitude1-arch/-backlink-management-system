<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\FulfillmentWorkflowSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FulfillmentWorkflowSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_sample_orders_cover_assignment_publication_and_approval(): void
    {
        $this->seed(FulfillmentWorkflowSeeder::class);

        $admin = User::query()->where('email', 'admin@backlink.test')->firstOrFail();
        $customer = User::query()->where('email', 'noah.williams@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('BM-FLOW-0001')
            ->assertSee('Assign to SEO staff')
            ->assertDontSee('No customer orders are waiting');

        $this->actingAs($admin)
            ->get(route('seo.dashboard'))
            ->assertOk()
            ->assertSee('BM-FLOW-0002')
            ->assertSee('Maya Chen')
            ->assertSee('BM-FLOW-0004');

        $this->actingAs($customer)
            ->get(route('customer.backlinks'))
            ->assertOk()
            ->assertSee('https://growth-ledger.example/spring-range-launch')
            ->assertSee('Approve')
            ->assertSee('https://founder-path.example/warehouse-tour')
            ->assertSee('Approved');

        $this->assertDatabaseMissing('order_assignments', [
            'order_item_id' => $customer->orders()->where('order_number', 'BM-FLOW-0001')->firstOrFail()->items()->firstOrFail()->id,
        ]);
        $this->assertDatabaseHas('orders', [
            'order_number' => 'BM-FLOW-0005',
            'order_status' => 'completed',
        ]);
    }
}
