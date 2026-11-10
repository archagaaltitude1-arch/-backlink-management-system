<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['website_id', 'DR', 'DA', 'TF', 'CF', 'traffic', 'spam_score', 'backlinks'])]
class SeoMetric extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'website_id' => 'integer',
            'DR' => 'decimal:2',
            'DA' => 'decimal:2',
            'TF' => 'decimal:2',
            'CF' => 'decimal:2',
            'traffic' => 'integer',
            'spam_score' => 'decimal:2',
            'backlinks' => 'integer',
        ];
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
