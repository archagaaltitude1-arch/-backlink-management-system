<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BacklinkReport extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'verification_id',
        'report_details',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function verification(): BelongsTo
    {
        return $this->belongsTo(BacklinkVerification::class, 'verification_id');
    }
}
