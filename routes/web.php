<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('customer.dashboard')
        : redirect()->route('auth.login');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('auth.register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('auth.authenticate');
});

Route::prefix('/api/auth')->name('api.auth.')->middleware('guest')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('register');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('auth.logout');

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

Route::view('/seo/dashboard', 'seo.dashboard')->name('seo.dashboard');
Route::view('/seo/assigned-orders', 'seo.assigned-orders')->name('seo.assigned-orders');
Route::view('/seo/articles', 'seo.articles')->name('seo.articles');
Route::view('/seo/submissions', 'seo.submissions')->name('seo.submissions');
Route::view('/seo/completed-orders', 'seo.completed-orders')->name('seo.completed-orders');
