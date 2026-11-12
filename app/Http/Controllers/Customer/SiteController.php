<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\SiteListing;
use App\Support\PublicSites;
use Illuminate\Contracts\View\View;

class SiteController extends Controller
{
    public function __construct(private SiteListing $sites) {}

    public function index(): View
    {
        return view('customer.sites.index', [
            'sites' => PublicSites::items(),
        ]);
    }

    public function show(string $slug): View
    {
        set_time_limit(120);

        $site = PublicSites::find($slug);
        abort_if($site === null, 404);

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
}
