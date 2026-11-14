<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class ServicePageController extends Controller
{
    public function index(): View
    {
        $overview = config('backlink_services.overview', []);
        $cards = [];

        foreach (config('backlink_services.menu', []) as $item) {
            $slug = (string) $item['slug'];
            $service = config('backlink_services.'.$slug, []);
            $cards[] = [
                'slug' => $slug,
                'name' => $item['name'],
                'icon' => $this->iconFor($slug),
                'menu' => $item['line'],
                'turnaround' => $service['turnaround'] ?? '',
                'price' => $this->fromPriceLabel($this->matchedService($slug), $slug),
            ];
        }

        $compareRows = [];

        foreach ($overview['compare_rows'] ?? [] as $row) {
            $cells = [];

            foreach ($cards as $card) {
                $cells[] = ($row['label'] ?? '') === 'Price from'
                    ? $this->listedPrice($card['slug'])
                    : ($row[$card['slug']] ?? '');
            }

            $compareRows[] = [
                'label' => $row['label'] ?? '',
                'cells' => $cells,
            ];
        }

        return view('customer.services.index', [
            'overview' => $overview,
            'cards' => $cards,
            'compareRows' => $compareRows,
        ]);
    }

    public function show(string $slug): View
    {
        $services = $this->services();
        abort_unless(isset($services[$slug]), 404);

        $shared = config('backlink_services.shared', []);
        $page = $services[$slug];
        $page['slug'] = $slug;
        $page['tiers'] = $this->tiersFor($page);
        $page['trust'] = $this->trustItems();
        $page['buttons'] = $shared['buttons'] ?? [];
        $page['reviews_title'] = $shared['reviews_title'] ?? 'What Our Customers Say';
        $page['areas'] = $page['areas'] ?? ($shared['areas'] ?? []);
        $page['niches'] = $this->nicheTiles();
        $page['using_fallback_niches'] = $page['niches'] === [];
        if ($page['niches'] === []) {
            $page['niches'] = collect($shared['niches'] ?? [])
                ->map(fn (string $name) => ['name' => $name, 'count' => null])
                ->all();
        }
        $page['start_href'] = auth()->check()
            ? url('/marketplace?service='.$slug)
            : ( \Illuminate\Support\Facades\Route::has('auth.register') ? route('auth.register') : url('/register') );
        $page['faq_schema'] = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $faq) => [
                '@type' => 'Question',
                'name' => $faq[0],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq[1],
                ],
            ], $page['faqs']),
        ];

        return view('customer.services.show', [
            'page' => $page,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function services(): array
    {
        $services = [];

        foreach (config('backlink_services.menu', []) as $item) {
            $slug = (string) ($item['slug'] ?? '');
            $page = config('backlink_services.'.$slug);

            if (is_array($page)) {
                $services[$slug] = $page;
            }
        }

        return $services;
    }

    /**
     * @param  array<string, mixed>  $page
     * @return list<array<string, mixed>>
     */
    private function tiersFor(array $page): array
    {
        $tiers = [];

        foreach ($page['tiers'] ?? [] as $tier) {
            $price = $this->tierPrice((string) $page['name'], (string) $tier['name']);
            $tier['price'] = $price === '[PRICE]' ? ($tier['price'] ?? '[PRICE]') : $price;
            $tiers[] = $tier;
        }

        return $tiers;
    }

    /**
     * @return list<string>
     */
    private function trustItems(): array
    {
        $label = $this->publisherLabel();

        return array_map(
            fn (string $item) => str_replace('[N]+', $label, $item),
            config('backlink_services.shared.trust', [])
        );
    }

    private function listedPrice(string $slug): string
    {
        $service = $this->matchedService($slug);

        if ($service && is_numeric($service->base_price ?? null) && (float) $service->base_price > 0) {
            return $this->money($service->base_price);
        }

        return $this->lowestTierPrice($slug) ?? '[PRICE]';
    }

    private function iconFor(string $slug): string
    {
        return match ($slug) {
            'guest-post' => 'pen',
            'niche-edit' => 'edit',
            'contextual-link' => 'article',
            'text-link' => 'type',
            default => 'link',
        };
    }

    private function publisherLabel(): string
    {
        $count = $this->activeCount('App\\Models\\Website', 'websites');

        return $count > 0 ? number_format($count).'+' : '[N]+';
    }

    /**
     * @return list<array{name: string, count: int}>
     */
    private function nicheTiles(): array
    {
        if (! $this->canQuery('App\\Models\\Niche', 'niches')) {
            return [];
        }

        try {
            $query = \App\Models\Niche::query();

            if (Schema::hasColumn('niches', 'status')) {
                $query->where('status', 'active');
            }

            if (Schema::hasColumn('niches', 'niche_name')) {
                $query->orderBy('niche_name');
            }

            return $query->get()->map(function ($niche) {
                $name = (string) ($niche->niche_name ?? '');

                return [
                    'name' => $name,
                    'count' => $name === '' ? 0 : $this->websiteCountForNiche($niche, $name),
                ];
            })->filter(fn (array $tile) => $tile['name'] !== '')->values()->all();
        } catch (Throwable) {
            return [];
        }
    }

    private function websiteCountForNiche(object $niche, string $name): int
    {
        if (! $this->canQuery('App\\Models\\Website', 'websites')) {
            return 0;
        }

        try {
            $query = \App\Models\Website::query();

            if (Schema::hasColumn('websites', 'status')) {
                $query->where('status', 'active');
            }

            $query->where(function ($websites) use ($niche, $name) {
                $matched = false;

                if (Schema::hasColumn('websites', 'niche')) {
                    $websites->where('niche', $name);
                    $matched = true;
                }

                if (Schema::hasTable('website_niches') && Schema::hasColumn('website_niches', 'niche_id') && isset($niche->id)) {
                    $ids = \Illuminate\Support\Facades\DB::table('website_niches')
                        ->where('niche_id', $niche->id)
                        ->pluck('website_id');

                    $matched
                        ? $websites->orWhereIn('id', $ids)
                        : $websites->whereIn('id', $ids);
                    $matched = true;
                }

                if (! $matched) {
                    $websites->whereRaw('1 = 0');
                }
            });

            return (int) $query->count();
        } catch (Throwable) {
            return 0;
        }
    }

    private function matchedService(string $slug): ?object
    {
        if (! $this->canQuery('App\\Models\\Service', 'services')) {
            return null;
        }

        try {
            $query = \App\Models\Service::query();

            if (Schema::hasColumn('services', 'status')) {
                $query->where('status', 'active');
            }

            foreach ($query->get() as $service) {
                if ($this->serviceKey((string) ($service->service_name ?? '')) === $slug) {
                    return $service;
                }
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    private function tierPrice(string $serviceName, string $tierName): string
    {
        if (! $this->canQuery('App\\Models\\Package', 'packages') || ! Schema::hasColumn('packages', 'package_name')) {
            return '[PRICE]';
        }

        $names = array_unique([
            $tierName,
            $serviceName.' '.$tierName,
            $serviceName.' - '.$tierName,
        ]);

        try {
            $query = \App\Models\Package::query()->whereIn('package_name', $names);

            if (Schema::hasColumn('packages', 'status')) {
                $query->where('status', 'active');
            }

            $package = $query->first();

            if ($package && is_numeric($package->price ?? null)) {
                return $this->money($package->price);
            }
        } catch (Throwable) {
            return '[PRICE]';
        }

        return '[PRICE]';
    }

    private function fromPriceLabel(?object $service, string $slug): string
    {
        if ($service && is_numeric($service->base_price ?? null) && (float) $service->base_price > 0) {
            return 'From '.$this->money($service->base_price);
        }

        $lowest = $this->lowestTierPrice($slug);

        return $lowest === null ? 'From [PRICE]' : 'From '.$lowest;
    }

    private function lowestTierPrice(string $slug): ?string
    {
        $tiers = config('backlink_services.'.$slug.'.tiers', []);
        $lowest = null;

        foreach (is_array($tiers) ? $tiers : [] as $tier) {
            $price = (string) ($tier['price'] ?? '');

            if (! preg_match('/\$([0-9,]+(?:\.[0-9]+)?)/', $price, $match)) {
                continue;
            }

            $value = (float) str_replace(',', '', $match[1]);

            if ($lowest === null || $value < $lowest) {
                $lowest = $value;
            }
        }

        return $lowest === null ? null : $this->money($lowest);
    }

    private function money(mixed $value): string
    {
        $number = (float) $value;
        $formatted = floor($number) == $number
            ? number_format($number, 0)
            : number_format($number, 2);

        return '$'.$formatted;
    }

    private function serviceKey(string $name): ?string
    {
        $slug = Str::slug($name);

        foreach (array_keys($this->services()) as $key) {
            if ($slug === $key) {
                return $key;
            }
        }

        if (str_contains($slug, 'guest')) {
            return 'guest-post';
        }

        if (str_contains($slug, 'contextual')) {
            return 'contextual-link';
        }

        if (str_contains($slug, 'niche')) {
            return 'niche-edit';
        }

        if (str_contains($slug, 'text')) {
            return 'text-link';
        }

        return null;
    }

    private function activeCount(string $class, string $table): int
    {
        if (! $this->canQuery($class, $table)) {
            return 0;
        }

        try {
            $query = $class::query();

            if (Schema::hasColumn($table, 'status')) {
                $query->where('status', 'active');
            }

            return (int) $query->count();
        } catch (Throwable) {
            return 0;
        }
    }

    private function canQuery(string $class, string $table): bool
    {
        return class_exists($class) && Schema::hasTable($table);
    }
}
