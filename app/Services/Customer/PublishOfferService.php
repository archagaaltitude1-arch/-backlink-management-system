<?php

namespace App\Services\Customer;

use App\Models\OrderItem;
use App\Models\PublisherReview;
use App\Models\User;
use App\Support\DemoNicheData;
use App\Support\DemoPublisherData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PublishOfferService
{
    /**
     * @return array<string, mixed>|null
     */
    public function resolve(string $site): ?array
    {
        if (ctype_digit($site)) {
            if (! class_exists(\App\Models\Website::class) || ! Schema::hasTable('websites')) {
                return null;
            }

            $model = \App\Models\Website::query()->find($site);

            if ($model === null) {
                return null;
            }

            if (Schema::hasColumn('websites', 'status') && strtolower((string) ($model->status ?? '')) !== 'active') {
                return null;
            }

            return $this->build($model);
        }

        $demo = (bool) config('marketplace.demo')
            || ! class_exists(\App\Models\Website::class)
            || ! Schema::hasTable('websites');

        if (! $demo) {
            return null;
        }

        $row = DemoPublisherData::find($site);

        return $row === null ? null : $this->build($row);
    }

    /**
     * @param  array<string, mixed>|object  $site
     * @return array<string, mixed>
     */
    public function build(array|object $site): array
    {
        $demo = is_array($site) ? (bool) ($site['demo'] ?? false) : false;
        $domain = $this->text($site, 'domain') ?: 'Website';
        $id = is_numeric($this->raw($site, 'id') ?? null) ? (int) $this->raw($site, 'id') : null;
        $categories = $this->categories($site);
        $primary = $categories[0] ?? '';
        $language = $this->text($site, 'language');
        $dr = $this->number($site, 'dr', 'domain_rating');
        $da = $this->number($site, 'da', 'domain_authority');
        $pa = $this->number($site, 'pa', 'page_authority');
        $tf = $this->number($site, 'tf', 'trust_flow');
        $cf = $this->citation($site);
        $traffic = $this->number($site, 'traffic', 'monthly_traffic');
        $keywords = $this->number($site, 'keywords', 'organic_keywords');
        $spam = $this->number($site, 'spam', 'spam_score');
        $backlinks = $this->number($site, 'backlinks', 'backlinks');
        $referring = $this->number($site, 'referring_domains', 'referring_domains');
        $price = $this->price($site);
        $credits = $this->number($site, 'credits', 'credit_cost');
        $extras = $demo ? DemoPublisherData::extras($domain) : [];
        $linkType = $this->linkType($site, $extras);
        $googleNews = array_key_exists('google_news', $extras)
            ? (bool) $extras['google_news']
            : $this->boolish($site, 'google_news');
        $foreign = array_key_exists('foreign_language_accepted', $extras)
            ? (bool) $extras['foreign_language_accepted']
            : $this->boolish($site, 'foreign_language_accepted');
        $allowed = $extras['backlinks_allowed'] ?? $this->number($site, 'backlinks_allowed', 'backlinks_allowed');
        $articleLength = $extras['article_length'] ?? $this->text($site, 'article_length');
        $tat = $extras['tat_days'] ?? $this->number($site, 'tat_days', 'tat_days');
        $updated = $this->updatedAt($site);
        $countries = $this->countries($site);
        $trends = $demo
            ? [
                'dr' => DemoPublisherData::series($domain, 'dr', (int) ($dr ?? 20)),
                'traffic' => DemoPublisherData::series($domain, 'traffic', (int) ($traffic ?? 1000)),
            ]
            : $this->realTrends($id);
        $hash = (int) sprintf('%u', crc32($domain));
        $rules = $this->rules($site, $primary !== '' ? $primary : 'the listed category');
        $reviews = $this->reviews($id, $demo ? $domain : null);

        $offer = [
            'demo' => $demo,
            'domain' => $domain,
            'display_name' => config('marketplace.hide_urls') ? 'Hidden until purchase' : $domain,
            'website_id' => $id,
            'site_id' => $demo
                ? '#'.(($hash % 9000000) + 1000000)
                : '#'.($id ?? ''),
            'key' => $demo ? $domain : (string) ($id ?? $domain),
            'price' => $price,
            'price_label' => $price === null ? '—' : '$'.number_format($price, 2),
            'credits' => $credits,
            'premium' => $dr !== null && $dr >= 60,
            'primary_category' => $primary,
            'primary_slug' => $primary !== '' ? Str::slug($primary) : null,
            'categories' => $categories,
            'language' => $language ?? '—',
            'foreign_language_accepted' => $foreign,
            'google_news' => $googleNews,
            'link_type' => $linkType,
            'link_validity' => 'Permanent',
            'backlinks_allowed' => $allowed,
            'article_length' => $articleLength ?? '—',
            'tat_days' => $tat,
            'updated_at' => $updated,
            'metrics' => [
                'da' => $da,
                'pa' => $pa,
                'dr' => $dr,
                'tf' => $tf,
                'cf' => $cf,
                'traffic' => $traffic,
                'keywords' => $keywords,
                'spam' => $spam,
                'backlinks' => $backlinks,
                'referring_domains' => $referring,
            ],
            'trends' => $trends,
            'top_countries' => $countries['rows'],
            'others_percent' => $countries['others'],
            'restricted' => $extras['restricted'] ?? $this->restricted($site),
            'rules' => $rules,
            'sample_links' => $extras['sample_links'] ?? $this->sampleLinks($site),
            'screenshots' => $this->screenshots($site, $demo),
            'reviews' => $reviews,
            'similar' => $this->similar($demo, $primary, $domain, $id, $dr),
            'order_types' => config('marketplace.order_types', []),
            'content_topics' => config('marketplace.content_topics', []),
            'writing_addon_price' => is_numeric(config('marketplace.writing_addon_price'))
                ? (float) config('marketplace.writing_addon_price')
                : null,
        ];

        $offer['lead'] = sprintf(
            'Order a %s placement on %s, a %s-language website in %s. Publish your own article, let our writers create one, or add your link to an existing post.',
            $offer['link_type'],
            $offer['display_name'],
            $offer['language'],
            $primary !== '' ? $primary : 'its category'
        );
        $offer['meta_description'] = mb_strlen($offer['lead']) > 160
            ? mb_substr($offer['lead'], 0, 157).'...'
            : $offer['lead'];

        // TODO(Archaga): add language, google_news, foreign_language_accepted, backlinks_allowed, article_length, tat_days, top_countries, restricted topics, rules, sample_links, screenshot_path, organic_keywords to websites.

        return $offer;
    }

    /**
     * @param  array<string, mixed>  $offer
     */
    public function canReview(?User $user, array $offer): bool
    {
        if ($user === null || $offer['website_id'] === null) {
            return false;
        }

        if ($this->alreadyReviewed($user->id, (int) $offer['website_id'])) {
            return false;
        }

        return $this->hasPaidOrder($user->id, (int) $offer['website_id']);
    }

    public function hasPaidOrder(int $userId, int $websiteId): bool
    {
        if (! class_exists(OrderItem::class) || ! Schema::hasTable('order_items') || ! Schema::hasTable('orders')) {
            return false;
        }

        return OrderItem::query()
            ->where('website_id', $websiteId)
            ->whereHas('order', function ($query) use ($userId): void {
                $query->where('customer_id', $userId)->where('payment_status', 'paid');
            })
            ->exists();
    }

    public function alreadyReviewed(int $userId, ?int $websiteId): bool
    {
        if ($websiteId === null || ! Schema::hasTable('publisher_reviews')) {
            return false;
        }

        return PublisherReview::query()
            ->where('user_id', $userId)
            ->where('website_id', $websiteId)
            ->exists();
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

    /**
     * @param  array<string, mixed>|object  $site
     */
    private function text(array|object $site, string $key): ?string
    {
        $value = $this->raw($site, $key);

        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @param  array<string, mixed>|object  $site
     */
    private function number(array|object $site, string $arrayKey, string $column): ?float
    {
        $value = is_array($site) ? ($site[$arrayKey] ?? $site[$column] ?? null) : ($site->{$column} ?? $site->{$arrayKey} ?? null);

        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * @param  array<string, mixed>|object  $site
     */
    private function price(array|object $site): ?float
    {
        $value = $this->raw($site, 'price');

        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * @param  array<string, mixed>|object  $site
     */
    private function citation(array|object $site): ?float
    {
        $direct = $this->number($site, 'cf', 'citation_flow');

        if ($direct !== null) {
            return $direct;
        }

        if (is_object($site) && method_exists($site, 'seoMetric')) {
            $metric = $site->relationLoaded('seoMetric') ? $site->seoMetric : $site->seoMetric()->first();

            if ($metric !== null && is_numeric($metric->CF ?? null)) {
                return (float) $metric->CF;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>|object  $site
     * @param  array<string, mixed>  $extras
     */
    private function linkType(array|object $site, array $extras): string
    {
        $value = $extras['link_type'] ?? $this->text($site, 'link_type');

        if (is_string($value) && in_array(strtolower($value), ['dofollow', 'nofollow', 'sponsored', 'ugc'], true)) {
            return ucfirst(strtolower($value));
        }

        return 'Dofollow';
    }

    /**
     * @param  array<string, mixed>|object  $site
     */
    private function boolish(array|object $site, string $key): ?bool
    {
        $value = $this->raw($site, $key);

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'yes', 'true', 'accepted'], true);
        }

        return null;
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

            if (method_exists($site, 'niches')) {
                $related = $site->relationLoaded('niches') ? $site->niches : $site->niches()->get();

                foreach ($related as $niche) {
                    $label = (string) ($niche->niche_name ?? '');

                    if ($label !== '') {
                        $names[] = $label;
                    }
                }
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * @param  array<string, mixed>|object  $site
     */
    private function updatedAt(array|object $site): Carbon
    {
        $value = $this->raw($site, 'updated_at');

        if ($value instanceof Carbon) {
            return $value;
        }

        if (is_string($value) && strtotime($value) !== false) {
            return Carbon::parse($value);
        }

        return Carbon::now();
    }

    /**
     * @param  array<string, mixed>|object  $site
     * @return array{rows: list<array{code: string, name: string, percent: float, label: string}>, others: float}
     */
    private function countries(array|object $site): array
    {
        $raw = [];

        if (is_array($site)) {
            $raw = $site['top_countries'] ?? [];
        } elseif (Schema::hasTable('websites') && Schema::hasColumn('websites', 'top_countries')) {
            $stored = $site->top_countries ?? null;
            $raw = is_string($stored) ? (json_decode($stored, true) ?: []) : (is_array($stored) ? $stored : []);
        }

        $rows = [];

        foreach (is_array($raw) ? $raw : [] as $row) {
            if (! is_array($row)) {
                continue;
            }

            if (array_is_list($row)) {
                $code = strtoupper((string) ($row[0] ?? ''));
                $percent = (float) ($row[1] ?? 0);
            } else {
                $code = strtoupper((string) ($row['code'] ?? ''));
                $percent = (float) ($row['percent'] ?? 0);
            }

            if ($code !== '') {
                $rows[] = ['code' => $code, 'percent' => $percent];
            }
        }

        if ($rows === []) {
            return ['rows' => [], 'others' => 0.0];
        }

        $pool = ['DE', 'FR', 'CA', 'AU', 'IN', 'BR', 'ES', 'IT', 'SE', 'PL', 'JP', 'MX'];
        $used = array_column($rows, 'code');
        $sum = array_sum(array_column($rows, 'percent'));
        $remaining = max(0, 100 - $sum);

        while (count($rows) < 5 && $pool !== []) {
            $code = array_shift($pool);

            if (in_array($code, $used, true)) {
                continue;
            }

            $share = $remaining > 0 ? min(8, round($remaining / 2, 2)) : 1;
            $rows[] = ['code' => $code, 'percent' => $share];
            $remaining = max(0, $remaining - $share);
            $used[] = $code;
        }

        $rows = array_slice($rows, 0, 5);
        $sum = array_sum(array_column($rows, 'percent'));
        $others = max(0, round(100 - $sum, 2));
        $named = [];

        foreach ($rows as $row) {
            $named[] = [
                'code' => $row['code'],
                'name' => $this->countryName($row['code']),
                'percent' => $row['percent'],
                'label' => number_format($row['percent'], 2).'%',
            ];
        }

        return ['rows' => $named, 'others' => $others];
    }

    private function countryName(string $code): string
    {
        $names = [
            'US' => 'United States', 'UK' => 'United Kingdom', 'GB' => 'United Kingdom', 'NL' => 'Netherlands',
            'DE' => 'Germany', 'FR' => 'France', 'CA' => 'Canada', 'AU' => 'Australia', 'IN' => 'India',
            'BR' => 'Brazil', 'ES' => 'Spain', 'IT' => 'Italy', 'SE' => 'Sweden', 'PL' => 'Poland',
            'JP' => 'Japan', 'MX' => 'Mexico', 'CH' => 'Switzerland', 'PT' => 'Portugal', 'AR' => 'Argentina',
            'UY' => 'Uruguay', 'RO' => 'Romania', 'KE' => 'Kenya', 'PK' => 'Pakistan', 'HU' => 'Hungary',
            'ID' => 'Indonesia', 'IE' => 'Ireland', 'BE' => 'Belgium', 'NG' => 'Nigeria', 'KW' => 'Kuwait',
            'NP' => 'Nepal', 'BD' => 'Bangladesh', 'CO' => 'Colombia', 'TW' => 'Taiwan',
        ];

        return $names[$code] ?? $code;
    }

    /**
     * @return array{dr: list<int>|null, traffic: list<int>|null}
     */
    private function realTrends(?int $websiteId): array
    {
        $empty = ['dr' => null, 'traffic' => null];

        if ($websiteId === null || ! Schema::hasTable('website_traffic_history')) {
            return $empty;
        }

        $columns = Schema::getColumnListing('website_traffic_history');

        if (! in_array('website_id', $columns, true)) {
            return $empty;
        }

        $query = \Illuminate\Support\Facades\DB::table('website_traffic_history')->where('website_id', $websiteId);

        if (in_array('recorded_at', $columns, true)) {
            $query->orderBy('recorded_at');
        }

        $rows = $query->limit(12)->get();

        if ($rows->isEmpty()) {
            return $empty;
        }

        $dr = in_array('domain_rating', $columns, true)
            ? $rows->map(fn ($row) => (int) ($row->domain_rating ?? 0))->all()
            : null;
        $traffic = in_array('traffic', $columns, true)
            ? $rows->map(fn ($row) => (int) ($row->traffic ?? 0))->all()
            : null;

        return [
            'dr' => $dr === [] ? null : $dr,
            'traffic' => $traffic === [] ? null : $traffic,
        ];
    }

    /**
     * @param  array<string, mixed>|object  $site
     * @return array<string, string>
     */
    private function restricted(array|object $site): array
    {
        $map = [
            'casino_gambling' => 'casino_gambling',
            'crypto' => 'crypto_allowed',
            'cbd' => 'cbd',
        ];
        $result = [];

        foreach (['casino_gambling', 'crypto', 'cbd'] as $key) {
            $value = $this->raw($site, $key) ?? $this->raw($site, $map[$key]);

            if ($value === null) {
                $result[$key] = 'not_accepted';
            } elseif (is_string($value) && in_array($value, ['accepted', 'not_accepted'], true)) {
                $result[$key] = $value;
            } else {
                $result[$key] = $value ? 'accepted' : 'not_accepted';
            }
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>|object  $site
     * @return list<string>
     */
    private function rules(array|object $site, string $category): array
    {
        $stored = $this->raw($site, 'rules');

        if (is_array($stored) && $stored !== []) {
            return array_values(array_filter($stored, 'is_string'));
        }

        return [
            "Articles must be relevant to {$category} or closely related topics.",
            'Topics the publisher does not accept are listed under Restricted topics below.',
            'Adult content and hate speech are never accepted.',
            "The publisher's editors may adjust titles, images or wording to match their quality and style.",
        ];
    }

    /**
     * @param  array<string, mixed>|object  $site
     * @return list<string>
     */
    private function sampleLinks(array|object $site): array
    {
        $stored = $this->raw($site, 'sample_links');

        if (! is_array($stored)) {
            return [];
        }

        return array_values(array_filter($stored, 'is_string'));
    }

    /**
     * @param  array<string, mixed>|object  $site
     * @return list<string>
     */
    private function screenshots(array|object $site, bool $demo): array
    {
        if ($demo) {
            return [];
        }

        if (! is_object($site) || ! Schema::hasTable('websites') || ! Schema::hasColumn('websites', 'screenshot_path')) {
            return [];
        }

        $path = $site->screenshot_path ?? null;

        return is_string($path) && $path !== '' ? [$path] : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function reviews(?int $websiteId, ?string $demoDomain): array
    {
        $empty = [
            'average' => 0.0,
            'count' => 0,
            'stars' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0],
            'items' => [],
        ];

        if (! Schema::hasTable('publisher_reviews')) {
            return $empty;
        }

        $base = PublisherReview::query()->where('status', 'approved');

        if ($websiteId !== null) {
            $base->where('website_id', $websiteId);
        } elseif ($demoDomain !== null) {
            $base->where('demo_domain', $demoDomain)->whereNull('website_id');
        } else {
            return $empty;
        }

        $count = (clone $base)->count();
        $stars = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

        if ($count > 0) {
            $grouped = (clone $base)->selectRaw('rating, COUNT(*) as total')->groupBy('rating')->pluck('total', 'rating');

            foreach ($grouped as $rating => $total) {
                $stars[(int) $rating] = (int) $total;
            }
        }

        $rows = (clone $base)->with('user')->latest()->limit(10)->get();
        $items = [];

        foreach ($rows as $review) {
            $items[] = [
                'name' => $this->publicName((string) ($review->user->name ?? 'Customer')),
                'rating' => (int) $review->rating,
                'date' => $review->created_at?->format('j F Y') ?? '',
                'title' => (string) ($review->title ?? ''),
                'comment' => (string) $review->comment,
            ];
        }

        return [
            'average' => $count > 0 ? round((float) (clone $base)->avg('rating'), 1) : 0.0,
            'count' => $count,
            'stars' => $stars,
            'items' => $items,
        ];
    }

    private function publicName(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $first = $parts[0] ?? 'Customer';
        $last = $parts[1] ?? '';

        return $last === '' ? $first : $first.' '.strtoupper(mb_substr($last, 0, 1)).'.';
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function similar(bool $demo, string $primary, string $domain, ?int $id, ?float $dr): array
    {
        $target = $dr ?? 0;

        if ($demo) {
            $rows = $primary !== '' ? DemoNicheData::rowsFor($primary) : [];
            $cards = [];

            foreach ($rows as $row) {
                if (($row['domain'] ?? '') === $domain) {
                    continue;
                }

                $cards[] = [
                    'domain' => (string) $row['domain'],
                    'key' => (string) $row['domain'],
                    'category' => $primary,
                    'dr' => (float) ($row['dr'] ?? 0),
                    'price_label' => is_numeric($row['price'] ?? null) ? '$'.number_format((float) $row['price'], 2) : '—',
                    'rating' => null,
                    'reviews' => 0,
                ];
            }

            usort($cards, fn (array $a, array $b) => abs($a['dr'] - $target) <=> abs($b['dr'] - $target));

            return array_slice($cards, 0, 10);
        }

        if ($id === null || $primary === '' || ! class_exists(\App\Models\Website::class) || ! Schema::hasTable('websites')) {
            return [];
        }

        $query = \App\Models\Website::query()->where('id', '!=', $id);

        if (Schema::hasColumn('websites', 'status')) {
            $query->where('status', 'active');
        }

        if (Schema::hasColumn('websites', 'niche')) {
            $query->where('niche', $primary);
        }

        $sites = $query->get()->sortBy(fn ($site) => abs(((float) ($site->domain_rating ?? 0)) - $target))->take(10);
        $cards = [];

        foreach ($sites as $site) {
            $cards[] = [
                'domain' => (string) $site->domain,
                'key' => (string) $site->id,
                'category' => $primary,
                'dr' => (float) ($site->domain_rating ?? 0),
                'price_label' => is_numeric($site->price) ? '$'.number_format((float) $site->price, 2) : '—',
                'rating' => null,
                'reviews' => 0,
            ];
        }

        return $cards;
    }
}
