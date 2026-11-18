<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class MaxKeywords implements ValidationRule
{
    public function __construct(public int $max = 5) {}

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        $count = count(self::parse($value));

        if ($count > $this->max) {
            $fail("Add no more than {$this->max} keywords.");
        }
    }

    /**
     * @return list<string>
     */
    public static function parse(string $value): array
    {
        return array_values(array_filter(array_map(
            static fn (string $keyword): string => trim($keyword),
            preg_split('/\s*,\s*/', $value) ?: [],
        ), static fn (string $keyword): bool => $keyword !== ''));
    }

    public static function normalize(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $keywords = self::parse($value);

        return $keywords === [] ? null : implode(', ', $keywords);
    }
}
