<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\CreditPricingController;
use App\Http\Controllers\Admin\CustomerStatisticController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NicheController;
use App\Http\Controllers\Admin\NicheSpecificationController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StaffPerformanceController;
use App\Http\Controllers\Admin\WebsiteController as AdminWebsiteController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Seo\SeoDashboardController;
use App\Http\Controllers\Seo\StaffAuthController;
use App\Http\Controllers\Seo\StaffOrderController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->to(auth()->user()->dashboardUrl())
        : redirect()->route('auth.login');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('auth.register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('auth.authenticate');
    Route::get('/login/google', [AuthController::class, 'redirectToGoogle'])->middleware('throttle:10,1')->name('auth.google');
    Route::get('/login/google/callback', [AuthController::class, 'handleGoogleCallback'])->middleware('throttle:10,1')->name('auth.google.callback');
    Route::post('/login/google/firebase', [AuthController::class, 'loginWithFirebase'])->middleware('throttle:10,1')->name('auth.google.firebase');
});

Route::prefix('/api/auth')->name('api.auth.')->middleware('guest')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('register');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('auth.logout');

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});
Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

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

    Route::get('/admin/websites', [AdminWebsiteController::class, 'index'])->name('admin.websites');
    Route::get('/admin/websites/create', [AdminWebsiteController::class, 'create'])->name('admin.websites.create');
    Route::post('/admin/websites', [AdminWebsiteController::class, 'store'])->name('admin.websites.store');
    Route::get('/admin/websites/{website}', [AdminWebsiteController::class, 'show'])->name('admin.websites.show');
    Route::get('/admin/websites/{website}/edit', [AdminWebsiteController::class, 'edit'])->name('admin.websites.edit');
    Route::put('/admin/websites/{website}', [AdminWebsiteController::class, 'update'])->name('admin.websites.update');
    Route::delete('/admin/websites/{website}', [AdminWebsiteController::class, 'destroy'])->name('admin.websites.destroy');

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

    Route::get('/admin/orders', [AdminOrderController::class, 'index'])->name('admin.orders');
    Route::get('/admin/orders/{order}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
    Route::post('/admin/orders/{order}/approve', [AdminOrderController::class, 'approve'])->name('admin.orders.approve');
    Route::post('/admin/orders/{order}/items/{orderItem}/assign', [AdminOrderController::class, 'assign'])->name('admin.orders.assign');
    Route::get('/admin/payments', [AdminPaymentController::class, 'index'])->name('admin.payments');
    Route::get('/admin/payments/{payment}', [AdminPaymentController::class, 'show'])->name('admin.payments.show');
    Route::get('/admin/reports', ReportController::class)->name('admin.reports');
    Route::get('/admin/staff-performance', StaffPerformanceController::class)->name('admin.staff-performance');
    Route::get('/admin/customer-statistics', CustomerStatisticController::class)->name('admin.customer-statistics');
    Route::get('/admin/notifications', [AdminNotificationController::class, 'index'])->name('admin.notifications');
    Route::patch('/admin/notifications/{notification}/read', [AdminNotificationController::class, 'read'])->name('admin.notifications.read');
    Route::get('/admin/settings', [SettingController::class, 'edit'])->name('admin.settings');
    Route::put('/admin/settings', [SettingController::class, 'update'])->name('admin.settings.update');
    Route::get('/admin/profile-photo', [SettingController::class, 'photo'])->name('admin.profile-photo');
});

Route::view('/customer/dashboard', 'customer.dashboard')->name('customer.dashboard');
Route::view('/customer/profile', 'customer.profile')->name('customer.profile');
Route::get('/customer/websites', [WebsiteController::class, 'index'])->name('customer.websites');
Route::view('/customer/services', 'customer.services')->name('customer.services');

