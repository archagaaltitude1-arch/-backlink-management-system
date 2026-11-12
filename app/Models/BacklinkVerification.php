<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BacklinkVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'publication_id',
        'target_url',
        'anchor_text',
        'verification_status',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function report(): HasOne
    {
        return $this->hasOne(BacklinkReport::class, 'verification_id');
    }
}
