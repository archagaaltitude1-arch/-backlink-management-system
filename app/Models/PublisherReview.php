<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublisherReview extends Model
{
    protected $fillable = [
        'user_id',
        'website_id',
        'demo_domain',
        'rating',
        'title',
        'comment',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'website_id' => 'integer',
            'rating' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
