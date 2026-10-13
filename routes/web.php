<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin-api')->group(function () {
    Route::get('/csrf', [AdminController::class, 'csrf']);
    Route::post('/login', [AdminController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware(['auth', 'auth.session', 'admin'])->group(function () {
        Route::get('/session', [AdminController::class, 'session']);
        Route::post('/logout', [AdminController::class, 'logout']);
        Route::put('/content', [AdminController::class, 'updateContent']);
        Route::put('/account', [AdminController::class, 'updateAccount']);
        Route::post('/cv', [AdminController::class, 'replaceCv']);
    });
});

Route::get('/portfolio-content', [AdminController::class, 'content']);
