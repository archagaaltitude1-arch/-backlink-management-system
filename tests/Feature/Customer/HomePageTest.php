<?php

namespace Tests\Feature\Customer;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_the_home_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Real, Traffic-Driven Websites', false);
    }

    public function test_logged_in_customer_is_redirected_to_the_dashboard(): void
    {
        $user = User::factory()->create();

        $target = method_exists($user, 'dashboardUrl')
            ? $user->dashboardUrl()
            : route('customer.dashboard');

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect($target);
    }
}
