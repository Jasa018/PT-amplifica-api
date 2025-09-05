<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShopifyController;
use App\Http\Controllers\WooCommerceController;
use App\Http\Controllers\LogController;

// Authentication Routes
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Shopify OAuth Routes (Note: Not used for Custom Apps, but kept for reference)
Route::get('/shopify/install', [ShopifyController::class, 'install'])->name('shopify.install');
Route::get('/shopify/callback', [ShopifyController::class, 'callback'])->name('shopify.callback');

// Shopify API Routes
Route::get('/shopify/test', [ShopifyController::class, 'testApi']);
Route::get('/shopify/orders', [ShopifyController::class, 'getRecentOrders']);
Route::get('/shopify/products/export', [ShopifyController::class, 'exportProductsCsv']);
Route::get('/shopify/orders/export', [ShopifyController::class, 'exportOrdersCsv']);

// WooCommerce API Routes
Route::get('/woocommerce/products', [WooCommerceController::class, 'getProducts']);
Route::get('/woocommerce/orders', [WooCommerceController::class, 'getRecentOrders']);
Route::get('/woocommerce/products/export', [WooCommerceController::class, 'exportProductsCsv']);
Route::get('/woocommerce/orders/export', [WooCommerceController::class, 'exportOrdersCsv']);

// Protected API Resources
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('stores', StoreController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('users', UserController::class);
    Route::get('logs', [LogController::class, 'index']);
});
