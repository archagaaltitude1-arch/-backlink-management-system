<?php

namespace Tests\Feature\Customer;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_the_home_page(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Real, Traffic-Driven', false)
            ->assertSee('href="'.route('customer.dashboard').'"', false)
            ->assertSee('href="'.route('auth.register').'">Start Your Order</a>', false);

        $this->assertMatchesRegularExpression(
            '/href="'.preg_quote(route('customer.dashboard'), '/').'"[^>]*>\s*Start Building Links\s*</',
            $response->getContent(),
        );
    }

    public function test_mobile_home_endpoint_returns_the_existing_homepage_data(): void
    {
        $this->getJson('/api/home')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'publisherCount',
                    'nicheCount',
                    'servicesPublished',
                    'serviceCards' => [
                        ['slug', 'title', 'description', 'price'],
                    ],
                    'nicheTiles' => [
                        ['name', 'slug'],
                    ],
                ],
            ]);
    }

    public function test_logged_in_customer_still_sees_the_home_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('Real, Traffic-Driven', false);
    }

    public function test_logged_in_admin_sees_the_public_home_page(): void
    {
        $adminRole = Role::query()->firstOrCreate(['role_name' => 'Admin'], ['permissions' => []]);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $this->actingAs($admin)
            ->get('/')
            ->assertOk()
            ->assertSee('Real, Traffic-Driven', false);
    }
}
