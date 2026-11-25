<?php

namespace App\Support;

use App\Models\Coupon;
use App\Models\DiscountCampaign;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use JsonSerializable;
use Stringable;

class SpaViewData
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function convert(array $data): array
    {
        $safe = [];

        foreach ($data as $key => $value) {
            if (! is_string($key) || str_starts_with($key, '__')) {
                continue;
            }

            $safe[$key] = $this->value($value, 0);
        }

        return $safe;
    }

    private function value(mixed $value, int $depth): mixed
    {
        if ($depth > 8 || $value === null || is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value)) {
            return $value;
        }

        if ($value instanceof Coupon) {
            $row = $value->toArray();
            $row['discount_summary'] = $value->discountSummary();
            $row['package_label'] = $value->packageLabel();

            return $this->value($row, $depth + 1);
        }

        if ($value instanceof DiscountCampaign) {
            $row = $value->toArray();
            $row['summary'] = $value->summary();

            return $this->value($row, $depth + 1);
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if ($value instanceof AbstractPaginator) {
            return $this->value($value->toArray(), $depth + 1);
        }

        if ($value instanceof Collection) {
            $items = $value->all();

            if ($this->hasIntegerKeys($items)) {
                $items = array_values($items);
            }

            return $this->value($items, $depth + 1);
        }

        if ($value instanceof Arrayable) {
            return $this->value($value->toArray(), $depth + 1);
        }

        if ($value instanceof JsonSerializable) {
            return $this->value($value->jsonSerialize(), $depth + 1);
        }

        if ($value instanceof Stringable) {
            return (string) $value;
        }

        if (is_array($value)) {
            $list = array_is_list($value);
            $converted = [];

            foreach ($value as $key => $item) {
                $converted[$key] = $this->value($item, $depth + 1);
            }

            return $list ? array_values($converted) : $converted;
        }

        return null;
    }

    /**
     * Filtered collections keep their original keys, so JSON would turn the list into an object.
     *
     * @param  array<array-key, mixed>  $items
     */
    private function hasIntegerKeys(array $items): bool
    {
        if ($items === [] || array_is_list($items)) {
            return false;
        }

        foreach (array_keys($items) as $key) {
            if (! is_int($key)) {
                return false;
            }
        }

        return true;
    }
}
