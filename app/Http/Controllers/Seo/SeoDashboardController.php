<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Models\OrderAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SeoDashboardController extends Controller
{
    /**
     * Display the SEO Staff Dashboard.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Sample / seed data aligned with Phase 2 parallel development
        $sampleOrders = [
            [
                'id' => 'ORD-10480',
                'order_item_id' => 101,
                'customer' => 'Sofia Alvarez',
                'service' => 'Link Insertion',
                'website' => 'techinsider-journal.com',
                'domain_authority' => 64,
                'domain_rating' => 68,
                'niche' => 'Technology & AI',
                'target_url' => 'https://innovate-ai.io/deep-learning-insights',
                'anchor_text' => 'neural network architectures',
                'assigned_staff' => 'Raweesha Lakruwan',
                'assigned_by' => 'Archaga (Team Lead)',
                'status' => 'in_progress',
                'status_label' => 'In Progress',
                'due_date' => 'Nov 12, 2026',
                'article_status' => 'Ready for Placement',
            ],
            [
                'id' => 'ORD-10481',
                'order_item_id' => 102,
                'customer' => 'James Okonkwo',
                'service' => 'Niche Edit',
                'website' => 'healthwire-daily.org',
                'domain_authority' => 58,
                'domain_rating' => 61,
                'niche' => 'Health & Wellness',
                'target_url' => 'https://purevitamins.store/organic-supplements',
                'anchor_text' => 'natural organic vitamins',
                'assigned_staff' => 'Raweesha Lakruwan',
                'assigned_by' => 'Archaga (Team Lead)',
                'status' => 'assigned',
                'status_label' => 'Assigned',
                'due_date' => 'Nov 14, 2026',
                'article_status' => 'Pending Review',
            ],
            [
                'id' => 'ORD-10482',
                'order_item_id' => 103,
                'customer' => 'Priya Fernando',
                'service' => 'Guest Post',
                'website' => 'fintech-chronicle.com',
                'domain_authority' => 72,
                'domain_rating' => 75,
                'niche' => 'Finance & Banking',
                'target_url' => 'https://securepay-solutions.com/cross-border-gateway',
                'anchor_text' => 'instant global payment rails',
                'assigned_staff' => 'Raweesha Lakruwan',
                'assigned_by' => 'Raweesha Lakruwan',
                'status' => 'published',
                'status_label' => 'Published / Verification Ready',
                'due_date' => 'Nov 10, 2026',
                'article_status' => 'Published on Site',
            ],
            [
                'id' => 'ORD-10479',
                'order_item_id' => 104,
                'customer' => 'Liam Chen',
                'service' => 'Guest Post',
                'website' => 'traveler-horizons.net',
                'domain_authority' => 51,
                'domain_rating' => 54,
                'niche' => 'Travel & Hospitality',
                'target_url' => 'https://boutique-escapes.travel/alpine-villas',
                'anchor_text' => 'luxury alpine chalets',
                'assigned_staff' => 'Sarah Silva',
                'assigned_by' => 'Raweesha Lakruwan',
                'status' => 'completed',
                'status_label' => 'Verified & Completed',
                'due_date' => 'Nov 06, 2026',
                'article_status' => 'Verified Live',
            ],
            [
                'id' => 'ORD-10483',
                'order_item_id' => 105,
                'customer' => 'Elena Rostova',
                'service' => 'Niche Edit',
                'website' => 'automotive-future.io',
                'domain_authority' => 61,
                'domain_rating' => 63,
                'niche' => 'Automotive & EV',
                'target_url' => 'https://ev-chargenet.com/ultra-fast-stations',
                'anchor_text' => 'next-gen fast chargers',
                'assigned_staff' => 'Unassigned',
                'assigned_by' => null,
                'status' => 'unassigned',
                'status_label' => 'Needs Assignment',
                'due_date' => 'Nov 15, 2026',
                'article_status' => 'Awaiting Staff',
            ],
        ];

        // If order_assignments table exists and has rows, load real assignments
        $dbAssignmentsCount = 0;
        try {
            if (Schema::hasTable('order_assignments')) {
                $dbAssignmentsCount = OrderAssignment::count();
            }
        } catch (\Throwable $e) {
            $dbAssignmentsCount = 0;
        }

        $stats = [
            'total_assigned' => count(array_filter($sampleOrders, fn ($o) => $o['status'] !== 'unassigned')),
            'in_progress' => count(array_filter($sampleOrders, fn ($o) => $o['status'] === 'in_progress')),
            'pending_review' => count(array_filter($sampleOrders, fn ($o) => $o['status'] === 'assigned')),
            'completed' => count(array_filter($sampleOrders, fn ($o) => $o['status'] === 'completed')),
        ];

        $staffMembers = [
            ['id' => 1, 'name' => 'Raweesha Lakruwan', 'role' => 'SEO Specialist & Verifier', 'active_orders' => 3],
            ['id' => 2, 'name' => 'Sarah Silva', 'role' => 'Content & Placement Lead', 'active_orders' => 2],
            ['id' => 3, 'name' => 'Kavindu Perera', 'role' => 'Junior SEO Analyst', 'active_orders' => 1],
        ];

        return view('seo.dashboard', [
            'stats' => $stats,
            'orders' => $sampleOrders,
            'staffMembers' => $staffMembers,
            'currentStaff' => $user->name ?? 'Raweesha Lakruwan',
        ]);
    }
}
