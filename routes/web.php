<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ProductController as WebProductController;
use App\Http\Controllers\Web\OrderController as WebOrderController;
use App\Http\Controllers\Web\AuthController as WebAuthController;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Auth routes
Route::get('login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [WebAuthController::class, 'login']);
Route::post('logout', [WebAuthController::class, 'logout'])->name('logout');


// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/products', [WebProductController::class, 'index'])->name('products.index');
    Route::get('/orders', [WebOrderController::class, 'index'])->name('orders.index');
});