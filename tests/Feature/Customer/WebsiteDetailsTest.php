<?php

namespace Tests\Feature\Customer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WebsiteDetailsModel extends Model
{
    protected $table = 'websites';

    protected $guarded = [];
}

if (! class_exists(\App\Models\Website::class)) {
    class_alias(WebsiteDetailsModel::class, \App\Models\Website::class);
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
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->string('domain');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        $id = \App\Models\Website::query()->create([
            'domain' => 'paused.example',
            'status' => 'inactive',
        ])->id;

        $this->get('/marketplace/'.$id)->assertNotFound();
    }
}
