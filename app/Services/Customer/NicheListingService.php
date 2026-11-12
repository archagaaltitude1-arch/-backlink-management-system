<?php

namespace App\Services\Customer;

use App\Support\DemoNicheData;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class NicheListingService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>|null
     */
    public function forNiche(string $slug, array $filters = []): ?array
    {
        $name = $this->nameForSlug($slug);

        if ($name === null) {
            return null;
        }

        $demo = true;
        $rows = DemoNicheData::rowsFor($name);

        if (! config('marketplace.demo')) {
            $live = $this->liveRows($name);

            if ($live !== []) {
                $demo = false;
                $rows = $live;
            }
        }

        $all = collect($rows);
        $filtered = $this->sortRows($this->applyFilters($all, $filters), (string) ($filters['sort'] ?? 'traffic_desc'));
        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = (int) ($filters['per_page'] ?? 20);
        $perPage = in_array($perPage, [20, 25, 50, 100], true) ? $perPage : 20;
        $slice = $filtered->slice(($page - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $slice,
            $filtered->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'pageName' => 'page']
        );
        $paginator->withQueryString();

        $related = $this->related($name, 6);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'demo' => $demo,
            'websites' => $paginator,
            'total' => $filtered->count(),
            'languages' => $all->pluck('language')->filter(fn ($language) => $language && $language !== '—')->unique()->sort()->values()->all(),
            'countries' => $all->flatMap(fn (array $row) => collect($row['top_countries'])->pluck(0))->unique()->sort()->values()->all(),
            'related' => $related,
            'intro_related' => array_slice($related, 0, 3),
            'custom_intro' => DemoNicheData::intros()[Str::slug($name)] ?? null,
        ];
    }

    /**
     * @return list<string>
     */
    public function catalog(): array
    {
        return $this->knownNames();
    }

    /**
     * Every niche name the public site can open, including the full sample list.
     *
     * @return list<string>
     */
    public function categoryNames(): array
    {
        return $this->knownNames();
    }

    public function countFor(string $name): int
    {
        if (! config('marketplace.demo')) {
            $live = $this->liveRows($name);

            if ($live !== []) {
                return count($live);
            }
        }

        return count(DemoNicheData::rowsFor($name));
    }

    /**
     * Unique publisher rows for the public checkers.
     * Live rows are used when demo mode is off and active websites exist.
     *
     * @return array{demo: bool, rows: list<array<string, mixed>>}
     */
    public function catalogRows(): array
    {
        if (! config('marketplace.demo')) {
            $live = $this->allLiveRows();

            if ($live !== []) {
                return ['demo' => false, 'rows' => $live];
            }
        }

        $rows = [];
        $seen = [];

        foreach (DemoNicheData::rowsFor('Technology') as $row) {
            $key = mb_strtolower((string) $row['domain']);

            if ($key === '' || isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $rows[] = $row;
        }

        return ['demo' => true, 'rows' => $rows];
    }

    private function nameForSlug(string $slug): ?string
    {
        foreach ($this->knownNames() as $name) {
            if (Str::slug($name) === $slug) {
                return $name;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function knownNames(): array
    {
        $names = $this->databaseNames();

        if (config('marketplace.demo') || $names === []) {
            $names = array_values(array_unique(array_merge($names, DemoNicheData::names())));
        }

        return $names;
    }

    /**
     * @return list<string>
     */
    private function databaseNames(): array
    {
        if (! class_exists(\App\Models\Niche::class) || ! Schema::hasTable('niches')) {
            return [];
        }

        try {
            $query = \App\Models\Niche::query();

            if (Schema::hasColumn('niches', 'status')) {
                $query->where('status', 'active');
            }

            return $query->pluck('niche_name')
                ->filter(fn ($name) => is_string($name) && $name !== '')
                ->values()
                ->all();
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function liveRows(string $name): array
    {
        if (! class_exists(\App\Models\Website::class) || ! Schema::hasTable('websites')) {
            return [];
        }

        $hasNicheColumn = Schema::hasColumn('websites', 'niche');
        $hasRelation = method_exists(\App\Models\Website::class, 'niches');

        if (! $hasNicheColumn && ! $hasRelation) {
            return [];
        }

        try {
            $query = \App\Models\Website::query();

            if (Schema::hasColumn('websites', 'status')) {
                $query->where('status', 'active');
            }

            $query->where(function ($websites) use ($name, $hasNicheColumn, $hasRelation) {
                if ($hasNicheColumn) {
                    $websites->where('niche', $name);
                }

                if ($hasRelation) {
                    $hasNicheColumn
                        ? $websites->orWhereHas('niches', fn ($niches) => $niches->where('niche_name', $name))
                        : $websites->whereHas('niches', fn ($niches) => $niches->where('niche_name', $name));
                }
            });

            if (method_exists(\App\Models\Website::class, 'seoMetric')) {
                $query->with('seoMetric');
            }

            return $query->get()->map(fn ($website) => $this->mapWebsite($website, $name))->all();
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function allLiveRows(): array
    {
        if (! class_exists(\App\Models\Website::class) || ! Schema::hasTable('websites')) {
            return [];
        }

        try {
            $query = \App\Models\Website::query();

            if (Schema::hasColumn('websites', 'status')) {
                $query->where('status', 'active');
            }

            if (method_exists(\App\Models\Website::class, 'seoMetric')) {
                $query->with('seoMetric');
            }

            if (method_exists(\App\Models\Website::class, 'niches')) {
                $query->with('niches');
            }

            $rows = [];
            $seen = [];

            foreach ($query->get() as $website) {
                $name = is_string($website->niche ?? null) && $website->niche !== '' ? $website->niche : 'Publisher';
                $row = $this->mapWebsite($website, $name);
                $key = mb_strtolower((string) $row['domain']);

                if ($key === '' || isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $rows[] = $row;
            }

            return $rows;
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * language, keywords and top_countries are not publisher columns yet.
     * TODO(Archaga): add language, country, top_countries (json), organic_keywords,
     * authority_score, semrush_traffic, sports_allowed, pharmacy_allowed, crypto_allowed,
     * backlinks, link_type, link_validity, google_news, sponsored, foreign_language columns to websites.
     *
     * @return array<string, mixed>
     */
    private function mapWebsite(object $website, string $name): array
    {
        $niches = [];

        if (isset($website->niche) && is_string($website->niche) && $website->niche !== '') {
            $niches[] = $website->niche;
        }

        if (method_exists($website, 'relationLoaded') && $website->relationLoaded('niches')) {
            foreach ($website->niches as $niche) {
                $label = (string) ($niche->niche_name ?? '');

                if ($label !== '') {
                    $niches[] = $label;
                }
            }
        }

        $niches = array_values(array_unique($niches));
        usort($niches, fn (string $left, string $right) => ($left === $name ? 0 : 1) <=> ($right === $name ? 0 : 1));

        if ($niches === []) {
            $niches = [$name];
        }

        $language = '';

        if (Schema::hasColumn('websites', 'language') && filled($website->language ?? null)) {
            $language = (string) $website->language;
        }

        $keywords = null;

        if (Schema::hasColumn('websites', 'organic_keywords') && is_numeric($website->organic_keywords ?? null)) {
            $keywords = (int) $website->organic_keywords;
        }

        $countries = [];

        if (Schema::hasColumn('websites', 'top_countries') && filled($website->top_countries ?? null)) {
            $decoded = is_array($website->top_countries) ? $website->top_countries : json_decode((string) $website->top_countries, true);
            $countries = is_array($decoded) ? $decoded : [];
        }

        return [
            'id' => $website->id ?? null,
            'domain' => (string) ($website->domain ?? $website->website_name ?? ''),
            'niches' => $niches,
            'language' => $language,
            'dr' => is_numeric($website->domain_rating ?? null) ? (int) $website->domain_rating : null,
            'da' => is_numeric($website->domain_authority ?? null) ? (int) $website->domain_authority : null,
            'as' => $this->intColumn($website, 'authority_score'),
            'spam' => is_numeric($website->spam_score ?? null) ? (int) $website->spam_score : null,
            'traffic' => is_numeric($website->monthly_traffic ?? null) ? (int) $website->monthly_traffic : null,
            'semrush_traffic' => $this->intColumn($website, 'semrush_traffic'),
            'keywords' => $keywords,
            'top_countries' => $countries,
            'referring_domains' => is_numeric($website->referring_domains ?? null) ? (int) $website->referring_domains : null,
            'backlinks' => $this->intColumn($website, 'backlinks'),
            'price' => is_numeric($website->price ?? null) ? (float) $website->price : null,
            'sports_allowed' => $this->boolColumn($website, 'sports_allowed'),
            'pharmacy_allowed' => $this->boolColumn($website, 'pharmacy_allowed'),
            'crypto_allowed' => $this->boolColumn($website, 'crypto_allowed'),
            'link_type' => $this->stringColumn($website, 'link_type'),
            'link_validity' => $this->stringColumn($website, 'link_validity'),
            'google_news' => $this->boolColumn($website, 'google_news'),
            'sponsored' => $this->boolColumn($website, 'sponsored'),
            'foreign_language' => $this->boolColumn($website, 'foreign_language'),
            'credits' => is_numeric($website->credit_cost ?? null) ? (int) $website->credit_cost : null,
            'added_at' => $website->created_at ?? now(),
            'demo' => false,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function applyFilters(Collection $rows, array $filters): Collection
    {
        $search = mb_strtolower(trim((string) ($filters['search'] ?? '')));
        $bulk = preg_split('/[\s,]+/', mb_strtolower((string) ($filters['bulk'] ?? ''))) ?: [];
        $bulk = array_values(array_filter($bulk, fn (string $term) => $term !== ''));
        $drMin = $this->number($filters['dr_min'] ?? null);
        $daMin = $this->number($filters['da_min'] ?? null);
        $asMin = $this->number($filters['as_min'] ?? null);
        $trafficMin = $this->number($filters['traffic_min'] ?? null);
        $semrushMin = $this->number($filters['semrush_min'] ?? null);
        $priceMax = $this->number($filters['price_max'] ?? null);
        $backlinksMin = $this->number($filters['backlinks_min'] ?? null);
        $language = trim((string) ($filters['language'] ?? ''));
        $country = trim((string) ($filters['country'] ?? ''));
        $linkType = trim((string) ($filters['link_type'] ?? ''));
        $linkValidity = trim((string) ($filters['link_validity'] ?? ''));

        return $rows->filter(function (array $row) use ($search, $bulk, $drMin, $daMin, $asMin, $trafficMin, $semrushMin, $priceMax, $backlinksMin, $language, $country, $linkType, $linkValidity, $filters) {
            $domain = mb_strtolower((string) $row['domain']);
            $nicheText = mb_strtolower(implode(' ', $row['niches'] ?? []));

            if ($search !== '' && ! str_contains($domain.' '.$nicheText, $search)) {
                return false;
            }

            if ($bulk !== []) {
                $matched = false;

                foreach ($bulk as $term) {
                    if (str_contains($domain, $term)) {
                        $matched = true;
                        break;
                    }
                }

                if (! $matched) {
                    return false;
                }
            }

            if ($drMin !== null && (int) ($row['dr'] ?? -1) < $drMin) {
                return false;
            }

            if ($daMin !== null && (int) ($row['da'] ?? -1) < $daMin) {
                return false;
            }

            if ($asMin !== null && (int) ($row['as'] ?? -1) < $asMin) {
                return false;
            }

            if ($trafficMin !== null && (int) ($row['traffic'] ?? -1) < $trafficMin) {
                return false;
            }

            if ($semrushMin !== null && (int) ($row['semrush_traffic'] ?? -1) < $semrushMin) {
                return false;
            }

            if ($priceMax !== null && (float) ($row['price'] ?? INF) > $priceMax) {
                return false;
            }

            if ($backlinksMin !== null && (int) ($row['backlinks'] ?? -1) < $backlinksMin) {
                return false;
            }

            if ($language !== '' && strcasecmp((string) $row['language'], $language) !== 0) {
                return false;
            }

            if ($linkType !== '' && strcasecmp((string) ($row['link_type'] ?? ''), $linkType) !== 0) {
                return false;
            }

            if ($linkValidity !== '' && strcasecmp((string) ($row['link_validity'] ?? ''), $linkValidity) !== 0) {
                return false;
            }

            if ($country !== '') {
                $codes = collect($row['top_countries'])->map(fn ($pair) => (string) ($pair[0] ?? ''))->all();

                if (! in_array(strtoupper($country), array_map('strtoupper', $codes), true)) {
                    return false;
                }
            }

            foreach ([
                'sports' => 'sports_allowed',
                'pharmacy' => 'pharmacy_allowed',
                'crypto' => 'crypto_allowed',
                'google_news' => 'google_news',
                'sponsored' => 'sponsored',
                'foreign_language' => 'foreign_language',
            ] as $key => $field) {
                $choice = strtolower(trim((string) ($filters[$key] ?? '')));

                if ($choice === '') {
                    continue;
                }

                $value = $row[$field] ?? null;

                if (! is_bool($value) || $value !== ($choice === 'yes')) {
                    return false;
                }
            }

            return true;
        })->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    private function sortRows(Collection $rows, string $sort): Collection
    {
        $sorted = match ($sort) {
            'dr_desc' => $rows->sortByDesc(fn (array $row) => $row['dr'] ?? -1),
            'da_desc' => $rows->sortByDesc(fn (array $row) => $row['da'] ?? -1),
            'as_desc' => $rows->sortByDesc(fn (array $row) => $row['as'] ?? -1),
            'semrush_desc' => $rows->sortByDesc(fn (array $row) => $row['semrush_traffic'] ?? -1),
            'price_asc' => $rows->sortBy(fn (array $row) => $row['price'] ?? INF),
            'price_desc' => $rows->sortByDesc(fn (array $row) => $row['price'] ?? -1),
            'newest' => $rows->sortByDesc(fn (array $row) => $row['added_at'] instanceof \DateTimeInterface ? $row['added_at']->getTimestamp() : 0),
            default => $rows->sortByDesc(fn (array $row) => $row['traffic'] ?? -1),
        };

        return $sorted->values();
    }

    /**
     * @return list<array{name: string, slug: string}>
     */
    private function related(string $name, int $limit): array
    {
        $names = $this->knownNames();
        $others = array_values(array_filter($names, fn (string $item) => $item !== $name));
        $start = array_search($name, $names, true);
        $ordered = [];

        if ($start !== false) {
            $count = count($names);

            for ($step = 1; $step < $count && count($ordered) < $limit; $step++) {
                $candidate = $names[($start + $step) % $count];

                if ($candidate !== $name) {
                    $ordered[] = $candidate;
                }
            }
        } else {
            $ordered = array_slice($others, 0, $limit);
        }

        return array_map(fn (string $item) => ['name' => $item, 'slug' => Str::slug($item)], $ordered);
    }

    private function intColumn(object $website, string $column): ?int
    {
        if (! Schema::hasColumn('websites', $column) || ! is_numeric($website->{$column} ?? null)) {
            return null;
        }

        return (int) $website->{$column};
    }

    private function stringColumn(object $website, string $column): ?string
    {
        if (! Schema::hasColumn('websites', $column) || ! filled($website->{$column} ?? null)) {
            return null;
        }

        return (string) $website->{$column};
    }

    private function boolColumn(object $website, string $column): ?bool
    {
        if (! Schema::hasColumn('websites', $column) || ! isset($website->{$column}) || $website->{$column} === '' || $website->{$column} === null) {
            return null;
        }

        $value = $website->{$column};

        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower((string) $value);

        return match ($normalized) {
            '1', 'true', 'yes' => true,
            '0', 'false', 'no' => false,
            default => null,
        };
    }

    private function number(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }
}
