@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>Registrar Nuevo Grupo
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

                    <form action="{{ route('grupos.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="sigla" class="form-label">
                                Sigla del Grupo <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('sigla') is-invalid @enderror" 
                                   id="sigla" 
                                   name="sigla" 
                                   value="{{ old('sigla') }}"
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
                                        {{ old('id_periodo', $periodoActivo?->id) == $periodo->id ? 'selected' : '' }}
                                        {{ $periodo->activo ? 'style=font-weight:bold;' : '' }}>
                                        {{ $periodo->abreviatura }}
                                        {{ $periodo->activo ? '(Activo)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_periodo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">El período activo se selecciona por defecto</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-book me-1"></i>Materias Asignadas
                            </label>
                            <div class="card">
                                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
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
                                                               {{ in_array($materia->sigla, old('materias', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="materia_{{ $materia->sigla }}">
                                                            <strong>{{ $materia->sigla }}</strong> - {{ $materia->nombre }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
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
                            <small class="text-muted">Seleccione las materias que se dictarán en este grupo</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Los campos marcados con <span class="text-danger">*</span> son obligatorios
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('grupos.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Guardar Grupo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
