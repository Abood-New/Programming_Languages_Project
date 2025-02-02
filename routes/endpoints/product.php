<?php
use App\Http\Controllers\ProductController;

Route::middleware(['auth:sanctum'])->prefix('product')->group(function () {
    Route::middleware(['isStoreOwner'])->group((function () {
        Route::post('/', [ProductController::class, 'createProduct']);
        Route::get('/get-my-store-products', [ProductController::class, "getMyStoreProducts"]);
        Route::put('/{product_id}', [ProductController::class, 'updateProduct']);
        Route::delete('/{product_id}', [ProductController::class, 'destroy']);

    }));
    Route::get('/', [ProductController::class, 'getAllProducts']);
    Route::get('/detail/{product_id}', [ProductController::class, "getProductDetails"]);
    Route::get('/search', [ProductController::class, "search"]);
});

