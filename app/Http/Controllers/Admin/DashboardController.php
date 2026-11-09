<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Niche;
use App\Models\Package;
use App\Models\Service;
use App\Models\User;
use App\Models\Website;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show live counts and recent records from the existing tables.
     */
    public function __invoke(): View
    {
        $customers = User::query()->whereHas('role', fn ($query) => $query->where('role_name', 'Customer'));
        $staff = User::query()->whereHas('role', fn ($query) => $query->where('role_name', 'Staff'));

        $orderCount = Schema::hasTable('orders') ? (int) Schema::getConnection()->table('orders')->count() : null;
        $paymentCount = Schema::hasTable('payments') ? (int) Schema::getConnection()->table('payments')->count() : null;

        return view('admin.dashboard', [
            'adminName' => auth()->user()?->name ?? 'Admin',
            'stats' => [
                ['label' => 'Total Customers', 'value' => $customers->count(), 'hint' => 'Customer accounts'],
                ['label' => 'Total Staff', 'value' => $staff->count(), 'hint' => 'Staff accounts'],
                ['label' => 'Total Websites', 'value' => Website::query()->count(), 'hint' => 'Publisher websites'],
                ['label' => 'Total Services', 'value' => Service::query()->count(), 'hint' => 'Backlink services'],
                ['label' => 'Total Niches', 'value' => Niche::query()->count(), 'hint' => 'Industry categories'],
                ['label' => 'Total Packages', 'value' => Package::query()->count(), 'hint' => 'Credit packages'],
                ['label' => 'Total Orders', 'value' => $orderCount ?? '—', 'hint' => $orderCount === null ? 'Orders module is not in this database yet' : 'Order records'],
                ['label' => 'Total Payments', 'value' => $paymentCount ?? '—', 'hint' => $paymentCount === null ? 'Payments module is not in this database yet' : 'Payment records'],
            ],
            'recentCustomers' => $customers->latest()->limit(5)->get(),
            'recentWebsites' => Website::query()->with('niches')->latest()->limit(6)->get(),
            'websiteSummary' => [
                ['label' => 'Total Websites', 'value' => Website::query()->count(), 'tone' => 'text-navy'],
                ['label' => 'Active Websites', 'value' => Website::query()->where('status', 'active')->count(), 'tone' => 'text-emerald-700'],
                ['label' => 'Pending Approval', 'value' => Website::query()->where('status', 'pending')->count(), 'tone' => 'text-brand'],
                ['label' => 'Inactive Websites', 'value' => Website::query()->where('status', 'inactive')->count(), 'tone' => 'text-navy/60'],
            ],
            'services' => Service::query()->orderBy('service_name')->get(),
            'niches' => Niche::query()->orderBy('niche_name')->get(),
            'packages' => Package::query()->orderBy('package_name')->get(),
        ]);
    }
}
