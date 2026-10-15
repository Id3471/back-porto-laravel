<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PublicPortfolioController;
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

Route::get('/portfolio-content', [PublicPortfolioController::class, 'content']);
Route::get('/cv', [PublicPortfolioController::class, 'downloadCv']);
Route::post('/contact', [ContactController::class, 'send'])->middleware('throttle:3,10');
