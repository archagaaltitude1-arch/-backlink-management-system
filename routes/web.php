<?php

use App\Http\Controllers\Seo\SeoDashboardController;
use App\Http\Controllers\Seo\StaffAuthController;
use App\Http\Controllers\Seo\StaffOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/admin/dashboard', 'admin.dashboard')->name('admin.dashboard');
Route::view('/admin/customers', 'admin.section', ['heading' => 'Customer Management', 'summary' => 'Customer accounts for the backlink system.'])->name('admin.customers');
Route::view('/admin/staff', 'admin.section', ['heading' => 'Staff Management', 'summary' => 'Staff accounts and team access.'])->name('admin.staff');
Route::view('/admin/websites', 'admin.section', ['heading' => 'Website Management', 'summary' => 'Publisher websites in the directory.'])->name('admin.websites');
Route::view('/admin/services', 'admin.section', ['heading' => 'Service Management', 'summary' => 'Backlink services offered to customers.'])->name('admin.services');
Route::view('/admin/niches', 'admin.section', ['heading' => 'Niche Management', 'summary' => 'Content niches used across websites and packages.'])->name('admin.niches');
Route::view('/admin/packages', 'admin.section', ['heading' => 'Package Management', 'summary' => 'Service packages and pricing groups.'])->name('admin.packages');
Route::view('/admin/orders', 'admin.section', ['heading' => 'Order Management', 'summary' => 'Orders placed across the system.'])->name('admin.orders');
Route::view('/admin/payments', 'admin.section', ['heading' => 'Payment Management', 'summary' => 'Payment records for completed orders.'])->name('admin.payments');
Route::view('/admin/reports', 'admin.section', ['heading' => 'Reports & Analytics', 'summary' => 'Summary reports for customers, orders, and revenue.'])->name('admin.reports');
Route::view('/admin/settings', 'admin.section', ['heading' => 'Settings', 'summary' => 'System settings for the admin workspace.'])->name('admin.settings');

Route::view('/customer/dashboard', 'customer.dashboard')->name('customer.dashboard');
Route::view('/customer/profile', 'customer.profile')->name('customer.profile');
Route::view('/customer/websites', 'customer.websites')->name('customer.websites');
Route::view('/customer/services', 'customer.services')->name('customer.services');
Route::view('/customer/orders', 'customer.orders')->name('customer.orders');

/*
|--------------------------------------------------------------------------
| Member 4 - Raweesha Lakruwan
| SEO Staff Dashboard, Authentication, Order Management & Backlink Verification
|--------------------------------------------------------------------------
*/
// Staff Authentication
Route::get('/seo/login', [StaffAuthController::class, 'showLogin'])->name('seo.login');
Route::post('/seo/login', [StaffAuthController::class, 'login'])->name('seo.login.post');
Route::post('/seo/logout', [StaffAuthController::class, 'logout'])->name('seo.logout');

// Staff Profile Management
Route::get('/seo/profile', [StaffAuthController::class, 'profile'])->name('seo.profile');
Route::put('/seo/profile', [StaffAuthController::class, 'updateProfile'])->name('seo.profile.update');
Route::put('/seo/profile/password', [StaffAuthController::class, 'updatePassword'])->name('seo.profile.password');

// Staff Dashboard & Order Management
Route::get('/seo/dashboard', [SeoDashboardController::class, 'index'])->name('seo.dashboard');
Route::get('/seo/assigned-orders', [StaffOrderController::class, 'index'])->name('seo.assigned-orders');
Route::post('/seo/orders/{id}/assign', [StaffOrderController::class, 'assign'])->name('seo.orders.assign');
Route::put('/seo/orders/{id}/status', [StaffOrderController::class, 'updateStatus'])->name('seo.orders.status');

// Other Member 4 Views
Route::view('/seo/articles', 'seo.articles')->name('seo.articles');
Route::view('/seo/submissions', 'seo.submissions')->name('seo.submissions');
Route::view('/seo/completed-orders', 'seo.completed-orders')->name('seo.completed-orders');
