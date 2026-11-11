<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'website_id', 'service_id', 'item_name', 'unit_price', 'quantity', 'credit_cost', 'target_url', 'anchor_text', 'article_topic', 'requirements'];

    protected function casts(): array
    {
        return ['unit_price' => 'decimal:2'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(OrderAssignment::class, 'order_item_id');
    }
}
