<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ArticleController;

// Public route for logging in
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (must be logged in)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Admin Only Routes
    Route::middleware('is_admin')->group(function () {
        Route::apiResource('categories', CategoryController::class);
    });

    // Routes for all authenticated users (Admin & Author)
    Route::apiResource('articles', ArticleController::class);
});