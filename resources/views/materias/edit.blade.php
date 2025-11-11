@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Editar Materia
                    </h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('materias.update', $materia->sigla) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sigla" class="form-label">Sigla</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="sigla" 
                                       value="{{ $materia->sigla }}"
                                       disabled>
                                <small class="text-muted">La sigla no se puede modificar</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="nivel" class="form-label">Nivel Curricular</label>
                                <input type="number" 
                                       class="form-control @error('nivel') is-invalid @enderror" 
                                       id="nivel" 
                                       name="nivel" 
                                       value="{{ old('nivel', $materia->nivel) }}"
                                       min="0"
                                       max="10"
                                       placeholder="0-10">
                                @error('nivel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Nivel curricular (0 = sin especificar)</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                Nombre de la Materia <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control text-uppercase @error('nombre') is-invalid @enderror" 
                                   id="nombre" 
                                   name="nombre" 
                                   value="{{ old('nombre', $materia->nombre) }}"
                                   maxlength="100"
                                   style="text-transform: uppercase;"
                                   required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Máximo 100 caracteres (se guardará en MAYÚSCULAS)</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Los campos marcados con <span class="text-danger">*</span> son obligatorios
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('materias.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-1"></i>Actualizar Materia
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
