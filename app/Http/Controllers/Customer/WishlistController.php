<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\NicheListingService;
use App\Support\PublicSites;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class WishlistController extends Controller
{
    public function index(NicheListingService $listings): View
    {
        set_time_limit(120);

        $catalog = $this->catalog($listings);
        $saved = $this->saved($catalog);

        $savedDomains = [];

        foreach ($saved as $item) {
            $savedDomains[mb_strtolower((string) $item['domain'])] = true;
        }

        $picks = $catalog
            ->reject(fn (array $row) => isset($savedDomains[mb_strtolower((string) ($row['domain'] ?? ''))]))
            ->sortByDesc(fn (array $row) => $row['dr'] ?? -1)
            ->take(8)
            ->values();

        return view('customer.wishlist.index', [
            'saved' => $saved,
            'picks' => $picks,
            'sites' => PublicSites::items(),
        ]);
    }

    public function store(Request $request, NicheListingService $listings): RedirectResponse
    {
        set_time_limit(120);

        $domain = mb_strtolower(trim((string) $request->validate([
            'domain' => ['required', 'string', 'max:255'],
        ])['domain']));

        $row = $this->catalog($listings)->first(
            fn (array $item) => mb_strtolower((string) ($item['domain'] ?? '')) === $domain
        );

        if ($row === null) {
            return redirect()->route('wishlist.index')->with('wishlist_error', 'That publisher is not on the list.');
        }

        $items = session('wishlist', []);
        $items[$domain] = [
            'domain' => (string) $row['domain'],
            'niche' => (string) (($row['niches'][0] ?? '') ?: ''),
            'language' => (string) ($row['language'] ?? ''),
            'dr' => $row['dr'],
            'da' => $row['da'],
            'traffic' => $row['traffic'],
            'price' => $row['price'],
        ];
        session(['wishlist' => $items]);

        return redirect()->route('wishlist.index')->with('wishlist_status', $row['domain'].' is on your wishlist.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $domain = mb_strtolower(trim((string) $request->validate([
            'domain' => ['required', 'string', 'max:255'],
        ])['domain']));

        $items = session('wishlist', []);
        unset($items[$domain]);
        session(['wishlist' => $items]);

        return redirect()->route('wishlist.index')->with('wishlist_status', 'Removed from your wishlist.');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function catalog(NicheListingService $listings): Collection
    {
        $payload = $listings->catalogRows();
        $rows = is_array($payload['rows'] ?? null) ? $payload['rows'] : [];

        return collect($rows)->filter(
            fn ($row) => is_array($row) && filled($row['domain'] ?? null)
        )->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $catalog
     * @return Collection<int, array<string, mixed>>
     */
    private function saved(Collection $catalog): Collection
    {
        $items = [];

        foreach (session('wishlist', []) as $domain => $item) {
            if (! is_array($item) || ! filled($item['domain'] ?? null)) {
                continue;
            }

            $items[mb_strtolower((string) $item['domain'])] = $item + ['source' => 'session'];
        }

        session(['wishlist' => collect($items)->mapWithKeys(
            fn (array $item) => [mb_strtolower((string) $item['domain']) => array_diff_key($item, ['source' => true])]
        )->all()]);

        if (auth()->check() && method_exists(auth()->user(), 'savedWebsites')) {
            try {
                foreach (auth()->user()->savedWebsites()->latest()->get() as $website) {
                    $key = mb_strtolower((string) $website->domain);

                    if ($key === '' || isset($items[$key])) {
                        continue;
                    }

                    $items[$key] = [
                        'domain' => (string) $website->domain,
                        'niche' => '',
                        'language' => '',
                        'dr' => $website->domain_rating,
                        'da' => null,
                        'traffic' => $website->monthly_traffic,
                        'price' => $website->price,
                        'source' => 'account',
                        'id' => $website->id,
                        'name' => (string) ($website->website_name ?: $website->domain),
                        'url' => (string) ($website->url ?? ''),
                    ];
                }
            } catch (\Throwable) {
                // The account table is not available in this checkout.
            }
        }

        return collect(array_values($items))->map(function (array $item) use ($catalog) {
            $match = $catalog->first(
                fn (array $row) => mb_strtolower((string) ($row['domain'] ?? '')) === mb_strtolower((string) $item['domain'])
            );

            if (is_array($match)) {
                $item['dr'] = $item['dr'] ?? $match['dr'];
                $item['da'] = $item['da'] ?? $match['da'];
                $item['traffic'] = $item['traffic'] ?? $match['traffic'];
                $item['price'] = $item['price'] ?? $match['price'];
                $item['niche'] = $item['niche'] !== '' ? $item['niche'] : (string) ($match['niches'][0] ?? '');
                $item['language'] = $item['language'] !== '' ? $item['language'] : (string) ($match['language'] ?? '');
            }

            return $item;
        })->values();
    }
}
