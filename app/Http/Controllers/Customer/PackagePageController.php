<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Services\Customer\NicheListingService;
use App\Support\PublicPackages;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class PackagePageController extends Controller
{
    public function index(): View
    {
        return view('customer.packages.index', [
            'packages' => $this->withStoredPrice(PublicPackages::items()),
            'guestPostPrices' => PublicPackages::guestPostPrices(),
        ]);
    }

    public function show(string $slug): View
    {
        $package = PublicPackages::find($slug);
        abort_if($package === null, 404);

        $priced = $this->withStoredPrice([$package])[0];
        $priced['story'] = PublicPackages::story($slug);

        return view('customer.packages.show', [
            'package' => $priced,
            'packages' => $this->withStoredPrice(PublicPackages::items()),
            'guestPostPrices' => PublicPackages::guestPostPrices(),
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
        $quantity = min(10, max(1, (int) $request->query('quantity', 1)));
        $limit = PublicPackages::websiteLimit($story, $quantity);
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
            'orderType' => trim((string) $request->query('order_type', '')),
            'rows' => collect($catalog['rows'])
                ->sortByDesc(fn (array $row) => $row['dr'] ?? -1)
                ->values()
                ->all(),
            'demo' => $catalog['demo'],
            'categories' => $this->categories($listings),
            'lead' => $lead,
        ]);
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
