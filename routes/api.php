<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->name('v1.')->group(function () {
    Route::apiResource('products', ProductController::class);
    Route::apiResource('favorites', FavoriteController::class);
    Route::apiResource('carts', CartController::class);
    Route::apiResource('orders', OrderController::class);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
