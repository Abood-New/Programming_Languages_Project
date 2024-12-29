<?php

Route::put('/user', [UserController::class, 'updateProfile'])->middleware('auth:sanctum');
