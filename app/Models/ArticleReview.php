<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'staff_id',
        'review_status',
        'feedback',
        'reviewed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function decisionLabel(): string
    {
        return match ($this->review_status) {
            'in_review' => 'Review',
            'approved' => 'Approval',
            'rejected' => 'Reject',
            'corrections_requested' => 'Corrections requested',
            default => ucfirst(str_replace('_', ' ', (string) $this->review_status)),
        };
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
