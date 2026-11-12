<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'website_id', 'service_id', 'item_name', 'unit_price', 'quantity', 'credit_cost'];

    protected function casts(): array
    {
        return ['unit_price' => 'decimal:2'];
    }
}
