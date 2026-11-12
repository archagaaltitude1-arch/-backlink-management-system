<?php

namespace App\Services\Customer;

use Illuminate\Support\Collection;

class SiteListing
{
    public function __construct(private NicheListingService $listings) {}

    /**
     * @return array{demo: bool, rows: list<array<string, mixed>>, groups: list<array{country: string, rows: list<array<string, mixed>>}>|null}
     */
    public function forKind(string $kind): array
    {
        $catalog = $this->listings->catalogRows();
        $rows = collect($catalog['rows']);

        if ($kind === 'by-country') {
            return [
                'demo' => $catalog['demo'],
                'rows' => [],
                'groups' => $this->byCountry($rows),
            ];
        }

        $filtered = match ($kind) {
            'premium-guest' => $this->guestPosts($rows)->sortByDesc(fn (array $row) => $row['dr'] ?? -1),
            'niche-edit' => $rows
                ->filter(fn (array $row) => strcasecmp((string) ($row['link_type'] ?? ''), 'Niche edit') === 0)
                ->sortByDesc(fn (array $row) => $row['dr'] ?? -1),
            'guest-traffic' => $this->guestPosts($rows)
                ->filter(fn (array $row) => (int) ($row['traffic'] ?? 0) > 0)
                ->sortByDesc(fn (array $row) => $row['traffic'] ?? -1),
            'sidebar' => $rows->filter(function (array $row) {
                $type = strtolower((string) ($row['link_type'] ?? ''));

                return str_contains($type, 'sidebar') || str_contains($type, 'footer') || str_contains($type, 'blogroll');
            }),
            'keywords' => $this->guestPosts($rows)
                ->filter(fn (array $row) => (int) ($row['keywords'] ?? 0) >= 100)
                ->sortByDesc(fn (array $row) => $row['keywords'] ?? -1),
            default => collect(),
        };

        return [
            'demo' => $catalog['demo'],
            'rows' => $filtered->values()->all(),
            'groups' => null,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    private function guestPosts(Collection $rows): Collection
    {
        return $rows->filter(fn (array $row) => strcasecmp((string) ($row['link_type'] ?? ''), 'Guest post') === 0);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return list<array{country: string, rows: list<array<string, mixed>>}>
     */
    private function byCountry(Collection $rows): array
    {
        $groups = [];

        foreach ($rows as $row) {
            $country = strtoupper((string) ($row['top_countries'][0][0] ?? ''));
            $country = $country !== '' ? $country : 'Not stored';
            $groups[$country][] = $row;
        }

        $built = [];

        foreach ($groups as $country => $groupRows) {
            usort($groupRows, fn (array $left, array $right) => ($right['traffic'] ?? -1) <=> ($left['traffic'] ?? -1));
            $built[] = [
                'country' => $country,
                'rows' => $groupRows,
                'top' => (int) ($groupRows[0]['traffic'] ?? 0),
            ];
        }

        usort($built, fn (array $left, array $right) => $right['top'] <=> $left['top']);

        return array_map(fn (array $group) => [
            'country' => $group['country'],
            'rows' => $group['rows'],
        ], $built);
    }
}
