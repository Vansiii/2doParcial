@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Mis Asistencias
                    </h5>
                    <a href="{{ route('asistencias.marcar') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-clipboard-check me-1"></i>Marcar Asistencia
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('asistencias.mis-asistencias') }}" method="GET">
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label for="fecha_desde" class="form-label">Desde</label>
                                        <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" 
                                               value="{{ request('fecha_desde') }}">
                                    </div>
                                    <div class="col-md-5">
                                        <label for="fecha_hasta" class="form-label">Hasta</label>
                                        <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" 
                                               value="{{ request('fecha_hasta') }}">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end gap-2">
                                        <button type="submit" class="btn btn-primary flex-fill">
                                            <i class="fas fa-search me-1"></i>Buscar
                                        </button>
                                        <a href="{{ route('asistencias.mis-asistencias') }}" class="btn btn-secondary">
                                            <i class="fas fa-redo"></i>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabla de asistencias -->
                    @if($asistencias->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th><i class="fas fa-calendar me-1"></i>Fecha</th>
                                        <th><i class="fas fa-clock me-1"></i>Hora</th>
                                        <th><i class="fas fa-info-circle me-1"></i>Tipo</th>
                                        <th><i class="fas fa-clock me-1"></i>Horario</th>
                                        <th><i class="fas fa-book me-1"></i>Materia</th>
                                        <th><i class="fas fa-users me-1"></i>Grupo</th>
                                        <th><i class="fas fa-door-open me-1"></i>Aula</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($asistencias as $asistencia)
                                        <tr>
                                            <td>
                                                {{ \Carbon\Carbon::parse($asistencia->fecha)->format('d/m/Y') }}
                                            </td>
                                            <td>
                                                {{ $asistencia->hora }}
                                            </td>
                                            <td>
                                                @if($asistencia->tipo === 'Puntual')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check me-1"></i>{{ $asistencia->tipo }}
                                                    </span>
                                                @elseif($asistencia->tipo === 'Tardanza')
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>{{ $asistencia->tipo }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $asistencia->tipo }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $asistencia->horario->hora_inicio ?? 'N/A' }} - 
                                                {{ $asistencia->horario->hora_fin ?? 'N/A' }}
                                            </td>
                                            <td>
                                                @if($asistencia->horario && $asistencia->horario->materias->count() > 0)
                                                    <span class="badge bg-info">{{ $asistencia->horario->materias->first()->sigla }}</span>
                                                    <br><small>{{ $asistencia->horario->materias->first()->nombre }}</small>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                @if($asistencia->horario && $asistencia->horario->grupo)
                                                    Grupo {{ $asistencia->horario->grupo->sigla }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                {{ $asistencia->horario->aula->nroaula ?? 'N/A' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Mostrando {{ $asistencias->firstItem() }} - {{ $asistencias->lastItem() }} de {{ $asistencias->total() }} registros
                            </div>
                            {{ $asistencias->links() }}
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                            <p class="mb-0">No se encontraron registros de asistencia.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
