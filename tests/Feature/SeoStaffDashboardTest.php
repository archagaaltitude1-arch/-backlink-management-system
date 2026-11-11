<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderAssignment;
use App\Models\OrderItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoStaffDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_seo_staff_dashboard_displays_only_orders_assigned_to_authenticated_staff_member(): void
    {
        $staffOne = $this->staff('Rae Staff', 'rae.staff@example.test');
        $staffTwo = $this->staff('Other Staff', 'other.staff@example.test');
        $customer = $this->customer();
        $ownItem = $this->item($customer, 'BM-20261111-STF001', 'https://client-one.example/ai-tools', 'enterprise AI tools');
        $otherItem = $this->item($customer, 'BM-20261111-STF002', 'https://other-client.example/hidden', 'other staff anchor');

        OrderAssignment::query()->create([
            'order_item_id' => $ownItem->id,
            'staff_id' => $staffOne->id,
            'assigned_by' => $this->admin()->id,
            'status' => 'assigned',
            'notes' => 'Priority client placement.',
        ]);
        OrderAssignment::query()->create([
            'order_item_id' => $otherItem->id,
            'staff_id' => $staffTwo->id,
            'assigned_by' => $this->admin()->id,
            'status' => 'in_progress',
        ]);

        $this->actingAs($staffOne)
            ->get(route('seo.dashboard'))
            ->assertOk()
            ->assertSee('BM-20261111-STF001')
            ->assertSee('https://client-one.example/ai-tools')
            ->assertSee('enterprise AI tools')
            ->assertSee('Assign Order to Staff')
            ->assertDontSee('BM-20261111-STF002')
            ->assertDontSee('https://other-client.example/hidden');

        $this->actingAs($staffOne)
            ->get(route('seo.assigned-orders'))
            ->assertOk()
            ->assertSee('BM-20261111-STF001')
            ->assertSee('https://client-one.example/ai-tools')
            ->assertSee('enterprise AI tools')
            ->assertSee('Assign / Reassign Staff')
            ->assertDontSee('BM-20261111-STF002');
    }

    public function test_seo_staff_can_update_their_assigned_work_status_and_cannot_update_others(): void
    {
        $staffOne = $this->staff('Rae Staff', 'rae.staff@example.test');
        $staffTwo = $this->staff('Other Staff', 'other.staff@example.test');
        $customer = $this->customer();
        $ownItem = $this->item($customer, 'BM-20261111-UPD001', 'https://example.com/target-1', 'anchor one');
        $otherItem = $this->item($customer, 'BM-20261111-UPD002', 'https://example.com/target-2', 'anchor two');
        $ownAssignment = OrderAssignment::query()->create([
            'order_item_id' => $ownItem->id,
            'staff_id' => $staffOne->id,
            'status' => 'assigned',
        ]);
        $otherAssignment = OrderAssignment::query()->create([
            'order_item_id' => $otherItem->id,
            'staff_id' => $staffTwo->id,
            'status' => 'assigned',
        ]);

        $this->actingAs($staffOne)
            ->from(route('seo.dashboard'))
            ->put(route('seo.orders.status', 'BM-20261111-UPD001'), [
                'order_item_id' => $ownItem->id,
                'status' => 'in_progress',
                'notes' => 'Drafting outreach email.',
            ])
            ->assertRedirect(route('seo.dashboard'));

        $this->assertDatabaseHas('order_assignments', [
            'id' => $ownAssignment->id,
            'status' => 'in_progress',
            'notes' => 'Drafting outreach email.',
        ]);

        $this->actingAs($staffOne)
            ->put(route('seo.orders.status', 'BM-20261111-UPD002'), [
                'order_item_id' => $otherItem->id,
                'status' => 'in_progress',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('order_assignments', [
            'id' => $otherAssignment->id,
            'status' => 'assigned',
        ]);
    }

    public function test_staff_api_returns_only_the_authenticated_staff_members_assignments(): void
    {
        $staffOne = $this->staff('Rae Staff', 'rae.staff@example.test');
        $staffTwo = $this->staff('Other Staff', 'other.staff@example.test');
        $customer = $this->customer();
        $ownItem = $this->item($customer, 'BM-20261111-API001', 'https://client-one.example/ai-tools', 'enterprise AI tools');
        $otherItem = $this->item($customer, 'BM-20261111-API002', 'https://other-client.example/hidden', 'other staff anchor');

        OrderAssignment::query()->create([
            'order_item_id' => $ownItem->id,
            'staff_id' => $staffOne->id,
            'status' => 'assigned',
        ]);
        OrderAssignment::query()->create([
            'order_item_id' => $otherItem->id,
            'staff_id' => $staffTwo->id,
            'status' => 'in_progress',
        ]);

        $this->getJson(route('api.staff.dashboard'))
            ->assertOk()
            ->assertJsonPath('staff', 'SEO Staff')
            ->assertJsonPath('recent_assigned_orders', []);

        $this->actingAs($staffOne)
            ->getJson(route('api.staff.assigned-orders'))
            ->assertOk()
            ->assertJsonPath('orders.0.id', 'BM-20261111-API001')
            ->assertJsonMissing(['id' => 'BM-20261111-API002']);
    }

    private function admin(): User
    {
        $role = Role::query()->firstOrCreate(['role_name' => 'Admin'], ['permissions' => []]);

        return User::factory()->create([
            'role_id' => $role->id,
            'status' => 'active',
            'account_type' => 'admin',
        ]);
    }

    private function staff(string $name, string $email): User
    {
        $role = Role::query()->firstOrCreate(['role_name' => 'Staff'], ['permissions' => []]);

        return User::factory()->create([
            'name' => $name,
            'email' => $email,
            'role_id' => $role->id,
            'status' => 'active',
            'account_type' => 'staff',
        ]);
    }

    private function customer(): User
    {
        $role = Role::query()->firstOrCreate(['role_name' => 'Customer'], ['permissions' => []]);

        return User::factory()->create([
            'name' => 'Alex Customer',
            'role_id' => $role->id,
            'account_type' => 'customer',
        ]);
    }

    private function item(User $customer, string $orderNumber, string $targetUrl, string $anchorText): OrderItem
    {
        $order = Order::query()->create([
            'order_number' => $orderNumber,
            'customer_id' => $customer->id,
            'session_id' => 'staff-dashboard-test',
            'total_amount' => 120,
            'payment_status' => 'paid',
            'order_status' => 'processing',
        ]);

        return $order->items()->create([
            'item_name' => 'Guest Post Placement',
            'unit_price' => 120,
            'quantity' => 1,
            'target_url' => $targetUrl,
            'anchor_text' => $anchorText,
            'article_topic' => 'Enterprise AI Adoption in 2026',
        ]);
    }
}
