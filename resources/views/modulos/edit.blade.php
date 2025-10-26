@extends('layouts.app')

@section('title', 'Editar Módulo')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Editar Módulo
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('modulos.update', $modulo->codigo) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label for="codigo" class="form-label">Código del Módulo</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="codigo" 
                                   value="{{ $modulo->codigo }}"
                                   disabled>
                            <small class="text-muted">El código del módulo no se puede modificar (es la clave primaria)</small>
                        </div>

                        <div class="mb-4">
                            <label for="ubicacion" class="form-label">
                                Ubicación <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('ubicacion') is-invalid @enderror" 
                                   id="ubicacion" 
                                   name="ubicacion" 
                                   value="{{ old('ubicacion', $modulo->ubicacion) }}"
                                   maxlength="50"
                                   required>
                            @error('ubicacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-1"></i>Actualizar
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
