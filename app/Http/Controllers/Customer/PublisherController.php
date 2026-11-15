<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\NicheListingService;
use App\Support\BrowseCountries;
use App\Support\PublicPackages;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublisherController extends Controller
{
    public function __construct(private NicheListingService $listings) {}

    public function index(Request $request): View
    {
        set_time_limit(120);

        $catalog = $this->listings->catalogRows();
        $country = BrowseCountries::find((string) $request->query('country', ''));
        $rows = collect($catalog['rows'])
            ->sortByDesc(fn (array $row) => $row['dr'] ?? -1)
            ->values()
            ->all();

        if ($country !== null) {
            $rows = collect(BrowseCountries::only($rows, $country['code']))
                ->sortByDesc(fn (array $row) => $row['traffic'] ?? -1)
                ->values()
                ->all();
        }

        $categories = [];

        foreach ($this->listings->categoryNames() as $name) {
            $slug = Str::slug($name);

            if ($name !== '' && $slug !== '') {
                $categories[$name] = $slug;
            }
        }

        ksort($categories);

        $linkTypes = ['Guest post', 'Niche edit', 'Contextual', 'Text link'];
        $requestedType = (string) $request->query('type', '');
        $defaultType = in_array($requestedType, $linkTypes, true) ? $requestedType : '';
        $requestedDr = $request->query('dr_min');
        $defaultDr = is_numeric($requestedDr) ? (string) (int) $requestedDr : '';
        $package = PublicPackages::find((string) $request->query('package', ''));
        $paidLabel = $request->query('confirmed') === '1' && $package !== null ? (string) $package['label'] : '';

        return view('customer.publishers.index', [
            'demo' => $catalog['demo'],
            'rows' => $rows,
            'categories' => $categories,
            'country' => $country,
            'defaultType' => $defaultType,
            'defaultDr' => $defaultDr,
            'paidLabel' => $paidLabel,
        ]);
    }
}
