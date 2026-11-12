<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ServicePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_services_overview_is_available(): void
    {
        $this->get('/services')
            ->assertOk()
            ->assertSee('Link Building Services for Every Goal and Budget', false)
            ->assertSee('Price from', false);
    }

    public function test_each_service_page_shows_its_heading(): void
    {
        foreach (config('backlink_services.menu') as $item) {
            $slug = $item['slug'];
            $service = config('backlink_services.'.$slug);
            $response = $this->get('/services/'.$slug)->assertOk();
            $response->assertSee(Str::before($service['h1'], $service['h1_highlight']), false);
            $response->assertSee($service['h1_highlight'], false);
            $response->assertSee(Str::after($service['h1'], $service['h1_highlight']), false);
        }
    }

    public function test_unknown_service_is_not_found(): void
    {
        $this->get('/services/unknown')->assertNotFound();
    }

    public function test_navbar_links_to_every_service_page(): void
    {
        $response = $this->get('/services')->assertOk();

        foreach (config('backlink_services.menu') as $item) {
            $response->assertSee('/services/'.$item['slug'], false);
        }
    }
}
