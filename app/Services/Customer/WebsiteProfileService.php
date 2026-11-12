<?php

namespace App\Services\Customer;

use App\Support\DemoNicheData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class WebsiteProfileService
{
    /**
     * @param  array<string, mixed>|object  $site
     * @return array<string, mixed>
     */
    public function build(array|object $site): array
    {
        $demo = is_array($site) ? (bool) ($site['demo'] ?? false) : false;
        $domain = $this->domain($site);
        $categories = $this->categories($site);
        $primary = $categories[0] ?? '';
        $language = $this->text($site, 'language');
        $dr = $this->intValue($site, 'dr', 'domain_rating');
        $da = $this->intValue($site, 'da', 'domain_authority');
        $pa = $this->intValue($site, 'pa', 'page_authority');
        $tf = $this->intValue($site, 'tf', 'trust_flow');
        $cf = $this->intValue($site, 'cf', 'citation_flow');
        $spam = $this->intValue($site, 'spam', 'spam_score');
        $traffic = $this->intValue($site, 'traffic', 'monthly_traffic');
        $keywords = $this->intValue($site, 'keywords', 'organic_keywords');
        $backlinks = $this->intValue($site, 'backlinks', 'backlinks');
        $referring = $this->intValue($site, 'referring_domains', 'referring_domains');
        $price = $this->floatValue($site, 'price', 'price');
        $credits = $this->intValue($site, 'credits', 'credit_cost');
        $countries = $this->countries($site, $demo);
        $series = $demo
            ? ($traffic === null ? [] : $this->demoSeries($domain, $traffic))
            : $this->realSeries($site);
        $description = $this->text($site, 'description');
        $about = $this->about($domain, $language, $categories, $traffic, $countries, $dr, $da, $spam, $description);
        $peers = $this->peers($site, $demo, $primary, $domain, $dr);

        return [
            'demo' => $demo,
            'domain' => $domain,
            'name' => $domain,
            'url' => $this->url($site, $domain),
            'key' => $demo ? $domain : (string) ($this->raw($site, 'id') ?? $domain),
            'website_id' => is_numeric($this->raw($site, 'id') ?? null) ? (int) $this->raw($site, 'id') : null,
            'categories' => $categories,
            'primary' => $primary,
            'primary_slug' => $primary !== '' ? Str::slug($primary) : null,
            'language' => $language,
            'dr' => $dr,
            'da' => $da,
            'pa' => $pa,
            'tf' => $tf,
            'cf' => $cf,
            'spam' => $spam,
            'traffic' => $traffic,
            'keywords' => $keywords,
            'backlinks' => $backlinks,
            'referring_domains' => $referring,
            'price' => $price,
            'credits' => $credits,
            'link_type' => 'Dofollow',
            'turnaround' => '3–14 days',
            'updated_at' => $this->updatedAt($site),
            'about_p1' => $about[0],
            'about_p2' => $about[1],
            'traffic_series' => $series,
            'traffic_change' => $this->change($series),
            'has_traffic_history' => $series !== [],
            'countries_ready' => $countries !== null,
            'top_countries' => $countries ?? [],
            'similar' => $peers['similar'],
            'popular_in_niche' => $peers['popular'],
        ];
    }

    /**
     * @param  array<string, mixed>|object  $site
     */
    private function domain(array|object $site): string
    {
        $domain = $this->text($site, 'domain');

        if ($domain !== null) {
            return $domain;
        }

        return (string) ($this->text($site, 'website_name') ?? 'Website');
    }

    /**
     * @param  array<string, mixed>|object  $site
     */
    private function url(array|object $site, string $domain): string
    {
        $url = $this->text($site, 'url') ?? $this->text($site, 'website_url');

        if ($url !== null) {
            return $url;
        }

        return 'https://'.$domain;
    }

    /**
     * @param  array<string, mixed>|object  $site
     * @return list<string>
     */
    private function categories(array|object $site): array
    {
        $names = [];

        if (is_array($site)) {
            foreach ($site['niches'] ?? [] as $name) {
                if (is_string($name) && $name !== '') {
                    $names[] = $name;
                }
            }
        } else {
            $primary = $this->text($site, 'niche');

            if ($primary !== null) {
                $names[] = $primary;
            }

            if (method_exists($site, 'relationLoaded') && ! $site->relationLoaded('niches') && method_exists($site, 'niches')) {
                $site->load('niches');
            }

            if (isset($site->niches)) {
                foreach ($site->niches as $niche) {
                    $label = (string) ($niche->niche_name ?? '');

                    if ($label !== '') {
                        $names[] = $label;
                    }
                }
            }
        }

        $names = array_values(array_unique($names));

        return $names === [] ? [] : $names;
    }

    /**
     * @param  array<string, mixed>|object  $site
     * @return list<array{code: string, name: string, percent: float}>|null
     */
    private function countries(array|object $site, bool $demo): ?array
    {
        if ($demo) {
            return $this->normalizeCountries(is_array($site) ? ($site['top_countries'] ?? []) : []);
        }

        if (! is_object($site) || ! Schema::hasTable('websites') || ! Schema::hasColumn('websites', 'top_countries')) {
            return null;
        }

        $raw = $site->top_countries ?? null;

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($raw) || $raw === []) {
            return null;
        }

        return $this->normalizeCountries($raw);
    }

    /**
     * @param  array<int, mixed>  $raw
     * @return list<array{code: string, name: string, percent: float}>
     */
    private function normalizeCountries(array $raw): array
    {
        $rows = [];

        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }

            if (array_is_list($row)) {
                $code = strtoupper((string) ($row[0] ?? ''));
                $percent = (float) ($row[1] ?? 0);
            } else {
                $code = strtoupper((string) ($row['code'] ?? $row['country'] ?? ''));
                $percent = (float) ($row['percent'] ?? $row['share'] ?? 0);
            }

            if ($code === '') {
                continue;
            }

            $rows[] = [
                'code' => $code,
                'name' => $this->countryName($code),
                'percent' => $percent,
            ];

            if (count($rows) === 5) {
                break;
            }
        }

        $sum = array_sum(array_column($rows, 'percent'));

        if ($rows !== [] && $sum > 0 && $sum < 100) {
            $rows[] = [
                'code' => '',
                'name' => 'Other',
                'percent' => round(100 - $sum, 1),
            ];
        }

        return $rows;
    }

    private function countryName(string $code): string
    {
        return [
            'US' => 'United States',
            'UK' => 'United Kingdom',
            'GB' => 'United Kingdom',
            'DE' => 'Germany',
            'NL' => 'Netherlands',
            'BR' => 'Brazil',
            'ES' => 'Spain',
            'AR' => 'Argentina',
            'KE' => 'Kenya',
            'IN' => 'India',
            'AU' => 'Australia',
            'IE' => 'Ireland',
            'ID' => 'Indonesia',
            'HU' => 'Hungary',
            'CH' => 'Switzerland',
            'RO' => 'Romania',
            'PT' => 'Portugal',
            'MX' => 'Mexico',
            'UY' => 'Uruguay',
            'JP' => 'Japan',
            'IT' => 'Italy',
            'PK' => 'Pakistan',
            'NP' => 'Nepal',
            'CO' => 'Colombia',
            'TW' => 'Taiwan',
            'NG' => 'Nigeria',
            'PL' => 'Poland',
            'KW' => 'Kuwait',
            'BE' => 'Belgium',
            'BD' => 'Bangladesh',
            'FR' => 'France',
            'CA' => 'Canada',
            'ZA' => 'South Africa',
            'SE' => 'Sweden',
            'AE' => 'United Arab Emirates',
        ][$code] ?? $code;
    }

    /**
     * @return list<array{label: string, short: string, value: int}>
     */
    private function demoSeries(string $domain, int $traffic): array
    {
        $end = Carbon::now()->startOfMonth()->subMonth();
        $points = [];

        for ($index = 0; $index < 12; $index++) {
            $month = $end->copy()->subMonths(11 - $index);
            $progress = $index / 11;
            $base = $traffic * (0.7 + (0.3 * $progress));
            $hash = abs((int) crc32($domain.'|'.$index));
            $wobble = (($hash % 10000) / 9999 * 0.30) - 0.15;
            $value = $index === 11 ? $traffic : max(0, (int) round($base * (1 + $wobble)));

            $points[] = [
                'label' => $month->format('M Y'),
                'short' => $month->format('M'),
                'value' => $value,
            ];
        }

        return $points;
    }

    /**
     * @param  array<string, mixed>|object  $site
     * @return list<array{label: string, short: string, value: int}>
     */
    private function realSeries(array|object $site): array
    {
        // TODO(Archaga): monthly traffic history table.
        if (! Schema::hasTable('website_traffic_history')) {
            return [];
        }

        $query = DB::table('website_traffic_history');
        $id = $this->raw($site, 'id');

        if (is_numeric($id) && Schema::hasColumn('website_traffic_history', 'website_id')) {
            $query->where('website_id', $id);
        }

        $order = 'id';

        foreach (['recorded_at', 'month', 'created_at'] as $column) {
            if (Schema::hasColumn('website_traffic_history', $column)) {
                $order = $column;
                break;
            }
        }

        $valueColumn = null;

        foreach (['traffic', 'visits', 'monthly_traffic'] as $column) {
            if (Schema::hasColumn('website_traffic_history', $column)) {
                $valueColumn = $column;
                break;
            }
        }

        if ($valueColumn === null) {
            return [];
        }

        $rows = $query->orderByDesc($order)->limit(12)->get()->reverse()->values();
        $points = [];

        foreach ($rows as $row) {
            $when = $row->{$order} ?? null;
            $month = $when ? Carbon::parse($when) : Carbon::now();
            $points[] = [
                'label' => $month->format('M Y'),
                'short' => $month->format('M'),
                'value' => max(0, (int) ($row->{$valueColumn} ?? 0)),
            ];
        }

        return $points;
    }

    /**
     * @param  list<array{label: string, short: string, value: int}>  $series
     */
    private function change(array $series): ?int
    {
        if (count($series) < 2 || $series[0]['value'] <= 0) {
            return null;
        }

        $last = $series[array_key_last($series)]['value'];

        return (int) round((($last - $series[0]['value']) / $series[0]['value']) * 100);
    }

    /**
     * @param  list<string>  $categories
     * @param  list<array{code: string, name: string, percent: float}>|null  $countries
     * @return array{0: string, 1: string}
     */
    private function about(string $domain, ?string $language, array $categories, ?int $traffic, ?array $countries, ?int $dr, ?int $da, ?int $spam, ?string $description): array
    {
        $primary = $categories[0] ?? null;
        $others = array_slice($categories, 1);
        $named = array_values(array_filter($countries ?? [], fn (array $country) => $country['name'] !== 'Other'));

        if ($description !== null && $description !== '') {
            $first = $description;
        } else {
            $opening = $domain.' is a';

            if ($language !== null && $language !== '' && $language !== '—') {
                $opening .= ' '.$language.'-language';
            }

            $opening .= ' website';

            if ($primary !== null) {
                $opening .= ' covering '.$primary;

                if ($others !== []) {
                    $opening .= ', with articles on '.$this->joinList($others);
                }
            }

            $opening .= '.';
            $parts = [$opening];

            if ($traffic !== null) {
                $reach = 'It reaches around '.DemoNicheData::compact($traffic).' visitors a month';

                if (isset($named[0])) {
                    $reach .= ', mostly from '.$named[0]['name'];

                    if (isset($named[1])) {
                        $reach .= ' and '.$named[1]['name'];
                    }

                    $reach .= ', which makes it a good fit for brands targeting that audience';
                }

                $parts[] = $reach.'.';
            }

            $first = implode(' ', $parts);
        }

        $authority = $this->authorityPhrase($dr);
        $second = '';

        if ($dr !== null && $da !== null && $authority !== null) {
            $second = 'With a Domain Rating of '.$dr.' and Domain Authority of '.$da.', '.$domain.' '.$authority.'. ';
        } elseif ($dr !== null && $authority !== null) {
            $second = 'With a Domain Rating of '.$dr.', '.$domain.' '.$authority.'. ';
        } elseif ($da !== null && $authority !== null) {
            $second = 'With a Domain Authority of '.$da.', '.$domain.' '.$authority.'. ';
        } elseif ($authority !== null) {
            $second = $domain.' '.$authority.'. ';
        }

        $second .= 'A placement here '.$this->spamPhrase($spam).', and you can order a guest post, contextual link, niche edit or text link. Links are usually live within 3–14 days and are verified before we send your report.';

        return [$first, $second];
    }

    private function authorityPhrase(?int $dr): ?string
    {
        if ($dr === null) {
            return null;
        }

        if ($dr >= 60) {
            return 'is one of the stronger sites in its niche';
        }

        if ($dr >= 40) {
            return 'offers solid, trusted authority';
        }

        if ($dr >= 20) {
            return 'is a growing site with steady authority';
        }

        return 'is an affordable starter option';
    }

    private function spamPhrase(?int $spam): string
    {
        if ($spam === null) {
            return 'has no spam score recorded yet';
        }

        if ($spam <= 3) {
            return 'carries a low spam score';
        }

        if ($spam <= 7) {
            return 'has a moderate spam score worth reviewing';
        }

        return 'has a higher spam score, so check it before you buy';
    }

    /**
     * @param  list<string>  $items
     */
    private function joinList(array $items): string
    {
        if (count($items) < 2) {
            return $items[0] ?? '';
        }

        $last = array_pop($items);

        return implode(', ', $items).' and '.$last;
    }

    /**
     * @param  array<string, mixed>|object  $site
     * @return array{similar: list<array<string, mixed>>, popular: list<array{domain: string, key: string}>}
     */
    private function peers(array|object $site, bool $demo, string $primary, string $domain, ?int $dr): array
    {
        $rows = $demo
            ? DemoNicheData::rowsFor($primary !== '' ? $primary : 'Agriculture & Farming')
            : $this->livePeers();

        $others = array_values(array_filter($rows, fn (array $row) => strcasecmp((string) $row['domain'], $domain) !== 0));

        $same = array_values(array_filter($others, function (array $row) use ($primary) {
            if ($primary === '') {
                return false;
            }

            return in_array($primary, $row['niches'] ?? [], true);
        }));

        usort($same, function (array $left, array $right) use ($dr) {
            $distance = abs(((int) ($left['dr'] ?? 0)) - (int) $dr) <=> abs(((int) ($right['dr'] ?? 0)) - (int) $dr);

            return $distance !== 0 ? $distance : ((int) ($right['traffic'] ?? -1) <=> (int) ($left['traffic'] ?? -1));
        });

        $similar = array_slice($same, 0, 6);

        if (count($similar) < 6) {
            $picked = array_map(fn (array $row) => mb_strtolower((string) $row['domain']), $similar);
            $fill = array_values(array_filter($others, fn (array $row) => ! in_array(mb_strtolower((string) $row['domain']), $picked, true)));
            usort($fill, fn (array $left, array $right) => ((int) ($right['dr'] ?? -1) <=> (int) ($left['dr'] ?? -1)) ?: ((int) ($right['traffic'] ?? -1) <=> (int) ($left['traffic'] ?? -1)));
            $similar = array_merge($similar, array_slice($fill, 0, 6 - count($similar)));
        }

        $popularPool = $same !== [] ? $same : $others;
        usort($popularPool, fn (array $left, array $right) => (int) ($right['traffic'] ?? -1) <=> (int) ($left['traffic'] ?? -1));

        return [
            'similar' => array_map(fn (array $row) => [
                'domain' => (string) $row['domain'],
                'key' => DemoNicheData::profileKey($row),
                'category' => (string) ($row['niches'][0] ?? $primary),
                'dr' => $row['dr'] ?? null,
                'da' => $row['da'] ?? null,
                'traffic' => $row['traffic'] ?? null,
                'price' => $row['price'] ?? null,
            ], $similar),
            'popular' => array_map(fn (array $row) => [
                'domain' => (string) $row['domain'],
                'key' => DemoNicheData::profileKey($row),
            ], array_slice($popularPool, 0, 6)),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function livePeers(): array
    {
        if (! class_exists(\App\Models\Website::class) || ! Schema::hasTable('websites')) {
            return [];
        }

        try {
            $query = \App\Models\Website::query();

            if (Schema::hasColumn('websites', 'status')) {
                $query->where('status', 'active');
            }

            if (method_exists(\App\Models\Website::class, 'niches')) {
                $query->with('niches');
            }

            return $query->get()->map(function ($website) {
                $niches = [];

                if (is_string($website->niche ?? null) && $website->niche !== '') {
                    $niches[] = $website->niche;
                }

                foreach ($website->niches ?? [] as $niche) {
                    $label = (string) ($niche->niche_name ?? '');

                    if ($label !== '') {
                        $niches[] = $label;
                    }
                }

                return [
                    'id' => $website->id,
                    'domain' => (string) ($website->domain ?? ''),
                    'niches' => array_values(array_unique($niches)),
                    'dr' => is_numeric($website->domain_rating ?? null) ? (int) $website->domain_rating : null,
                    'da' => is_numeric($website->domain_authority ?? null) ? (int) $website->domain_authority : null,
                    'traffic' => is_numeric($website->monthly_traffic ?? null) ? (int) $website->monthly_traffic : null,
                    'price' => is_numeric($website->price ?? null) ? (float) $website->price : null,
                    'demo' => false,
                ];
            })->filter(fn (array $row) => $row['domain'] !== '')->values()->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @param  array<string, mixed>|object  $site
     */
    private function updatedAt(array|object $site): ?Carbon
    {
        $value = is_array($site)
            ? ($site['updated_at'] ?? $site['added_at'] ?? null)
            : ($site->updated_at ?? $site->created_at ?? null);

        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance(\DateTime::createFromInterface($value));
        }

        return is_string($value) && $value !== '' ? Carbon::parse($value) : null;
    }

    /**
     * @param  array<string, mixed>|object  $site
     */
    private function text(array|object $site, string $key): ?string
    {
        $value = $this->raw($site, $key);

        if (is_object($site) && ($value === null || $value === '')) {
            $metric = $this->metric($site);
            $value = $metric->{$key} ?? $value;
        }

        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    /**
     * @param  array<string, mixed>|object  $site
     */
    private function intValue(array|object $site, string $key, string $column): ?int
    {
        $value = $this->raw($site, $key);

        if (! is_numeric($value)) {
            $value = $this->raw($site, $column);
        }

        if (! is_numeric($value) && is_object($site)) {
            $metric = $this->metric($site);
            $value = $metric->{$column} ?? $metric->{$key} ?? null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * @param  array<string, mixed>|object  $site
     */
    private function floatValue(array|object $site, string $key, string $column): ?float
    {
        $value = $this->raw($site, $key);

        if (! is_numeric($value)) {
            $value = $this->raw($site, $column);
        }

        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * @param  array<string, mixed>|object  $site
     */
    private function raw(array|object $site, string $key): mixed
    {
        if (is_array($site)) {
            return $site[$key] ?? null;
        }

        return $site->{$key} ?? null;
    }

    private function metric(object $site): ?object
    {
        if (! method_exists($site, 'seoMetric')) {
            return null;
        }

        try {
            $metric = $site->seoMetric;
        } catch (\Throwable) {
            return null;
        }

        return is_object($metric) ? $metric : null;
    }
}
