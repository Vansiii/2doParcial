@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>CU16: Gestionar Justificaciones
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

                    <!-- Filtros -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('justificaciones.index') }}" method="GET">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="estado" class="form-label">Estado</label>
                                        <select class="form-select" id="estado" name="estado">
                                            <option value="">Todos</option>
                                            <option value="Pendiente" {{ request('estado') == 'Pendiente' ? 'selected' : '' }}>
                                                Pendiente
                                            </option>
                                            <option value="Aprobada" {{ request('estado') == 'Aprobada' ? 'selected' : '' }}>
                                                Aprobada
                                            </option>
                                            <option value="Rechazada" {{ request('estado') == 'Rechazada' ? 'selected' : '' }}>
                                                Rechazada
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="docente" class="form-label">Docente</label>
                                        <select class="form-select" id="docente" name="docente">
                                            <option value="">Todos</option>
                                            @foreach($docentes as $docente)
                                                <option value="{{ $docente->id }}" 
                                                    {{ request('docente') == $docente->id ? 'selected' : '' }}>
                                                    {{ $docente->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="fecha_desde" class="form-label">Desde</label>
                                        <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" 
                                               value="{{ request('fecha_desde') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="fecha_hasta" class="form-label">Hasta</label>
                                        <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" 
                                               value="{{ request('fecha_hasta') }}">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end gap-2">
                                        <button type="submit" class="btn btn-primary flex-fill">
                                            <i class="fas fa-search me-1"></i>Buscar
                                        </button>
                                        <a href="{{ route('justificaciones.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-redo"></i>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Resumen de estados -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="card bg-warning bg-opacity-10 border-warning">
                                <div class="card-body text-center">
                                    <h3 class="text-warning">
                                        {{ App\Models\Justificacion::where('estado', 'Pendiente')->count() }}
                                    </h3>
                                    <small class="text-muted">Pendientes</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success bg-opacity-10 border-success">
                                <div class="card-body text-center">
                                    <h3 class="text-success">
                                        {{ App\Models\Justificacion::where('estado', 'Aprobada')->count() }}
                                    </h3>
                                    <small class="text-muted">Aprobadas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-danger bg-opacity-10 border-danger">
                                <div class="card-body text-center">
                                    <h3 class="text-danger">
                                        {{ App\Models\Justificacion::where('estado', 'Rechazada')->count() }}
                                    </h3>
                                    <small class="text-muted">Rechazadas</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de justificaciones -->
                    @if($justificaciones->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th><i class="fas fa-hashtag me-1"></i>ID</th>
                                        <th><i class="fas fa-user me-1"></i>Docente</th>
                                        <th><i class="fas fa-calendar me-1"></i>Periodo</th>
                                        <th><i class="fas fa-tag me-1"></i>Motivo</th>
                                        <th><i class="fas fa-info-circle me-1"></i>Estado</th>
                                        <th><i class="fas fa-calendar-plus me-1"></i>Fecha Solicitud</th>
                                        <th class="text-center"><i class="fas fa-cog me-1"></i>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($justificaciones as $justificacion)
                                        <tr>
                                            <td><strong>#{{ $justificacion->id }}</strong></td>
                                            <td>
                                                <strong>{{ $justificacion->usuario->nombre }}</strong><br>
                                                <small class="text-muted">Código: {{ $justificacion->usuario->codigo }}</small>
                                            </td>
                                            <td>
                                                <small>
                                                    {{ $justificacion->fecha_inicio->format('d/m/Y') }} - 
                                                    {{ $justificacion->fecha_fin->format('d/m/Y') }}
                                                </small>
                                            </td>
                                            <td>{{ $justificacion->motivo }}</td>
                                            <td>
                                                <span class="badge bg-{{ $justificacion->badge_color }}">
                                                    <i class="fas {{ $justificacion->icono }} me-1"></i>
                                                    {{ $justificacion->estado }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ $justificacion->created_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('justificaciones.show', $justificacion->id) }}" 
                                                       class="btn btn-sm btn-info"
                                                       title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($justificacion->archivo)
                                                        <a href="{{ route('justificaciones.descargar', $justificacion->id) }}" 
                                                           class="btn btn-sm btn-primary"
                                                           title="Descargar archivo">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Mostrando {{ $justificaciones->firstItem() }} - {{ $justificaciones->lastItem() }} 
                                de {{ $justificaciones->total() }} registros
                            </div>
                            {{ $justificaciones->links() }}
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                            <p class="mb-0">No hay justificaciones para mostrar.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
