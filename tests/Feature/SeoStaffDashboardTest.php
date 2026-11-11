<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderAssignment;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SeoStaffDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_seo_staff_dashboard_displays_only_orders_assigned_to_authenticated_staff_member(): void
    {
        $staffRole = Role::query()->firstOrCreate(['role_name' => 'Staff'], ['permissions' => []]);
        $customerRole = Role::query()->firstOrCreate(['role_name' => 'Customer'], ['permissions' => []]);
        $adminRole = Role::query()->firstOrCreate(['role_name' => 'Admin'], ['permissions' => []]);

        $staffOne = User::factory()->create([
            'name' => 'Rae Staff',
            'role_id' => $staffRole->id,
            'account_type' => 'staff',
            'status' => 'active',
        ]);
        $staffTwo = User::factory()->create([
            'name' => 'Other Staff',
            'role_id' => $staffRole->id,
            'account_type' => 'staff',
            'status' => 'active',
        ]);
        $customer = User::factory()->create([
            'name' => 'Alex Customer',
            'role_id' => $customerRole->id,
            'account_type' => 'customer',
        ]);
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'role_id' => $adminRole->id,
            'account_type' => 'admin',
        ]);

        $website = Website::create([
            'website_name' => 'Tech Ledger',
            'domain' => 'techledger.example',
            'url' => 'https://techledger.example',
            'niche' => 'Technology',
            'domain_rating' => 64,
            'domain_authority' => 58,
            'price' => 120,
            'credit_cost' => 12,
            'status' => 'active',
        ]);
        $service = Service::create([
            'service_name' => 'Guest Post',
            'description' => 'Guest post placement',
            'base_price' => 120,
            'credit_cost' => 12,
        ]);

        $orderOne = Order::create([
            'order_number' => 'BM-20261111-STF001',
            'customer_id' => $customer->id,
            'session_id' => 'sess-1',
            'total_amount' => 120,
            'payment_status' => 'paid',
            'order_status' => 'processing',
        ]);
        $itemOne = $orderOne->items()->create([
            'website_id' => $website->id,
            'service_id' => $service->id,
            'item_name' => 'Tech Ledger — Guest Post',
            'unit_price' => 120,
            'quantity' => 1,
            'credit_cost' => 12,
            'target_url' => 'https://client-one.example/ai-tools',
            'anchor_text' => 'enterprise AI tools',
            'article_topic' => 'Enterprise AI Adoption in 2026',
            'requirements' => 'Place the anchor naturally in the second paragraph with a dofollow attribute.',
        ]);

        $orderTwo = Order::create([
            'order_number' => 'BM-20261111-STF002',
            'customer_id' => $customer->id,
            'session_id' => 'sess-2',
            'total_amount' => 80,
            'payment_status' => 'paid',
            'order_status' => 'processing',
        ]);
        $itemTwo = $orderTwo->items()->create([
            'website_id' => $website->id,
            'service_id' => $service->id,
            'item_name' => 'Tech Ledger — Niche Edit',
            'unit_price' => 80,
            'quantity' => 1,
            'credit_cost' => 8,
            'target_url' => 'https://other-client.example/hidden',
            'anchor_text' => 'other staff anchor',
            'article_topic' => 'Other Staff Topic',
            'requirements' => 'Assigned to another staff member.',
        ]);

        OrderAssignment::create([
            'order_item_id' => $itemOne->id,
            'staff_id' => $staffOne->id,
            'assigned_by' => $admin->id,
            'status' => 'assigned',
            'notes' => 'Priority client placement.',
        ]);

        OrderAssignment::create([
            'order_item_id' => $itemTwo->id,
            'staff_id' => $staffTwo->id,
            'assigned_by' => $admin->id,
            'status' => 'in_progress',
        ]);

        $this->assertFalse(Route::has('seo.orders.assign'));

        $this->actingAs($staffOne)
            ->get(route('seo.dashboard'))
            ->assertOk()
            ->assertSee('BM-20261111-STF001')
            ->assertSee('https://client-one.example/ai-tools')
            ->assertSee('enterprise AI tools')
            ->assertSee('Place the anchor naturally in the second paragraph with a dofollow attribute.')
            ->assertDontSee('BM-20261111-STF002')
            ->assertDontSee('https://other-client.example/hidden')
            ->assertDontSee('Assign Order to Staff')
            ->assertDontSee('Assign / Reassign Staff');

        $this->actingAs($staffOne)
            ->get(route('seo.assigned-orders'))
            ->assertOk()
            ->assertSee('BM-20261111-STF001')
            ->assertSee('https://client-one.example/ai-tools')
            ->assertSee('enterprise AI tools')
            ->assertSee('Place the anchor naturally in the second paragraph with a dofollow attribute.')
            ->assertDontSee('BM-20261111-STF002')
            ->assertDontSee('Assign / Reassign Staff');
    }

    public function test_seo_staff_can_update_their_assigned_work_status_and_cannot_update_others(): void
    {
        $staffRole = Role::query()->firstOrCreate(['role_name' => 'Staff'], ['permissions' => []]);
        $staffOne = User::factory()->create(['role_id' => $staffRole->id, 'account_type' => 'staff']);
        $staffTwo = User::factory()->create(['role_id' => $staffRole->id, 'account_type' => 'staff']);
        $customer = User::factory()->create();

        $order = Order::create([
            'order_number' => 'BM-20261111-UPD001',
            'customer_id' => $customer->id,
            'session_id' => 'sess-upd',
            'total_amount' => 100,
            'payment_status' => 'paid',
            'order_status' => 'processing',
        ]);

        $itemOne = $order->items()->create([
            'item_name' => 'Guest Post',
            'unit_price' => 100,
            'quantity' => 1,
            'credit_cost' => 10,
            'target_url' => 'https://example.com/target-1',
            'anchor_text' => 'anchor one',
            'article_topic' => 'Topic One',
            'requirements' => 'Requirement one',
        ]);

        $itemTwo = $order->items()->create([
            'item_name' => 'Niche Edit',
            'unit_price' => 100,
            'quantity' => 1,
            'credit_cost' => 10,
            'target_url' => 'https://example.com/target-2',
            'anchor_text' => 'anchor two',
            'article_topic' => 'Topic Two',
            'requirements' => 'Requirement two',
        ]);

        $assignmentOne = OrderAssignment::create([
            'order_item_id' => $itemOne->id,
            'staff_id' => $staffOne->id,
            'status' => 'assigned',
        ]);

        $assignmentTwo = OrderAssignment::create([
            'order_item_id' => $itemTwo->id,
            'staff_id' => $staffTwo->id,
            'status' => 'assigned',
        ]);

        $this->actingAs($staffOne)
            ->from(route('seo.dashboard'))
            ->put(route('seo.orders.status', ['id' => $assignmentOne->id]), [
                'assignment_id' => $assignmentOne->id,
                'order_item_id' => $itemOne->id,
                'status' => 'in_progress',
                'notes' => 'Drafting outreach email.',
            ])
            ->assertRedirect(route('seo.dashboard'));

        $this->assertDatabaseHas('order_assignments', [
            'id' => $assignmentOne->id,
            'status' => 'in_progress',
            'notes' => 'Drafting outreach email.',
        ]);

        $this->actingAs($staffOne)
            ->put(route('seo.orders.status', ['id' => $assignmentTwo->id]), [
                'assignment_id' => $assignmentTwo->id,
                'status' => 'completed',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('order_assignments', [
            'id' => $assignmentTwo->id,
            'status' => 'assigned',
        ]);
    }
}
