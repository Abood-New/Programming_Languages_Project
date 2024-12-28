<?php
use App\Http\Controllers\FavoriteController;

Route::middleware('auth:sanctum')->group(function () {
    // Add a product to favorites
    Route::post('/favorites', [FavoriteController::class, 'store']);

    // Remove a product from favorites
    Route::delete('/favorites/{product}', [FavoriteController::class, 'destroy']);

    // Get all user's favorite products
    Route::get('/favorites', [FavoriteController::class, 'index']);
});
