<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        return ['spam_score' => 'decimal:1', 'price' => 'decimal:2'];
    }
}
