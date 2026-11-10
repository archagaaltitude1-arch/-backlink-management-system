<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WebsiteRequest;
use App\Models\Niche;
use App\Models\Website;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $status = $request->string('status')->toString();

        $websites = Website::query()
            ->with('niches')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('website_name', 'like', "%{$search}%")
                        ->orWhere('domain', 'like', "%{$search}%")
                        ->orWhere('url', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['active', 'inactive', 'pending'], true), fn ($query) => $query->where('status', $status))
            ->orderBy('website_name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.websites.index', [
            'websites' => $websites,
            'search' => $search,
            'status' => $status,
            'total' => Website::query()->count(),
            'activeCount' => Website::query()->where('status', 'active')->count(),
            'pendingCount' => Website::query()->where('status', 'pending')->count(),
            'inactiveCount' => Website::query()->where('status', 'inactive')->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.websites.form', [
            'website' => new Website,
            'niches' => Niche::query()->orderBy('niche_name')->get(),
        ]);
    }

    public function store(WebsiteRequest $request): RedirectResponse
    {
        $website = DB::transaction(function () use ($request): Website {
            $website = Website::query()->create($request->safe()->only([
                'website_name', 'domain', 'url', 'price', 'credit_cost', 'status',
            ]));
            $website->niches()->sync($request->input('niche_ids', []));
            $website->seoMetric()->create($request->safe()->only([
                'DR', 'DA', 'TF', 'CF', 'traffic', 'spam_score', 'backlinks',
            ]));

            return $website;
        });

        return redirect()->route('admin.websites.show', $website)->with('success', 'Website added.');
    }

    public function show(Website $website): View
    {
        $website->load(['niches', 'seoMetric']);

        return view('admin.websites.show', [
            'website' => $website,
        ]);
    }

    public function edit(Website $website): View
    {
        $website->load(['niches', 'seoMetric']);

        return view('admin.websites.form', [
            'website' => $website,
            'niches' => Niche::query()->orderBy('niche_name')->get(),
        ]);
    }

    public function update(WebsiteRequest $request, Website $website): RedirectResponse
    {
        DB::transaction(function () use ($request, $website): void {
            $website->update($request->safe()->only([
                'website_name', 'domain', 'url', 'price', 'credit_cost', 'status',
            ]));
            $website->niches()->sync($request->input('niche_ids', []));
            $website->seoMetric()->updateOrCreate(
                ['website_id' => $website->id],
                $request->safe()->only(['DR', 'DA', 'TF', 'CF', 'traffic', 'spam_score', 'backlinks']),
            );
        });

        return redirect()->route('admin.websites.show', $website)->with('success', 'Website updated.');
    }

    public function destroy(Website $website): RedirectResponse
    {
        $website->delete();

        return redirect()->route('admin.websites')->with('success', 'Website deleted.');
    }
}
