@extends('layouts.app')

@section('title', 'Cambiar Contraseña')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-key"></i> Cambiar Contraseña
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('change-password.post') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">
                            <i class="fas fa-lock"></i> Contraseña Actual <span class="text-danger">*</span>
                        </label>
                        <input type="password" 
                               class="form-control @error('current_password') is-invalid @enderror" 
                               id="current_password" 
                               name="current_password" 
                               required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">
                            <i class="fas fa-key"></i> Nueva Contraseña <span class="text-danger">*</span>
                        </label>
                        <input type="password" 
                               class="form-control @error('new_password') is-invalid @enderror" 
                               id="new_password" 
                               name="new_password" 
                               required
                               minlength="6">
                        <small class="form-text text-muted">Mínimo 6 caracteres</small>
                        @error('new_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">
                            <i class="fas fa-key"></i> Confirmar Nueva Contraseña <span class="text-danger">*</span>
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="new_password_confirmation" 
                               name="new_password_confirmation" 
                               required
                               minlength="6">
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cambiar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle"></i>
            <strong>Nota:</strong> Por seguridad, después de cambiar su contraseña, se cerrará su sesión automáticamente.
        </div>
    </div>
</div>
@endsection
