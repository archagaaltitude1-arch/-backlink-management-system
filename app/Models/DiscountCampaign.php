<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountCampaign extends Model
{
    protected $fillable = [
        'type',
        'is_enabled',
        'discount_type',
        'discount_value',
        'minimum_order_amount',
        'combines_with_package_discount',
        'qualifying_orders',
        'recurring',
        'inactivity_days',
        'expiry_days',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'discount_value' => 'decimal:2',
            'minimum_order_amount' => 'decimal:2',
            'combines_with_package_discount' => 'boolean',
            'qualifying_orders' => 'integer',
            'recurring' => 'boolean',
            'inactivity_days' => 'integer',
            'expiry_days' => 'integer',
        ];
    }

    public static function findType(string $type): ?self
    {
        return static::query()->where('type', $type)->first();
    }

    public function summary(): string
    {
        $value = $this->discount_type === 'fixed'
            ? '$'.number_format((float) $this->discount_value, 2)
            : rtrim(rtrim(number_format((float) $this->discount_value, 2), '0'), '.').'%';

        return $value.' off';
    }
}
