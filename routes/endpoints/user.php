<?php

use App\Http\Controllers\UserController;

Route::put('/user', [UserController::class, 'updateProfile'])->middleware('auth:sanctum');
