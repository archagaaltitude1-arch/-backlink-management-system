<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\OrderAssignment;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $this->viewer($request);

        if ($user->role?->role_name === 'Admin') {
            return redirect()
                ->route('admin.articles')
                ->with('success', 'Blog reviews are in the admin Blog section. SEO staff use their own Articles page.');
        }

        abort_unless(in_array($user->role?->role_name, ['Staff', 'SEO'], true), 403);

        $articles = OrderItem::query()
            ->with(['order.customer', 'website', 'assignment', 'article'])
            ->whereHas('assignment', fn ($query) => $query->where('staff_id', $user->id))
            ->latest()
            ->get()
            ->map(fn (OrderItem $item): array => $this->card($item, $user));

        return view('seo.articles', [
            'articles' => $articles,
            'counts' => [
                'total' => $articles->count(),
                'pending_admin' => $articles->where('status', 'pending_admin')->count(),
                'admin_approved' => $articles->where('status', 'admin_approved')->count(),
                'published' => $articles->where('status', 'published')->count(),
            ],
        ]);
    }

    public function submitForApproval(Request $request, OrderItem $orderItem): RedirectResponse
    {
        $staff = $this->seoStaff($request);
        $assignment = OrderAssignment::query()
            ->where('order_item_id', $orderItem->id)
            ->where('staff_id', $staff->id)
            ->firstOrFail();

        $article = Article::query()->where('order_item_id', $orderItem->id)->first();
        if ($article !== null && ! $article->canBeSentToAdmin()) {
            return back()->withErrors([
                'body' => 'This blog is already with the admin or has already been approved.',
            ])->withInput();
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:50000'],
            'staff_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        Article::query()->updateOrCreate(
            ['order_item_id' => $orderItem->id],
            [
                'staff_id' => $staff->id,
                'title' => $data['title'],
                'body' => $data['body'],
                'staff_notes' => $data['staff_notes'] ?? null,
                'status' => 'pending_admin',
                'feedback' => null,
                'submitted_at' => now(),
                'approved_at' => null,
            ],
        );

        if ($assignment->status === 'assigned') {
            $assignment->update(['status' => 'in_progress']);
        }

        return redirect()
            ->route('seo.articles')
            ->with('success', 'Blog sent to admin for approval.');
    }

    public function publish(Request $request, Article $article, StaffOrderController $orders): RedirectResponse
    {
        $staff = $this->seoStaff($request);
        $article->loadMissing('orderItem.assignment', 'orderItem.order');
        $assignment = $article->orderItem?->assignment;

        if ($assignment === null || $assignment->staff_id !== $staff->id) {
            abort(404);
        }

        if ($article->status !== 'admin_approved') {
            return back()->withErrors([
                'published_url' => 'Admin must approve the blog before it can be published.',
            ])->withInput();
        }

        $data = $request->validate([
            'published_url' => ['required', 'url', 'max:2000'],
        ]);

        if (! $orders->recordPublication($assignment, $data['published_url'])) {
            return back()->withErrors([
                'published_url' => 'This order has no customer account to approve the backlink.',
            ])->withInput();
        }

        $assignment->update(['status' => 'published']);
        $article->update([
            'status' => 'published',
            'published_url' => $data['published_url'],
            'published_at' => now(),
        ]);

        return back()->with('success', 'Blog published. The customer can review the live placement.');
    }

    /**
     * @return array<string, mixed>
     */
    private function card(OrderItem $item, User $staff): array
    {
        $article = $item->article;
        $status = $article?->status ?? 'draft';
        $canSubmit = $article === null || $article->canBeSentToAdmin();

        return [
            'order_item_id' => $item->id,
            'article_id' => $article?->id,
            'order_number' => $item->order?->order_number ?? 'Item '.$item->id,
            'title' => $article?->title ?? $item->article_topic,
            'customer' => $item->order?->customer?->name ?? 'Guest checkout',
            'website' => $item->website?->domain ?? 'No publisher selected',
            'da' => $item->website?->domain_authority ?? '—',
            'dr' => $item->website?->domain_rating ?? '—',
            'word_count' => $article?->wordCount() ?? 0,
            'target_url' => $item->target_url,
            'anchor_text' => $item->anchor_text,
            'requirements' => $item->requirements,
            'body' => $article?->body ?? '',
            'paragraphs' => $article?->paragraphs() ?? [],
            'staff_notes' => $article?->staff_notes ?? '',
            'status' => $status,
            'status_label' => $article?->statusLabel() ?? 'Ready to write',
            'feedback' => $article?->feedback ?? '',
            'published_url' => $article?->published_url ?? '',
            'submitted_at' => $article?->submitted_at?->format('M d, Y · h:i A'),
            'can_submit' => $canSubmit && $item->assignment?->staff_id === $staff->id,
            'can_publish' => $status === 'admin_approved' && $item->assignment?->staff_id === $staff->id,
        ];
    }

    private function viewer(Request $request): User
    {
        $user = $request->user();
        if ($user === null) {
            throw new HttpResponseException(redirect()->guest(route('seo.login')));
        }

        $user->loadMissing('role');

        return $user;
    }

    private function seoStaff(Request $request): User
    {
        $user = $this->viewer($request);
        abort_unless(in_array($user->role?->role_name, ['Staff', 'SEO'], true), 403);

        return $user;
    }
}
