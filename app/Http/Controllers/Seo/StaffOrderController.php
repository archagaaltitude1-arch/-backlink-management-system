<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Models\OrderAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffOrderController extends Controller
{
    /**
     * Display orders assigned to the currently authenticated SEO staff member.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $statusFilter = $request->query('status', 'all');
        $search = strtolower(trim((string) $request->query('search', '')));

        $assignments = collect();

        if ($user && Schema::hasTable('order_assignments')) {
            $assignments = $user->assignedOrders()
                ->with([
                    'orderItem.order.customer',
                    'orderItem.website.seoMetric',
                    'orderItem.service',
                    'assignedBy',
                    'staff',
                ])
                ->latest()
                ->get();
        }

        $allOrders = $assignments
            ->map(fn (OrderAssignment $assignment): array => $assignment->toPortalArray())
            ->all();

        $filtered = array_filter($allOrders, function (array $order) use ($statusFilter, $search): bool {
            $matchesStatus = match ($statusFilter) {
                'assigned' => $order['status'] === 'assigned',
                'in_progress' => $order['status'] === 'in_progress',
                'published' => $order['status'] === 'published',
                'completed' => $order['status'] === 'completed',
                default => true,
            };

            $matchesSearch = true;
            if ($search !== '') {
                $haystack = strtolower(implode(' ', [
                    $order['id'],
                    $order['website'],
                    $order['website_name'],
                    $order['target_url'],
                    $order['anchor_text'],
                    $order['article_topic'],
                    (string) ($order['requirements'] ?? ''),
                    $order['customer'],
                ]));
                $matchesSearch = str_contains($haystack, $search);
            }

            return $matchesStatus && $matchesSearch;
        });

        return view('seo.assigned-orders', [
            'orders' => array_values($filtered),
            'currentFilter' => $statusFilter,
            'searchTerm' => $search,
            'totalCount' => count($allOrders),
        ]);
    }

    /**
     * Update the progress/status of an order assigned to the authenticated SEO staff member.
     */
    public function updateStatus(Request $request, string $orderId): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['assigned', 'in_progress', 'published', 'completed'])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'assignment_id' => ['nullable', 'integer'],
            'order_item_id' => ['nullable', 'integer'],
        ]);

        abort_unless(Schema::hasTable('order_assignments'), 404);

        $assignment = $this->resolveStaffAssignment($request, $orderId);

        $payload = ['status' => $validated['status']];
        if ($request->has('notes')) {
            $payload['notes'] = $validated['notes'];
        }

        $assignment->update($payload);

        $assignment->load([
            'orderItem.order.customer',
            'orderItem.website.seoMetric',
            'orderItem.service',
            'assignedBy',
            'staff',
        ]);

        $portalOrder = $assignment->toPortalArray();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $portalOrder,
            ]);
        }

        return back()->with(
            'success',
            "Order #{$portalOrder['id']} status updated to {$portalOrder['status_label']}."
        );
    }

    private function resolveStaffAssignment(Request $request, string $orderId): OrderAssignment
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $baseQuery = $user->assignedOrders();

        if ($request->filled('assignment_id')) {
            $assignment = (clone $baseQuery)->whereKey($request->integer('assignment_id'))->first();
            if ($assignment) {
                return $assignment;
            }
        }

        if ($request->filled('order_item_id')) {
            $assignment = (clone $baseQuery)->where('order_item_id', $request->integer('order_item_id'))->first();
            if ($assignment) {
                return $assignment;
            }
        }

        if (ctype_digit($orderId)) {
            $numericId = (int) $orderId;
            $assignment = (clone $baseQuery)
                ->where(function ($query) use ($numericId): void {
                    $query->whereKey($numericId)
                        ->orWhere('order_item_id', $numericId);
                })
                ->first();

            if ($assignment) {
                return $assignment;
            }
        }

        return (clone $baseQuery)
            ->whereHas('orderItem.order', fn ($query) => $query->where('order_number', $orderId))
            ->firstOrFail();
    }
}
