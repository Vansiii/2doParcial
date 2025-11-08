@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>Detalles del Grupo
                    </h5>
                    @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Coordinador'))
                    <a href="{{ route('grupos.edit', $grupo->sigla) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-edit me-1"></i>Editar
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <strong>Número de Grupo:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="badge bg-primary fs-5">
                                <i class="fas fa-users me-1"></i>Grupo {{ $grupo->sigla }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <strong>Materias y Docentes:</strong>
                        </div>
                        <div class="col-md-8">
                            @if($grupo->grupoMaterias && $grupo->grupoMaterias->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Materia</th>
                                                <th>Docente</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($grupo->grupoMaterias as $gm)
                                                <tr>
                                                    <td><span class="badge bg-info">{{ $gm->materia->sigla }}</span></td>
                                                    <td>
                                                        @if($gm->docente)
                                                            <i class="fas fa-user-tie text-primary me-1"></i>
                                                            {{ $gm->docente->nombre }}
                                                        @else
                                                            <span class="text-muted">Sin asignar</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Sin docentes asignados
                                </div>
                            @endif
                            @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Coordinador'))
                            <a href="{{ route('grupos.asignar-docentes', $grupo->sigla) }}" class="btn btn-success btn-sm mt-2">
                                <i class="fas fa-user-plus me-1"></i>Gestionar Docentes
                            </a>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('grupos.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Volver
                        </a>
                        <a href="{{ route('horarios.grupo', $grupo->id) }}" class="btn btn-primary">
                            <i class="fas fa-calendar me-1"></i>Ver Horario
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Horarios asignados -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Horarios
                    </h6>
                </div>
                <div class="card-body">
                    @if($grupo->horarios && $grupo->horarios->count() > 0)
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>{{ $grupo->horarios->count() }}</strong> horarios asignados
                        </div>
                        <ul class="list-group list-group-flush mt-3">
                            @foreach($grupo->horarios->take(5) as $horario)
                                <li class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-clock text-primary me-1"></i>
                                            <small>
                                                {{ \Carbon\Carbon::parse($horario->horaini)->format('H:i') }} - 
                                                {{ \Carbon\Carbon::parse($horario->horafin)->format('H:i') }}
                                            </small>
                                        </div>
                                        @if($horario->aula)
                                            <span class="badge bg-info">{{ $horario->aula->nroaula }}</span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        @if($grupo->horarios->count() > 5)
                            <small class="text-muted mt-2 d-block">
                                Y {{ $grupo->horarios->count() - 5 }} más...
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

            <!-- Estadísticas -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Estadísticas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span><i class="fas fa-book text-primary me-2"></i>Materias:</span>
                        <strong>{{ $grupo->grupoMaterias ? $grupo->grupoMaterias->count() : 0 }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span><i class="fas fa-user-tie text-info me-2"></i>Asignaciones:</span>
                        <strong>{{ $grupo->grupoMaterias ? $grupo->grupoMaterias->count() : 0 }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><i class="fas fa-calendar text-success me-2"></i>Horarios:</span>
                        <strong>{{ $grupo->horarios ? $grupo->horarios->count() : 0 }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
