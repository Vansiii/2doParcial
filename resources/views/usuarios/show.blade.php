@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-circle me-2"></i>Detalles del Usuario
                    </h5>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="fas fa-id-card me-2"></i>Código:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="badge bg-primary fs-6">{{ $usuario->codigo }}</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="fas fa-user me-2"></i>Nombre:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $usuario->nombre }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="fas fa-envelope me-2"></i>Correo:</strong>
                        </div>
                        <div class="col-md-8">
                            <a href="mailto:{{ $usuario->correo }}">{{ $usuario->correo }}</a>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="fas fa-phone me-2"></i>Teléfono:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $usuario->telefono }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="fas fa-user-tag me-2"></i>Roles:</strong>
                        </div>
                        <div class="col-md-8">
                            @if($usuario->roles && $usuario->roles->count() > 0)
                                @foreach($usuario->roles as $rol)
                                    <span class="badge bg-info me-1">
                                        {{ $rol->descripcion }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-muted">Sin roles asignados</span>
                            @endif
                        </div>
                    </div>

                    @if(auth()->user()->hasRole('Administrador'))
                    <div class="d-flex gap-2">
                        <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>Editar
                        </a>
                        @if($usuario->id !== auth()->id())
                        <form action="{{ route('usuarios.destroy', $usuario->id) }}" 
                              method="POST" 
                              onsubmit="return confirm('¿Está seguro de eliminar este usuario?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-1"></i>Eliminar
                            </button>
                        </form>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
