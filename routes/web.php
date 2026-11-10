<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\CreditPricingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NicheController;
use App\Http\Controllers\Admin\NicheSpecificationController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\WebsiteController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', DashboardController::class)->name('admin.dashboard');
    Route::get('/admin/customers', [AccountController::class, 'index'])->defaults('audience', 'customer')->name('admin.customers');
    Route::get('/admin/customers/create', [AccountController::class, 'create'])->defaults('audience', 'customer')->name('admin.customers.create');
    Route::post('/admin/customers', [AccountController::class, 'store'])->defaults('audience', 'customer')->name('admin.customers.store');
    Route::get('/admin/customers/{user}', [AccountController::class, 'show'])->defaults('audience', 'customer')->name('admin.customers.show');
    Route::get('/admin/customers/{user}/edit', [AccountController::class, 'edit'])->defaults('audience', 'customer')->name('admin.customers.edit');
    Route::put('/admin/customers/{user}', [AccountController::class, 'update'])->defaults('audience', 'customer')->name('admin.customers.update');
    Route::delete('/admin/customers/{user}', [AccountController::class, 'destroy'])->defaults('audience', 'customer')->name('admin.customers.destroy');

    Route::get('/admin/staff', [AccountController::class, 'index'])->defaults('audience', 'staff')->name('admin.staff');
    Route::get('/admin/staff/create', [AccountController::class, 'create'])->defaults('audience', 'staff')->name('admin.staff.create');
    Route::post('/admin/staff', [AccountController::class, 'store'])->defaults('audience', 'staff')->name('admin.staff.store');
    Route::get('/admin/staff/{user}', [AccountController::class, 'show'])->defaults('audience', 'staff')->name('admin.staff.show');
    Route::get('/admin/staff/{user}/edit', [AccountController::class, 'edit'])->defaults('audience', 'staff')->name('admin.staff.edit');
    Route::put('/admin/staff/{user}', [AccountController::class, 'update'])->defaults('audience', 'staff')->name('admin.staff.update');
    Route::delete('/admin/staff/{user}', [AccountController::class, 'destroy'])->defaults('audience', 'staff')->name('admin.staff.destroy');

    Route::get('/admin/roles', [RoleController::class, 'index'])->name('admin.roles');
    Route::get('/admin/roles/create', [RoleController::class, 'create'])->name('admin.roles.create');
    Route::post('/admin/roles', [RoleController::class, 'store'])->name('admin.roles.store');
    Route::get('/admin/roles/{role}', [RoleController::class, 'show'])->name('admin.roles.show');
    Route::get('/admin/roles/{role}/edit', [RoleController::class, 'edit'])->name('admin.roles.edit');
    Route::put('/admin/roles/{role}', [RoleController::class, 'update'])->name('admin.roles.update');
    Route::delete('/admin/roles/{role}', [RoleController::class, 'destroy'])->name('admin.roles.destroy');

    Route::get('/admin/permissions/create', [PermissionController::class, 'create'])->name('admin.permissions.create');
    Route::post('/admin/permissions', [PermissionController::class, 'store'])->name('admin.permissions.store');
    Route::get('/admin/permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('admin.permissions.edit');
    Route::put('/admin/permissions/{permission}', [PermissionController::class, 'update'])->name('admin.permissions.update');
    Route::delete('/admin/permissions/{permission}', [PermissionController::class, 'destroy'])->name('admin.permissions.destroy');

    Route::get('/admin/websites', [WebsiteController::class, 'index'])->name('admin.websites');
    Route::get('/admin/websites/create', [WebsiteController::class, 'create'])->name('admin.websites.create');
    Route::post('/admin/websites', [WebsiteController::class, 'store'])->name('admin.websites.store');
    Route::get('/admin/websites/{website}', [WebsiteController::class, 'show'])->name('admin.websites.show');
    Route::get('/admin/websites/{website}/edit', [WebsiteController::class, 'edit'])->name('admin.websites.edit');
    Route::put('/admin/websites/{website}', [WebsiteController::class, 'update'])->name('admin.websites.update');
    Route::delete('/admin/websites/{website}', [WebsiteController::class, 'destroy'])->name('admin.websites.destroy');

    Route::get('/admin/services', [ServiceController::class, 'index'])->name('admin.services');
    Route::get('/admin/services/create', [ServiceController::class, 'create'])->name('admin.services.create');
    Route::post('/admin/services', [ServiceController::class, 'store'])->name('admin.services.store');
    Route::get('/admin/services/{service}', [ServiceController::class, 'show'])->name('admin.services.show');
    Route::get('/admin/services/{service}/edit', [ServiceController::class, 'edit'])->name('admin.services.edit');
    Route::put('/admin/services/{service}', [ServiceController::class, 'update'])->name('admin.services.update');
    Route::delete('/admin/services/{service}', [ServiceController::class, 'destroy'])->name('admin.services.destroy');
    Route::get('/admin/niches', [NicheController::class, 'index'])->name('admin.niches');
    Route::get('/admin/niches/create', [NicheController::class, 'create'])->name('admin.niches.create');
    Route::post('/admin/niches', [NicheController::class, 'store'])->name('admin.niches.store');
    Route::get('/admin/niches/{niche}', [NicheController::class, 'show'])->name('admin.niches.show');
    Route::get('/admin/niches/{niche}/edit', [NicheController::class, 'edit'])->name('admin.niches.edit');
    Route::put('/admin/niches/{niche}', [NicheController::class, 'update'])->name('admin.niches.update');
    Route::delete('/admin/niches/{niche}', [NicheController::class, 'destroy'])->name('admin.niches.destroy');
    Route::get('/admin/niche-specifications/create', [NicheSpecificationController::class, 'create'])->name('admin.niche-specifications.create');
    Route::post('/admin/niche-specifications', [NicheSpecificationController::class, 'store'])->name('admin.niche-specifications.store');
    Route::get('/admin/niche-specifications/{nicheSpecification}/edit', [NicheSpecificationController::class, 'edit'])->name('admin.niche-specifications.edit');
    Route::put('/admin/niche-specifications/{nicheSpecification}', [NicheSpecificationController::class, 'update'])->name('admin.niche-specifications.update');
    Route::delete('/admin/niche-specifications/{nicheSpecification}', [NicheSpecificationController::class, 'destroy'])->name('admin.niche-specifications.destroy');

    Route::get('/admin/packages', [PackageController::class, 'index'])->name('admin.packages');
    Route::get('/admin/packages/create', [PackageController::class, 'create'])->name('admin.packages.create');
    Route::post('/admin/packages', [PackageController::class, 'store'])->name('admin.packages.store');
    Route::get('/admin/packages/{package}', [PackageController::class, 'show'])->name('admin.packages.show');
    Route::get('/admin/packages/{package}/edit', [PackageController::class, 'edit'])->name('admin.packages.edit');
    Route::put('/admin/packages/{package}', [PackageController::class, 'update'])->name('admin.packages.update');
    Route::delete('/admin/packages/{package}', [PackageController::class, 'destroy'])->name('admin.packages.destroy');

    Route::get('/admin/credit-pricing/create', [CreditPricingController::class, 'create'])->name('admin.credit-pricing.create');
    Route::post('/admin/credit-pricing', [CreditPricingController::class, 'store'])->name('admin.credit-pricing.store');
    Route::get('/admin/credit-pricing/{creditPricing}/edit', [CreditPricingController::class, 'edit'])->name('admin.credit-pricing.edit');
    Route::put('/admin/credit-pricing/{creditPricing}', [CreditPricingController::class, 'update'])->name('admin.credit-pricing.update');
    Route::delete('/admin/credit-pricing/{creditPricing}', [CreditPricingController::class, 'destroy'])->name('admin.credit-pricing.destroy');

    Route::view('/admin/orders', 'admin.unavailable', [
        'heading' => 'Order Management',
        'summary' => 'Orders placed across the system.',
    ])->name('admin.orders');
    Route::view('/admin/payments', 'admin.unavailable', [
        'heading' => 'Payment Management',
        'summary' => 'Payment records for completed orders.',
    ])->name('admin.payments');
    Route::get('/admin/reports', ReportController::class)->name('admin.reports');
    Route::get('/admin/settings', [SettingController::class, 'edit'])->name('admin.settings');
    Route::put('/admin/settings', [SettingController::class, 'update'])->name('admin.settings.update');
});

