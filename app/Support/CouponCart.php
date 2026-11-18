<?php

namespace App\Support;

class CouponCart
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{tiers: array<int, string>, purchase: string}
     */
    public static function fromShoppingItems(array $items): array
    {
        $tiers = [];
        $hasPackage = false;
        $hasIndividual = false;

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            if (($item['kind'] ?? '') === 'package') {
                $hasPackage = true;
                $tier = self::tier((string) ($item['package_slug'] ?? $item['domain'] ?? $item['display_name'] ?? ''));

                if ($tier !== '') {
                    $tiers[] = $tier;
                }

                continue;
            }

            $hasIndividual = true;
        }

        return self::pack($tiers, $hasPackage, $hasIndividual);
    }

    /**
     * @param  iterable<int, mixed>  $items
     * @return array{tiers: array<int, string>, purchase: string}
     */
    public static function fromNamedItems(iterable $items): array
    {
        $tiers = [];
        $hasPackage = false;
        $hasIndividual = false;

        foreach ($items as $item) {
            $name = is_object($item) ? (string) ($item->item_name ?? '') : (string) $item;

            if (preg_match('/package/i', $name) === 1) {
                $hasPackage = true;
                $tier = self::tier($name);

                if ($tier !== '') {
                    $tiers[] = $tier;
                }

                continue;
            }

            if ($name !== '') {
                $hasIndividual = true;
            }
        }

        return self::pack($tiers, $hasPackage, $hasIndividual);
    }

    public static function tier(string $value): string
    {
        $value = mb_strtolower($value);

        if (str_contains($value, 'enterprise')) {
            return 'enterprise';
        }

        if (str_contains($value, 'professional')) {
            return 'professional';
        }

        if (str_contains($value, 'starter')) {
            return 'starter';
        }

        if (str_contains($value, 'growth') || preg_match('/(^|[^a-z])pro([^a-z]|$)/', $value) === 1) {
            return 'pro';
        }

        return '';
    }

    /**
     * @param  array<int, string>  $tiers
     * @return array{tiers: array<int, string>, purchase: string}
     */
    private static function pack(array $tiers, bool $hasPackage, bool $hasIndividual): array
    {
        $purchase = match (true) {
            $hasPackage && $hasIndividual => 'both',
            $hasPackage => 'package',
            $hasIndividual => 'individual',
            default => '',
        };

        return [
            'tiers' => array_values(array_unique($tiers)),
            'purchase' => $purchase,
        ];
    }
}
