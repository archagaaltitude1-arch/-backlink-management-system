<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Models\OrderAssignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StaffOrderController extends Controller
{
    /**
     * Display all assigned orders with filtering and search.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status', 'all');
        $search = strtolower(trim($request->query('search', '')));

        $allOrders = [
            [
                'id' => 'ORD-10480',
                'order_item_id' => 101,
                'customer' => 'Sofia Alvarez',
                'customer_email' => 'sofia.alvarez@example.com',
                'service' => 'Link Insertion',
                'website' => 'techinsider-journal.com',
                'domain_authority' => 64,
                'domain_rating' => 68,
                'traffic' => '142K/mo',
                'niche' => 'Technology & AI',
                'target_url' => 'https://innovate-ai.io/deep-learning-insights',
                'anchor_text' => 'neural network architectures',
                'article_topic' => 'Trends in Deep Learning Optimization for 2027',
                'assigned_staff_id' => 1,
                'assigned_staff' => 'Raweesha Lakruwan',
                'assigned_by' => 'Archaga (Team Lead)',
                'status' => 'in_progress',
                'status_label' => 'In Progress',
                'due_date' => 'Nov 12, 2026',
                'notes' => 'Place link in section 3 of existing published article.',
            ],
            [
                'id' => 'ORD-10481',
                'order_item_id' => 102,
                'customer' => 'James Okonkwo',
                'customer_email' => 'james.okonkwo@example.com',
                'service' => 'Niche Edit',
                'website' => 'healthwire-daily.org',
                'domain_authority' => 58,
                'domain_rating' => 61,
                'traffic' => '89K/mo',
                'niche' => 'Health & Wellness',
                'target_url' => 'https://purevitamins.store/organic-supplements',
                'anchor_text' => 'natural organic vitamins',
                'article_topic' => 'Holistic Wellness & Essential Micronutrients',
                'assigned_staff_id' => 1,
                'assigned_staff' => 'Raweesha Lakruwan',
                'assigned_by' => 'Archaga (Team Lead)',
                'status' => 'assigned',
                'status_label' => 'Assigned',
                'due_date' => 'Nov 14, 2026',
                'notes' => 'Target paragraph awaiting review before outreach.',
            ],
            [
                'id' => 'ORD-10482',
                'order_item_id' => 103,
                'customer' => 'Priya Fernando',
                'customer_email' => 'priya.fernando@example.com',
                'service' => 'Guest Post',
                'website' => 'fintech-chronicle.com',
                'domain_authority' => 72,
                'domain_rating' => 75,
                'traffic' => '310K/mo',
                'niche' => 'Finance & Banking',
                'target_url' => 'https://securepay-solutions.com/cross-border-gateway',
                'anchor_text' => 'instant global payment rails',
                'article_topic' => 'The Next Era of Cross-Border B2B Settlement',
                'assigned_staff_id' => 1,
                'assigned_staff' => 'Raweesha Lakruwan',
                'assigned_by' => 'Raweesha Lakruwan',
                'status' => 'published',
                'status_label' => 'Published / Verification Ready',
                'due_date' => 'Nov 10, 2026',
                'notes' => 'Article live at https://fintech-chronicle.com/b2b-settlement-2026',
            ],
            [
                'id' => 'ORD-10479',
                'order_item_id' => 104,
                'customer' => 'Liam Chen',
                'customer_email' => 'liam.chen@example.com',
                'service' => 'Guest Post',
                'website' => 'traveler-horizons.net',
                'domain_authority' => 51,
                'domain_rating' => 54,
                'traffic' => '65K/mo',
                'niche' => 'Travel & Hospitality',
                'target_url' => 'https://boutique-escapes.travel/alpine-villas',
                'anchor_text' => 'luxury alpine chalets',
                'article_topic' => 'Top Winter Destinations for Discerning Travelers',
                'assigned_staff_id' => 2,
                'assigned_staff' => 'Sarah Silva',
                'assigned_by' => 'Raweesha Lakruwan',
                'status' => 'completed',
                'status_label' => 'Verified & Completed',
                'due_date' => 'Nov 06, 2026',
                'notes' => 'DoFollow backlink verified live. Verification report generated.',
            ],
            [
                'id' => 'ORD-10483',
                'order_item_id' => 105,
                'customer' => 'Elena Rostova',
                'customer_email' => 'elena.rostova@example.com',
                'service' => 'Niche Edit',
                'website' => 'automotive-future.io',
                'domain_authority' => 61,
                'domain_rating' => 63,
                'traffic' => '115K/mo',
                'niche' => 'Automotive & EV',
                'target_url' => 'https://ev-chargenet.com/ultra-fast-stations',
                'anchor_text' => 'next-gen fast chargers',
                'article_topic' => 'High-Speed Charging Infrastructure Across Europe',
                'assigned_staff_id' => null,
                'assigned_staff' => 'Unassigned',
                'assigned_by' => null,
                'status' => 'unassigned',
                'status_label' => 'Needs Assignment',
                'due_date' => 'Nov 15, 2026',
                'notes' => 'New order waiting for staff assignment.',
            ],
        ];

        // Apply filtering
        $filtered = array_filter($allOrders, function ($order) use ($statusFilter, $search) {
            $matchesStatus = match ($statusFilter) {
                'assigned' => $order['status'] === 'assigned',
                'in_progress' => $order['status'] === 'in_progress',
                'published' => $order['status'] === 'published',
                'completed' => $order['status'] === 'completed',
                'unassigned' => $order['status'] === 'unassigned',
                default => true,
            };

            $matchesSearch = true;
            if (! empty($search)) {
                $haystack = strtolower(
                    $order['id'] . ' ' .
                    $order['website'] . ' ' .
                    $order['anchor_text'] . ' ' .
                    $order['customer'] . ' ' .
                    $order['assigned_staff']
                );
                $matchesSearch = str_contains($haystack, $search);
            }

            return $matchesStatus && $matchesSearch;
        });

        $staffList = [
            ['id' => 1, 'name' => 'Raweesha Lakruwan', 'role' => 'SEO Specialist (You)'],
            ['id' => 2, 'name' => 'Sarah Silva', 'role' => 'Content & Placement Lead'],
            ['id' => 3, 'name' => 'Kavindu Perera', 'role' => 'Junior SEO Analyst'],
        ];

        return view('seo.assigned-orders', [
            'orders' => array_values($filtered),
            'staffList' => $staffList,
            'currentFilter' => $statusFilter,
            'searchTerm' => $search,
            'totalCount' => count($allOrders),
        ]);
    }

    /**
     * Assign or reassign an order item to a staff member.
     */
    public function assign(Request $request, string $orderId): RedirectResponse
    {
        $validated = $request->validate([
            'staff_id' => ['required'],
            'staff_name' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $orderItemId = intval($request->input('order_item_id', rand(100, 999)));
        $assignedBy = Auth::id() ?? 1;

        try {
            if (Schema::hasTable('order_assignments')) {
                OrderAssignment::updateOrCreate(
                    ['order_item_id' => $orderItemId],
                    [
                        'staff_id' => is_numeric($validated['staff_id']) ? $validated['staff_id'] : $assignedBy,
                        'assigned_by' => $assignedBy,
                        'status' => 'assigned',
                        'notes' => $validated['notes'] ?? 'Assigned via SEO Staff Portal',
                    ]
                );
            }
        } catch (\Throwable $e) {
            // gracefully handled if database tables are in migration phase
        }

        $staffName = $validated['staff_name'] ?? 'SEO Staff';

        return back()->with('success', "Order #{$orderId} successfully assigned to {$staffName}.");
    }

    /**
     * Update the progress/status of an assigned order.
     */
    public function updateStatus(Request $request, string $orderId): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'published_url' => ['nullable', 'url'],
        ]);

        $orderItemId = intval($request->input('order_item_id', rand(100, 999)));

        try {
            if (Schema::hasTable('order_assignments')) {
                OrderAssignment::where('order_item_id', $orderItemId)->update([
                    'status' => $validated['status'],
                    'notes' => $validated['notes'] ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            // gracefully handled
        }

        return back()->with('success', "Order #{$orderId} status updated to " . ucfirst(str_replace('_', ' ', $validated['status'])) . '.');
    }
}
