<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

        Schema::create('roles', function ($table) {
            $table->id();
            $table->string('role_name')->unique();
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignId('role_id')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('permissions', function ($table) {
            $table->id();
            $table->string('permission_name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('websites', function ($table) {
            $table->id();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('niches', function ($table) {
            $table->id();
            $table->string('niche_name');
            $table->timestamps();
        });

        Schema::create('website_niches', function ($table) {
            $table->id();
            $table->foreignId('website_id');
            $table->foreignId('niche_id');
            $table->timestamps();
        });

        Schema::create('services', function ($table) {
            $table->id();
            $table->string('service_name');
            $table->timestamps();
        });

        Schema::create('packages', function ($table) {
            $table->id();
            $table->string('package_name');
            $table->timestamps();
        });

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
        $this->get('/login')->assertOk()->assertSee('Admin sign in');
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->post('/login', [
            'email' => 'missing@example.com',
            'password' => 'not-the-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_and_unassigned_accounts_cannot_sign_in(): void
    {
        $adminRole = Role::query()->where('role_name', 'Admin')->firstOrFail();

        User::query()->create([
            'name' => 'Inactive Admin',
            'email' => 'inactive@example.com',
            'password' => 'password-1',
            'role_id' => $adminRole->id,
            'status' => 'inactive',
        ]);

        User::query()->create([
            'name' => 'Unassigned User',
            'email' => 'unassigned@example.com',
            'password' => 'password-1',
            'role_id' => null,
            'status' => 'active',
        ]);

        $this->post('/login', [
            'email' => 'inactive@example.com',
            'password' => 'password-1',
        ])->assertSessionHasErrors('email');

        $this->post('/login', [
            'email' => 'unassigned@example.com',
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
            ->assertSee(route('customer.dashboard'), false);
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
        ]);

        $this->post('/login', [
            'email' => 'rae@example.com',
            'password' => 'password-1',
        ])->assertRedirect(route('seo.dashboard'));

        $this->actingAs($staff)
            ->get('/login')
            ->assertRedirect(route('seo.dashboard'));
    }

    public function test_staff_login_opens_the_seo_dashboard(): void
    {
        Schema::create('staff_profiles', function ($table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('designation')->default('SEO Specialist');
            $table->string('contact_number')->nullable();
            $table->timestamps();
        });

        $staffRole = Role::query()->where('role_name', 'Staff')->firstOrFail();

        User::query()->create([
            'name' => 'Rae Staff',
            'email' => 'rae.staff@example.com',
            'password' => 'password-1',
            'role_id' => $staffRole->id,
            'status' => 'active',
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

        $this->post('/login', [
            'email' => 'avery@example.com',
            'password' => 'password-1',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Avery Admin')
            ->assertSee('Roles & Permissions');

        $this->post('/logout')->assertRedirect(route('login'));
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
