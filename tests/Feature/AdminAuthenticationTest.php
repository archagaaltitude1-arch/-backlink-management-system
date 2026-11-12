<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
        $this->get('/admin/login')->assertOk()->assertSee('Admin sign in');
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->post('/admin/login', [
            'email' => 'missing@example.com',
            'password' => 'not-the-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_and_non_admin_accounts_cannot_sign_in(): void
    {
        $adminRole = Role::query()->where('role_name', 'Admin')->firstOrFail();
        $customerRole = Role::query()->where('role_name', 'Customer')->firstOrFail();

        User::query()->create([
            'name' => 'Inactive Admin',
            'email' => 'inactive@example.com',
            'password' => 'password-1',
            'role_id' => $adminRole->id,
            'status' => 'inactive',
        ]);

        User::query()->create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => 'password-1',
            'role_id' => $customerRole->id,
            'status' => 'active',
        ]);

        $this->post('/admin/login', [
            'email' => 'inactive@example.com',
            'password' => 'password-1',
        ])->assertSessionHasErrors('email');

        $this->post('/admin/login', [
            'email' => 'customer@example.com',
            'password' => 'password-1',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_an_active_customer_is_sent_to_the_customer_dashboard(): void
    {
        $customerRole = Role::query()->where('role_name', 'Customer')->firstOrFail();

        $customer = User::query()->create([
            'name' => 'Casey Customer',
            'email' => 'casey@example.com',
            'password' => 'password-1',
            'role_id' => $customerRole->id,
            'status' => 'active',
        ]);

        $this->post('/login', [
            'email' => 'casey@example.com',
            'password' => 'password-1',
        ])->assertRedirect(route('customer.dashboard'));

        $this->actingAs($customer)
            ->get('/login')
            ->assertRedirect(route('customer.dashboard'));

        $this->actingAs($customer)
            ->get('/')
            ->assertRedirect(route('customer.dashboard'));
    }

    public function test_an_active_staff_member_is_sent_to_the_seo_dashboard(): void
    {
        $staffRole = Role::query()->where('role_name', 'Staff')->firstOrFail();

        $staff = User::query()->create([
            'name' => 'Rae Staff',
            'email' => 'rae@example.com',
            'password' => 'password-1',
            'role_id' => $staffRole->id,
            'status' => 'active',
            'account_type' => 'staff',
        ]);

        $this->actingAs($staff)
            ->get('/login')
            ->assertRedirect(route('seo.dashboard'));

        $this->actingAs($staff)
            ->get('/')
            ->assertRedirect(route('seo.dashboard'));
    }

    public function test_staff_login_opens_the_seo_dashboard(): void
    {
        $staffRole = Role::query()->where('role_name', 'Staff')->firstOrFail();

        User::query()->create([
            'name' => 'Rae Staff',
            'email' => 'rae.staff@example.com',
            'password' => 'password-1',
            'role_id' => $staffRole->id,
            'status' => 'active',
            'account_type' => 'staff',
        ]);

        $this->withSession(['url.intended' => route('admin.dashboard')])
            ->post('/seo/login', [
                'email' => 'rae.staff@example.com',
                'password' => 'password-1',
            ])->assertRedirect(route('seo.dashboard'));
    }

    public function test_an_active_admin_can_sign_in_and_out(): void
    {
        $adminRole = Role::query()->where('role_name', 'Admin')->firstOrFail();

        User::query()->create([
            'name' => 'Avery Admin',
            'email' => 'avery@example.com',
            'password' => 'password-1',
            'role_id' => $adminRole->id,
            'status' => 'active',
        ]);

        $this->post('/admin/login', [
            'email' => 'avery@example.com',
            'password' => 'password-1',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Avery Admin')
            ->assertSee('Roles & Permissions');

        $this->post('/admin/logout')->assertRedirect(route('login'));
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
    }

    public function test_admin_create_command_hashes_the_password(): void
    {
        $this->artisan('admin:create', [
            '--name' => 'Casey Admin',
            '--email' => 'casey@example.com',
            '--password' => 'password-1',
        ])->assertSuccessful();

        $user = User::query()->where('email', 'casey@example.com')->firstOrFail();

        $this->assertNotSame('password-1', $user->password);
        $this->assertTrue(Hash::check('password-1', $user->password));
        $this->assertSame('Admin', $user->role?->role_name);
        $this->assertSame('active', $user->status);
    }
}