Route::middleware('auth')->group(function () {
    Route::get('/customer/dashboard', [CustomerAccountController::class, 'dashboard'])->name('customer.dashboard');
    Route::get('/customer/profile', [CustomerAccountController::class, 'profile'])->name('customer.profile');
    Route::patch('/customer/profile', [CustomerAccountController::class, 'updateProfile'])->name('customer.profile.update');
    Route::patch('/customer/password', [CustomerAccountController::class, 'updatePassword'])->name('customer.password.update');
    Route::get('/customer/wallet', [CustomerAccountController::class, 'wallet'])->name('customer.wallet');
    Route::get('/customer/payment-methods', [CustomerAccountController::class, 'paymentMethods'])->name('customer.payment-methods');
    Route::post('/customer/payment-methods', [CustomerAccountController::class, 'storePaymentMethod'])->name('customer.payment-methods.store');
    Route::delete('/customer/payment-methods/{paymentMethod}', [CustomerAccountController::class, 'destroyPaymentMethod'])->name('customer.payment-methods.destroy');
    Route::get('/customer/payment-history', [PaymentController::class, 'history'])->name('customer.payment-history');
    Route::get('/customer/invoices', [CustomerAccountController::class, 'invoices'])->name('customer.invoices');
    Route::get('/customer/invoices/{invoice}/download', [CustomerAccountController::class, 'downloadInvoice'])->name('customer.invoices.download');
    Route::get('/customer/addresses', [CustomerAccountController::class, 'addresses'])->name('customer.addresses');
    Route::post('/customer/addresses', [CustomerAccountController::class, 'storeAddress'])->name('customer.addresses.store');
    Route::patch('/customer/addresses/{address}', [CustomerAccountController::class, 'updateAddress'])->name('customer.addresses.update');
    Route::delete('/customer/addresses/{address}', [CustomerAccountController::class, 'destroyAddress'])->name('customer.addresses.destroy');
    Route::get('/customer/saved-websites', [CustomerAccountController::class, 'savedWebsites'])->name('customer.saved-websites');
    Route::post('/customer/saved-websites', [CustomerAccountController::class, 'storeSavedWebsite'])->name('customer.saved-websites.store');
    Route::delete('/customer/saved-websites/{savedWebsite}', [CustomerAccountController::class, 'destroySavedWebsite'])->name('customer.saved-websites.destroy');
    Route::get('/customer/notifications', [CustomerAccountController::class, 'notifications'])->name('customer.notifications');
    Route::patch('/customer/notifications/{notification}/read', [CustomerAccountController::class, 'markNotificationRead'])->name('customer.notifications.read');
    Route::get('/customer/projects', [CustomerAccountController::class, 'projects'])->name('customer.projects');
    Route::get('/customer/backlinks', [CustomerAccountController::class, 'backlinks'])->name('customer.backlinks');
    Route::post('/customer/backlinks/{project}/decision', [CustomerAccountController::class, 'decideBacklink'])->name('customer.backlinks.decide');
    Route::get('/customer/reports', [CustomerAccountController::class, 'reports'])->name('customer.reports');
    Route::post('/customer/wallet/packages/{creditPackage}/purchase', [PaymentController::class, 'purchaseCredits'])->name('customer.wallet.purchase');

    Route::get('/orders/cart', [OrderController::class, 'cart'])->name('orders.cart');
    Route::post('/orders/cart/items', [OrderController::class, 'addToCart'])->name('orders.cart.items.store');
    Route::patch('/orders/cart/items/{cartItem}', [OrderController::class, 'updateCartItem'])->name('orders.cart.items.update');
    Route::delete('/orders/cart/items/{cartItem}', [OrderController::class, 'removeCartItem'])->name('orders.cart.items.destroy');
    Route::post('/orders/cart/coupon', [OrderController::class, 'applyCoupon'])->name('orders.cart.coupon');
    Route::delete('/orders/cart/coupon', [OrderController::class, 'removeCoupon'])->name('orders.cart.coupon.destroy');
    Route::get('/orders/checkout', [OrderController::class, 'checkout'])->name('orders.checkout');
    Route::post('/orders', [OrderController::class, 'placeOrder'])->name('orders.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}/payment', [PaymentController::class, 'showOrderPayment'])->name('orders.payment');
    Route::post('/orders/{order}/payment', [PaymentController::class, 'payOrder'])->name('orders.payment.process');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::prefix('/api')->name('api.')->group(function () {
        Route::get('/cart', [OrderController::class, 'cart'])->name('cart');
        Route::post('/cart/items', [OrderController::class, 'addToCart'])->name('cart.items.store');
        Route::patch('/cart/items/{cartItem}', [OrderController::class, 'updateCartItem'])->name('cart.items.update');
        Route::delete('/cart/items/{cartItem}', [OrderController::class, 'removeCartItem'])->name('cart.items.destroy');
        Route::post('/cart/coupon', [OrderController::class, 'applyCoupon'])->name('cart.coupon');
        Route::delete('/cart/coupon', [OrderController::class, 'removeCoupon'])->name('cart.coupon.destroy');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'placeOrder'])->name('orders.store');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('/payments', [PaymentController::class, 'history'])->name('payments.index');
        Route::post('/payments/orders/{order}', [PaymentController::class, 'payOrder'])->name('payments.orders.store');
        Route::get('/wallet', [CustomerAccountController::class, 'apiWallet'])->name('wallet');
        Route::post('/wallet/packages/{creditPackage}/purchase', [PaymentController::class, 'purchaseCredits'])->name('wallet.purchase');
    });

    Route::prefix('/api/customer')->name('api.customer.')->group(function () {
        Route::get('/profile', [CustomerAccountController::class, 'apiProfile'])->name('profile');
        Route::patch('/profile', [CustomerAccountController::class, 'updateProfile'])->name('profile.update');
        Route::get('/invoices', [CustomerAccountController::class, 'apiInvoices'])->name('invoices');
        Route::get('/notifications', [CustomerAccountController::class, 'apiNotifications'])->name('notifications');
        Route::get('/saved-websites', [CustomerAccountController::class, 'apiSavedWebsites'])->name('saved-websites');
        Route::post('/saved-websites', [CustomerAccountController::class, 'apiStoreSavedWebsite'])->name('saved-websites.store');
        Route::post('/addresses', [CustomerAccountController::class, 'apiStoreAddress'])->name('addresses.store');
    });

    Route::prefix('/api/credits')->name('api.credits.')->group(function () {
        Route::post('/reserve', [PaymentController::class, 'reserveCredits'])->name('reserve');
        Route::post('/settle/{orderItem}', [PaymentController::class, 'settleCredits'])->name('settle');
    });
});

/*
|--------------------------------------------------------------------------
| SEO Staff Portal & Backlink Verification
| Staff Dashboard, Authentication, Order Management & Verification
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
Route::match(['put', 'patch'], '/seo/orders/{id}/status', [StaffOrderController::class, 'updateStatus'])->name('seo.orders.status');

// SEO Operations Views
Route::view('/seo/articles', 'seo.articles')->name('seo.articles');
Route::view('/seo/submissions', 'seo.submissions')->name('seo.submissions');
Route::view('/seo/completed-orders', 'seo.completed-orders')->name('seo.completed-orders');
