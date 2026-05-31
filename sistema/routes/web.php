<?php

use Illuminate\Support\Facades\Route;

// Importación de Controladores
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MenuPrincipalController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\EstudioController;
use App\Http\Controllers\ImagenController;
use App\Http\Controllers\PrediccionController;
use App\Http\Controllers\DiagnosticoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RitmoCardiacoController;
use App\Http\Controllers\GrupoCardiacoController;
use App\Http\Controllers\NivelGravedadController;
use App\Http\Controllers\ClasificacionArritmiaController;
use App\Http\Controllers\PrefijoPacienteController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () { 
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/login', function () { 
    return view('autenticacion.index'); 
})->name('login');

Route::controller(LoginController::class)->group(function () {
    Route::post('/iniciar-sesion', 'iniciarSesion')->name('login.post');
    Route::post('/logout', 'logout')->name('logout');
});

/*
|--------------------------------------------------------------------------
| Private / Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // 1. DASHBOARD & PERFIL
    Route::controller(MenuPrincipalController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/resumen', 'index')->name('resumen');
        Route::get('/dashboard/statistics/pdf', 'downloadStatisticsPdf')->name('dashboard.statistics.pdf');
        Route::get('/perfil', 'indexActualizarContrasena')->name('perfil.index');
        Route::post('/perfil/update', 'actualizarContrasena')->name('perfil.actualizarContrasena');
    });

    // 2. MODULO SEGURIDAD
    Route::prefix('seguridad')->middleware(['rol:administrador'])->group(function () {
        
        Route::controller(UserController::class)->prefix('usuarios')->group(function () {
            Route::get('/', 'index')->name('usuarios.index');
            Route::get('create', 'create')->name('usuarios.create');
            Route::post('store', 'store')->name('usuarios.store');
            Route::get('edit/{usuario}', 'edit')->name('usuarios.edit');
            Route::put('update/{usuario}', 'update')->name('usuarios.update');
            Route::delete('delete/{usuario}', 'destroy')->name('usuarios.destroy');
            Route::put('activar/{usuario}', 'activar')->name('usuarios.activar');
            
            // Gestión de Roles por Usuario
            Route::get('{usuario}/roles', 'editrol')->name('usuarios.roles_edit');
            Route::put('{usuario}/roles', 'updaterol')->name('usuarios.roles_update');
        });

        Route::controller(RoleController::class)->prefix('roles')->group(function () {
            Route::get('/', 'index')->name('roles.index');
            Route::get('create', 'create')->name('roles.create');
            Route::post('store', 'store')->name('roles.store');
            Route::get('show/{role}', 'show')->name('roles.show');
            Route::get('edit/{role}', 'edit')->name('roles.edit');
            Route::put('update/{role}', 'update')->name('roles.update');
            Route::delete('delete/{role}', 'destroy')->name('roles.destroy');
        });

        Route::controller(AuditoriaController::class)->prefix('auditoria')->group(function () {
            Route::get('/', 'index')->name('auditoria.index');
        });
    });

    // 3. MODULO CLÍNICO (Análisis ECG)
    Route::prefix('clinico')->group(function () {

        Route::get('/history', [EstudioController::class, 'index'])->name('history');
        Route::get('/upload', [ImagenController::class, 'index'])->name('upload');
        Route::get('/reports', [ReporteController::class, 'index'])->name('reports');

        Route::controller(PacienteController::class)->prefix('pacientes')->group(function () {
            Route::get('/', 'index')->name('pacientes.index');
            Route::post('store', 'store')->name('pacientes.store');
            Route::put('update/{paciente}', 'update')->name('pacientes.update');
            Route::delete('delete/{paciente}', 'destroy')->name('pacientes.destroy');
            Route::put('activar/{paciente}', 'activar')->name('pacientes.activar');
        });

        Route::controller(EstudioController::class)->prefix('estudios')->group(function () {
            Route::get('/', 'index')->name('estudios.index');
            Route::post('store', 'store')->name('estudios.store');
            Route::put('update/{estudio}', 'update')->name('estudios.update');
            Route::delete('delete/{estudio}', 'destroy')->name('estudios.destroy');
            Route::put('activar/{estudio}', 'activar')->name('estudios.activar');
        });

        Route::controller(ImagenController::class)->prefix('imagenes')->group(function () {
            Route::get('/', 'index')->name('imagenes.index');
            Route::post('store', 'store')->name('imagenes.store');
            Route::put('update/{imagen}', 'update')->name('imagenes.update');
            Route::delete('delete/{imagen}', 'destroy')->name('imagenes.destroy');
            Route::put('activar/{imagen}', 'activar')->name('imagenes.activar');
            
            // Procesamiento de IA
            Route::post('analyze', 'analyze')->name('imagenes.analyze');
            Route::post('{imagen}/ejecutar-analisis', 'analizar')->name('imagenes.analizar');
        });

        Route::controller(PrediccionController::class)->prefix('predicciones')->group(function () {
            Route::get('/', 'index')->name('predicciones.index');
            Route::delete('delete/{prediccion}', 'destroy')->name('predicciones.destroy');
            Route::put('activar/{prediccion}', 'activar')->name('predicciones.activar');
        });

        Route::controller(DiagnosticoController::class)->prefix('diagnosticos')->group(function () {
            Route::get('/', 'index')->name('diagnosticos.index');
            Route::post('store', 'store')->name('diagnosticos.store');
            Route::put('update/{diagnostico}', 'update')->name('diagnosticos.update');
            Route::delete('delete/{diagnostico}', 'destroy')->name('diagnosticos.destroy');
            Route::put('activar/{diagnostico}', 'activar')->name('diagnosticos.activar');
            
            // Valoración rápida desde Historial (AJAX)
            Route::middleware(['rol:administrador,cardiologo'])->group(function () {
                Route::post('{imagen}/review', 'review')->name('diagnosticos.review');
                Route::delete('{imagen}/review', 'deleteReview')->name('diagnosticos.deleteReview');
            });
        });
    });

    // 4. MODULO MANTENIMIENTOS
    Route::prefix('mantenimientos')->middleware(['rol:administrador'])->group(function () {

        Route::controller(RitmoCardiacoController::class)->prefix('ritmos-cardiacos')->group(function () {
            Route::get('/', 'index')->name('ritmos-cardiacos.index');
            Route::post('store', 'store')->name('ritmos-cardiacos.store');
            Route::put('update/{ritmoCardiaco}', 'update')->name('ritmos-cardiacos.update');
            Route::delete('delete/{ritmoCardiaco}', 'destroy')->name('ritmos-cardiacos.destroy');
            Route::put('activar/{ritmoCardiaco}', 'activar')->name('ritmos-cardiacos.activar');
        });

        Route::controller(GrupoCardiacoController::class)->prefix('grupos-cardiacos')->group(function () {
            Route::get('/', 'index')->name('grupos-cardiacos.index');
            Route::post('store', 'store')->name('grupos-cardiacos.store');
            Route::put('update/{grupoCardiaco}', 'update')->name('grupos-cardiacos.update');
            Route::delete('delete/{grupoCardiaco}', 'destroy')->name('grupos-cardiacos.destroy');
            Route::put('activar/{grupoCardiaco}', 'activar')->name('grupos-cardiacos.activar');
        });

        Route::controller(NivelGravedadController::class)->prefix('niveles-gravedad')->group(function () {
            Route::get('/', 'index')->name('niveles-gravedad.index');
            Route::post('store', 'store')->name('niveles-gravedad.store');
            Route::put('update/{nivelGravedad}', 'update')->name('niveles-gravedad.update');
            Route::delete('delete/{nivelGravedad}', 'destroy')->name('niveles-gravedad.destroy');
            Route::put('activar/{nivelGravedad}', 'activar')->name('niveles-gravedad.activar');
        });

        Route::controller(ClasificacionArritmiaController::class)->prefix('clasificaciones-arritmia')->group(function () {
            Route::get('/', 'index')->name('clasificaciones-arritmia.index');
            Route::post('store', 'store')->name('clasificaciones-arritmia.store');
            Route::put('update/{clasificacionArritmia}', 'update')->name('clasificaciones-arritmia.update');
            Route::delete('delete/{clasificacionArritmia}', 'destroy')->name('clasificaciones-arritmia.destroy');
            Route::put('activar/{clasificacionArritmia}', 'activar')->name('clasificaciones-arritmia.activar');
        });

        Route::controller(PrefijoPacienteController::class)->prefix('prefijos-paciente')->group(function () {
            Route::get('/', 'index')->name('prefijos-paciente.index');
            Route::post('store', 'store')->name('prefijos-paciente.store');
            Route::put('update/{prefijoPaciente}', 'update')->name('prefijos-paciente.update');
            Route::delete('delete/{prefijoPaciente}', 'destroy')->name('prefijos-paciente.destroy');
            Route::put('activar/{prefijoPaciente}', 'activar')->name('prefijos-paciente.activar');
        });
    });

    // 5. MODULO REPORTES
    Route::prefix('reportes')->group(function () {
        Route::controller(ReporteController::class)->group(function () {
            Route::get('/', 'index')->name('reportes.index');
            Route::post('descargar', 'download')->name('reportes.download');
            Route::post('store', 'store')->name('reportes.store');
            Route::put('update/{reporte}', 'update')->name('reportes.update');
            Route::delete('delete/{reporte}', 'destroy')->name('reportes.destroy');
            Route::put('activar/{reporte}', 'activar')->name('reportes.activar');
        });
    });

    // RUTA PARA DESCARGAR EL REPORTE PDF DEL ESTUDIO (PROTEGIDA)
    Route::get('reportes/estudio/{id}/pdf', [App\Http\Controllers\ReporteController::class, 'descargarPDF'])->name('reportes.estudio.pdf');
});
