<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArticleApprovalController extends Controller
{
    public function index(): View
    {
        $articles = Article::query()
            ->with(['staff:id,name', 'latestReview', 'orderItem.order.customer', 'orderItem.website', 'orderItem.assignment.staff'])
            ->whereIn('status', ['pending_admin', 'in_review', 'corrections_requested', 'rejected', 'admin_approved', 'published'])
            ->latest('submitted_at')
            ->get();

        return view('admin.articles.index', [
            'articles' => $articles,
            'counts' => [
                'total' => $articles->count(),
                'pending_admin' => $articles->whereIn('status', ['pending_admin', 'in_review'])->count(),
                'admin_approved' => $articles->where('status', 'admin_approved')->count(),
                'published' => $articles->where('status', 'published')->count(),
            ],
        ]);
    }

    public function decide(Request $request, Article $article): RedirectResponse
    {
        if ($article->status === 'published') {
            return back()->withErrors([
                'decision' => 'This blog is already published.',
            ]);
        }

        $data = $request->validate([
            'decision' => ['required', Rule::in(['review', 'approve', 'corrections_requested', 'rejected'])],
            'feedback' => ['nullable', 'string', 'max:2000', 'required_if:decision,corrections_requested', 'required_if:decision,rejected'],
        ]);

        $status = match ($data['decision']) {
            'review' => 'in_review',
            'approve' => 'admin_approved',
            'corrections_requested' => 'corrections_requested',
            default => 'rejected',
        };

        $reviewStatus = match ($data['decision']) {
            'review' => 'in_review',
            'approve' => 'approved',
            'corrections_requested' => 'corrections_requested',
            default => 'rejected',
        };

        $article->update([
            'status' => $status,
            'feedback' => $data['feedback'] ?? null,
            'approved_at' => $status === 'admin_approved' ? now() : null,
        ]);

        ArticleReview::query()->create([
            'article_id' => $article->id,
            'staff_id' => $request->user()->id,
            'review_status' => $reviewStatus,
            'feedback' => $data['feedback'] ?? null,
            'reviewed_at' => now(),
        ]);

        $message = match ($status) {
            'in_review' => 'Blog saved for review. You can still approve or reject it.',
            'admin_approved' => 'Blog approved. The assigned SEO staff can now publish it.',
            'corrections_requested' => 'Edit request sent back to the SEO staff.',
            default => 'Blog rejected.',
        };

        return back()->with('success', $message);
    }
}
