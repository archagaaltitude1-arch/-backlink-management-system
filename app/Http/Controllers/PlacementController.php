<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\View\View;

class PlacementController extends Controller
{
    public function show(Article $article): View
    {
        abort_unless($article->status === 'published', 404);

        $article->load(['orderItem.website', 'orderItem.order.customer']);
        $viewer = auth()->user();
        $viewer?->loadMissing('role');

        $layout = match ($viewer?->role?->role_name) {
            'Staff', 'SEO' => 'layouts.seo',
            'Admin' => 'layouts.admin',
            default => 'layouts.customer',
        };

        return view('placements.show', [
            'article' => $article,
            'layout' => $layout,
        ]);
    }
}
