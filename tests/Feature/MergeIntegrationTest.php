<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Niche;
use App\Models\Role;
use App\Models\User;
use App\Models\Website;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MergeIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_both_catalogues_and_customer_workflow_seed_without_duplicates(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('websites', 46);
        $this->assertDatabaseCount('seo_metrics', 46);
        $this->assertDatabaseCount('users', 11);
        $this->assertDatabaseCount('orders', 3);
        $this->assertDatabaseCount('wallets', 1);
        $customer = User::query()->where('email', 'customer@example.test')->firstOrFail();
        $this->assertSame('Customer', $customer->role->role_name);
        $this->assertSame(3, $customer->orders()->count());
        $this->assertSame(435, $customer->wallet->available_balance);

        $admin = User::query()->where('email', 'admin@backlink.test')->firstOrFail();
        $this->assertTrue($admin->isActiveAdmin());
        $this->actingAs($admin);
        foreach (['admin.dashboard', 'admin.customers', 'admin.staff', 'admin.websites', 'admin.services', 'admin.niches', 'admin.packages', 'admin.roles', 'admin.reports', 'admin.settings'] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_customer_login_uses_assigned_role_and_adopts_the_existing_cart(): void
    {
        $role = Role::query()->firstOrCreate(['role_name' => 'Customer']);
        $customer = User::factory()->create(['role_id' => $role->id, 'password' => 'CustomerPass123!']);
        $this->get(route('auth.login'))->assertOk();
        $sessionId = session()->getId();
        $cart = Cart::create(['session_id' => $sessionId, 'status' => 'active']);
        $cart->items()->create(['item_name' => 'Guest Post Placement', 'unit_price' => 120, 'quantity' => 1, 'credit_cost' => 12]);

        $this->withCookie(config('session.cookie'), $sessionId)->post(route('auth.authenticate'), ['email' => $customer->email, 'password' => 'CustomerPass123!'])
            ->assertRedirect(route('customer.dashboard'));

        $this->assertAuthenticatedAs($customer);
        $this->assertDatabaseHas('carts', ['id' => $cart->id, 'customer_id' => $customer->id, 'session_id' => session()->getId()]);
        $this->assertNotSame($sessionId, session()->getId());
        $this->get(route('orders.cart'))->assertOk()->assertSee('Guest Post Placement');
    }

    public function test_customer_login_cannot_bypass_an_admin_role_with_legacy_customer_metadata(): void
    {
        $adminRole = Role::query()->firstOrCreate(['role_name' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id, 'account_type' => 'customer']);

        $this->post(route('auth.authenticate'), ['email' => $admin->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_customer_cannot_access_admin_routes_and_authenticated_guests_redirect_by_role(): void
    {
        $customer = User::factory()->create();
        $this->actingAs($customer)->get(route('login'))->assertRedirect(route('customer.dashboard'));
        $this->get(route('admin.customers'))->assertRedirect(route('login'));
        $this->assertGuest();

        $adminRole = Role::query()->firstOrCreate(['role_name' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $this->actingAs($admin)->get(route('auth.login'))->assertRedirect(route('admin.dashboard'));
        $this->get('/')->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_website_changes_are_visible_and_purchasable_in_customer_marketplace(): void
    {
        $adminRole = Role::query()->firstOrCreate(['role_name' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $technology = Niche::create(['niche_name' => 'Technology']);
        $business = Niche::create(['niche_name' => 'Business']);
        $data = [
            'website_name' => 'Integrated Publisher', 'domain' => 'integrated.example', 'url' => 'https://integrated.example',
            'price' => 125, 'credit_cost' => 14, 'status' => 'active', 'niche_ids' => [$technology->id, $business->id],
            'DR' => 62, 'DA' => 50, 'TF' => 30, 'CF' => 32, 'traffic' => 42000, 'spam_score' => 2, 'backlinks' => 9000,
        ];
        $this->actingAs($admin)->post(route('admin.websites.store'), $data)->assertSessionHasNoErrors()->assertRedirect();
        $website = Website::query()->where('domain', 'integrated.example')->firstOrFail();
        $this->assertSame('62.00', $website->seoMetric->DR);
        $this->assertSame(2, $website->niches()->count());
        $this->get(route('customer.websites', ['niche' => 'Business', 'min_dr' => 60]))->assertOk()->assertSee('Integrated Publisher');

        $this->put(route('admin.websites.update', $website), array_replace($data, ['price' => 150, 'credit_cost' => 16, 'DR' => 70]))
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('websites', ['id' => $website->id, 'price' => 150, 'credit_cost' => 16, 'domain_rating' => 70]);
        $this->assertDatabaseHas('seo_metrics', ['website_id' => $website->id, 'DR' => 70]);

        $customer = User::factory()->create();
        $this->actingAs($customer)->post(route('orders.cart.items.store'), ['product_key' => 'guest_post', 'website_id' => $website->id, 'quantity' => 1, 'unit_price' => 1])->assertRedirect();
        $this->assertDatabaseHas('cart_items', ['website_id' => $website->id, 'unit_price' => 150, 'credit_cost' => 16]);
    }

    public function test_role_and_marketplace_migration_preserves_existing_customer_data(): void
    {
        $migration = require database_path('migrations/2026_11_10_064458_reconcile_customer_and_admin_roles.php');
        $migration->down();
        $customerId = DB::table('users')->insertGetId([
            'name' => 'Existing Customer', 'email' => 'existing@example.test', 'password' => 'unused',
            'role' => 'customer', 'status' => 'active', 'phone' => '+1 555 0100',
        ]);
        $websiteId = DB::table('websites')->insertGetId([
            'website_name' => 'Existing Publisher', 'domain' => 'existing.example', 'url' => 'https://existing.example',
            'niche' => 'Technology', 'domain_rating' => 64, 'domain_authority' => 52, 'trust_flow' => 30,
            'page_authority' => 40, 'monthly_traffic' => 25000, 'spam_score' => 2, 'backlinks' => 7000, 'price' => 120,
        ]);

        $migration->up();

        $customer = User::findOrFail($customerId);
        $this->assertSame('customer', $customer->account_type);
        $this->assertSame('Customer', $customer->role->role_name);
        $this->assertSame('+1 555 0100', $customer->phone);
        $website = Website::findOrFail($websiteId);
        $this->assertSame('64.00', $website->seoMetric->DR);
        $this->assertSame('Technology', $website->niches()->firstOrFail()->niche_name);
    }

    public function test_public_api_registration_cannot_assign_an_admin_role(): void
    {
        $adminRole = Role::query()->firstOrCreate(['role_name' => 'Admin']);
        $this->postJson(route('api.auth.register'), [
            'name' => 'New Customer', 'email' => 'new@example.test',
            'password' => 'CustomerPass123!', 'password_confirmation' => 'CustomerPass123!',
            'role_id' => $adminRole->id, 'account_type' => 'admin', 'role' => 'admin',
        ])->assertCreated()->assertJsonPath('data.role', 'customer');

        $user = User::query()->where('email', 'new@example.test')->firstOrFail();
        $this->assertSame('Customer', $user->role->role_name);
        $this->assertFalse($user->isActiveAdmin());
    }

    public function test_migration_preserves_imported_roles_metrics_and_niche_assignments(): void
    {
        $migration = require database_path('migrations/2026_11_10_064458_reconcile_customer_and_admin_roles.php');
        $migration->down();
        Schema::table('websites', function ($table): void {
            $table->dropIndex(['niche']);
            $table->dropIndex(['status', 'domain_rating']);
            $table->dropIndex(['status', 'monthly_traffic']);
            $table->dropColumn(['niche', 'description', 'domain_rating', 'domain_authority', 'trust_flow', 'page_authority', 'monthly_traffic', 'spam_score', 'backlinks', 'referring_domains']);
        });
        $role = Role::query()->firstOrCreate(['role_name' => 'Admin']);
        $userId = DB::table('users')->insertGetId([
            'name' => 'Imported Admin', 'email' => 'imported@example.test', 'password' => 'unused',
            'role' => 'customer', 'role_id' => $role->id, 'status' => 'active',
        ]);
        $websiteId = DB::table('websites')->insertGetId([
            'website_name' => 'Imported Publisher', 'domain' => 'imported.example', 'url' => 'https://imported.example', 'price' => 120,
        ]);
        DB::table('seo_metrics')->insert(['website_id' => $websiteId, 'DR' => 64.5, 'traffic' => 28000]);
        $niche = Niche::create(['niche_name' => 'Business']);
        DB::table('website_niches')->insert(['website_id' => $websiteId, 'niche_id' => $niche->id]);

        $migration->up();

        $this->assertTrue(User::findOrFail($userId)->isActiveAdmin());
        $this->assertDatabaseHas('websites', ['id' => $websiteId, 'niche' => 'Business', 'domain_rating' => 64.5, 'monthly_traffic' => 28000]);
        $this->assertDatabaseCount('seo_metrics', 1);
        $this->assertDatabaseCount('website_niches', 1);
    }
}
