@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Gestionar Períodos Académicos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Materia:</strong> {{ $materia->sigla }} - {{ $materia->nombre }} (Nivel {{ $materia->nivel }})
                    </div>

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

                    <form action="{{ route('materias.actualizar-periodos', $materia->sigla) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-calendar-check me-1"></i>Períodos Académicos
                            </label>
                            <p class="text-muted">Seleccione los períodos en los que esta materia está activa</p>
                            
                            <div class="card">
                                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                    @if($periodos->count() > 0)
                                        <div class="row">
                                            @foreach($periodos as $periodo)
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" 
                                                               type="checkbox" 
                                                               name="periodos[]" 
                                                               value="{{ $periodo->id }}"
                                                               id="periodo_{{ $periodo->id }}"
                                                               {{ in_array($periodo->id, $periodosAsignados) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="periodo_{{ $periodo->id }}">
                                                            <strong class="{{ $periodo->activo ? 'text-success' : '' }}">
                                                                {{ $periodo->abreviatura }}
                                                            </strong>
                                                            @if($periodo->activo)
                                                                <span class="badge bg-success ms-1">Activo</span>
                                                            @endif
                                                            <br>
                                                            <small class="text-muted">
                                                                Gestión {{ $periodo->gestion }} - {{ ucfirst($periodo->periodo) }}
                                                            </small>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-info-circle me-1"></i>
                                            No hay períodos académicos disponibles. 
                                            <a href="{{ route('periodos.create') }}">Crear un período</a>
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <small class="text-muted">
                                La materia solo estará disponible para grupos en los períodos seleccionados
                            </small>
                        </div>

                        @if($materia->periodos && $materia->periodos->count() > 0)
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Períodos actuales:</strong> {{ $materia->periodos->count() }} período(s) asignado(s)
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Esta materia aún no está asignada a ningún período académico
                            </div>
                        @endif

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('materias.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>Guardar Períodos
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
