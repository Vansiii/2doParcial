@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>Asignar Docentes al Grupo {{ $grupo->sigla }}
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Información del Grupo -->
                    <div class="alert alert-info mb-4">
                        <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Información del Grupo</h6>
                        <div><strong>Sigla:</strong> {{ $grupo->sigla }}</div>
                        <div><strong>Materias:</strong> 
                            @if($grupo->materias && $grupo->materias->count() > 0)
                                @foreach($grupo->materias as $materia)
                                    <span class="badge bg-info">{{ $materia->sigla }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">Sin materias asignadas</span>
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('grupos.guardar-docentes', $grupo->sigla) }}" method="POST">
                        @csrf

                        <!-- Lista de Docentes -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-chalkboard-teacher me-2"></i>Seleccione los Docentes
                            </label>
                            
                            @if($docentes->count() > 0)
                                <div class="card">
                                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                        @foreach($docentes as $docente)
                                            <div class="form-check mb-2">
                                                <input 
                                                    class="form-check-input" 
                                                    type="checkbox" 
                                                    name="docentes[]" 
                                                    value="{{ $docente->id }}" 
                                                    id="docente{{ $docente->id }}"
                                                    {{ $grupo->docentes->contains($docente->id) ? 'checked' : '' }}
                                                >
                                                <label class="form-check-label" for="docente{{ $docente->id }}">
                                                    <i class="fas fa-user-tie text-primary me-2"></i>
                                                    <strong>{{ $docente->nombre }}</strong>
                                                    @if($docente->correo)
                                                        <small class="text-muted">({{ $docente->correo }})</small>
                                                    @endif
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Controles de selección -->
                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                                        <i class="fas fa-check-square me-1"></i>Seleccionar Todos
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">
                                        <i class="fas fa-square me-1"></i>Deseleccionar Todos
                                    </button>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    No hay docentes registrados en el sistema.
                                </div>
                            @endif
                        </div>

                        <!-- Botones de acción -->
                        <div class="d-flex gap-2 justify-content-between">
                            <a href="{{ route('grupos.show', $grupo->sigla) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Cancelar
                            </a>
                            @if($docentes->count() > 0)
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i>Guardar Asignación
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Docentes Actualmente Asignados -->
            @if($grupo->docentes && $grupo->docentes->count() > 0)
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-users me-2"></i>Docentes Actualmente Asignados
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($grupo->docentes as $docente)
                            <div class="list-group-item">
                                <i class="fas fa-user-tie text-success me-2"></i>
                                <strong>{{ $docente->nombre }}</strong>
                                @if($docente->correo)
                                    <small class="text-muted ms-2">{{ $docente->correo }}</small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function selectAll() {
    const checkboxes = document.querySelectorAll('input[name="docentes[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = true);
}

function deselectAll() {
    const checkboxes = document.querySelectorAll('input[name="docentes[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);
}
</script>
@endsection
