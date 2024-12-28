<?php
use App\Http\Controllers\StoreController;

Route::prefix('stores')->middleware('auth:sanctum')->group(function () {
    Route::middleware('adminOrStoreOwner')->group(function () {
        // add\update\delete only admin store
        Route::post('/', [StoreController::class, "createStore"]);
        Route::put('/{store_id}', [StoreController::class, "updateStore"]);
        Route::delete('/{store_id}', [StoreController::class, "destroy"]);
    });
    Route::get('/search', [StoreController::class, "filterStoreByName"]);
    Route::get('/', [StoreController::class, "getAllStores"]);
    Route::get('/get-my-store', [StoreController::class, "getMyStore"])->middleware('isStoreOwner');
    Route::get('/{storeId}/products', [StoreController::class, 'getProductsByStore']);
});
