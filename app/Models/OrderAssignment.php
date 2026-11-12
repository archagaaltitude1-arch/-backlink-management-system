<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'staff_id',
        'assigned_by',
        'status',
        'notes',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Format this assignment for the staff API without changing fulfillment records.
     *
     * @return array<string, mixed>
     */
    public function toPortalArray(): array
    {
        $orderItem = $this->orderItem;
        $order = $orderItem?->order;
        $customer = $order?->customer;
        $website = $orderItem?->website;
        $seoMetric = $website?->seoMetric;
        $service = $orderItem?->service;

        $status = $this->status ?: 'assigned';
        $statusLabel = match ($status) {
            'assigned' => 'Assigned',
            'in_progress' => 'In Progress',
            'published' => 'Published / Verification Ready',
            'completed' => 'Verified & Completed',
            default => ucfirst(str_replace('_', ' ', $status)),
        };

        $monthlyTraffic = $website?->monthly_traffic ?? $seoMetric?->traffic;

        return [
            'assignment_id' => $this->id,
            'id' => $order?->order_number ?? ('ORD-'.($order?->id ?? $this->order_item_id)),
            'order_id' => $order?->id,
            'order_item_id' => $this->order_item_id,
            'customer' => $customer?->name ?? 'Customer',
            'customer_email' => $customer?->email ?? '—',
            'service' => $service?->service_name ?? $orderItem?->item_name ?? 'Backlink Placement',
            'item_name' => $orderItem?->item_name ?? $service?->service_name ?? 'Backlink Placement',
            'website' => $website?->domain ?? $website?->website_name ?? 'Publisher Website',
            'website_name' => $website?->website_name ?? $website?->domain ?? 'Publisher Website',
            'website_url' => $website?->url,
            'domain_authority' => (int) round((float) ($website?->domain_authority ?? $seoMetric?->DA ?? 0)),
            'domain_rating' => (int) round((float) ($website?->domain_rating ?? $seoMetric?->DR ?? 0)),
            'traffic' => $monthlyTraffic !== null ? number_format((int) $monthlyTraffic).'/mo' : '—',
            'niche' => $website?->niche ?? 'General',
            'target_url' => $orderItem?->target_url ?? '',
            'anchor_text' => $orderItem?->anchor_text ?? '',
            'article_topic' => $orderItem?->article_topic ?? '—',
            'requirements' => $orderItem?->requirements,
            'assigned_staff_id' => $this->staff_id,
            'assigned_staff' => $this->staff?->name ?? 'SEO Staff',
            'assigned_by' => $this->assignedBy?->name ?? 'Admin',
            'status' => $status,
            'status_label' => $statusLabel,
            'due_date' => $this->created_at ? $this->created_at->copy()->addDays(7)->format('M d, Y') : '—',
            'assigned_at' => $this->created_at?->format('M d, Y') ?? '—',
            'notes' => $this->notes,
        ];
    }
}
