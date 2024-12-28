<?php
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreOrderController;

// Customer Routes
Route::middleware('auth:sanctum')->group(function () {
    // Place an order
    Route::post('/orders', [OrderController::class, 'store']);
    // Get all orders for authenticated user
    Route::get('/orders', [OrderController::class, 'index']);
    // Get a specific order by ID
    Route::get('/orders/detail/{order_id}', [OrderController::class, 'show']);
    // Cancel an order
    Route::put('/orders/{orderItem_id}/cancel', [OrderController::class, 'cancel']);

    Route::put('/orders/{order_id}/update-order-items', [OrderController::class, 'updateOrderItems']);
});

// Admin Routes
Route::middleware('auth:sanctum', 'isAdmin')->group(function () {
    // Get all orders (admin can see all orders)
    Route::get('/admin/orders', [AdminOrderController::class, 'index']);
    // Get a specific order by ID (admin can see order details)
    Route::get('/admin/orders/{order_id}', [AdminOrderController::class, 'show']);
    // Update order status (admin can change the status of the order)
});

Route::middleware('auth:sanctum', 'isStoreOwner')->group(function () {
    // Get all orders related to the store (store owner can see only their store's orders)
    Route::get('/store/orders', [StoreOrderController::class, 'index']);
    // Get a specific order by ID (store owner can see their store's order details)
    Route::get('/store/orders/{order_id}', [StoreOrderController::class, 'show']);
    // update the order_status
    Route::post('/store/orders/update_status/{order_item_id}', [StoreOrderController::class, 'updateStatus']);
});

