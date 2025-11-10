@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>Registrar Nuevo Usuario
                    </h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Error:</strong>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('usuarios.store') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="codigo" class="form-label">
                                    <i class="fas fa-id-card me-1"></i>Código *
                                </label>
                                <input type="number" 
                                       class="form-control @error('codigo') is-invalid @enderror" 
                                       id="codigo" 
                                       name="codigo" 
                                       value="{{ old('codigo') }}" 
                                       placeholder="Ej: 123456"
                                       required>
                                @error('codigo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="ci" class="form-label">
                                    <i class="fas fa-id-badge me-1"></i>CI (Cédula de Identidad) *
                                </label>
                                <input type="number" 
                                       class="form-control @error('ci') is-invalid @enderror" 
                                       id="ci" 
                                       name="ci" 
                                       value="{{ old('ci') }}" 
                                       placeholder="Ej: 1234567"
                                       min="1"
                                       max="99999999"
                                       required>
                                @error('ci')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="nombre" class="form-label">
                                    <i class="fas fa-user me-1"></i>Nombre Completo *
                                </label>
                                <input type="text" 
                                       class="form-control @error('nombre') is-invalid @enderror" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="{{ old('nombre') }}" 
                                       placeholder="Ej: Juan Pérez" 
                                       maxlength="40"
                                       style="text-transform: uppercase;"
                                       required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">El nombre se guardará automáticamente en MAYÚSCULAS</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="correo" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Correo Electrónico *
                                </label>
                                <input type="email" 
                                       class="form-control @error('correo') is-invalid @enderror" 
                                       id="correo" 
                                       name="correo" 
                                       value="{{ old('correo') }}" 
                                       placeholder="ejemplo@correo.com" 
                                       maxlength="40"
                                       required>
                                @error('correo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="telefono" class="form-label">
                                    <i class="fas fa-phone me-1"></i>Teléfono *
                                </label>
                                <input type="number" 
                                       class="form-control @error('telefono') is-invalid @enderror" 
                                       id="telefono" 
                                       name="telefono" 
                                       value="{{ old('telefono') }}" 
                                       placeholder="Ej: 77123456"
                                       required>
                                @error('telefono')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>Contraseña *
                                </label>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password"
                                       placeholder="Mínimo 6 caracteres"
                                       required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">
                                    <i class="fas fa-lock me-1"></i>Confirmar Contraseña *
                                </label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation"
                                       placeholder="Repita la contraseña"
                                       required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-user-tag me-1"></i>Roles * (Seleccione al menos uno)
                            </label>
                            <div class="card">
                                <div class="card-body">
                                    @if($roles->count() > 0)
                                        <div class="row">
                                            @foreach($roles as $rol)
                                                <div class="col-md-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" 
                                                               type="checkbox" 
                                                               name="roles[]" 
                                                               value="{{ $rol->id }}"
                                                               id="rol_{{ $rol->id }}"
                                                               {{ in_array($rol->id, old('roles', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="rol_{{ $rol->id }}">
                                                            {{ $rol->descripcion }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-info-circle me-1"></i>No hay roles disponibles.
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>Registrar Usuario
                            </button>
                            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
