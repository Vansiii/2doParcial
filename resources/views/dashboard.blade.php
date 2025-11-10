@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4 text-gray-800">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </h1>
    </div>
</div>

@if($usuario->hasRole('Administrador') || $usuario->hasRole('Coordinador'))
<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Usuarios</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['usuarios'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Materias</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['materias'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-book fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Aulas</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['aulas'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-door-open fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Grupos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['grupos'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-friends fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Semestres</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['semestres'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Módulos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['modulos'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-building fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Carreras</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['carreras'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Justificaciones Pendientes (Admin) -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Justificaciones Pendientes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['justificaciones_pendientes'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Estadísticas para Docentes -->
@if($usuario->hasRole('Docente'))
<div class="row">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Mis Justificaciones</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['mis_justificaciones'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-list-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pendientes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['justificaciones_pendientes'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Aprobadas</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['justificaciones_aprobadas'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <!-- Información del Usuario -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-user"></i> Información de Usuario</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Nombre:</th>
                        <td>{{ $usuario->nombre }}</td>
                    </tr>
                    <tr>
                        <th>Correo:</th>
                        <td>{{ $usuario->correo }}</td>
                    </tr>
                    <tr>
                        <th>Teléfono:</th>
                        <td>{{ $usuario->telefono }}</td>
                    </tr>
                    <tr>
                        <th>Roles:</th>
                        <td>
                            @foreach($roles as $rol)
                                <span class="badge bg-primary">{{ $rol->descripcion }}</span>
                            @endforeach
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Accesos Rápidos -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-link"></i> Accesos Rápidos</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($usuario->hasRole('Administrador') || $usuario->hasRole('Coordinador'))
                    <a href="{{ route('horarios.asignar') }}" class="btn btn-outline-primary">
                        <i class="fas fa-calendar-plus"></i> Asignar Horarios
                    </a>
                    <a href="{{ route('materias.index') }}" class="btn btn-outline-success">
                        <i class="fas fa-book"></i> Gestionar Materias
                    </a>
                    <a href="{{ route('grupos.index') }}" class="btn btn-outline-info">
                        <i class="fas fa-users"></i> Gestionar Grupos
                    </a>
                    @endif

                    @if($usuario->hasRole('Administrador') || $usuario->hasRole('Autoridad') || $usuario->hasRole('Coordinador'))
                    <a href="{{ route('justificaciones.index') }}" class="btn btn-outline-warning">
                        <i class="fas fa-clipboard-list"></i> Revisar Justificaciones
                    </a>
                    <a href="{{ route('reportes.index') }}" class="btn btn-outline-info">
                        <i class="fas fa-chart-bar"></i> Generar Reportes
                    </a>
                    @endif

                    @if($usuario->hasRole('Docente'))
                    <a href="{{ route('asistencias.marcar') }}" class="btn btn-outline-success">
                        <i class="fas fa-clipboard-check"></i> Marcar Asistencia
                    </a>
                    <a href="{{ route('justificaciones.create') }}" class="btn btn-outline-warning">
                        <i class="fas fa-file-medical"></i> Nueva Justificación
                    </a>
                    @endif
                    
                    <a href="{{ route('horarios.docente', auth()->id()) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-calendar"></i> Ver Mi Horario
                    </a>
                    
                    <a href="{{ route('change-password') }}" class="btn btn-outline-info">
                        <i class="fas fa-key"></i> Cambiar Contraseña
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
