<?php

namespace App\Support;

class PublicPackages
{
    /**
     * @return array<int, array{title: string, sections: array<int, array{title: string|null, items: array<int, array{label: string, slug: string, flag: string|null}>}>}>
     */
    public static function groups(): array
    {
        return [
            [
                'title' => 'Link Building',
                'sections' => [
                    [
                        'title' => 'Display backlink packages',
                        'items' => [
                            ['label' => 'Starter, 5 backlinks', 'slug' => 'display-starter', 'flag' => null],
                            ['label' => 'Growth, 10 backlinks', 'slug' => 'display-growth', 'flag' => null],
                            ['label' => 'Professional, 25 backlinks', 'slug' => 'display-professional', 'flag' => null],
                            ['label' => 'Enterprise, custom backlink campaign', 'slug' => 'display-enterprise', 'flag' => null],
                        ],
                    ],
                ],
            ],
        ];
    }
}
