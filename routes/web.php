<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\SemestreController;
use App\Http\Controllers\ModuloController;

// Ruta principal - redirigir a login
Route::get('/', function () {
    return redirect('/login');
});

// Rutas de autenticación (CU01, CU02)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Rutas protegidas (requieren autenticación)
Route::middleware('auth')->group(function () {
    // CU02: Cerrar Sesión
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // CU03: Cambiar Contraseña (todos los usuarios autenticados)
    Route::get('/change-password', [AuthController::class, 'showChangePasswordForm'])->name('change-password');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('change-password.post');
    
    // CU04 y CU05: Gestión de Docentes (solo Administrador y Coordinador)
    Route::middleware('role:Administrador,Coordinador')->group(function () {
        Route::resource('docentes', DocenteController::class);
        
        // CU07: Gestionar Materias
        Route::resource('materias', MateriaController::class);
        
        // CU08: Gestionar Aulas
        Route::resource('aulas', AulaController::class);
        
        // CU09: Gestionar Grupos
        Route::resource('grupos', GrupoController::class);
        
        // Asignar Docentes a Grupos
        Route::get('grupos/{sigla}/asignar-docentes', [GrupoController::class, 'asignarDocentes'])->name('grupos.asignar-docentes');
        Route::post('grupos/{sigla}/guardar-docentes', [GrupoController::class, 'guardarDocentes'])->name('grupos.guardar-docentes');
        
        // Gestionar Semestres
        Route::resource('semestres', SemestreController::class);
        
        // Gestionar Módulos
        Route::resource('modulos', ModuloController::class);
        
        // CU10: Asignar Horario Manualmente
        Route::get('horarios/asignar', [HorarioController::class, 'asignar'])->name('horarios.asignar');
        Route::post('horarios/guardar', [HorarioController::class, 'guardar'])->name('horarios.guardar');
        Route::delete('horarios/{id}', [HorarioController::class, 'destroy'])->name('horarios.destroy');
    });
    
    // CU11: Consultar Horario por Docente (todos los usuarios)
    Route::get('horarios/docente/{id?}', [HorarioController::class, 'porDocente'])->name('horarios.docente');
    
    // CU12: Consultar Horario por Grupo (Administrador, Coordinador, Docente)
    Route::middleware('role:Administrador,Coordinador,Docente')->group(function () {
        Route::get('horarios/grupo/{id?}', [HorarioController::class, 'porGrupo'])->name('horarios.grupo');
    });
});
