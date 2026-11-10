<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditPricing;
use App\Models\Niche;
use App\Models\Package;
use App\Models\SeoMetric;
use App\Models\Service;
use App\Models\User;
use App\Models\Website;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __invoke(): View
    {
        $customers = User::query()->whereHas('role', fn ($query) => $query->where('role_name', 'Customer'));
        $staff = User::query()->whereHas('role', fn ($query) => $query->whereNotIn('role_name', ['Customer', 'Admin']));

        return view('admin.reports.index', [
            'customerCount' => (clone $customers)->count(),
            'activeCustomers' => (clone $customers)->where('status', 'active')->count(),
            'staffCount' => (clone $staff)->count(),
            'activeStaff' => (clone $staff)->where('status', 'active')->count(),
            'websiteCount' => Website::query()->count(),
            'activeWebsites' => Website::query()->where('status', 'active')->count(),
            'pendingWebsites' => Website::query()->where('status', 'pending')->count(),
            'serviceCount' => Service::query()->count(),
            'nicheCount' => Niche::query()->count(),
            'packageCount' => Package::query()->count(),
            'activeCreditPrices' => CreditPricing::query()->where('status', 'active')->count(),
            'metrics' => SeoMetric::query()->selectRaw('ROUND(AVG(`DR`), 2) as avg_dr, ROUND(AVG(`DA`), 2) as avg_da, ROUND(AVG(`traffic`), 0) as avg_traffic')->first(),
            'topWebsites' => SeoMetric::query()->with('website')->orderByDesc('DR')->limit(5)->get(),
            'services' => Service::query()->orderBy('service_name')->get(),
            'packages' => Package::query()->orderBy('package_name')->get(),
            'hasOrders' => Schema::hasTable('orders'),
            'hasPayments' => Schema::hasTable('payments'),
        ]);
    }
}
