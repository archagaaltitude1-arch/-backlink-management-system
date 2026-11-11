<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoDashboardController extends Controller
{
    public function index(Request $request, StaffOrderController $orders): View|RedirectResponse
    {
        $user = $request->user();
        if ($user === null) {
            throw new HttpResponseException(redirect()->guest(route('seo.login')));
        }

        $user->loadMissing('role');
        abort_unless(in_array($user->role?->role_name, ['Admin', 'Staff', 'SEO'], true), 403);

        $cards = $orders->cards($user);
        $staffMembers = $orders->staffList();

        return view('seo.dashboard', [
            'stats' => [
                'total_assigned' => $cards->where('status', '!=', 'unassigned')->count(),
                'in_progress' => $cards->where('status', 'in_progress')->count(),
                'pending_review' => $cards->where('status', 'published')->count(),
                'completed' => $cards->where('status', 'completed')->count(),
            ],
            'orders' => $cards->take(8),
            'unassignedItems' => $orders->unassignedItems(),
            'staffMembers' => $staffMembers,
            'currentStaff' => $user->name,
        ]);
    }
}
