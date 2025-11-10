@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Editar Grupo: {{ $grupo->sigla }}
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

                    <form action="{{ route('grupos.update', $grupo->sigla) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label for="sigla" class="form-label">
                                Sigla del Grupo <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('sigla') is-invalid @enderror" 
                                   id="sigla" 
                                   name="sigla" 
                                   value="{{ old('sigla', $grupo->sigla) }}"
                                   placeholder="Ej: A1, B2, C3"
                                   maxlength="3"
                                   required>
                            @error('sigla')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Máximo 3 caracteres (Ej: A1, GRP)</small>
                        </div>

                        <div class="mb-4">
                            <label for="id_periodo" class="form-label">
                                Período Académico <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('id_periodo') is-invalid @enderror" 
                                    id="id_periodo" 
                                    name="id_periodo" 
                                    required>
                                <option value="">Seleccione un período</option>
                                @foreach($periodos as $periodo)
                                    <option value="{{ $periodo->id }}" 
                                        {{ old('id_periodo', $grupo->id_periodo) == $periodo->id ? 'selected' : '' }}
                                        {{ $periodo->activo ? 'style=font-weight:bold;' : '' }}>
                                        {{ $periodo->abreviatura }}
                                        {{ $periodo->activo ? '(Activo)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_periodo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Puede cambiar el período del grupo</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Nota:</strong> Para modificar las materias y docentes, use la opción 
                            <a href="{{ route('grupos.asignar-docentes', $grupo->sigla) }}" class="alert-link">Gestionar Docentes</a>
                        </div>

                        @if($grupo->grupoMaterias && $grupo->grupoMaterias->count() > 0)
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Asignaciones Actuales</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2"><strong>Total:</strong> {{ $grupo->grupoMaterias->count() }} materia(s) con docente(s) asignados</p>
                                    <ul class="mb-0">
                                        @foreach($grupo->grupoMaterias as $gm)
                                            <li>{{ $gm->materia->sigla }} - {{ $gm->materia->nombre }} (Docente: {{ $gm->docente->nombre }})</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Este grupo aún no tiene materias ni docentes asignados
                            </div>
                        @endif

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('grupos.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </a>
                            <a href="{{ route('grupos.asignar-docentes', $grupo->sigla) }}" class="btn btn-success">
                                <i class="fas fa-user-plus me-1"></i>Gestionar Docentes
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-1"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
