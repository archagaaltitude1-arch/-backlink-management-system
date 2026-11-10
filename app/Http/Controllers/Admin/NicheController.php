<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NicheRequest;
use App\Models\Niche;
use App\Models\NicheSpecification;
use App\Models\Website;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NicheController extends Controller
{
    /**
     * Show the niche catalogue, linked-website counts, and filter specifications.
     */
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $linked = $request->string('linked')->toString();

        $niches = Niche::query()
            ->withCount('websites')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('niche_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($linked === 'yes', fn ($query) => $query->has('websites'))
            ->when($linked === 'no', fn ($query) => $query->doesntHave('websites'))
            ->orderBy('niche_name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.niches.index', [
            'niches' => $niches,
            'search' => $search,
            'linked' => $linked,
            'nicheCount' => Niche::query()->count(),
            'linkedWebsiteCount' => Website::query()->whereHas('niches')->count(),
            'activeSpecificationCount' => NicheSpecification::query()->where('status', 'active')->count(),
            'specifications' => NicheSpecification::query()->orderBy('specification_name')->get(),
        ]);
    }

    /**
     * Show the form for creating a niche.
     */
    public function create(): View
    {
        return view('admin.niches.form', [
            'niche' => new Niche,
        ]);
    }

    /**
     * Store a new niche.
     */
    public function store(NicheRequest $request): RedirectResponse
    {
        $niche = Niche::query()->create($request->validated());

        return redirect()
            ->route('admin.niches.show', $niche)
            ->with('success', 'Niche added.');
    }

    /**
     * Show one niche, its websites, and the shared specifications.
     */
    public function show(Niche $niche): View
    {
        $niche->loadCount('websites');
        $niche->load(['websites' => fn ($query) => $query->orderBy('website_name')]);

        return view('admin.niches.show', [
            'niche' => $niche,
            'specifications' => NicheSpecification::query()->orderBy('specification_name')->get(),
        ]);
    }

    /**
     * Show the form for editing a niche.
     */
    public function edit(Niche $niche): View
    {
        return view('admin.niches.form', [
            'niche' => $niche,
        ]);
    }

    /**
     * Update a niche.
     */
    public function update(NicheRequest $request, Niche $niche): RedirectResponse
    {
        $niche->update($request->validated());

        return redirect()
            ->route('admin.niches.show', $niche)
            ->with('success', 'Niche updated.');
    }

    /**
     * Delete a niche that is not linked to any website.
     */
    public function destroy(Niche $niche): RedirectResponse
    {
        if ($niche->websites()->exists()) {
            return back()->with('error', 'This niche is linked to websites and cannot be deleted.');
        }

        $niche->delete();

        return redirect()
            ->route('admin.niches')
            ->with('success', 'Niche deleted.');
    }
}
