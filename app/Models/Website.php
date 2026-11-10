<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['domain', 'website_name', 'url', 'price', 'credit_cost', 'status'])]
class Website extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'credit_cost' => 'integer',
        ];
    }

    public function seoMetric(): HasOne
    {
        return $this->hasOne(SeoMetric::class);
    }

    public function niches(): BelongsToMany
    {
        return $this->belongsToMany(Niche::class, 'website_niches')
            ->using(WebsiteNiche::class)
            ->withTimestamps();
    }
}
