@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-book me-2"></i>Detalles de la Materia
                    </h5>
                    @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Coordinador'))
                    <a href="{{ route('materias.edit', $materia->sigla) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-edit me-1"></i>Editar
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Sigla:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="badge bg-primary fs-6">{{ $materia->sigla }}</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Nombre:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $materia->nombre }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Semestre:</strong>
                        </div>
                        <div class="col-md-8">
                            @if($materia->semestre)
                                <span class="badge bg-info">
                                    {{ \Carbon\Carbon::parse($materia->semestre->fechaini)->format('Y') }} - 
                                    {{ $materia->semestre->periodo }}
                                </span>
                                <br>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($materia->semestre->fechaini)->format('d/m/Y') }} - 
                                    {{ \Carbon\Carbon::parse($materia->semestre->fechafin)->format('d/m/Y') }}
                                </small>
                            @else
                                <span class="text-muted">Sin semestre asignado</span>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('materias.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Docentes asignados por Grupo -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chalkboard-teacher me-2"></i>Docentes por Grupo
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        // Obtener asignaciones grupo-materia para esta materia
                        $asignaciones = $materia->grupoMaterias;
                    @endphp
                    
                    @if($asignaciones && $asignaciones->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($asignaciones as $gm)
                                <li class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="badge bg-success mb-1">{{ $gm->grupo->sigla }}</span>
                                            <br>
                                            @if($gm->docente)
                                                <i class="fas fa-user-tie me-1 text-primary"></i>
                                                <small>{{ $gm->docente->nombre }}</small>
                                            @else
                                                <small class="text-muted">Sin docente</small>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                                    </small>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            No hay docentes asignados a los grupos de esta materia
                        </p>
                    @endif
                </div>
            </div>

            <!-- Grupos -->
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-users me-2"></i>Grupos
                    </h6>
                </div>
                <div class="card-body">
                    @if($materia->grupos->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($materia->grupos as $grupo)
                                <li class="list-group-item px-0">
                                    <i class="fas fa-users me-2 text-success"></i>
                                    Grupo {{ $grupo->sigla }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            No hay grupos asignados
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
