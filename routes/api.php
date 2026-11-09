<?php

use App\Http\Controllers\Seo\StaffApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Member 4 - Raweesha Lakruwan
| Staff APIs: /api/staff/dashboard, /api/staff/assigned-orders, etc.
|--------------------------------------------------------------------------
*/
Route::prefix('staff')->group(function () {
    Route::get('/dashboard', [StaffApiController::class, 'dashboard'])->name('api.staff.dashboard');
    Route::get('/assigned-orders', [StaffApiController::class, 'assignedOrders'])->name('api.staff.assigned-orders');
});
