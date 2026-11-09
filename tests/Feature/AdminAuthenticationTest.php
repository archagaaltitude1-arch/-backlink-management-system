<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

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

        $this->post('/login', [
            'email' => 'inactive@example.com',
            'password' => 'password-1',
        ])->assertSessionHasErrors('email');

        $this->post('/login', [
            'email' => 'customer@example.com',
            'password' => 'password-1',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
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
