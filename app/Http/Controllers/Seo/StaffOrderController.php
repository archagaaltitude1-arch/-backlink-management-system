<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Models\CustomerProject;
use App\Models\OrderAssignment;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffOrderController extends Controller
{
    public function index(Request $request): View
    {
        $viewer = $this->fulfillmentUser($request);
        $statusFilter = $request->string('status')->toString() ?: 'all';
        $search = trim($request->string('search')->toString());

        $orders = $this->cards($viewer)
            ->when($statusFilter !== 'all' && $statusFilter !== '', fn (Collection $orders) => $orders->where('status', $statusFilter))
            ->when($search !== '', function (Collection $orders) use ($search) {
                $needle = mb_strtolower($search);

                return $orders->filter(fn (array $order): bool => str_contains(mb_strtolower(implode(' ', [
                    $order['id'],
                    $order['customer'],
                    $order['customer_email'],
                    $order['service'],
                    $order['website'],
                    $order['anchor_text'],
                    $order['target_url'],
                    $order['assigned_staff'],
                ])), $needle));
            })
            ->values();

        return view('seo.assigned-orders', [
            'orders' => $orders,
            'currentFilter' => $statusFilter,
            'searchTerm' => $search,
        ]);
    }

    public function updateStatus(Request $request, string $orderId): RedirectResponse
    {
        $viewer = $this->fulfillmentUser($request);
        $data = $request->validate([
            'order_item_id' => ['required', 'integer', 'exists:order_items,id'],
            'status' => ['required', Rule::in(['assigned', 'in_progress', 'published'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'published_url' => ['nullable', 'url', 'max:2000', 'required_if:status,published'],
        ]);

        $assignment = OrderAssignment::query()->where('order_item_id', $data['order_item_id'])->firstOrFail();
        if ($viewer->role?->role_name !== 'Admin' && $assignment->staff_id !== $viewer->id) {
            abort(403);
        }

        $assignment->update([
            'status' => $data['status'],
            'notes' => $data['notes'] ?? $assignment->notes,
        ]);

        if ($data['status'] === 'published' && ! $this->publishBacklink($assignment, $data['published_url'])) {
            return back()->withErrors(['published_url' => 'This order has no customer account to approve the backlink.'])->withInput();
        }

        return back()->with('success', 'Order '.$orderId.' is now '.str_replace('_', ' ', $data['status']).'.');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function cards(User $viewer): Collection
    {
        $items = OrderItem::query()
            ->with(['order.customer', 'assignment.staff', 'assignment.assignedBy', 'website', 'project'])
            ->when($viewer->role?->role_name !== 'Admin', function ($query) use ($viewer): void {
                $query->whereHas('assignment', fn ($query) => $query->where('staff_id', $viewer->id));
            })
            ->latest()
            ->get();

        return $items->map(fn (OrderItem $item): array => $this->card($item));
    }

    private function publishBacklink(OrderAssignment $assignment, string $publishedUrl): bool
    {
        $item = $assignment->orderItem()->with('order')->firstOrFail();
        $order = $item->order;
        if ($order?->customer_id === null) {
            return false;
        }

        $project = CustomerProject::query()->where('order_item_id', $item->id)->first()
            ?? CustomerProject::query()->where('order_id', $order->id)->whereNull('order_item_id')->first()
            ?? new CustomerProject;

        $project->fill([
            'customer_id' => $order->customer_id,
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'name' => $item->item_name,
            'status' => 'published',
            'target_url' => $item->target_url,
            'published_url' => $publishedUrl,
            'verification_status' => 'pending',
            'completed_at' => null,
        ])->save();

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    private function card(OrderItem $item): array
    {
        $status = $item->assignment?->status ?? 'unassigned';

        return [
            'id' => $item->order?->order_number ?? 'Item '.$item->id,
            'order_item_id' => $item->id,
            'customer' => $item->order?->customer?->name ?? 'Guest checkout',
            'customer_email' => $item->order?->customer?->email ?? '',
            'service' => $item->item_name,
            'website' => $item->website?->domain ?? 'No publisher selected',
            'domain_authority' => $item->website?->domain_authority ?? '—',
            'domain_rating' => $item->website?->domain_rating ?? '—',
            'traffic' => $item->website?->monthly_traffic !== null ? number_format((int) $item->website->monthly_traffic) : '—',
            'niche' => $item->website?->niche ?? '—',
            'target_url' => $item->target_url,
            'anchor_text' => $item->anchor_text,
            'article_topic' => $item->article_topic,
            'requirements' => $item->requirements,
            'assigned_staff_id' => $item->assignment?->staff_id,
            'assigned_staff' => $item->assignment?->staff?->name ?? 'Unassigned',
            'assigned_by' => $item->assignment?->assignedBy?->name,
            'status' => $status,
            'status_label' => match ($status) {
                'in_progress' => 'In Progress',
                'assigned' => 'Assigned',
                'published' => 'Published / Ready for approval',
                'completed' => 'Approved by customer',
                'reassigned' => 'Reassigned',
                default => 'Needs assignment',
            },
            'due_date' => $item->order?->created_at?->addDays(7)->format('M d, Y') ?? '—',
            'notes' => $item->assignment?->notes,
            'published_url' => $item->project?->published_url,
            'verification_status' => $item->project?->verification_status,
        ];
    }

    private function fulfillmentUser(Request $request): User
    {
        $user = $request->user();
        if ($user === null) {
            throw new HttpResponseException(redirect()->guest(route('seo.login')));
        }

        $user->loadMissing('role');
        abort_unless(in_array($user->role?->role_name, ['Admin', 'Staff', 'SEO'], true), 403);

        return $user;
    }
}
