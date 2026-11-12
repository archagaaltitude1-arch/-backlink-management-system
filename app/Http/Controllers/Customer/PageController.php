<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\NicheListingService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class PageController extends Controller
{
    public function home(): View
    {
        set_time_limit(120);

        $listings = app(NicheListingService::class);
        $serviceCards = $this->homeServiceCards();

        return view('customer.pages.home', [
            'publisherCount' => $this->activeCountOrNull('App\\Models\\Website', 'websites'),
            'nicheCount' => $this->activeCountOrNull('App\\Models\\Niche', 'niches'),
            'serviceCards' => $serviceCards['cards'],
            'servicesPublished' => $serviceCards['published'],
            'countryCounts' => $this->websiteCountsByCountry(),
            'nicheTiles' => collect($listings->catalog())->map(fn (string $name) => [
                'name' => $name,
                'slug' => Str::slug($name),
            ])->all(),
        ]);
    }

    public function services(): View
    {
        $services = $this->activeModels('App\\Models\\Service', 'services', 'service_name')
            ->map(function ($service) {
                $name = (string) ($service->service_name ?? '');

                return [
                    'service' => $service,
                    'slug' => Str::slug($name),
                    'profile' => $this->serviceProfile($name),
                ];
            });

        return view('customer.pages.services', [
            'services' => $services,
        ]);
    }

    public function niches(): View
    {
        abort_unless(view()->exists('customer.pages.niches'), 404);

        return view('customer.pages.niches', [
            'niches' => $this->nicheCards(),
        ]);
    }

    private function featuredWebsites(): Collection
    {
        if (! $this->canQuery('App\\Models\\Website', 'websites')) {
            return collect();
        }

        try {
            $query = \App\Models\Website::query();

            if (Schema::hasColumn('websites', 'status')) {
                $query->where('status', 'active');
            }

            if (Schema::hasColumn('websites', 'domain_rating')) {
                $query->orderByDesc('domain_rating');
            }

            return $query->limit(6)->get();
        } catch (Throwable) {
            return collect();
        }
    }

    private function nicheCards(): Collection
    {
        return $this->activeModels('App\\Models\\Niche', 'niches', 'niche_name')
            ->map(function ($niche) {
                $name = (string) ($niche->niche_name ?? '');

                return [
                    'name' => $name,
                    'count' => $name === '' ? 0 : $this->websiteCountForNiche($niche, $name),
                ];
            })
            ->filter(fn (array $card) => $card['name'] !== '')
            ->values();
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

    /**
     * @return array{published: bool, cards: list<array<string, mixed>>}
     */
    private function homeServiceCards(): array
    {
        $models = $this->activeModels('App\\Models\\Service', 'services', 'service_name');
        $matched = [];

        foreach ($models as $service) {
            $key = $this->serviceKey((string) ($service->service_name ?? ''));

            if ($key !== null && ! isset($matched[$key])) {
                $matched[$key] = $service;
            }
        }

        $cards = [];

        foreach ($this->homeServiceCatalog() as $card) {
            $service = $matched[$card['slug']] ?? null;
            $card['price'] = $service ? $this->fromPrice($service->base_price ?? null) : null;
            $cards[] = $card;
        }

        return [
            'published' => $models->isNotEmpty(),
            'cards' => $cards,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function homeServiceCatalog(): array
    {
        $market = fn (array $params): string => url('/marketplace?'.http_build_query($params));

        return [
            [
                'slug' => 'text-link',
                'icon' => 'type',
                'title' => 'Text Link',
                'description' => 'A clean link to your website on a relevant page of a publisher site.',
                'best_for' => 'Budget-friendly authority and brand mentions.',
                'button' => 'View Text Link Prices',
                'featured' => false,
                'options' => [
                    ['label' => 'Text links on Technology sites', 'href' => $market(['service' => 'text-link', 'niche' => 'Technology'])],
                    ['label' => 'Text links on Business sites', 'href' => $market(['service' => 'text-link', 'niche' => 'Business'])],
                    ['label' => 'DR 40+ text links', 'href' => $market(['service' => 'text-link', 'dr_min' => 40])],
                    ['label' => 'DR 60+ text links', 'href' => $market(['service' => 'text-link', 'dr_min' => 60])],
                ],
            ],
            [
                'slug' => 'contextual-link',
                'icon' => 'article',
                'title' => 'Contextual Link',
                'description' => 'Your link placed inside the body of a relevant article, surrounded by related content.',
                'best_for' => 'Strong relevance signals and steady ranking gains.',
                'button' => 'View Contextual Link Prices',
                'featured' => true,
                'options' => [
                    ['label' => 'Contextual links on Health sites', 'href' => $market(['service' => 'contextual-link', 'niche' => 'Health'])],
                    ['label' => 'Contextual links on Finance sites', 'href' => $market(['service' => 'contextual-link', 'niche' => 'Finance'])],
                    ['label' => 'DR 50+ contextual links', 'href' => $market(['service' => 'contextual-link', 'dr_min' => 50])],
                    ['label' => 'DR 70+ contextual links', 'href' => $market(['service' => 'contextual-link', 'dr_min' => 70])],
                ],
            ],
            [
                'slug' => 'guest-post',
                'icon' => 'pen',
                'title' => 'Guest Post',
                'description' => 'A new article on the publisher website with your link inside. We write it, or you send your own.',
                'best_for' => 'Building topical authority at scale.',
                'button' => 'View Guest Post Prices',
                'featured' => false,
                'options' => [
                    ['label' => 'Guest posts by niche', 'href' => url('/niches')],
                    ['label' => 'Premium guest posts DR 60+', 'href' => $market(['service' => 'guest-post', 'dr_min' => 60])],
                    ['label' => 'Guest post packages', 'href' => url('/packages')],
                    ['label' => 'Submit your own article', 'href' => url('/articles/create')],
                ],
            ],
            [
                'slug' => 'niche-edit',
                'icon' => 'edit',
                'title' => 'Niche Edit',
                'description' => 'Your link added to an existing, already-indexed article that has age and traffic.',
                'best_for' => 'Faster results on pages that already rank.',
                'button' => 'View Niche Edit Prices',
                'featured' => false,
                'options' => [
                    ['label' => 'Niche edits on aged content', 'href' => $market(['service' => 'niche-edit'])],
                    ['label' => 'Niche edits on Technology sites', 'href' => $market(['service' => 'niche-edit', 'niche' => 'Technology'])],
                    ['label' => 'DR 50+ niche edits', 'href' => $market(['service' => 'niche-edit', 'dr_min' => 50])],
                    ['label' => 'DR 70+ niche edits', 'href' => $market(['service' => 'niche-edit', 'dr_min' => 70])],
                ],
            ],
        ];
    }

    private function serviceKey(string $name): ?string
    {
        $slug = Str::slug($name);

        foreach (['text-link', 'contextual-link', 'guest-post', 'niche-edit'] as $key) {
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

    private function fromPrice(mixed $value): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;
        $formatted = floor($number) == $number
            ? number_format($number, 0)
            : number_format($number, 2);

        return 'From $'.$formatted;
    }

    /**
     * @return array{available: bool, counts: array<string, int>}
     */
    private function websiteCountsByCountry(): array
    {
        if (! $this->canQuery('App\\Models\\Website', 'websites') || ! Schema::hasColumn('websites', 'country')) {
            // TODO(Archaga): add a country column to websites so this filter works.
            return ['available' => false, 'counts' => []];
        }

        try {
            $query = \App\Models\Website::query();

            if (Schema::hasColumn('websites', 'status')) {
                $query->where('status', 'active');
            }

            $counts = [];

            foreach ($query->get(['country']) as $website) {
                $name = mb_strtolower(trim((string) $website->country));

                if ($name === '') {
                    continue;
                }

                $counts[$name] = ($counts[$name] ?? 0) + 1;
            }

            return ['available' => true, 'counts' => $counts];
        } catch (Throwable) {
            return ['available' => false, 'counts' => []];
        }
    }

    private function activeCountOrNull(string $class, string $table): ?int
    {
        if (! $this->canQuery($class, $table)) {
            return null;
        }

        try {
            $query = $class::query();

            if (Schema::hasColumn($table, 'status')) {
                $query->where('status', 'active');
            }

            return (int) $query->count();
        } catch (Throwable) {
            return null;
        }
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

    private function activeModels(string $class, string $table, ?string $orderColumn = null): Collection
    {
        if (! $this->canQuery($class, $table)) {
            return collect();
        }

        try {
            $query = $class::query();

            if (Schema::hasColumn($table, 'status')) {
                $query->where('status', 'active');
            }

            if ($orderColumn && Schema::hasColumn($table, $orderColumn)) {
                $query->orderBy($orderColumn);
            }

            return $query->get();
        } catch (Throwable) {
            return collect();
        }
    }

    private function canQuery(string $class, string $table): bool
    {
        return class_exists($class) && Schema::hasTable($table);
    }

    /**
     * @return array{icon: string, provides: list<string>, content_by: string, link_type: string, turnaround: string}
     */
    private function serviceProfile(string $name): array
    {
        $slug = Str::slug($name);

        if (str_contains($slug, 'guest')) {
            return [
                'icon' => 'guest',
                'provides' => ['Article topic or your own article', 'Target URL', 'Anchor text'],
                'content_by' => 'You or our team',
                'link_type' => 'Contextual',
                'turnaround' => '5–14 days',
            ];
        }

        if (str_contains($slug, 'contextual') || str_contains($slug, 'niche')) {
            return [
                'icon' => 'niche',
                'provides' => ['Target URL', 'Anchor text'],
                'content_by' => 'Existing publisher article',
                'link_type' => 'Contextual',
                'turnaround' => '3–7 days',
            ];
        }

        return [
            'icon' => 'insertion',
            'provides' => ['Target URL', 'Anchor text'],
            'content_by' => 'No article needed',
            'link_type' => 'Text link',
            'turnaround' => '2–5 days',
        ];
    }
}
