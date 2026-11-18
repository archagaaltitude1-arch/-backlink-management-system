<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'website_id', 'service_id', 'item_name', 'unit_price', 'quantity', 'credit_cost', 'backlinks', 'target_url', 'anchor_text', 'article_topic', 'keywords', 'requirements', 'selected_websites'];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'backlinks' => 'integer',
            'selected_websites' => 'array',
        ];
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

    public function assignment(): HasOne
    {
        return $this->hasOne(OrderAssignment::class);
    }

    public function project(): HasOne
    {
        return $this->hasOne(CustomerProject::class);
    }

    public function article(): HasOne
    {
        return $this->hasOne(Article::class);
    }
}
