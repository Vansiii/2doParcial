@extends('layouts.app')

@section('title', 'Registrar Docente')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user-plus"></i> Registrar Nuevo Docente
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('docentes.store') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="nombre" class="form-label">
                                <i class="fas fa-user"></i> Nombre Completo <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('nombre') is-invalid @enderror" 
                                   id="nombre" 
                                   name="nombre" 
                                   value="{{ old('nombre') }}" 
                                   required
                                   maxlength="40"
                                   placeholder="Ej: Juan Pérez García">
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="correo" class="form-label">
                                <i class="fas fa-envelope"></i> Correo Electrónico <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   class="form-control @error('correo') is-invalid @enderror" 
                                   id="correo" 
                                   name="correo" 
                                   value="{{ old('correo') }}" 
                                   required
                                   maxlength="40"
                                   placeholder="ejemplo@correo.com">
                            @error('correo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">
                                <i class="fas fa-phone"></i> Teléfono <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   class="form-control @error('telefono') is-invalid @enderror" 
                                   id="telefono" 
                                   name="telefono" 
                                   value="{{ old('telefono') }}" 
                                   required
                                   placeholder="Ej: 71234567">
                            @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Contraseña <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required
                                   minlength="6"
                                   placeholder="Mínimo 6 caracteres">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">
                                <i class="fas fa-lock"></i> Confirmar Contraseña <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required
                                   minlength="6"
                                   placeholder="Repita la contraseña">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="detalle" class="form-label">
                            <i class="fas fa-info-circle"></i> Detalle (Opcional)
                        </label>
                        <textarea class="form-control" 
                                  id="detalle" 
                                  name="detalle" 
                                  rows="3"
                                  placeholder="Información adicional sobre el docente">{{ old('detalle') }}</textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Nota:</strong> El docente será registrado automáticamente con el rol de "Docente".
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('docentes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Registrar Docente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
