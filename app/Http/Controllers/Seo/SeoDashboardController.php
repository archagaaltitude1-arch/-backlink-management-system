<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Models\OrderAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SeoDashboardController extends Controller
{
    /**
     * Display the SEO Staff Dashboard for the authenticated staff member.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
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

        $orders = $assignments->map(fn (OrderAssignment $assignment): array => $assignment->toPortalArray());

        $stats = [
            'total_assigned' => $orders->count(),
            'in_progress' => $orders->where('status', 'in_progress')->count(),
            'pending_review' => $orders->whereIn('status', ['assigned', 'pending'])->count(),
            'completed' => $orders->where('status', 'completed')->count(),
        ];

        return view('seo.dashboard', [
            'stats' => $stats,
            'orders' => $orders->all(),
            'currentStaff' => $user?->name ?? 'SEO Staff',
        ]);
    }
}
