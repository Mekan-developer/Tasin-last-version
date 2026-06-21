<?php

use App\Http\Controllers\Shop\ShopApiController;
use App\Http\Controllers\Shop\ShopController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Витрина (storefront) — публичные маршруты
|--------------------------------------------------------------------------
| Отдельный файл, чтобы не трогать админ-маршруты (routes/web.php).
| Регистрируется в bootstrap/app.php с middleware-группой "web".
| Авторизации нет — только чтение каталога.
*/

// Route::prefix('shop')->name('shop.')->group(function () {
    // Оболочка приложения + начальные данные
    Route::get('/', [ShopController::class, 'index'])->name('index');
// });

// JSON для клиентского экранного стека (без перезагрузки страницы)
Route::prefix('api/shop')->name('shop.api.')->group(function () {
    Route::get('/categories/{category}/products', [ShopApiController::class, 'products'])->name('products');
    Route::get('/products', [ShopApiController::class, 'all'])->name('all');
    Route::get('/search', [ShopApiController::class, 'search'])->name('search');
    Route::get('/products/{product}', [ShopApiController::class, 'show'])->name('product');
    Route::get('/gallery', [ShopApiController::class, 'gallery'])->name('gallery');

    // Трекинг визитов — троттлинг от злоупотреблений
    Route::post('/track', [ShopApiController::class, 'track'])
        ->middleware('throttle:60,1')
        ->name('track');
});
