<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class BrowseCountries
{
    /**
     * @return list<array{label: string, flag: string, code: string}>
     */
    public static function all(): array
    {
        return [
            ['label' => 'Argentina', 'flag' => 'ar', 'code' => 'AR'],
            ['label' => 'Australia', 'flag' => 'au', 'code' => 'AU'],
            ['label' => 'Bangladesh', 'flag' => 'bd', 'code' => 'BD'],
            ['label' => 'Belarus', 'flag' => 'by', 'code' => 'BY'],
            ['label' => 'Belgium', 'flag' => 'be', 'code' => 'BE'],
            ['label' => 'Brazil', 'flag' => 'br', 'code' => 'BR'],
            ['label' => 'Bulgaria', 'flag' => 'bg', 'code' => 'BG'],
            ['label' => 'Canada', 'flag' => 'ca', 'code' => 'CA'],
            ['label' => 'China', 'flag' => 'cn', 'code' => 'CN'],
            ['label' => 'Cyprus', 'flag' => 'cy', 'code' => 'CY'],
            ['label' => 'Czech Republic', 'flag' => 'cz', 'code' => 'CZ'],
            ['label' => 'Denmark', 'flag' => 'dk', 'code' => 'DK'],
            ['label' => 'Finland', 'flag' => 'fi', 'code' => 'FI'],
            ['label' => 'France', 'flag' => 'fr', 'code' => 'FR'],
            ['label' => 'Georgia', 'flag' => 'ge', 'code' => 'GE'],
            ['label' => 'Germany', 'flag' => 'de', 'code' => 'DE'],
            ['label' => 'Hong Kong', 'flag' => 'hk', 'code' => 'HK'],
            ['label' => 'Hungary', 'flag' => 'hu', 'code' => 'HU'],
            ['label' => 'India', 'flag' => 'in', 'code' => 'IN'],
            ['label' => 'Indonesia', 'flag' => 'id', 'code' => 'ID'],
            ['label' => 'Ireland', 'flag' => 'ie', 'code' => 'IE'],
            ['label' => 'Israel', 'flag' => 'il', 'code' => 'IL'],
            ['label' => 'Italy', 'flag' => 'it', 'code' => 'IT'],
            ['label' => 'Lithuania', 'flag' => 'lt', 'code' => 'LT'],
            ['label' => 'Malaysia', 'flag' => 'my', 'code' => 'MY'],
            ['label' => 'Malta', 'flag' => 'mt', 'code' => 'MT'],
            ['label' => 'Moldova', 'flag' => 'md', 'code' => 'MD'],
            ['label' => 'Montenegro', 'flag' => 'me', 'code' => 'ME'],
            ['label' => 'Netherlands', 'flag' => 'nl', 'code' => 'NL'],
            ['label' => 'New Zealand', 'flag' => 'nz', 'code' => 'NZ'],
            ['label' => 'Pakistan', 'flag' => 'pk', 'code' => 'PK'],
            ['label' => 'Philippines', 'flag' => 'ph', 'code' => 'PH'],
            ['label' => 'Poland', 'flag' => 'pl', 'code' => 'PL'],
            ['label' => 'Portugal', 'flag' => 'pt', 'code' => 'PT'],
            ['label' => 'Romania', 'flag' => 'ro', 'code' => 'RO'],
            ['label' => 'Russia', 'flag' => 'ru', 'code' => 'RU'],
            ['label' => 'Serbia', 'flag' => 'rs', 'code' => 'RS'],
            ['label' => 'Singapore', 'flag' => 'sg', 'code' => 'SG'],
            ['label' => 'Slovenia', 'flag' => 'si', 'code' => 'SI'],
            ['label' => 'South Africa', 'flag' => 'za', 'code' => 'ZA'],
            ['label' => 'Spain', 'flag' => 'es', 'code' => 'ES'],
            ['label' => 'Sweden', 'flag' => 'se', 'code' => 'SE'],
            ['label' => 'Taiwan', 'flag' => 'tw', 'code' => 'TW'],
            ['label' => 'Thailand', 'flag' => 'th', 'code' => 'TH'],
            ['label' => 'Turkey', 'flag' => 'tr', 'code' => 'TR'],
            ['label' => 'Ukraine', 'flag' => 'ua', 'code' => 'UA'],
            ['label' => 'UAE', 'flag' => 'ae', 'code' => 'AE'],
            ['label' => 'UK', 'flag' => 'gb', 'code' => 'UK'],
            ['label' => 'USA', 'flag' => 'us', 'code' => 'US'],
            ['label' => 'Vietnam', 'flag' => 'vn', 'code' => 'VN'],
        ];
    }

    /**
     * @return array{label: string, flag: string, code: string}|null
     */
    public static function find(string $value): ?array
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        foreach (self::all() as $country) {
            if (strcasecmp($country['code'], $value) === 0 || strcasecmp($country['label'], $value) === 0) {
                return $country;
            }
        }

        return null;
    }

    /**
     * Sample comparison rows used when a country has no stored publishers.
     *
     * @return list<array<string, mixed>>
     */
    public static function chartRows(string $code): array
    {
        $country = self::find($code);

        if ($country === null) {
            return [];
        }

        $code = $country['code'];
        $stems = ['signaldesk', 'northbyte', 'harborstack', 'pixelroute', 'brightcircuit', 'fieldnote'];
        $languages = ['English', 'German', 'Portuguese', 'Spanish', 'French', 'Dutch'];
        $types = ['Guest post', 'Niche edit', 'Contextual', 'Text link', 'Guest post', 'Niche edit'];
        $validity = ['Permanent', '12 months', '6 months'];
        $traffic = [860000, 240000, 98000, 42000, 18600, 7400];
        $rows = [];

        foreach ($stems as $index => $stem) {
            $dr = 62 - ($index * 7);
            $da = 54 - ($index * 6);
            $language = $languages[$index];

            $rows[] = [
                'id' => null,
                'domain' => $stem.'-'.strtolower($code).'.example',
                'niches' => ['Technology'],
                'language' => $language,
                'dr' => $dr,
                'da' => $da,
                'pa' => max(1, $da - 4),
                'tf' => max(1, (int) round(($dr + $da) / 2) - 3),
                'as' => max(1, $da - ($index % 5)),
                'spam' => 1 + ($index % 6),
                'traffic' => $traffic[$index],
                'semrush_traffic' => (int) round($traffic[$index] * 0.72),
                'keywords' => 1200 + ($index * 2600),
                'top_countries' => self::countryMix($code, $index),
                'referring_domains' => 90 + ($index * 140),
                'backlinks' => 500 + ($index * 900),
                'price' => 80 + ($index * 35),
                'credits' => 8 + $index,
                'sports_allowed' => $index % 2 === 0,
                'pharmacy_allowed' => $index % 3 !== 0,
                'crypto_allowed' => $index % 4 === 0,
                'link_type' => $types[$index],
                'link_validity' => $validity[$index % 3],
                'google_news' => $index % 2 === 0,
                'sponsored' => $index % 3 === 1,
                'foreign_language' => $language !== 'English',
                'added_at' => Carbon::now()->subDays($index + 1),
                'demo' => true,
                'sample_metrics' => true,
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findChartRow(string $domain): ?array
    {
        $domain = mb_strtolower(trim($domain));

        if (! preg_match('/^[a-z]+-([a-z]{2})\.example$/', $domain, $matches)) {
            return null;
        }

        foreach (self::chartRows($matches[1]) as $row) {
            if (mb_strtolower((string) $row['domain']) === $domain) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    public static function only(array $rows, string $code): array
    {
        $code = strtoupper($code);
        $matched = [];

        foreach ($rows as $row) {
            $top = strtoupper((string) ($row['top_countries'][0][0] ?? ''));

            if ($top === $code) {
                $matched[] = $row;
            }
        }

        if ($matched !== []) {
            return $matched;
        }

        return self::chartRows($code);
    }

    /**
     * @return list<array{0: string, 1: float}>
     */
    private static function countryMix(string $code, int $index): array
    {
        $others = ['US', 'UK', 'DE', 'FR', 'IN', 'AU', 'BR', 'ES', 'NL', 'CA'];
        $second = $others[$index % count($others)];
        $third = $others[($index + 3) % count($others)];

        if ($second === $code) {
            $second = $others[($index + 1) % count($others)];
        }

        if ($third === $code || $third === $second) {
            $third = $others[($index + 5) % count($others)];
        }

        return [[$code, 68.0], [$second, 14.0], [$third, 6.0]];
    }
}
