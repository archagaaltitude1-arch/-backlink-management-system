<?php

namespace App\Support;

class PublicPackageMenu
{
    /**
     * @return array<int, array{title: string, id: string, sections: array<int, array{title: ?string, items: array<int, array{label: string, id: string, flag?: string}>}>}>
     */
    public static function groups(): array
    {
        return [
            [
                'title' => 'Link Building',
                'id' => 'link-building',
                'sections' => [
                    [
                        'title' => 'Display backlink packages',
                        'items' => [
                            ['label' => 'Starter, 5 backlinks', 'id' => 'display-starter'],
                            ['label' => 'Growth, 10 backlinks', 'id' => 'display-growth'],
                            ['label' => 'Professional, 25 backlinks', 'id' => 'display-professional'],
                            ['label' => 'Enterprise, custom backlink campaign', 'id' => 'display-enterprise'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
