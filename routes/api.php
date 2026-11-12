<?php

use App\Http\Controllers\Seo\StaffApiController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/stripe/webhook', [StripeWebhookController::class, '__invoke'])->name('stripe.webhook');

/*
|--------------------------------------------------------------------------
| SEO Staff APIs: /api/staff/dashboard, /api/staff/assigned-orders, etc.
|--------------------------------------------------------------------------
*/
Route::prefix('staff')->group(function () {
    Route::get('/dashboard', [StaffApiController::class, 'dashboard'])->name('api.staff.dashboard');
    Route::get('/assigned-orders', [StaffApiController::class, 'assignedOrders'])->name('api.staff.assigned-orders');
});
