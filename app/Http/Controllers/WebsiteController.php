<?php

namespace App\Http\Controllers;

use App\Models\Niche;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'niche' => ['nullable', 'string', 'max:80'],
            'min_dr' => ['nullable', 'integer', 'between:0,100'],
            'sort' => ['nullable', 'in:recommended,dr,traffic,price'],
        ]);

        $websites = Website::query()
            ->where('status', 'active')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('website_name', 'like', '%'.$search.'%')
                        ->orWhere('domain', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['niche'] ?? null, fn ($query, string $niche) => $query->where(fn ($query) => $query->where('niche', $niche)->orWhereHas('niches', fn ($query) => $query->where('niche_name', $niche))))
            ->when(isset($filters['min_dr']), fn ($query) => $query->where('domain_rating', '>=', $filters['min_dr']))
            ->when(($filters['sort'] ?? 'recommended') === 'dr', fn ($query) => $query->orderByDesc('domain_rating'))
            ->when(($filters['sort'] ?? 'recommended') === 'traffic', fn ($query) => $query->orderByDesc('monthly_traffic'))
            ->when(($filters['sort'] ?? 'recommended') === 'price', fn ($query) => $query->orderBy('price'))
            ->when(($filters['sort'] ?? 'recommended') === 'recommended', fn ($query) => $query->orderByDesc('domain_rating')->orderBy('price'))
            ->paginate(12)
            ->withQueryString();

        $niches = Niche::query()
            ->whereHas('websites', fn ($query) => $query->where('status', 'active'))
            ->orderBy('niche_name')
            ->pluck('niche_name')
            ->merge(Website::query()
                ->where('status', 'active')
                ->distinct()
                ->orderBy('niche')
                ->pluck('niche'))
            ->unique()->sort()->values();

        return view('customer.websites', compact('websites', 'niches', 'filters'));
    }
}
