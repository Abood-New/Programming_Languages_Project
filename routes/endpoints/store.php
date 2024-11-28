<?php
use App\Http\Controllers\StoreController;

Route::prefix('stores')->middleware('auth:sanctum')->group(function () {
    Route::middleware('admin')->group(function () {
        // add\update\delete only admin store
        Route::post('/', [StoreController::class, 'store'])->name('stores.store');
        Route::put('/{store_id}', [StoreController::class, 'update'])->name('stores.update');
        Route::delete('/{store_id}', [StoreController::class, 'destroy'])->name('stores.delete');
    });

    // list all stores
    Route::get('/', [StoreController::class, 'index'])->name('stores.index');
    Route::get('/{store_id}', [StoreController::class, 'show'])->name('stores.show');
});
