<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('admin')->group(function () {
        // add\update\delete only admin store
        Route::apiResource('stores', StoreController::class)->except(['index', 'show']);
        Route::apiResource('products', ProductController::class)->except(['index', 'show', 'store']);
        Route::get('products/get-my-products', [ProductController::class, 'myProducts']);
        Route::post('products/{store_id}', [ProductController::class, 'store']);
    });

    // list all stores
    Route::get('stores', [StoreController::class, 'index'])->name('stores.index');
    Route::get('stores/{store_id}', [StoreController::class, 'show'])->name('stores.show');

    // list all products
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/get-all-products/{store_id}', [ProductController::class, 'productInStore']);
    Route::get('products/search', [ProductController::class, 'search'])->name('products.search');

    Route::get('products/{product_id}', [ProductController::class, 'show'])->name('products.show');

});

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
