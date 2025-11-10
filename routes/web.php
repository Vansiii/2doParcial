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
use App\Http\Controllers\CarreraController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\JustificacionController;
use App\Http\Controllers\ReporteController;

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
    
    // CU18: Gestionar Usuarios (solo Administrador)
    Route::middleware('role:Administrador')->group(function () {
        Route::resource('usuarios', UsuarioController::class);
    });

    // CU04 y CU05: Gestión de Docentes (solo Administrador y Coordinador)
    Route::middleware('role:Administrador,Coordinador')->group(function () {
        Route::resource('docentes', DocenteController::class);
        
        // CU07: Gestionar Materias
        Route::resource('materias', MateriaController::class);
        
        // CU08: Gestionar Aulas
        Route::resource('aulas', AulaController::class);
        
        // CU09: Gestionar Grupos
        Route::resource('grupos', GrupoController::class);
        
        // Asignar Docentes a Grupos (con grupo_materia)
        Route::get('grupos/{sigla}/asignar-docentes', [GrupoController::class, 'asignarDocentes'])->name('grupos.asignar-docentes');
        Route::post('grupos/{sigla}/guardar-docentes', [GrupoController::class, 'guardarDocentes'])->name('grupos.guardar-docentes');
        Route::delete('grupos/{sigla}/eliminar-docente/{siglaMateria}', [GrupoController::class, 'eliminarDocente'])->name('grupos.eliminar-docente');
        
        // CU10: Gestionar Semestres
        Route::resource('semestres', SemestreController::class);
        
        // CU11: Gestionar Módulos
        Route::resource('modulos', ModuloController::class);
        
        // CU19: Gestionar Carreras
        Route::resource('carreras', CarreraController::class);
        
        // CU12: Asignar Horario Manualmente
        Route::get('horarios/asignar', [HorarioController::class, 'asignar'])->name('horarios.asignar');
        Route::post('horarios/guardar', [HorarioController::class, 'guardar'])->name('horarios.guardar');
        Route::delete('horarios/{id}', [HorarioController::class, 'destroy'])->name('horarios.destroy');
    });
    
    // CU13: Consultar Horario por Docente (todos los usuarios)
    Route::get('horarios/docente/{id?}', [HorarioController::class, 'porDocente'])->name('horarios.docente');
    
    // CU14: Consultar Horario por Grupo (Administrador, Coordinador, Docente)
    Route::middleware('role:Administrador,Coordinador,Docente')->group(function () {
        Route::get('horarios/grupo/{id?}', [HorarioController::class, 'porGrupo'])->name('horarios.grupo');
    });

    // CU15: Gestionar Asistencia
    // Marcar asistencia (solo Docentes)
    Route::middleware('role:Docente')->group(function () {
        Route::get('asistencias/marcar', [AsistenciaController::class, 'mostrarFormulario'])->name('asistencias.marcar');
        Route::post('asistencias/marcar', [AsistenciaController::class, 'marcar'])->name('asistencias.marcar.post');
        Route::get('asistencias/mis-asistencias', [AsistenciaController::class, 'misAsistencias'])->name('asistencias.mis-asistencias');
    });

    // Consultar asistencias de todos los docentes (Administrador, Autoridad, Coordinador)
    Route::middleware('role:Administrador,Autoridad,Coordinador')->group(function () {
        Route::get('asistencias', [AsistenciaController::class, 'index'])->name('asistencias.index');
    });

    // CU16: Gestionar Justificaciones
    // Rutas para Docentes
    Route::middleware('role:Docente')->group(function () {
        Route::get('justificaciones/mis-justificaciones', [JustificacionController::class, 'misJustificaciones'])->name('justificaciones.mis-justificaciones');
        Route::get('justificaciones/create', [JustificacionController::class, 'create'])->name('justificaciones.create');
        Route::post('justificaciones', [JustificacionController::class, 'store'])->name('justificaciones.store');
    });

    // Rutas compartidas (Docentes y Administradores)
    Route::middleware('role:Docente,Administrador,Autoridad,Coordinador')->group(function () {
        Route::get('justificaciones/{id}', [JustificacionController::class, 'show'])->name('justificaciones.show');
        Route::get('justificaciones/{id}/descargar', [JustificacionController::class, 'descargarArchivo'])->name('justificaciones.descargar');
    });

    // Rutas para Administrador, Autoridad, Coordinador
    Route::middleware('role:Administrador,Autoridad,Coordinador')->group(function () {
        Route::get('justificaciones', [JustificacionController::class, 'index'])->name('justificaciones.index');
        Route::patch('justificaciones/{id}/aprobar', [JustificacionController::class, 'aprobar'])->name('justificaciones.aprobar');
        Route::patch('justificaciones/{id}/rechazar', [JustificacionController::class, 'rechazar'])->name('justificaciones.rechazar');
    });

    // CU17: Generar y Exportar Reportes (Administrador, Autoridad, Coordinador)
    Route::middleware('role:Administrador,Autoridad,Coordinador')->group(function () {
        Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::post('reportes/horarios-semanal', [ReporteController::class, 'horariosSemanal'])->name('reportes.horarios-semanal');
        Route::post('reportes/carga-horaria', [ReporteController::class, 'cargaHoraria'])->name('reportes.carga-horaria');
        Route::post('reportes/asistencia', [ReporteController::class, 'asistencia'])->name('reportes.asistencia');
        Route::post('reportes/aulas-disponibles', [ReporteController::class, 'aulasDisponibles'])->name('reportes.aulas-disponibles');
    });
});
