<?php
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;

Route::prefix('orders')->middleware('auth:sanctum')->group(function () {
    Route::middleware('admin')->group(function () {
        Route::get('my-store-orders', [OrderController::class, 'my_store_orders']);
        Route::post('{order_id}/ship', [OrderController::class, 'ship']);

    });

    // list all products

});
