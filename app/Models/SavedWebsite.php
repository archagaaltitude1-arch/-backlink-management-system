<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedWebsite extends Model
{
    protected $fillable = ['customer_id', 'website_id', 'website_name', 'domain', 'url', 'domain_rating', 'monthly_traffic', 'price'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
