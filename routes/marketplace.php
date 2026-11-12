<?php

use App\Http\Controllers\Customer\NicheController;
use App\Http\Controllers\Customer\PageController;
use App\Http\Controllers\Customer\PublisherController;
use App\Http\Controllers\Customer\ServicePageController;
use App\Http\Controllers\Customer\SiteController;
use App\Http\Controllers\Customer\ToolController;
use Illuminate\Support\Facades\Route;

Route::get('/home', [PageController::class, 'home'])->name('home');
Route::get('/services', [ServicePageController::class, 'index'])->name('services.index');
Route::get('/services/{slug}', [ServicePageController::class, 'show'])->name('services.show');
Route::view('/packages', 'customer.packages.index')->name('packages.index');
Route::get('/tools', [ToolController::class, 'index'])->name('tools.index');
Route::get('/tools/{slug}', [ToolController::class, 'show'])->where('slug', '[a-z0-9\-]+')->name('tools.show');
Route::get('/publishers', [PublisherController::class, 'index'])->name('publishers.index');
Route::get('/sites', [SiteController::class, 'index'])->name('sites.index');
Route::get('/sites/{slug}', [SiteController::class, 'show'])->where('slug', '[a-z0-9\-]+')->name('sites.show');
Route::get('/niches', [NicheController::class, 'index'])->name('niches.index');
Route::get('/niches/{slug}', [NicheController::class, 'show'])->name('niches.show');

Route::view('/ui-kit', 'customer.pages.ui-kit')->name('ui.kit');
