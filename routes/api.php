<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\StoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('admin')->group(function () {
        // add\update\delete only admin store
        Route::apiResource('stores', StoreController::class)->except(['index', 'show']);
    });

    // list all stores
    Route::get('stores', [StoreController::class, 'index'])->name('stores.index');
    Route::get('stores/{store_id}', [StoreController::class, 'show'])->name('stores.show');
});

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
