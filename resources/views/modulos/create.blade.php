@extends('layouts.app')

@section('title', 'Registrar Módulo')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-building me-2"></i>Registrar Nuevo Módulo
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('modulos.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="codigo" class="form-label">
                                Código del Módulo <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   class="form-control @error('codigo') is-invalid @enderror" 
                                   id="codigo" 
                                   name="codigo" 
                                   value="{{ old('codigo') }}"
                                   placeholder="Ej: 1, 2, 3"
                                   min="1"
                                   required>
                            @error('codigo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Ingrese un número único para identificar el módulo.</small>
                        </div>

                        <div class="mb-4">
                            <label for="ubicacion" class="form-label">
                                Ubicación <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('ubicacion') is-invalid @enderror" 
                                   id="ubicacion" 
                                   name="ubicacion" 
                                   value="{{ old('ubicacion') }}"
                                   placeholder="Ej: Edificio A, Bloque Norte, Módulo Principal"
                                   maxlength="50"
                                   required>
                            @error('ubicacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Máximo 50 caracteres. Indique la ubicación física del módulo.</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>Guardar
                            </button>
                            <a href="{{ route('modulos.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
