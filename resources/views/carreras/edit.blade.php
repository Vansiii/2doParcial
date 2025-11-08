@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Editar Carrera
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

                    <form action="{{ route('carreras.update', $carrera->cod) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Información básica -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="cod" class="form-label">
                                    <i class="fas fa-hashtag me-1"></i>Código
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="cod" 
                                       value="{{ $carrera->cod }}"
                                       disabled>
                                <small class="text-muted">El código no se puede modificar</small>
                            </div>

                            <div class="col-md-8">
                                <label for="nombre" class="form-label">
                                    <i class="fas fa-graduation-cap me-1"></i>Nombre de la Carrera *
                                </label>
                                <input type="text" 
                                       class="form-control @error('nombre') is-invalid @enderror" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="{{ old('nombre', $carrera->nombre) }}" 
                                       placeholder="Ej: Ingeniería de Sistemas" 
                                       maxlength="50"
                                       required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Máximo 50 caracteres</small>
                            </div>
                        </div>

                        <!-- Materias -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-book me-1"></i>Materias del Plan de Estudios
                            </label>
                            <div class="card">
                                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                    @if($materias->count() > 0)
                                        <div class="row">
                                            @foreach($materias as $materia)
                                                <div class="col-md-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" 
                                                               type="checkbox" 
                                                               name="materias[]" 
                                                               value="{{ $materia->sigla }}"
                                                               id="materia_{{ $materia->sigla }}"
                                                               {{ in_array($materia->sigla, old('materias', $carrera->materias->pluck('sigla')->toArray())) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="materia_{{ $materia->sigla }}">
                                                            <strong>{{ $materia->sigla }}</strong> - {{ $materia->nombre }}
                                                            @if($materia->semestre)
                                                                <span class="badge bg-info ms-1">
                                                                    Sem {{ $materia->semestre->periodo }}
                                                                </span>
                                                            @endif
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <!-- Controles de selección -->
                                        <div class="mt-3 pt-3 border-top">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllMaterias()">
                                                <i class="fas fa-check-square me-1"></i>Seleccionar Todas
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllMaterias()">
                                                <i class="fas fa-square me-1"></i>Deseleccionar Todas
                                            </button>
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-info-circle me-1"></i>
                                            No hay materias disponibles. 
                                            <a href="{{ route('materias.create') }}">Crear una materia</a>
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <small class="text-muted">Seleccione las materias que forman parte del plan de estudios</small>
                        </div>

                        <!-- Botones de acción -->
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('carreras.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-1"></i>Actualizar Carrera
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectAllMaterias() {
    const checkboxes = document.querySelectorAll('input[name="materias[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = true);
}

function deselectAllMaterias() {
    const checkboxes = document.querySelectorAll('input[name="materias[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);
}
</script>
@endsection
