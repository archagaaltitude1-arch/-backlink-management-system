<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Services\Customer\NicheListingService;
use App\Support\PublicPackages;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class PackagePageController extends Controller
{
    public function index(): View
    {
        return view('customer.packages.index', [
            'guestPostPrices' => PublicPackages::guestPostPrices(),
            'packageSelections' => $this->packageSelections(),
        ]);
    }

    /**
     * @return list<array{slug: string, name: string, tabs: list<array<string, mixed>>}>
     */
    private function packageSelections(): array
    {
        $names = [];

        foreach (config('backlink_services.menu', []) as $item) {
            $names[(string) ($item['slug'] ?? '')] = (string) ($item['name'] ?? '');
        }

        $selections = [];

        foreach (['text-link', 'contextual-link', 'guest-post'] as $slug) {
            $selections[] = [
                'slug' => $slug,
                'name' => ($names[$slug] ?? '') !== '' ? $names[$slug] : $slug,
                'tabs' => PublicPackages::tabs($slug),
            ];
        }

        return $selections;
    }

    public function show(string $slug): View
    {
        $package = PublicPackages::find($slug);
        abort_if($package === null, 404);

        $priced = $this->withStoredPrice([$package])[0];
        $priced['story'] = PublicPackages::story($slug);
        $selectedBacklinks = null;

        if (PublicPackages::serviceKey($slug) !== null) {
            $line = $this->cartPackage($slug);
            $selectedBacklinks = (int) ($line['backlinks'] ?? 0);

            if ($selectedBacklinks > 0 && is_array($priced['story'])) {
                $priced['story']['count'] = (string) $selectedBacklinks;
                $priced['story']['badge'] = $selectedBacklinks.' backlinks';

                if (is_numeric($line['price'] ?? null)) {
                    $priced['price'] = (float) $line['price'];
                    $priced['price_prefix'] = '';
                }
            }
        }

        return view('customer.packages.show', [
            'package' => $priced,
            'packages' => $this->withStoredPrice(PublicPackages::items()),
            'guestPostPrices' => PublicPackages::guestPostPrices(),
            'selectedBacklinks' => $selectedBacklinks > 0 ? $selectedBacklinks : null,
        ]);
    }

    public function websites(Request $request, string $slug, NicheListingService $listings): View
    {
        set_time_limit(120);

        $package = PublicPackages::find($slug);
        $story = PublicPackages::story($slug);
        abort_if($package === null || $story === null, 404);

        $priced = $this->withStoredPrice([$package])[0];
        $priced['story'] = $story;
        $catalog = $listings->catalogRows();
        $requestedBacklinks = (int) $request->query('backlinks', 0);
        $orderId = (int) $request->query('order', 0);

        if (PublicPackages::serviceKey($slug) !== null && $requestedBacklinks < 1) {
            $requestedBacklinks = $this->cartBacklinks($slug);
        }

        if (PublicPackages::serviceKey($slug) !== null && $requestedBacklinks > 0) {
            $story['count'] = (string) $requestedBacklinks;
            $priced['story'] = $story;
            $quantity = 1;
            $limit = $requestedBacklinks;
        } else {
            $quantity = min(10, max(1, (int) $request->query('quantity', 1)));
            $limit = PublicPackages::websiteLimit($story, $quantity);
        }
        $choice = PublicPackages::choiceLabel($priced, $story);
        $websiteWord = $limit === 1 ? 'website' : 'websites';
        $lead = $limit === null
            ? $choice.'. Choose the websites for this package, then add it to your cart.'
            : $choice.'. Choose '.$limit.' '.$websiteWord.' for this package, then add it to your cart.';

        return view('customer.packages.websites', [
            'package' => $priced,
            'choice' => $choice,
            'limit' => $limit,
            'quantity' => $quantity,
            'backlinks' => $requestedBacklinks > 0 ? $requestedBacklinks : null,
            'orderId' => $orderId > 0 ? $orderId : null,
            'orderType' => trim((string) $request->query('order_type', '')),
            'rows' => collect($catalog['rows'])
                ->map(function (array $row): array {
                    $row['metrics_source'] = filled($row['backlinks_refreshed_at'] ?? null) ? 'dataforseo' : 'sample';

                    return $row;
                })
                ->sortByDesc(fn (array $row) => $row['dr'] ?? -1)
                ->values()
                ->all(),
            'demo' => $catalog['demo'],
            'categories' => $this->categories($listings),
            'lead' => $lead,
        ]);
    }

    public function assign(Request $request, string $slug, NicheListingService $listings): RedirectResponse
    {
        $package = PublicPackages::find($slug);
        abort_if($package === null || PublicPackages::serviceKey($slug) === null, 404);
        abort_unless($request->user() !== null, 403);

        $data = $request->validate([
            'order' => ['required', 'integer'],
            'backlinks' => ['required', 'integer', 'min:1', 'max:500'],
            'websites' => ['required', 'array'],
            'websites.*' => ['string', 'max:255'],
        ]);

        $count = (int) $data['backlinks'];

        if (! PublicPackages::backlinksAllowed($slug, $count)) {
            throw ValidationException::withMessages([
                'backlinks' => 'That quantity is not available for this package.',
            ]);
        }

        $order = $request->user()->orders()->whereKey($data['order'])->firstOrFail();
        abort_unless($order->payment_status === 'paid', 403);

        $item = $order->items()->where('item_name', $package['label'])->firstOrFail();
        $expected = (int) ($item->backlinks ?: $count);
        $back = route('packages.websites', [
            'slug' => $slug,
            'backlinks' => $expected,
            'order' => $order->id,
        ]);

        if ($count !== $expected) {
            return redirect()->to($back)->with('shopping_error', 'Choose exactly '.$expected.' websites for this order.');
        }

        $known = [];

        foreach ($listings->catalogRows()['rows'] as $row) {
            $domain = trim((string) ($row['domain'] ?? ''));

            if ($domain !== '') {
                $known[mb_strtolower($domain)] = $domain;
            }
        }

        $chosen = [];

        foreach ($data['websites'] as $website) {
            $key = mb_strtolower(trim((string) $website));

            if ($key === '' || isset($chosen[$key])) {
                continue;
            }

            if (! isset($known[$key])) {
                return redirect()->to($back)->with('shopping_error', 'That website is not on the list.');
            }

            $chosen[$key] = $known[$key];
        }

        $websites = array_values($chosen);

        if (count($websites) !== $expected) {
            return redirect()->to($back)->with('shopping_error', 'Choose exactly '.$expected.' websites for this order.');
        }

        $item->update([
            'backlinks' => $expected,
            'selected_websites' => $websites,
        ]);

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Your '.$expected.' websites are saved for this order.');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function cartPackage(string $slug): ?array
    {
        $items = session('shopping_cart', []);

        if (! is_array($items)) {
            return null;
        }

        foreach ($items as $item) {
            if (is_array($item) && ($item['package_slug'] ?? '') === $slug) {
                return $item;
            }
        }

        return null;
    }

    private function cartBacklinks(string $slug): int
    {
        $item = $this->cartPackage($slug);

        return $item === null ? 0 : (int) ($item['backlinks'] ?? 0);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function withStoredPrice(array $items): array
    {
        $stored = $this->storedRows();

        return array_map(function (array $item) use ($stored): array {
            $match = null;

            foreach ($item['lookup'] ?? [] as $name) {
                $key = mb_strtolower((string) $name);

                if (isset($stored[$key])) {
                    $match = $stored[$key];
                    break;
                }
            }

            $price = isset($item['amount']) && is_numeric($item['amount']) && (float) $item['amount'] > 0
                ? (float) $item['amount']
                : null;
            $credits = null;

            if (is_array($match)) {
                if (isset($match['price']) && is_numeric($match['price']) && (float) $match['price'] > 0) {
                    $price = (float) $match['price'];
                }

                if (isset($match['credit_amount']) && is_numeric($match['credit_amount']) && (int) $match['credit_amount'] > 0) {
                    $credits = (int) $match['credit_amount'];
                }

                if (isset($match['backlink_quantity']) && is_numeric($match['backlink_quantity']) && (int) $match['backlink_quantity'] > 0) {
                    $item['size'] = ((int) $match['backlink_quantity']).' backlinks';
                }
            }

            $prefix = $price === null ? '' : (string) ($item['price_prefix'] ?? '');
            $item['package_id'] = is_array($match) ? ($match['id'] ?? null) : null;
            $item['price'] = $price;
            $item['price_label'] = $price === null ? null : $prefix.$this->money($price);
            $item['each_label'] = $this->eachLabel($price, (string) ($item['size'] ?? ''));
            $item['credits'] = $credits;

            return $item;
        }, $items);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function storedRows(): array
    {
        if (! class_exists(Package::class) || ! Schema::hasTable('packages') || ! Schema::hasColumn('packages', 'package_name')) {
            return [];
        }

        try {
            $query = Package::query();

            if (Schema::hasColumn('packages', 'status')) {
                $query->where(function ($rows): void {
                    $rows->where('status', 'active')->orWhereNull('status');
                });
            }

            $rows = [];

            foreach ($query->get() as $package) {
                $name = mb_strtolower(trim((string) $package->package_name));

                if ($name === '') {
                    continue;
                }

                $rows[$name] = [
                    'id' => $package->id,
                    'price' => $package->price ?? null,
                    'credit_amount' => $package->credit_amount ?? null,
                    'backlink_quantity' => $package->backlink_quantity ?? null,
                ];
            }

            return $rows;
        } catch (Throwable) {
            return [];
        }
    }

    private function eachLabel(?float $price, string $size): ?string
    {
        if ($price === null || ! preg_match('/^(\d+)/', $size, $count) || (int) $count[1] < 1) {
            return null;
        }

        return $this->money($price / (int) $count[1]).' each';
    }

    /**
     * @return array<string, string>
     */
    private function categories(NicheListingService $listings): array
    {
        $categories = [];

        foreach ($listings->categoryNames() as $name) {
            $slug = Str::slug($name);

            if ($name !== '' && $slug !== '') {
                $categories[$name] = $slug;
            }
        }

        ksort($categories);

        return $categories;
    }

    private function money(float $amount): string
    {
        $formatted = floor($amount) == $amount
            ? number_format($amount, 0)
            : number_format($amount, 2);

        return '$'.$formatted;
    }
}
