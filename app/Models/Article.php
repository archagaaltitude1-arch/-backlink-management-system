<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Article extends Model
{
    protected $fillable = [
        'order_item_id',
        'staff_id',
        'title',
        'body',
        'status',
        'staff_notes',
        'feedback',
        'published_url',
        'submitted_at',
        'approved_at',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ArticleReview::class);
    }

    public function latestReview(): HasOne
    {
        return $this->hasOne(ArticleReview::class)->latestOfMany();
    }

    public function canBeSentToAdmin(): bool
    {
        return in_array($this->status, ['corrections_requested', 'rejected'], true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'pending_admin' => 'Awaiting Admin Approval',
            'in_review' => 'In Review',
            'corrections_requested' => 'Corrections Requested',
            'rejected' => 'Rejected',
            'admin_approved' => 'Admin Approved',
            'published' => 'Published',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function wordCount(): int
    {
        return str_word_count(strip_tags($this->body));
    }

    /**
     * @return list<string>
     */
    public function paragraphs(): array
    {
        $lines = preg_split("/\r\n|\n|\r/", trim($this->body)) ?: [];

        return array_values(array_filter(array_map(trim(...), $lines), fn (string $line): bool => $line !== ''));
    }
}
