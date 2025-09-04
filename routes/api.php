<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;

Route::apiResource('stores', StoreController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('orders', OrderController::class);
Route::apiResource('users', UserController::class);
