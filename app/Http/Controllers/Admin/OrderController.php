<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderAssignment;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * @var list<string>
     */
    private array $orderStatuses = ['pending', 'awaiting_approval', 'processing', 'completed'];

    /**
     * @var array<string, string>
     */
    private array $assignmentFilters = [
        'unassigned' => 'Needs SEO staff',
        'assigned' => 'Assigned',
        'in_progress' => 'In progress',
        'published' => 'Waiting for approval',
        'completed' => 'Approved',
    ];

    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $orderStatus = $this->allowed($request->string('order_status')->toString(), $this->orderStatuses);
        $assignment = $this->allowed($request->string('assignment')->toString(), array_keys($this->assignmentFilters));

        $orders = Order::query()
            ->with(['customer:id,name,email', 'items.assignment.staff:id,name', 'items.website:id,website_name,domain'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('items', function ($query) use ($search): void {
                            $query->where('item_name', 'like', "%{$search}%")
                                ->orWhere('anchor_text', 'like', "%{$search}%")
                                ->orWhere('target_url', 'like', "%{$search}%");
                        });
                });
            })
            ->when($orderStatus !== '', fn ($query) => $query->where('order_status', $orderStatus))
            ->when($assignment === 'unassigned', fn ($query) => $query->whereHas(
                'items',
                fn ($query) => $query->whereDoesntHave('assignment'),
            ))
            ->when($assignment !== '' && $assignment !== 'unassigned', fn ($query) => $query->whereHas(
                'items.assignment',
                fn ($query) => $query->where('status', $assignment),
            ))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'staff' => $this->staffMembers(),
            'search' => $search,
            'orderStatus' => $orderStatus,
            'assignment' => $assignment,
            'orderStatuses' => $this->orderStatuses,
            'assignmentFilters' => $this->assignmentFilters,
            'orderCount' => Order::query()->count(),
            'needsStaffCount' => OrderItem::query()->whereDoesntHave('assignment')->count(),
            'withStaffCount' => OrderAssignment::query()->whereIn('status', ['assigned', 'in_progress'])->count(),
            'awaitingApprovalCount' => OrderAssignment::query()->where('status', 'published')->count(),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['customer', 'items.assignment.staff', 'items.website', 'payments', 'invoice']);

        return view('admin.orders.show', [
            'order' => $order,
            'staff' => $this->staffMembers(),
        ]);
    }

    public function approve(Order $order): RedirectResponse
    {
        $approved = DB::transaction(function () use ($order): bool {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($lockedOrder->payment_status !== 'paid' || $lockedOrder->order_status !== 'awaiting_approval') {
                return false;
            }

            $lockedOrder->update(['order_status' => 'processing']);

            return true;
        });

        return back()->with($approved ? 'success' : 'error', $approved
            ? 'Order approved. You can now assign the content work.'
            : 'Only paid orders awaiting approval can be approved.');
    }

    public function assign(Request $request, Order $order, OrderItem $orderItem): RedirectResponse
    {
        abort_unless($orderItem->order_id === $order->id, 404);

        if ($order->payment_status !== 'paid' || ! in_array($order->order_status, ['processing', 'completed'], true)) {
            return back()->withErrors(['staff_id' => 'Approve the paid order before assigning content work.']);
        }

        $data = $request->validate([
            'staff_id' => ['required', 'integer'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $staff = $this->staffMembers()->firstWhere('id', (int) $data['staff_id']);
        if ($staff === null) {
            return back()->withErrors(['staff_id' => 'Choose an active staff member.'])->withInput();
        }

        OrderAssignment::query()->updateOrCreate(
            ['order_item_id' => $orderItem->id],
            [
                'staff_id' => $staff->id,
                'assigned_by' => $request->user()->id,
                'status' => 'assigned',
                'notes' => $data['notes'] ?? null,
            ],
        );

        return back()->with('success', 'Assigned '.$orderItem->item_name.' to '.$staff->name.'.');
    }

    /**
     * @return Collection<int, User>
     */
    private function staffMembers(): Collection
    {
        return User::query()
            ->where('status', 'active')
            ->whereHas('role', fn ($query) => $query->whereIn('role_name', ['Staff', 'SEO']))
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  list<string>  $allowed
     */
    private function allowed(string $value, array $allowed): string
    {
        return in_array($value, $allowed, true) ? $value : '';
    }
}
