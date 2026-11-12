<?php

namespace Tests\Feature\Customer;

use App\Models\Website;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteDetailsModel extends Model
{
    protected $table = 'websites';

    protected $guarded = [];
}

if (! class_exists(Website::class)) {
    class_alias(WebsiteDetailsModel::class, Website::class);
}

class WebsiteDetailsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['marketplace.demo' => true]);
    }

    public function test_demo_publisher_page_shows_the_profile(): void
    {
        $this->get('/marketplace/farmpulse.example')
            ->assertOk()
            ->assertSee('About farmpulse.example')
            ->assertSee('Categories')
            ->assertSee('role="img"', false)
            ->assertSee('<svg', false)
            ->assertSee('Also check these');
    }

    public function test_unknown_demo_domain_is_not_found(): void
    {
        $this->get('/marketplace/unknown.example')->assertNotFound();
    }

    public function test_inactive_website_is_not_found(): void
    {
        $id = Website::query()->create([
            'website_name' => 'Paused',
            'domain' => 'paused.example',
            'url' => 'https://paused.example',
            'niche' => 'Technology',
            'domain_rating' => 10,
            'domain_authority' => 10,
            'trust_flow' => 10,
            'page_authority' => 10,
            'monthly_traffic' => 100,
            'price' => 10,
            'status' => 'inactive',
        ])->id;

        $this->get('/marketplace/'.$id)->assertNotFound();
    }
}
