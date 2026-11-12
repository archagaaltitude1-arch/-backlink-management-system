<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Models\OrderAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class StaffApiController extends Controller
{
    /**
     * GET /api/staff/dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $orders = $this->staffOrders($request);

        return response()->json([
            'status' => 'success',
            'staff' => $user?->name ?? 'SEO Staff',
            'role' => 'SEO Staff Dashboard & Backlink Verification',
            'stats' => [
                'total_assigned' => count($orders),
                'in_progress' => count(array_filter($orders, fn (array $o) => $o['status'] === 'in_progress')),
                'pending_review' => count(array_filter($orders, fn (array $o) => in_array($o['status'], ['assigned', 'pending'], true))),
                'published_ready_for_verify' => count(array_filter($orders, fn (array $o) => $o['status'] === 'published')),
                'completed' => count(array_filter($orders, fn (array $o) => $o['status'] === 'completed')),
            ],
            'recent_assigned_orders' => array_slice($orders, 0, 5),
        ]);
    }

    /**
     * GET /api/staff/assigned-orders
     */
    public function assignedOrders(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'orders' => $this->staffOrders($request),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function staffOrders(Request $request): array
    {
        $user = $request->user();

        if (! $user || ! Schema::hasTable('order_assignments')) {
            return [];
        }

        return $user->assignedOrders()
            ->with([
                'orderItem.order.customer',
                'orderItem.website.seoMetric',
                'orderItem.service',
                'assignedBy',
                'staff',
            ])
            ->latest()
            ->get()
            ->map(fn (OrderAssignment $assignment): array => $assignment->toPortalArray())
            ->all();
    }
}
