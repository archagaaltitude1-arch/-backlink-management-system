<?php

namespace Tests\Feature\Customer;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PublisherReview;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['marketplace.demo' => true]);
    }

    public function test_demo_publish_page_shows_the_order_layout(): void
    {
        $this->get('/publish/farmpulse.example')
            ->assertOk()
            ->assertSee('Publish a Guest Post on farmpulse.example')
            ->assertSee('Site ID')
            ->assertSee('Authority')
            ->assertSee('Trust')
            ->assertSee('Strength')
            ->assertSee('Link profile')
            ->assertSee('Publishing rules')
            ->assertSee('Restricted topics')
            ->assertSee('Order type')
            ->assertSee('Your article topic')
            ->assertSee('Subtotal')
            ->assertSee('Description')
            ->assertSee('Reviews (0)')
            ->assertSee('There are no reviews yet.')
            ->assertSee('Similar publishers');
    }

    public function test_unknown_publisher_is_not_found(): void
    {
        $this->get('/publish/unknown.example')->assertNotFound();
    }

    public function test_guest_review_redirects_to_login(): void
    {
        $this->post('/publish/farmpulse.example/reviews', [
            'rating' => 5,
            'comment' => 'Helpful publisher.',
        ])->assertRedirect(route('auth.login'));
    }

    public function test_user_without_a_paid_order_cannot_review(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/publish/farmpulse.example/reviews', [
                'rating' => 4,
                'comment' => 'Not eligible yet.',
            ])
            ->assertForbidden();
    }

    public function test_eligible_review_is_saved_as_pending_and_hidden(): void
    {
        $user = User::factory()->create();
        $website = Website::query()->create([
            'website_name' => 'Review Site',
            'domain' => 'reviewsite.example',
            'url' => 'https://reviewsite.example',
            'niche' => 'Technology',
            'domain_rating' => 40,
            'domain_authority' => 30,
            'trust_flow' => 20,
            'page_authority' => 25,
            'monthly_traffic' => 1000,
            'price' => 120,
            'status' => 'active',
        ]);
        $order = Order::query()->create([
            'order_number' => 'ORD-REVIEW-1',
            'customer_id' => $user->id,
            'session_id' => 'test-session',
            'total_amount' => 120,
            'payment_status' => 'paid',
            'order_status' => 'completed',
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'website_id' => $website->id,
            'item_name' => 'Guest post',
            'unit_price' => 120,
            'quantity' => 1,
            'target_url' => 'https://buyer.example',
            'anchor_text' => 'buyer',
            'article_topic' => 'Technology',
        ]);

        $this->actingAs($user)
            ->post('/publish/'.$website->id.'/reviews', [
                'rating' => 5,
                'title' => 'Solid placement',
                'comment' => 'The article went live as described.',
            ])
            ->assertRedirect()
            ->assertSessionHas('review_status', 'Thanks! Your review will appear after approval.');

        $this->assertDatabaseHas('publisher_reviews', [
            'user_id' => $user->id,
            'website_id' => $website->id,
            'status' => 'pending',
        ]);

        $this->assertSame(0, PublisherReview::query()->where('status', 'approved')->count());

        $this->actingAs($user)
            ->get('/publish/'.$website->id)
            ->assertOk()
            ->assertDontSee('The article went live as described.')
            ->assertSee('There are no reviews yet.');
    }
}
