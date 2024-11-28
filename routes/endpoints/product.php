<?php
use App\Http\Controllers\ProductController;

Route::prefix('products')->middleware('auth:sanctum')->group(function () {
    Route::middleware('admin')->group(function () {
        // add\update\delete only admin store
        Route::post('/', [ProductController::class, 'store'])->name('products.store');
        Route::put('/{product_id}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/{product_id}', [ProductController::class, 'destroy'])->name('products.delete');
        Route::get('my-products', [ProductController::class, 'myProducts'])->name('products.my_products');
    });

    // list all products
    Route::get('/', [ProductController::class, 'index'])->name('products.index');
    Route::get('get-all-products/{store_id}', [ProductController::class, 'productInStore']);
    Route::get('search', [ProductController::class, 'search'])->name('products.search');
    Route::get('/{product_id}', [ProductController::class, 'show'])->name('products.show');
});