Route::view('/customer/dashboard', 'customer.dashboard')->name('customer.dashboard');
Route::view('/customer/profile', 'customer.profile')->name('customer.profile');
Route::view('/customer/websites', 'customer.websites')->name('customer.websites');
Route::view('/customer/services', 'customer.services')->name('customer.services');
Route::view('/customer/orders', 'customer.orders')->name('customer.orders');

Route::get('/orders/cart', [OrderController::class, 'cart'])->name('orders.cart');
Route::post('/orders/cart/items', [OrderController::class, 'addToCart'])->name('orders.cart.items.store');
Route::patch('/orders/cart/items/{cartItem}', [OrderController::class, 'updateCartItem'])->name('orders.cart.items.update');
Route::delete('/orders/cart/items/{cartItem}', [OrderController::class, 'removeCartItem'])->name('orders.cart.items.destroy');
Route::get('/orders/checkout', [OrderController::class, 'checkout'])->name('orders.checkout');
Route::post('/orders', [OrderController::class, 'placeOrder'])->name('orders.store');
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

Route::view('/seo/dashboard', 'seo.dashboard')->name('seo.dashboard');
Route::view('/seo/assigned-orders', 'seo.assigned-orders')->name('seo.assigned-orders');
Route::view('/seo/articles', 'seo.articles')->name('seo.articles');
Route::view('/seo/submissions', 'seo.submissions')->name('seo.submissions');
Route::view('/seo/completed-orders', 'seo.completed-orders')->name('seo.completed-orders');
