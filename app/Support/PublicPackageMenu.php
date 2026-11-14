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
                            ['label' => 'Starter Backlink Package', 'id' => 'display-starter'],
                            ['label' => 'Growth Backlink Package', 'id' => 'display-growth'],
                            ['label' => 'Professional Backlink Package', 'id' => 'display-professional'],
                            ['label' => 'Enterprise Backlink Package', 'id' => 'display-enterprise'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
