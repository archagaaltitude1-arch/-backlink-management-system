<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Niche;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Models\Website;
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

        $admin = auth()->user();

        return view('admin.dashboard', [
            'adminName' => $admin?->name ?? 'Admin',
            'insights' => $admin instanceof User ? app(AdminWorkspace::class)->dashboard($admin) : null,
            'stats' => [
                ['label' => 'Total Customers', 'value' => $customers->count(), 'hint' => 'Customer accounts'],
                ['label' => 'Total Staff', 'value' => $staff->count(), 'hint' => 'Staff accounts'],
                ['label' => 'Total Websites', 'value' => Website::query()->count(), 'hint' => 'Publisher websites'],
                ['label' => 'Total Services', 'value' => Service::query()->count(), 'hint' => 'Backlink services'],
                ['label' => 'Total Niches', 'value' => Niche::query()->count(), 'hint' => 'Industry categories'],
                ['label' => 'Total Packages', 'value' => Package::query()->count(), 'hint' => 'Credit packages'],
                ['label' => 'Total Orders', 'value' => Order::query()->count(), 'hint' => 'Orders placed by customers'],
                ['label' => 'Total Payments', 'value' => Payment::query()->count(), 'hint' => 'Payments recorded for orders and credits'],
            ],
            'unassignedItems' => OrderItem::query()
                ->with(['order.customer:id,name', 'assignment.staff:id,name'])
                ->whereDoesntHave('assignment')
                ->latest()
                ->limit(8)
                ->get(),
            'assignmentStaff' => User::query()
                ->where('status', 'active')
                ->whereHas('role', fn ($query) => $query->whereIn('role_name', ['Staff', 'SEO']))
                ->orderBy('name')
                ->get(['id', 'name']),
            'recentOrders' => Order::query()->with(['customer:id,name', 'items.assignment.staff:id,name'])->latest()->limit(5)->get(),
            'recentPayments' => Payment::query()->with(['customer:id,name', 'order:id,order_number'])->latest()->limit(5)->get(),
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
