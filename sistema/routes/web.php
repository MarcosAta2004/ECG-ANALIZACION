<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ControladorAutenticacion;
use App\Http\Controllers\ControladorResumen;
use App\Http\Controllers\ControladorHistorial;
use App\Http\Controllers\ControladorSubida;
use App\Http\Controllers\ControladorMetricas;
use App\Http\Controllers\ControladorReportes;
use App\Http\Controllers\ControladorUsuarios;
use App\Http\Controllers\ControladorAuditoria;
use App\Http\Middleware\MiddlewareAutenticacion;

// Raíz → redirige al login
Route::get('/', fn() => redirect()->route('login'));

// Rutas públicas
Route::get('/login',  [ControladorAutenticacion::class, 'showLogin'])->name('login');
Route::post('/login', [ControladorAutenticacion::class, 'login'])->name('login.post');
Route::post('/logout',[ControladorAutenticacion::class, 'logout'])->name('logout');

// Rutas protegidas
Route::middleware(MiddlewareAutenticacion::class)->group(function () {
    Route::get('/dashboard', [ControladorMetricas::class,   'index'])->name('dashboard');
    Route::get('/dashboard/statistics/pdf', [ControladorMetricas::class, 'downloadStatistics'])->name('dashboard.statistics.pdf');
    Route::get('/resumen',   [ControladorResumen::class, 'index'])->name('resumen');
    Route::get('/upload',   [ControladorSubida::class,  'index'])->name('upload');
    Route::post('/analyze', [ControladorSubida::class,  'analyze'])->name('analyze');
    Route::get('/history',           [ControladorHistorial::class, 'index'])->name('history');
    Route::post('/history/{id}/review',   [ControladorHistorial::class, 'review'])->middleware('rol:Administrador,Medico')->name('history.review');
    Route::delete('/history/{id}/review', [ControladorHistorial::class, 'removeReview'])->middleware('rol:Administrador,Medico')->name('history.review.remove');
    Route::get('/reports', [ControladorReportes::class, 'index'])->name('reports');
    Route::post('/reports/download', [ControladorReportes::class, 'download'])->name('reports.download');
    Route::middleware('rol:Administrador')->group(function () {
        Route::get('/usuarios', [ControladorUsuarios::class, 'index'])->name('usuarios.index');
        Route::post('/usuarios', [ControladorUsuarios::class, 'storeUser'])->name('usuarios.store');
        Route::put('/usuarios/{usuario}', [ControladorUsuarios::class, 'updateUser'])->name('usuarios.update');
        Route::post('/roles', [ControladorUsuarios::class, 'storeRole'])->name('roles.store');
        Route::put('/roles/{rol}', [ControladorUsuarios::class, 'updateRole'])->name('roles.update');
        Route::get('/auditoria', [ControladorAuditoria::class, 'index'])->name('auditoria.index');
    });
});
