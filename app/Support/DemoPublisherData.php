<?php

namespace App\Support;

class DemoPublisherData
{
    /**
     * @return array<string, mixed>|null
     */
    public static function find(string $domain): ?array
    {
        $row = DemoNicheData::find($domain);

        if ($row === null) {
            return null;
        }

        return array_merge($row, self::extras($domain));
    }

    /**
     * Extra preview fields. The same domain always produces the same values.
     *
     * @return array<string, mixed>
     */
    public static function extras(string $domain): array
    {
        $hash = (int) sprintf('%u', crc32($domain));
        $lengths = ['600–1,500 words', '800–2,000 words', '800–3,000 words'];
        $turnaround = [3, 5, 7, 10, 14];

        return [
            'google_news' => ($hash % 100) < 30,
            'foreign_language_accepted' => (($hash >> 8) % 100) < 40,
            'backlinks_allowed' => ($hash % 3) + 1,
            'article_length' => $lengths[$hash % 3],
            'tat_days' => $turnaround[$hash % 5],
            'restricted' => [
                'casino_gambling' => ($hash % 100) < 15 ? 'accepted' : 'not_accepted',
                'crypto' => (($hash >> 4) % 100) < 25 ? 'accepted' : 'not_accepted',
                'cbd' => (($hash >> 12) % 100) < 15 ? 'accepted' : 'not_accepted',
            ],
            'sample_links' => [
                'https://'.$domain.'/sample-article-1',
                'https://'.$domain.'/sample-article-2',
            ],
            'pa' => max(1, (int) (($hash >> 3) % 70) + 10),
            'cf' => max(1, (int) (($hash >> 6) % 55) + 5),
            'link_type' => 'Dofollow',
        ];
    }

    /**
     * @return list<int>
     */
    public static function series(string $domain, string $kind, int $anchor): array
    {
        $state = (int) sprintf('%u', crc32($domain.'|'.$kind));
        $points = [];

        for ($month = 0; $month < 12; $month++) {
            $state = (int) (($state * 1103515245 + 12345) & 0x7fffffff);
            $swing = ($state % 21) - 10;
            $points[] = max(0, $anchor + (int) round($anchor * ($swing / 100)));
        }

        return $points;
    }
}
