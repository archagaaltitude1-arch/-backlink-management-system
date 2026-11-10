<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['credit_amount', 'price', 'status'])]
class CreditPricing extends Model
{
    protected $table = 'credit_pricing';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credit_amount' => 'integer',
            'price' => 'decimal:2',
        ];
    }
}
