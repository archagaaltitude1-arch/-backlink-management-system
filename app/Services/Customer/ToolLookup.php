<?php

namespace App\Services\Customer;

class ToolLookup
{
    public function __construct(private NicheListingService $listings) {}

    /**
     * @return array{demo: bool, rows: list<array<string, mixed>>}
     */
    public function catalog(): array
    {
        return $this->listings->catalogRows();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $domain): ?array
    {
        $needle = $this->normalize($domain);

        if ($needle === '') {
            return null;
        }

        foreach ($this->catalog()['rows'] as $row) {
            if ($this->normalize((string) $row['domain']) === $needle) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function suggestions(int $limit = 6): array
    {
        return array_slice($this->catalog()['rows'], 0, $limit);
    }

    /**
     * @return array{total: int, unique: int, topShare: int, note: string, rows: list<array{label: string, count: int, share: int}>}
     */
    public function anchors(string $raw): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $lines = array_values(array_filter(array_map(static fn ($line) => trim((string) $line), $lines), static fn (string $line) => $line !== ''));
        $lines = array_slice($lines, 0, 200);
        $counts = [];

        foreach ($lines as $line) {
            $key = mb_strtolower($line);

            if (! isset($counts[$key])) {
                $counts[$key] = ['label' => $line, 'count' => 0];
            }

            $counts[$key]['count']++;
        }

        $total = count($lines);
        $rows = array_values($counts);
        usort($rows, static fn (array $left, array $right) => $right['count'] <=> $left['count']);

        foreach ($rows as $index => $row) {
            $rows[$index]['share'] = $total > 0 ? (int) round(($row['count'] / $total) * 100) : 0;
        }

        $topShare = $rows[0]['share'] ?? 0;

        return [
            'total' => $total,
            'unique' => count($rows),
            'topShare' => $topShare,
            'note' => $total === 0
                ? 'Paste at least one anchor.'
                : ($topShare >= 40
                    ? 'One anchor makes up '.$topShare.'% of this list. Spread the wording if you want a more varied profile.'
                    : 'No single anchor takes 40% or more of this list.'),
            'rows' => $rows,
        ];
    }

    public function normalize(string $value): string
    {
        $value = trim(mb_strtolower($value));
        $value = preg_replace('#^https?://#', '', $value) ?? $value;
        $value = preg_replace('#^www\.#', '', $value) ?? $value;
        $value = explode('/', $value, 2)[0];
        $value = explode('?', $value, 2)[0];
        $value = rtrim($value, '.');

        return $value;
    }
}
