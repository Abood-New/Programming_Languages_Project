<?php
use App\Http\Controllers\ProductController;

Route::prefix('orders')->middleware('auth:sanctum')->group(function () {
    Route::middleware('admin')->group(function () {
        // get\add\update\delete only admin products

    });

    // list all products

});
