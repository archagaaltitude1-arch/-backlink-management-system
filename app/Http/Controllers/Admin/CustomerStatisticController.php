<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerStatisticController extends Controller
{
    public function __invoke(Request $request, AdminWorkspace $workspace): View
    {
        return view('admin.customers.statistics', $workspace->customerStatistics(ReportPeriod::fromRequest($request)));
    }
}
