<?php

use App\Http\Controllers\Customer\MarketplaceController;
use App\Http\Controllers\Customer\NicheController;
use App\Http\Controllers\Customer\PublishController;
use App\Http\Controllers\Customer\PublisherReviewController;
use App\Http\Controllers\Customer\PackagePageController;
use App\Http\Controllers\Customer\PageController;
use App\Http\Controllers\Customer\PublisherController;
use App\Http\Controllers\Customer\ServicePageController;
use App\Http\Controllers\Customer\ShoppingController;
use App\Http\Controllers\Customer\SiteController;
use App\Http\Controllers\Customer\ToolController;
use App\Http\Controllers\Customer\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/home', [PageController::class, 'home'])->name('home');
Route::get('/services', [ServicePageController::class, 'index'])->name('services.index');
Route::get('/services/{slug}', [ServicePageController::class, 'show'])->name('services.show');
Route::get('/packages', [PackagePageController::class, 'index'])->name('packages.index');
Route::get('/packages/{slug}', [PackagePageController::class, 'show'])->where('slug', '[a-z0-9\-]+')->name('packages.show');
Route::get('/tools', [ToolController::class, 'index'])->name('tools.index');
Route::get('/tools/{slug}', [ToolController::class, 'show'])->where('slug', '[a-z0-9\-]+')->name('tools.show');
Route::get('/publishers', [PublisherController::class, 'index'])->name('publishers.index');
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
Route::delete('/wishlist', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
Route::get('/cart', [ShoppingController::class, 'index'])->name('shopping.index');
Route::post('/cart', [ShoppingController::class, 'store'])->name('shopping.store');
Route::patch('/cart', [ShoppingController::class, 'update'])->name('shopping.update');
Route::delete('/cart', [ShoppingController::class, 'destroy'])->name('shopping.destroy');
Route::post('/cart/clear', [ShoppingController::class, 'clear'])->name('shopping.clear');
Route::post('/cart/coupon', [ShoppingController::class, 'coupon'])->name('shopping.coupon');
Route::delete('/cart/coupon', [ShoppingController::class, 'removeCoupon'])->name('shopping.coupon.destroy');
Route::get('/cart/checkout', [ShoppingController::class, 'checkout'])->name('shopping.checkout');
Route::get('/sites', [SiteController::class, 'index'])->name('sites.index');
Route::get('/sites/{slug}', [SiteController::class, 'show'])->where('slug', '[a-z0-9\-]+')->name('sites.show');
Route::redirect('/marketplace', '/publishers');
Route::get('/marketplace/{website}', [MarketplaceController::class, 'show'])->name('marketplace.show');
Route::get('/publish/{site}', [PublishController::class, 'show'])->name('publish.show');
Route::post('/publish/{site}/reviews', [PublisherReviewController::class, 'store'])
    ->middleware(['auth', 'throttle:10,1'])
    ->name('publish.reviews.store');
Route::get('/niches',[NicheController::class, 'index'])->name('niches.index');
Route::get('/niches/{slug}', [NicheController::class, 'show'])->name('niches.show');

Route::view('/ui-kit', 'customer.pages.ui-kit')->name('ui.kit');
