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
                    <a href="{{ route('docentes.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-chalkboard-teacher"></i> Gestionar Docentes
                    </a>
                    <a href="{{ route('docentes.create') }}" class="btn btn-outline-success">
                        <i class="fas fa-user-plus"></i> Registrar Nuevo Docente
                    </a>
                    @endif
                    
                    <a href="{{ route('change-password') }}" class="btn btn-outline-warning">
                        <i class="fas fa-key"></i> Cambiar Contraseña
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
