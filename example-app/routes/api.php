<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderAdminController;

Route::middleware(['auth:sanctum', 'require.apitoken'])->group(function () {
    // Admin-only for all product endpoints (read + write)
    Route::middleware('admin')->group(function () {
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{id}', [ProductController::class, 'show']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);

        // Admin Order Management APIs (protected by Sanctum + admin)
        Route::get('/admin/orders', [OrderAdminController::class, 'index']);
        Route::get('/admin/orders/{id}', [OrderAdminController::class, 'show']);
        Route::put('/admin/orders/{id}/status', [OrderAdminController::class, 'updateStatus']);
    });

    // Customer endpoints (token required, no admin)
    Route::get('/orders', [OrderController::class, 'index']);
});
