<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'website_id', 'service_id', 'item_name', 'unit_price', 'quantity', 'target_url', 'anchor_text', 'article_topic', 'requirements'];

    protected function casts(): array
    {
        return ['unit_price' => 'decimal:2'];
    }
}