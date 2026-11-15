<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PublisherReview;
use App\Services\Customer\PublishOfferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublisherReviewController extends Controller
{
    public function __construct(private PublishOfferService $offers) {}

    public function store(Request $request, string $site): RedirectResponse
    {
        $offer = $this->offers->resolve($site);
        abort_if($offer === null, 404);
        abort_unless($this->offers->canReview($request->user(), $offer), 403);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:80'],
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        PublisherReview::query()->create([
            'user_id' => $request->user()->id,
            'website_id' => $offer['website_id'],
            'demo_domain' => $offer['demo'] ? $offer['domain'] : null,
            'rating' => $data['rating'],
            'title' => $data['title'] ?? null,
            'comment' => $data['comment'],
            'status' => 'pending',
        ]);

        // TODO(Archaga): approve reviews in admin.

        return back()->with('review_status', 'Thanks! Your review will appear after approval.');
    }
}
