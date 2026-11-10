<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class WebsiteNiche extends Pivot
{
    protected $table = 'website_niches';

    public $incrementing = true;

    public $timestamps = true;

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function niche(): BelongsTo
    {
        return $this->belongsTo(Niche::class);
    }
}
