@extends('layouts.app')

@section('title', 'Detalle del Semestre')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Detalle del Período Académico
                    </h4>
                    <a href="{{ route('semestres.edit', $semestre->id) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-edit me-1"></i>Editar
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Abreviatura:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="badge bg-{{ $semestre->activo ? 'success' : 'info' }} fs-5">
                                <i class="fas fa-calendar me-1"></i>{{ $semestre->abreviatura }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Gestión:</strong>
                        </div>
                        <div class="col-md-8">
                            <strong>{{ $semestre->gestion }}</strong>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Período:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $semestre->periodo }} {{ $semestre->periodo == 1 ? '(Primer Semestre)' : '(Segundo Semestre)' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Estado:</strong>
                        </div>
                        <div class="col-md-8">
                            @if($semestre->activo)
                                <span class="badge bg-success fs-6">
                                    <i class="fas fa-check-circle me-1"></i>Activo
                                </span>
                            @else
                                <span class="badge bg-secondary fs-6">Inactivo</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Fecha de Inicio:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ \Carbon\Carbon::parse($semestre->fechaini)->format('d/m/Y') }}
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <strong>Fecha de Fin:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ \Carbon\Carbon::parse($semestre->fechafin)->format('d/m/Y') }}
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">
                                <i class="fas fa-users me-2 text-primary"></i>Grupos Asignados
                            </h5>
                            @if($semestre->grupos && $semestre->grupos->count() > 0)
                                <ul class="list-group list-group-flush">
                                    @foreach($semestre->grupos as $grupo)
                                        <li class="list-group-item px-0">
                                            <i class="fas fa-users me-2 text-success"></i>
                                            Grupo <strong>{{ $grupo->sigla }}</strong>
                                            @if($grupo->grupoMaterias && $grupo->grupoMaterias->count() > 0)
                                                <small class="text-muted">({{ $grupo->grupoMaterias->count() }} materias)</small>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No hay grupos asignados a este período
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <h5 class="mb-3">
                                <i class="fas fa-book me-2 text-primary"></i>Materias Ofertadas
                            </h5>
                            @if($semestre->materias && $semestre->materias->count() > 0)
                                <ul class="list-group list-group-flush">
                                    @foreach($semestre->materias as $materia)
                                        <li class="list-group-item px-0">
                                            <i class="fas fa-book me-2 text-{{ $materia->pivot->activa ? 'success' : 'secondary' }}"></i>
                                            <strong>{{ $materia->sigla }}</strong> - {{ $materia->nombre }}
                                            @if($materia->pivot->activa)
                                                <span class="badge bg-success ms-1">Activa</span>
                                            @else
                                                <span class="badge bg-secondary ms-1">Inactiva</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No hay materias asignadas a este período
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('semestres.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
