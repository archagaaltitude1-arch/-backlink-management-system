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

        if (! $user->canAccessSeoPortal()) {
            return redirect()->route('seo.login')->withErrors([
                'email' => 'This account cannot access the SEO staff portal. Sign in with a staff or admin account.',
            ]);
        }

        $cards = $orders->cards($user);

        return view('seo.dashboard', [
            'stats' => [
                'total_assigned' => $cards->where('status', '!=', 'unassigned')->count(),
                'in_progress' => $cards->where('status', 'in_progress')->count(),
                'pending_review' => $cards->where('status', 'published')->count(),
                'completed' => $cards->where('status', 'completed')->count(),
            ],
            'orders' => $cards,
            'currentStaff' => $user->name,
        ]);
    }
}
