<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Website extends Model
{
    protected $fillable = [
        'website_name',
        'domain',
        'url',
        'niche',
        'description',
        'domain_rating',
        'domain_authority',
        'trust_flow',
        'page_authority',
        'monthly_traffic',
        'spam_score',
        'backlinks',
        'referring_domains',
        'price',
        'credit_cost',
        'status',
    ];

    protected function casts(): array
    {
        return ['spam_score' => 'decimal:1', 'price' => 'decimal:2', 'credit_cost' => 'integer'];
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
