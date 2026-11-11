<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request, AdminWorkspace $workspace): View
    {
        $admin = $request->user();
        $workspace->sync($admin);

        return view('admin.notifications.index', [
            'notifications' => $admin->notifications()->where('type', 'admin.insight')->latest()->paginate(15),
            'unread' => $admin->unreadNotifications()->where('type', 'admin.insight')->count(),
        ]);
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $record = $request->user()
            ->notifications()
            ->where('type', 'admin.insight')
            ->whereKey($notification)
            ->firstOrFail();

        $record->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }
}
