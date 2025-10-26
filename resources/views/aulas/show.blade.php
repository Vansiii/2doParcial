@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-door-open me-2"></i>Detalles del Aula
                    </h5>
                    @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Coordinador'))
                    <a href="{{ route('aulas.edit', $aula->nroaula) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-edit me-1"></i>Editar
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Número de Aula:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="badge bg-primary fs-6">{{ $aula->nroaula }}</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Piso:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="badge bg-secondary">
                                <i class="fas fa-layer-group me-1"></i>Piso {{ $aula->piso }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Capacidad:</strong>
                        </div>
                        <div class="col-md-8">
                            <i class="fas fa-users text-info me-2"></i>
                            <strong>{{ $aula->capacidad }}</strong> personas
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Módulo:</strong>
                        </div>
                        <div class="col-md-8">
                            @if($aula->modulo)
                                <span class="badge bg-info fs-6">
                                    Código {{ $aula->modulo->codigo }} - {{ $aula->modulo->ubicacion }}
                                </span>
                            @else
                                <span class="text-muted">Sin módulo asignado</span>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('aulas.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Horarios asignados -->
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Horarios Asignados
                    </h6>
                </div>
                <div class="card-body">
                    @if($aula->horarios && $aula->horarios->count() > 0)
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>{{ $aula->horarios->count() }}</strong> horarios asignados
                        </div>
                        <ul class="list-group list-group-flush mt-3">
                            @foreach($aula->horarios->take(5) as $horario)
                                <li class="list-group-item px-0">
                                    <i class="fas fa-clock text-primary me-2"></i>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($horario->horaini)->format('H:i') }} - 
                                        {{ \Carbon\Carbon::parse($horario->horafin)->format('H:i') }}
                                    </small>
                                </li>
                            @endforeach
                        </ul>
                        @if($aula->horarios->count() > 5)
                            <small class="text-muted">
                                Y {{ $aula->horarios->count() - 5 }} más...
                            </small>
                        @endif
                    @else
                        <p class="text-muted mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            No hay horarios asignados
                        </p>
                    @endif
                </div>
            </div>

            <!-- Información adicional -->
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Información
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-map-marker-alt text-danger me-2"></i>
                        <span>Ubicación: Piso {{ $aula->piso }}</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-building text-info me-2"></i>
                        <span>{{ $aula->modulo ? 'Módulo ' . $aula->modulo->codigo . ' - ' . $aula->modulo->ubicacion : 'Sin módulo' }}</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chair text-success me-2"></i>
                        <span>Capacidad máxima: {{ $aula->capacidad }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
