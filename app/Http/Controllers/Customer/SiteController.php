<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\NicheListingService;
use App\Services\Customer\SiteListing;
use App\Support\PublicSites;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SiteController extends Controller
{
    public function __construct(
        private SiteListing $sites,
        private NicheListingService $listings,
    ) {}

    public function index(): View
    {
        return view('customer.sites.index', [
            'sites' => PublicSites::items(),
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        set_time_limit(120);

        $site = PublicSites::find($slug);
        abort_if($site === null, 404);

        if ($site['kind'] === 'niche-edit') {
            return $this->marketplace($request, $site);
        }

        $listing = $site['kind'] === 'favorites'
            ? ['demo' => false, 'rows' => [], 'groups' => null]
            : $this->sites->forKind($site['kind']);

        return view('customer.sites.show', [
            'site' => $site,
            'sites' => PublicSites::items(),
            'demo' => $listing['demo'],
            'rows' => $listing['rows'],
            'groups' => $listing['groups'],
        ]);
    }

    /**
     * @param  array{label: string, slug: string, kind: string, about: string, detail: string, chart: string|null, checks: list<string>}  $site
     */
    private function marketplace(Request $request, array $site): View
    {
        $catalog = $this->listings->catalogRows();
        $rows = collect($catalog['rows'])
            ->sortByDesc(fn (array $row) => $row['dr'] ?? -1)
            ->values()
            ->all();

        $categories = [];

        foreach ($this->listings->categoryNames() as $name) {
            $slug = Str::slug($name);

            if ($name !== '' && $slug !== '') {
                $categories[$name] = $slug;
            }
        }

        ksort($categories);

        $defaultNiche = trim((string) $request->query('niche', ''));
        if ($defaultNiche !== '' && ! isset($categories[$defaultNiche])) {
            $defaultNiche = '';
        }

        $defaultDr = $request->query('dr_min');
        $defaultDr = is_numeric($defaultDr) ? (string) (int) $defaultDr : '';

        return view('customer.sites.marketplace', [
            'site' => $site,
            'sites' => PublicSites::items(),
            'demo' => $catalog['demo'],
            'rows' => $rows,
            'categories' => $categories,
            'defaultType' => 'Niche edit',
            'defaultNiche' => $defaultNiche,
            'defaultDr' => $defaultDr,
        ]);
    }
}
