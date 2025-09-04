<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

// Authentication Routes
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Protected API Resources
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('stores', StoreController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('users', UserController::class);
});
