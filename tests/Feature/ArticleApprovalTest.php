<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Order;
use App\Models\OrderAssignment;
use App\Models\OrderItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_articles_page_shows_the_previous_review_section(): void
    {
        $this->get(route('seo.articles'))
            ->assertOk()
            ->assertSee('Article Review')
            ->assertSee('Preview Content')
            ->assertSee('Reviewer Feedback')
            ->assertSee('Customer Submitted')
            ->assertSee('Approve Article')
            ->assertSee('Request Edits')
            ->assertSee('Reject');
    }

    public function test_seo_articles_and_the_admin_blog_section_stay_separate(): void
    {
        $admin = $this->admin();
        $staff = $this->staff('Rae Staff', 'rae.staff@example.test');
        $item = $this->item($this->customer(), 'BM-BLOG-SEP1');
        $this->assign($item, $staff);

        $this->actingAs($admin)
            ->get(route('seo.articles'))
            ->assertOk()
            ->assertSee('Article Review')
            ->assertSee('Approve Article');

        $this->actingAs($staff)
            ->get(route('admin.articles'))
            ->assertRedirect(route('login'));

        $this->actingAs($staff)
            ->post(route('seo.articles.approval', $item), $this->blogPayload())
            ->assertRedirect(route('seo.articles'))
            ->assertSessionHas('success', 'Blog sent to admin for approval.');

        $article = Article::query()->where('order_item_id', $item->id)->firstOrFail();

        $this->actingAs($staff)
            ->get(route('seo.articles'))
            ->assertOk()
            ->assertSee('Article Review')
            ->assertSee('Customer Submitted')
            ->assertSee('Approve Article');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Enterprise AI Adoption')
            ->assertSee('Review, approve, or reject them here.')
            ->assertSee('Review')
            ->assertSee('Reject')
            ->assertSee('Approval')
            ->assertDontSee('Send to Admin Approval');

        $this->actingAs($admin)
            ->from(route('admin.dashboard'))
            ->post(route('admin.articles.decide', $article), [
                'decision' => 'rejected',
                'feedback' => 'Rewrite the opening.',
            ])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('success', 'Blog rejected.');

        $this->assertSame('rejected', $article->fresh()->status);

        $this->actingAs($staff)
            ->get(route('seo.articles'))
            ->assertOk()
            ->assertSee('Article Review')
            ->assertSee('Request Edits')
            ->assertSee('Reject');
    }

    public function test_customer_cannot_send_a_blog_for_approval(): void
    {
        $customer = $this->customer();
        $staff = $this->staff('Rae Staff', 'rae.staff@example.test');
        $item = $this->item($customer, 'BM-BLOG-0002');
        $this->assign($item, $staff);

        $this->actingAs($customer)
            ->post(route('seo.articles.approval', $item), $this->blogPayload())
            ->assertForbidden();

        $this->assertDatabaseCount('articles', 0);
    }

    public function test_staff_sends_a_blog_to_admin_and_cannot_publish_until_it_is_approved(): void
    {
        $staff = $this->staff('Rae Staff', 'rae.staff@example.test');
        $otherStaff = $this->staff('Other Staff', 'other.staff@example.test');
        $item = $this->item($this->customer(), 'BM-BLOG-0003');
        $this->assign($item, $staff);

        $this->actingAs($otherStaff)
            ->post(route('seo.articles.approval', $item), $this->blogPayload())
            ->assertNotFound();

        $this->actingAs($staff)
            ->post(route('seo.articles.approval', $item), $this->blogPayload('Enterprise AI <script>alert(1)</script>'))
            ->assertRedirect(route('seo.articles'))
            ->assertSessionHas('success', 'Blog sent to admin for approval.');

        $article = Article::query()->where('order_item_id', $item->id)->firstOrFail();
        $this->assertSame('pending_admin', $article->status);
        $this->assertSame($staff->id, $article->staff_id);

        $this->actingAs($staff)
            ->get(route('seo.articles'))
            ->assertOk()
            ->assertSee('Article Review')
            ->assertSee('Approve Article')
            ->assertDontSee('<script>alert(1)</script>', false);

        $this->actingAs($staff)
            ->post(route('seo.articles.publish', $article), [
                'published_url' => 'https://publisher.example/enterprise-ai',
            ])
            ->assertSessionHasErrors('published_url');

        $this->assertSame('pending_admin', $article->fresh()->status);
    }

    public function test_admin_review_is_stored_before_approval_or_rejection(): void
    {
        $admin = $this->admin();
        $staff = $this->staff('Rae Staff', 'rae.staff@example.test');
        $item = $this->item($this->customer(), 'BM-BLOG-REV1');
        $this->assign($item, $staff);
        $article = $this->article($item, $staff, 'pending_admin');

        $this->actingAs($admin)
            ->get(route('admin.articles'))
            ->assertOk()
            ->assertSee('value="review"', false)
            ->assertSee('value="rejected"', false)
            ->assertSee('value="approve"', false);

        $this->actingAs($admin)
            ->post(route('admin.articles.decide', $article), [
                'decision' => 'review',
                'feedback' => 'Checking the anchor placement.',
            ])
            ->assertSessionHas('success', 'Blog saved for review. You can still approve or reject it.');

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'status' => 'in_review',
            'feedback' => 'Checking the anchor placement.',
        ]);
        $this->assertDatabaseHas('article_reviews', [
            'article_id' => $article->id,
            'staff_id' => $admin->id,
            'review_status' => 'in_review',
            'feedback' => 'Checking the anchor placement.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.articles'))
            ->assertOk()
            ->assertSee('Checking the anchor placement.')
            ->assertSee('Approval')
            ->assertSee('Reject');
    }

    public function test_admin_approves_a_blog_and_only_the_assigned_seo_staff_can_publish_it(): void
    {
        $admin = $this->admin();
        $staff = $this->staff('Rae Staff', 'rae.staff@example.test');
        $otherStaff = $this->staff('Other Staff', 'other.staff@example.test');
        $customer = $this->customer();
        $item = $this->item($customer, 'BM-BLOG-0004');
        $this->assign($item, $staff);
        $article = $this->article($item, $staff, 'pending_admin');

        $this->actingAs($customer)
            ->post(route('admin.articles.decide', $article), ['decision' => 'approve'])
            ->assertRedirect(route('login'));

        $this->actingAs($staff)
            ->post(route('admin.articles.decide', $article), ['decision' => 'approve'])
            ->assertRedirect(route('login'));

        $this->actingAs($admin)
            ->get(route('admin.articles'))
            ->assertOk()
            ->assertSee('Enterprise AI Adoption')
            ->assertSee('Review')
            ->assertSee('Reject')
            ->assertSee('Approval')
            ->assertDontSee('Publish Blog')
            ->assertDontSee('Customer Submitted');

        $this->actingAs($admin)
            ->post(route('admin.articles.decide', $article), ['decision' => 'approve'])
            ->assertRedirect()
            ->assertSessionHas('success', 'Blog approved. The assigned SEO staff can now publish it.');

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'status' => 'admin_approved',
        ]);
        $this->assertDatabaseHas('article_reviews', [
            'article_id' => $article->id,
            'staff_id' => $admin->id,
            'review_status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->post(route('seo.articles.publish', $article), [
                'published_url' => 'https://publisher.example/enterprise-ai',
            ])
            ->assertForbidden();

        $this->actingAs($customer)
            ->post(route('seo.articles.publish', $article), [
                'published_url' => 'https://publisher.example/enterprise-ai',
            ])
            ->assertForbidden();

        $this->actingAs($otherStaff)
            ->post(route('seo.articles.publish', $article), [
                'published_url' => 'https://publisher.example/enterprise-ai',
            ])
            ->assertNotFound();

        $this->actingAs($staff)
            ->get(route('seo.articles'))
            ->assertOk()
            ->assertSee('Article Review')
            ->assertSee('Approve Article')
            ->assertSee('Request Edits');

        $this->actingAs($staff)
            ->post(route('seo.articles.publish', $article), [
                'published_url' => 'https://publisher.example/enterprise-ai',
            ])
            ->assertRedirect(route('seo.articles'))
            ->assertSessionHas('success', 'Blog published. The customer can review the live placement.');

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'status' => 'published',
            'published_url' => 'https://publisher.example/enterprise-ai',
        ]);
        $this->assertDatabaseHas('customer_projects', [
            'order_item_id' => $item->id,
            'customer_id' => $customer->id,
            'published_url' => 'https://publisher.example/enterprise-ai',
            'verification_status' => 'pending',
        ]);
        $this->assertDatabaseHas('order_assignments', [
            'order_item_id' => $item->id,
            'status' => 'published',
        ]);
    }

    public function test_admin_edit_request_requires_feedback_and_returns_the_blog_to_staff(): void
    {
        $admin = $this->admin();
        $staff = $this->staff('Rae Staff', 'rae.staff@example.test');
        $item = $this->item($this->customer(), 'BM-BLOG-0005');
        $this->assign($item, $staff);
        $article = $this->article($item, $staff, 'pending_admin');

        $this->actingAs($admin)
            ->from(route('admin.articles'))
            ->post(route('admin.articles.decide', $article), ['decision' => 'corrections_requested'])
            ->assertRedirect(route('admin.articles'))
            ->assertSessionHasErrors('feedback');

        $this->assertSame('pending_admin', $article->fresh()->status);

        $this->actingAs($admin)
            ->post(route('admin.articles.decide', $article), [
                'decision' => 'corrections_requested',
                'feedback' => 'Move the anchor into the second paragraph.',
            ])
            ->assertSessionHas('success', 'Edit request sent back to the SEO staff.');

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'status' => 'corrections_requested',
            'feedback' => 'Move the anchor into the second paragraph.',
        ]);

        $this->actingAs($staff)
            ->get(route('seo.articles'))
            ->assertOk()
            ->assertSee('Article Review')
            ->assertSee('Request Edits')
            ->assertSee('Reject');
    }

    public function test_publishing_from_assigned_orders_requires_admin_approval_and_seo_staff(): void
    {
        $admin = $this->admin();
        $staff = $this->staff('Rae Staff', 'rae.staff@example.test');
        $item = $this->item($this->customer(), 'BM-BLOG-0006');
        $assignment = $this->assign($item, $staff);

        $this->actingAs($staff)
            ->from(route('seo.assigned-orders'))
            ->put(route('seo.orders.status', 'BM-BLOG-0006'), [
                'order_item_id' => $item->id,
                'status' => 'published',
                'published_url' => 'https://publisher.example/too-early',
            ])
            ->assertRedirect(route('seo.assigned-orders'))
            ->assertSessionHasErrors('status');

        $this->assertSame('assigned', $assignment->fresh()->status);

        $article = $this->article($item, $staff, 'admin_approved');

        $this->actingAs($admin)
            ->put(route('seo.orders.status', 'BM-BLOG-0006'), [
                'order_item_id' => $item->id,
                'status' => 'published',
                'published_url' => 'https://publisher.example/admin-publish',
            ])
            ->assertForbidden();

        $this->actingAs($staff)
            ->put(route('seo.orders.status', 'BM-BLOG-0006'), [
                'order_item_id' => $item->id,
                'status' => 'published',
                'published_url' => 'https://publisher.example/staff-publish',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('published', $article->fresh()->status);
        $this->assertSame('published', $assignment->fresh()->status);
    }

    /**
     * @return array{title: string, body: string, staff_notes: string}
     */
    private function blogPayload(string $title = 'Enterprise AI Adoption'): array
    {
        return [
            'title' => $title,
            'body' => 'SEO staff wrote this blog for the assigned order. The customer supplied only the topic, target URL, and anchor text.',
            'staff_notes' => 'Anchor sits in the second paragraph.',
        ];
    }

    private function article(OrderItem $item, User $staff, string $status): Article
    {
        return Article::query()->create([
            'order_item_id' => $item->id,
            'staff_id' => $staff->id,
            'title' => 'Enterprise AI Adoption',
            'body' => 'SEO staff wrote this blog for the assigned order. The customer supplied only the topic, target URL, and anchor text.',
            'status' => $status,
            'submitted_at' => now(),
            'approved_at' => $status === 'admin_approved' ? now() : null,
        ]);
    }

    private function assign(OrderItem $item, User $staff): OrderAssignment
    {
        return OrderAssignment::query()->create([
            'order_item_id' => $item->id,
            'staff_id' => $staff->id,
            'assigned_by' => $this->admin()->id,
            'status' => 'assigned',
        ]);
    }

    private function admin(): User
    {
        $role = Role::query()->firstOrCreate(['role_name' => 'Admin'], ['permissions' => []]);

        return User::query()->firstOrCreate(['email' => 'admin.blog@example.test'], [
            'name' => 'Avery Admin',
            'password' => 'password',
            'role_id' => $role->id,
            'status' => 'active',
            'account_type' => 'admin',
        ]);
    }

    private function staff(string $name, string $email): User
    {
        $role = Role::query()->firstOrCreate(['role_name' => 'Staff'], ['permissions' => []]);

        return User::factory()->create([
            'name' => $name,
            'email' => $email,
            'role_id' => $role->id,
            'status' => 'active',
            'account_type' => 'staff',
        ]);
    }

    private function customer(): User
    {
        $role = Role::query()->firstOrCreate(['role_name' => 'Customer'], ['permissions' => []]);

        return User::factory()->create([
            'name' => 'Alex Customer',
            'role_id' => $role->id,
            'status' => 'active',
            'account_type' => 'customer',
        ]);
    }

    private function item(User $customer, string $orderNumber): OrderItem
    {
        $order = Order::query()->create([
            'order_number' => $orderNumber,
            'customer_id' => $customer->id,
            'session_id' => 'article-approval-test',
            'total_amount' => 120,
            'payment_status' => 'paid',
            'order_status' => 'processing',
        ]);

        return $order->items()->create([
            'item_name' => 'Guest Post Placement',
            'unit_price' => 120,
            'quantity' => 1,
            'target_url' => 'https://client.example/ai-tools',
            'anchor_text' => 'enterprise AI tools',
            'article_topic' => 'Enterprise AI Adoption',
            'requirements' => 'Use an editorial tone.',
        ]);
    }
}
