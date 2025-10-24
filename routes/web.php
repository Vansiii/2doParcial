<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocenteController;

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
    Route::resource('docentes', DocenteController::class);
});
