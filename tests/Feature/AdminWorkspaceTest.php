<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_show_order_and_revenue_totals_for_the_selected_month(): void
    {
        $customer = $this->customer();
        $order = $this->order($customer, 'BM-REPORT-0001', 'completed');
        Payment::query()->create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'transaction_id' => 'PAY-REPORT-0001',
            'amount' => 120,
            'payment_method' => 'Card',
            'gateway' => 'demo',
            'status' => 'successful',
            'paid_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.reports', ['range' => 'month']))
            ->assertOk()
            ->assertSee('Business statistics')
            ->assertSee('Total revenue')
            ->assertSee('$120.00')
            ->assertSee('Completed orders')
            ->assertSee('Monthly revenue');
    }

    public function test_staff_performance_counts_existing_assignments(): void
    {
        $customer = $this->customer();
        $item = $this->order($customer, 'BM-REPORT-0002', 'processing')->items()->create([
            'item_name' => 'Guest Post',
            'unit_price' => 90,
            'quantity' => 1,
            'target_url' => 'https://example.com/target',
            'anchor_text' => 'example anchor',
            'article_topic' => 'Example topic',
        ]);
        $staff = $this->staff();
        $item->assignment()->create([
            'staff_id' => $staff->id,
            'assigned_by' => $this->admin()->id,
            'status' => 'completed',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.staff-performance', ['range' => 'month']))
            ->assertOk()
            ->assertSee('Maya Staff')
            ->assertSee('Completed 1 of 1 assignments in this period.');
    }

    public function test_admin_notifications_can_be_marked_read_by_that_admin_only(): void
    {
        $customer = $this->customer();
        $this->order($customer, 'BM-REPORT-0003', 'pending');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.notifications'))
            ->assertOk()
            ->assertSee('BM-REPORT-0003')
            ->assertSee('Unread');

        $notificationId = $admin->notifications()->where('type', 'admin.insight')->where('data->title', 'New customer order')->value('id');

        $this->actingAs($admin)
            ->patch(route('admin.notifications.read', $notificationId))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNotNull($admin->notifications()->whereKey($notificationId)->firstOrFail()->read_at);

        $this->actingAs($this->admin('other-admin@example.test'))
            ->patch(route('admin.notifications.read', $notificationId))
            ->assertNotFound();
    }

    public function test_customer_statistics_use_existing_customer_accounts(): void
    {
        $this->customer();

        $this->actingAs($this->admin())
            ->get(route('admin.customer-statistics', ['range' => 'month']))
            ->assertOk()
            ->assertSee('Morgan Digital')
            ->assertSee('New this month')
            ->assertSee('Active');
    }

    public function test_admin_profile_requires_the_current_password_and_stores_a_photo(): void
    {
        Storage::fake('local');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'name' => 'Avery Admin',
                'email' => $admin->email,
                'password' => 'new-password-1',
                'password_confirmation' => 'new-password-1',
            ])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'name' => 'Avery Admin',
                'email' => $admin->email,
                'current_password' => 'wrong-password',
                'password' => 'new-password-1',
                'password_confirmation' => 'new-password-1',
            ])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'name' => 'Avery Admin',
                'email' => $admin->email,
                'current_password' => 'password-1',
                'password' => 'new-password-1',
                'password_confirmation' => 'new-password-1',
                'photo' => UploadedFile::fake()->createWithContent('face.jpg', base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxISEhUQEhIVFhUXGBcYGBgYGBgYGBgYGBgYGBgYGBgYHiggGBolHRgXITEhJSkrLi4uFx8zODMtNygtLisBCgoKDg0OGxAQGy0lICUtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAAoACgMBIgACEQEDEQH/xAAbAAABBQEBAAAAAAAAAAAAAAADAAIEBQYBB//EADkQAAIBAwMCBAMGBQEBAAAAAAECAwAEEQUSITFBBhNRYXEigZGhscHR8BUjQlLh8RYzYvEkQ1Ni/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwD5gAAAA//Z')),
            ])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('new-password-1', $admin->fresh()->password));
        $this->actingAs($admin->fresh())
            ->get(route('admin.profile-photo'))
            ->assertOk();
    }

    public function test_customer_and_staff_dashboards_stay_available(): void
    {
        $this->actingAs($this->customer())->get(route('customer.dashboard'))->assertOk();
        $this->actingAs($this->staff())->get(route('seo.dashboard'))->assertOk();
    }

    private function admin(string $email = 'avery@example.test'): User
    {
        $role = Role::query()->firstOrCreate(['role_name' => 'Admin'], ['permissions' => []]);

        return User::query()->firstOrCreate(['email' => $email], [
            'name' => 'Avery Admin',
            'password' => 'password-1',
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
            'password' => 'password-1',
            'role_id' => $role->id,
            'status' => 'active',
            'account_type' => 'staff',
        ]);
    }

    private function customer(): User
    {
        $role = Role::query()->firstOrCreate(['role_name' => 'Customer'], ['permissions' => []]);

        return User::query()->firstOrCreate(['email' => 'morgan@example.test'], [
            'name' => 'Morgan Digital',
            'password' => 'password-1',
            'role_id' => $role->id,
            'status' => 'active',
            'account_type' => 'customer',
        ]);
    }

    private function order(User $customer, string $number, string $status): Order
    {
        return Order::query()->create([
            'order_number' => $number,
            'customer_id' => $customer->id,
            'session_id' => 'admin-workspace',
            'total_amount' => 120,
            'payment_status' => 'paid',
            'order_status' => $status,
        ]);
    }
}
