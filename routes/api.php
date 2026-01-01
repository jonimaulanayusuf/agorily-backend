<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('v1.')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('products', ProductController::class);
        Route::apiResource('favorites', FavoriteController::class);
        Route::apiResource('carts', CartController::class);
        Route::apiResource('orders', OrderController::class);
        Route::prefix('auth')->name('auth.')->controller(AuthController::class)->group(function () {
            Route::get('user', 'user')->name('user');
            Route::patch('user', 'update')->name('update');
            Route::delete('logout', 'logout')->name('logout');
        });
    });
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('register', [AuthController::class, 'register'])->name('register');
});
