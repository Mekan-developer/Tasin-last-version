<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SlideController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('search', [SearchController::class, 'index'])->name('search');

    Route::resource('categories', CategoryController::class)->except(['create', 'edit', 'show']);
    Route::patch('categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');

    Route::post('products/bulk', [ProductController::class, 'bulkStore'])->name('products.bulkStore');
    Route::get('products/sort-data', [ProductController::class, 'sortData'])->name('products.sortData');
    Route::post('products/reorder', [ProductController::class, 'reorder'])->name('products.reorder');
    Route::resource('products', ProductController::class)->except(['create', 'edit', 'show']);
    Route::patch('products/{product}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');

    Route::resource('slides', SlideController::class)->except(['create', 'edit', 'show']);

    Route::resource('users', AdminUserController::class)->except(['create', 'edit', 'show']);

    Route::resource('staff', StaffController::class)->except(['create', 'edit', 'show']);
    Route::post('staff/{staff}/pay', [StaffController::class, 'pay'])->name('staff.pay');

    Route::get('activity', [ActivityController::class, 'index'])->name('activity');

    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings/{tab}', [SettingsController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
