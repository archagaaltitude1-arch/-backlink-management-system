<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Publication extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'article_id',
        'website_id',
        'published_url',
        'status',
    ];

    public function verification(): HasOne
    {
        return $this->hasOne(BacklinkVerification::class);
    }
}
