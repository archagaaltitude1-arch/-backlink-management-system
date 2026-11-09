<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Models\OrderAssignment;
use App\Models\StaffProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class StaffApiController extends Controller
{
    /**
     * GET /api/staff/dashboard
     */
    public function dashboard(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'member' => 'Member 4 - Raweesha Lakruwan',
            'role' => 'SEO Staff Dashboard & Backlink Verification',
            'stats' => [
                'total_assigned' => 4,
                'in_progress' => 1,
                'pending_review' => 1,
                'published_ready_for_verify' => 1,
                'completed' => 1,
            ],
            'recent_assigned_orders' => [
                [
                    'order_id' => 'ORD-10480',
                    'order_item_id' => 101,
                    'website' => 'techinsider-journal.com',
                    'anchor_text' => 'neural network architectures',
                    'target_url' => 'https://innovate-ai.io/deep-learning-insights',
                    'status' => 'in_progress',
                ],
                [
                    'order_id' => 'ORD-10481',
                    'order_item_id' => 102,
                    'website' => 'healthwire-daily.org',
                    'anchor_text' => 'natural organic vitamins',
                    'target_url' => 'https://purevitamins.store/organic-supplements',
                    'status' => 'assigned',
                ],
            ],
        ]);
    }

    /**
     * GET /api/staff/assigned-orders
     */
    public function assignedOrders(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'orders' => [
                [
                    'id' => 'ORD-10480',
                    'order_item_id' => 101,
                    'customer' => 'Sofia Alvarez',
                    'website' => 'techinsider-journal.com',
                    'service' => 'Link Insertion',
                    'anchor_text' => 'neural network architectures',
                    'target_url' => 'https://innovate-ai.io/deep-learning-insights',
                    'status' => 'in_progress',
                ],
                [
                    'id' => 'ORD-10481',
                    'order_item_id' => 102,
                    'customer' => 'James Okonkwo',
                    'website' => 'healthwire-daily.org',
                    'service' => 'Niche Edit',
                    'anchor_text' => 'natural organic vitamins',
                    'target_url' => 'https://purevitamins.store/organic-supplements',
                    'status' => 'assigned',
                ],
                [
                    'id' => 'ORD-10482',
                    'order_item_id' => 103,
                    'customer' => 'Priya Fernando',
                    'website' => 'fintech-chronicle.com',
                    'service' => 'Guest Post',
                    'anchor_text' => 'instant global payment rails',
                    'target_url' => 'https://securepay-solutions.com/cross-border-gateway',
                    'status' => 'published',
                ],
            ],
        ]);
    }
}
