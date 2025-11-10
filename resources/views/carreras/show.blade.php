@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>Detalles de la Carrera
                    </h5>
                    @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Coordinador'))
                    <a href="{{ route('carreras.edit', $carrera->cod) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-edit me-1"></i>Editar
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Código:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="badge bg-primary fs-6">{{ $carrera->cod }}</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Nombre:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $carrera->nombre }}
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('carreras.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                </div>
            </div>

            <!-- Materias del Plan de Estudios -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-book me-2"></i>Plan de Estudios ({{ $carrera->materias->count() }} materias)
                    </h6>
                </div>
                <div class="card-body">
                    @if($carrera->materias && $carrera->materias->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sigla</th>
                                        <th>Nombre</th>
                                        <th>Nivel</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($carrera->materias->sortBy('sigla') as $materia)
                                        <tr>
                                            <td>
                                                <span class="badge bg-info">{{ $materia->sigla }}</span>
                                            </td>
                                            <td>{{ $materia->nombre }}</td>
                                            <td>
                                                <span class="badge bg-secondary">Nivel {{ $materia->nivel }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Esta carrera no tiene materias asignadas en su plan de estudios.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Estadísticas -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Estadísticas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span><i class="fas fa-book text-primary me-2"></i>Materias:</span>
                        <strong>{{ $carrera->materias ? $carrera->materias->count() : 0 }}</strong>
                    </div>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Información
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-0">
                        <i class="fas fa-check-circle text-success me-1"></i>
                        Carrera registrada en el sistema
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
