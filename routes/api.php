<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function() {
    Route::prefix('users')->group(function() {
        Route::get('show/{id}', [UserController::class, 'show']);
        Route::get('me', [UserController::class, 'me']);
    });

    Route::prefix('products')->group(function() {
        Route::post('/', [ProductsController::class, 'store']);
        Route::get('/', [ProductsController::class, 'index']);
        Route::get('/show/{id}', [ProductsController::class, 'show']);

    });
});
