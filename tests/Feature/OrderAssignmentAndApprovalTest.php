<?php

namespace Tests\Feature;

use App\Models\CustomerProject;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderAssignmentAndApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_the_staff_login_before_assignment(): void
    {
        $this->get(route('seo.assigned-orders'))->assertRedirect(route('seo.login'));
    }

    public function test_customers_cannot_assign_orders_to_staff(): void
    {
        $customer = User::factory()->create();
        $item = $this->item($customer, 'BM-ASSIGN-0001');

        $this->actingAs($customer)
            ->post(route('seo.orders.assign', 'BM-ASSIGN-0001'), [
                'order_item_id' => $item->id,
                'staff_id' => $this->staff()->id,
            ])
            ->assertForbidden();
    }

    public function test_dashboards_show_staff_assignment_for_a_waiting_order(): void
    {
        $customer = $this->customer('Morgan Digital');
        $this->item($customer, 'BM-ASSIGN-0003');
        $this->staff();

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Customer orders')
            ->assertSee('Maya Staff')
            ->assertSee('BM-ASSIGN-0003')
            ->assertSee('Assign to SEO staff');

        $this->actingAs($this->admin())
            ->get(route('seo.dashboard'))
            ->assertOk()
            ->assertSee('Assign customer orders to staff')
            ->assertSee('Maya Staff')
            ->assertSee('BM-ASSIGN-0003')
            ->assertSee('Assign Order to Staff');
    }

    public function test_admin_dashboard_lists_active_staff_when_no_order_is_waiting(): void
    {
        $this->staff();

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Customer orders')
            ->assertSee('Maya Staff')
            ->assertSee('No customer orders are waiting');
    }

    public function test_admin_assigns_a_customer_order_item_to_staff(): void
    {
        $customer = $this->customer('Morgan <script>alert(1)</script>');
        $item = $this->item($customer, 'BM-ASSIGN-0001');
        $staff = $this->staff();

        $this->actingAs($this->admin())
            ->from(route('admin.orders.show', $item->order))
            ->post(route('admin.orders.assign', [$item->order, $item]), ['staff_id' => $staff->id])
            ->assertRedirect(route('admin.orders.show', $item->order))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('order_assignments', [
            'order_item_id' => $item->id,
            'staff_id' => $staff->id,
            'status' => 'assigned',
        ]);

        $this->get(route('admin.orders.show', $item->order))
            ->assertSee('Maya Staff')
            ->assertSee('Morgan <script>alert(1)</script>')
            ->assertDontSee('<script>alert(1)</script>', false);

        $this->actingAs($staff)
            ->get(route('seo.assigned-orders'))
            ->assertSee('BM-ASSIGN-0001')
            ->assertSee('Guest Post Placement');
    }

    public function test_staff_publication_waits_for_the_customer_to_approve_it(): void
    {
        $customer = $this->customer('Morgan Digital');
        $item = $this->item($customer, 'BM-ASSIGN-0001');
        $staff = $this->staff();
        $item->assignment()->create([
            'staff_id' => $staff->id,
            'assigned_by' => $this->admin()->id,
            'status' => 'assigned',
        ]);

        $this->actingAs($staff)
            ->put(route('seo.orders.status', 'BM-ASSIGN-0001'), [
                'order_item_id' => $item->id,
                'status' => 'published',
            ])
            ->assertSessionHasErrors('published_url');

        $this->actingAs($staff)
            ->put(route('seo.orders.status', 'BM-ASSIGN-0001'), [
                'order_item_id' => $item->id,
                'status' => 'published',
                'published_url' => 'https://publisher.example/approved-placement',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('customer_projects', [
            'order_item_id' => $item->id,
            'customer_id' => $customer->id,
            'published_url' => 'https://publisher.example/approved-placement',
            'verification_status' => 'pending',
        ]);

        $this->actingAs($customer)
            ->get(route('customer.backlinks'))
            ->assertSee('https://publisher.example/approved-placement')
            ->assertSee('Approve');

        $project = $customer->hasMany(CustomerProject::class, 'customer_id')->firstOrFail();

        $this->actingAs($this->customer('Other Customer', 'other@example.test'))
            ->post(route('customer.backlinks.decide', $project), ['decision' => 'approve'])
            ->assertNotFound();

        $this->actingAs($customer)
            ->post(route('customer.backlinks.decide', $project), ['decision' => 'approve'])
            ->assertRedirect()
            ->assertSessionHas('success', 'Backlink approved.');

        $this->assertDatabaseHas('customer_projects', [
            'id' => $project->id,
            'verification_status' => 'approved',
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('order_assignments', [
            'order_item_id' => $item->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $item->order_id,
            'order_status' => 'completed',
        ]);
    }

    public function test_customer_can_send_a_published_backlink_back_to_staff(): void
    {
        $customer = $this->customer('Morgan Digital');
        $item = $this->item($customer, 'BM-ASSIGN-0002');
        $staff = $this->staff();
        $item->assignment()->create([
            'staff_id' => $staff->id,
            'status' => 'published',
        ]);
        $project = $customer->hasMany(CustomerProject::class, 'customer_id')->create([
            'order_id' => $item->order_id,
            'order_item_id' => $item->id,
            'name' => 'Guest Post Placement',
            'status' => 'published',
            'target_url' => $item->target_url,
            'published_url' => 'https://publisher.example/needs-work',
            'verification_status' => 'pending',
        ]);

        $this->actingAs($customer)
            ->post(route('customer.backlinks.decide', $project), [
                'decision' => 'changes',
                'feedback' => 'Please move the link higher in the article.',
            ])
            ->assertSessionHas('success', 'Change request sent back to the assigned staff.');

        $this->assertDatabaseHas('customer_projects', [
            'id' => $project->id,
            'verification_status' => 'changes_requested',
            'report_summary' => 'Please move the link higher in the article.',
        ]);
        $this->assertDatabaseHas('order_assignments', [
            'order_item_id' => $item->id,
            'status' => 'in_progress',
        ]);
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

    private function staff(): User
    {
        $role = Role::query()->firstOrCreate(['role_name' => 'Staff'], ['permissions' => []]);

        return User::query()->firstOrCreate(['email' => 'maya.staff@example.test'], [
            'name' => 'Maya Staff',
            'password' => 'password',
            'role_id' => $role->id,
            'status' => 'active',
            'account_type' => 'staff',
        ]);
    }

    private function customer(string $name, string $email = 'morgan@example.test'): User
    {
        $role = Role::query()->firstOrCreate(['role_name' => 'Customer'], ['permissions' => []]);

        return User::factory()->create([
            'name' => $name,
            'email' => $email,
            'role_id' => $role->id,
            'account_type' => 'customer',
        ]);
    }

    private function item(User $customer, string $orderNumber): OrderItem
    {
        $order = Order::query()->create([
            'order_number' => $orderNumber,
            'customer_id' => $customer->id,
            'session_id' => 'assignment-test',
            'total_amount' => 120,
            'payment_status' => 'paid',
            'order_status' => 'processing',
        ]);

        return $order->items()->create([
            'item_name' => 'Guest Post Placement',
            'unit_price' => 120,
            'quantity' => 1,
            'target_url' => 'https://morgandigital.example/seo-services',
            'anchor_text' => 'SEO services',
            'article_topic' => 'Search growth',
        ]);
    }
}
