<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_guest_root_shows_the_public_home_page(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertSee('Real, Traffic-Driven Websites', false);
    }
}
