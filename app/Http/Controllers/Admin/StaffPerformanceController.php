<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffPerformanceController extends Controller
{
    public function __invoke(Request $request, AdminWorkspace $workspace): View
    {
        $period = ReportPeriod::fromRequest($request);

        return view('admin.staff-performance.index', [
            'period' => $period,
            'staff' => $workspace->staffPerformance($period),
        ]);
    }
}
