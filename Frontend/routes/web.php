<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\MetricsController;
use App\Http\Middleware\AuthMiddleware;

// Raíz → redirige al login
Route::get('/', fn() => redirect()->route('login'));

// Rutas públicas
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

// Rutas protegidas
Route::middleware(AuthMiddleware::class)->group(function () {
    Route::get('/dashboard', [MetricsController::class,   'index'])->name('dashboard');
    Route::get('/resumen',   [DashboardController::class, 'index'])->name('resumen');
    Route::get('/upload',   [UploadController::class,  'index'])->name('upload');
    Route::post('/analyze', [UploadController::class,  'analyze'])->name('analyze');
    Route::get('/history',           [HistoryController::class, 'index'])->name('history');
    Route::post('/history/{id}/review',   [HistoryController::class, 'review'])->name('history.review');
    Route::delete('/history/{id}/review', [HistoryController::class, 'removeReview'])->name('history.review.remove');
});
