<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class DemoNicheData
{
    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return [
            'Technology',
            'Software & IT',
            'Business',
            'Finance',
            'Health',
            'Education',
            'Travel',
            'Fashion & Beauty',
            'Lifestyle',
            'Real Estate',
            'E-commerce',
            'Automotive',
            'Sports',
            'Food',
            'Agriculture & Farming',
        ];
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function intros(): array
    {
        return [
            'agriculture-farming' => [
                'Grow your reach in agriculture and farming with guest posts, contextual links and niche edits on websites read by farmers, agronomists and food producers.',
                'From sustainable growing and livestock to farm technology and rural business, compare publishers by DR, DA, traffic and price, and pick the sites that suit your campaign.',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function rowsFor(string $niche): array
    {
        $rows = [];

        foreach (self::raw() as $index => $row) {
            $niches = array_values(array_unique(array_merge([$niche], $row['extras'])));

            $linkTypes = ['Guest post', 'Niche edit', 'Contextual', 'Text link'];
            $validity = ['Permanent', '12 months', '6 months'];

            $rows[] = [
                'id' => null,
                'domain' => $row['domain'],
                'niches' => $niches,
                'language' => $row['language'],
                'dr' => $row['dr'],
                'da' => $row['da'],
                'as' => max(1, (int) $row['da'] - ($index % 8)),
                'spam' => $row['spam'],
                'traffic' => $row['traffic'],
                'semrush_traffic' => max(100, (int) round($row['traffic'] * (0.4 + ($index % 5) * 0.1))),
                'keywords' => $row['keywords'],
                'top_countries' => $row['countries'],
                'referring_domains' => $row['referring'],
                'backlinks' => max(1, (int) round($row['referring'] / 3)),
                'price' => $row['price'],
                'credits' => $row['credits'],
                'sports_allowed' => $index % 3 !== 0,
                'pharmacy_allowed' => $index % 4 !== 0,
                'crypto_allowed' => $index % 5 === 0,
                'link_type' => $linkTypes[$index % 4],
                'link_validity' => $validity[$index % 3],
                'google_news' => $index % 2 === 0,
                'sponsored' => $index % 3 === 1,
                'foreign_language' => $row['language'] !== 'English',
                'added_at' => Carbon::now()->subDays($index),
                'demo' => true,
            ];
        }

        return $rows;
    }

    public static function compact(?int $value): string
    {
        if ($value === null) {
            return '';
        }

        if ($value >= 1000000) {
            return self::trimNumber($value / 1000000).'M';
        }

        if ($value >= 1000) {
            return self::trimNumber($value / 1000).'K';
        }

        return (string) $value;
    }

    private static function trimNumber(float $value): string
    {
        return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.');
    }

    /**
     * Fictional .example publishers. The first niche pill is replaced with the current niche.
     *
     * @return list<array<string, mixed>>
     */
    private static function raw(): array
    {
        return [
            ['domain' => 'farmpulse.example', 'extras' => ['Gardening'], 'language' => 'English', 'dr' => 49, 'da' => 39, 'spam' => 2, 'traffic' => 211700, 'keywords' => 24600, 'countries' => [['US', 54], ['UK', 13], ['NL', 7]], 'referring' => 1860, 'price' => 433.90, 'credits' => 145],
            ['domain' => 'agrarblatt.example', 'extras' => ['Environment'], 'language' => 'German', 'dr' => 53, 'da' => 46, 'spam' => 1, 'traffic' => 65100, 'keywords' => 11100, 'countries' => [['DE', 99], ['US', 1], ['CH', 1]], 'referring' => 4200, 'price' => 771.90, 'credits' => 257],
            ['domain' => 'greenfieldsjournal.example', 'extras' => ['Education', 'News'], 'language' => 'English', 'dr' => 40, 'da' => 32, 'spam' => 3, 'traffic' => 30300, 'keywords' => 3200, 'countries' => [['US', 70], ['BR', 11], ['IN', 7]], 'referring' => 980, 'price' => 129.90, 'credits' => 43],
            ['domain' => 'cosechadiaria.example', 'extras' => ['Education', 'Sports'], 'language' => 'Spanish', 'dr' => 35, 'da' => 49, 'spam' => 4, 'traffic' => 18900, 'keywords' => 1400, 'countries' => [['ES', 96], ['RO', 1], ['CH', 1]], 'referring' => 1275, 'price' => 220.90, 'credits' => 74],
            ['domain' => 'campoforte.example', 'extras' => [], 'language' => 'Portuguese', 'dr' => 43, 'da' => 42, 'spam' => 2, 'traffic' => 18000, 'keywords' => 5400, 'countries' => [['BR', 97], ['PT', 1], ['US', 1]], 'referring' => 1335, 'price' => 362.90, 'credits' => 121],
            ['domain' => 'agromercado.example', 'extras' => ['Business', 'Travel'], 'language' => 'Spanish', 'dr' => 39, 'da' => 48, 'spam' => 3, 'traffic' => 15900, 'keywords' => 5100, 'countries' => [['AR', 73], ['MX', 11], ['UY', 2]], 'referring' => 539, 'price' => 124.90, 'credits' => 42],
            ['domain' => 'terraviva.example', 'extras' => ['Energy', 'Environment'], 'language' => 'Portuguese', 'dr' => 36, 'da' => 62, 'spam' => 2, 'traffic' => 11800, 'keywords' => 37600, 'countries' => [['BR', 78], ['JP', 5], ['US', 4]], 'referring' => 80, 'price' => 376.90, 'credits' => 126],
            ['domain' => 'voedselvandaag.example', 'extras' => ['Food', 'Health'], 'language' => 'Dutch', 'dr' => 34, 'da' => 52, 'spam' => 2, 'traffic' => 8700, 'keywords' => 3900, 'countries' => [['NL', 93], ['US', 2], ['IT', 1]], 'referring' => 195, 'price' => 520.90, 'credits' => 174],
            ['domain' => 'shambanews.example', 'extras' => ['Finance', 'Business'], 'language' => 'English', 'dr' => 29, 'da' => 44, 'spam' => 5, 'traffic' => 8000, 'keywords' => 2700, 'countries' => [['KE', 71], ['US', 10], ['PK', 5]], 'referring' => 467, 'price' => 157.90, 'credits' => 53],
            ['domain' => 'soilandseed.example', 'extras' => ['Gardening', 'Outdoors'], 'language' => 'English', 'dr' => 31, 'da' => 28, 'spam' => 4, 'traffic' => 7600, 'keywords' => 734, 'countries' => [['US', 64], ['NP', 9], ['KE', 5]], 'referring' => 59, 'price' => 260.90, 'credits' => 87],
            ['domain' => 'mezogazdasag.example', 'extras' => ['Food'], 'language' => 'Hungarian', 'dr' => 26, 'da' => 30, 'spam' => 6, 'traffic' => 6400, 'keywords' => 347, 'countries' => [['HU', 58], ['RO', 33], ['BR', 4]], 'referring' => 89, 'price' => 227.90, 'credits' => 76],
            ['domain' => 'gadoemfoco.example', 'extras' => [], 'language' => 'Portuguese', 'dr' => 35, 'da' => 48, 'spam' => 2, 'traffic' => 5300, 'keywords' => 1000, 'countries' => [['BR', 97], ['CO', 2], ['UK', 1]], 'referring' => 69, 'price' => 2066.90, 'credits' => 689],
            ['domain' => 'taniharian.example', 'extras' => ['Family', 'Travel'], 'language' => 'Indonesian', 'dr' => 11, 'da' => 18, 'spam' => 9, 'traffic' => 4900, 'keywords' => 601, 'countries' => [['ID', 89], ['ES', 11]], 'referring' => 10, 'price' => 74.90, 'credits' => 25],
            ['domain' => 'agronegociohoje.example', 'extras' => [], 'language' => 'Portuguese', 'dr' => 43, 'da' => 51, 'spam' => 1, 'traffic' => 4300, 'keywords' => 16100, 'countries' => [['BR', 92], ['TW', 2], ['JP', 2]], 'referring' => 342, 'price' => 504.90, 'credits' => 168],
            ['domain' => 'streekboer.example', 'extras' => ['Culture', 'Sports'], 'language' => 'Dutch', 'dr' => 32, 'da' => 29, 'spam' => 3, 'traffic' => 4000, 'keywords' => 748, 'countries' => [['NL', 98], ['NG', 1], ['PL', 1]], 'referring' => 248, 'price' => 391.90, 'credits' => 131],
            ['domain' => 'veehouderij.example', 'extras' => [], 'language' => 'Dutch', 'dr' => 29, 'da' => 37, 'spam' => 2, 'traffic' => 3600, 'keywords' => 618, 'countries' => [['NL', 70], ['KW', 13], ['BE', 12]], 'referring' => 34, 'price' => 605.90, 'credits' => 202],
            ['domain' => 'fieldandfarm.example', 'extras' => ['News', 'Sports'], 'language' => 'English', 'dr' => 35, 'da' => 49, 'spam' => 2, 'traffic' => 3500, 'keywords' => 1600, 'countries' => [['IE', 55], ['UK', 24], ['US', 6]], 'referring' => 268, 'price' => 605.90, 'credits' => 202],
            ['domain' => 'groundtruthagri.example', 'extras' => ['Environment', 'Health'], 'language' => 'English', 'dr' => 32, 'da' => 67, 'spam' => 3, 'traffic' => 3300, 'keywords' => 750, 'countries' => [['IN', 78], ['UK', 11], ['US', 3]], 'referring' => 125, 'price' => 88.90, 'credits' => 30],
            ['domain' => 'outbackfarmer.example', 'extras' => ['Animals', 'Machinery'], 'language' => 'English', 'dr' => 36, 'da' => 61, 'spam' => 2, 'traffic' => 2900, 'keywords' => 684, 'countries' => [['AU', 67], ['US', 21], ['IN', 6]], 'referring' => 127, 'price' => 1186.90, 'credits' => 396],
            ['domain' => 'morningharvest.example', 'extras' => [], 'language' => 'English', 'dr' => 36, 'da' => 54, 'spam' => 1, 'traffic' => 2800, 'keywords' => 1400, 'countries' => [['US', 53], ['BD', 27], ['IN', 5]], 'referring' => 389, 'price' => 88.90, 'credits' => 30],
        ];
    }
}
