<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['niche_name', 'description'])]
class Niche extends Model
{
    public function websites(): BelongsToMany
    {
        return $this->belongsToMany(Website::class, 'website_niches')
            ->using(WebsiteNiche::class)
            ->withTimestamps();
    }
}
