@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>Detalle de Justificación #{{ $justificacion->id }}
                    </h5>
                    <span class="badge bg-{{ $justificacion->badge_color }} fs-6">
                        <i class="fas {{ $justificacion->icono }} me-1"></i>{{ $justificacion->estado }}
                    </span>
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

                    <div class="row">
                        <!-- Información del Docente -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Docente</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2"><strong>Nombre:</strong> {{ $justificacion->usuario->nombre }}</p>
                                    <p class="mb-2"><strong>Código:</strong> {{ $justificacion->usuario->codigo }}</p>
                                    @if($justificacion->usuario->correo)
                                        <p class="mb-0"><strong>Correo:</strong> {{ $justificacion->usuario->correo }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Información de la Justificación -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-calendar me-2"></i>Periodo y Motivo</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2">
                                        <strong>Fecha Inicio:</strong> 
                                        {{ $justificacion->fecha_inicio->format('d/m/Y') }}
                                    </p>
                                    <p class="mb-2">
                                        <strong>Fecha Fin:</strong> 
                                        {{ $justificacion->fecha_fin->format('d/m/Y') }}
                                    </p>
                                    <p class="mb-2">
                                        <strong>Días:</strong> 
                                        <span class="badge bg-secondary">
                                            {{ $justificacion->fecha_inicio->diffInDays($justificacion->fecha_fin) + 1 }} día(s)
                                        </span>
                                    </p>
                                    <p class="mb-0">
                                        <strong>Motivo:</strong> 
                                        <span class="badge bg-primary">{{ $justificacion->motivo }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Descripción -->
                    @if($justificacion->descripcion)
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-align-left me-2"></i>Descripción</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $justificacion->descripcion }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Archivo Adjunto -->
                    @if($justificacion->archivo)
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-paperclip me-2"></i>Documento de Respaldo</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-pdf fa-3x text-danger me-3"></i>
                                    <div class="flex-grow-1">
                                        <p class="mb-1"><strong>{{ $justificacion->nombre_archivo }}</strong></p>
                                        <small class="text-muted">Adjuntado el {{ $justificacion->created_at->format('d/m/Y H:i') }}</small>
                                    </div>
                                    <a href="{{ route('justificaciones.descargar', $justificacion->id) }}" 
                                       class="btn btn-primary">
                                        <i class="fas fa-download me-1"></i>Descargar
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Información de Aprobación/Rechazo -->
                    @if($justificacion->aprobado_por)
                        <div class="card mb-4 border-{{ $justificacion->estaAprobada() ? 'success' : 'danger' }}">
                            <div class="card-header bg-{{ $justificacion->estaAprobada() ? 'success' : 'danger' }} text-white">
                                <h6 class="mb-0">
                                    <i class="fas {{ $justificacion->estaAprobada() ? 'fa-check-circle' : 'fa-times-circle' }} me-2"></i>
                                    {{ $justificacion->estado }} por
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-2"><strong>{{ $justificacion->aprobadoPor->nombre }}</strong></p>
                                <p class="mb-2">
                                    <small class="text-muted">
                                        Fecha: {{ $justificacion->fecha_aprobacion->format('d/m/Y H:i') }}
                                    </small>
                                </p>
                                @if($justificacion->observaciones)
                                    <hr>
                                    <p class="mb-0"><strong>Observaciones:</strong></p>
                                    <p class="mb-0">{{ $justificacion->observaciones }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Fecha de Solicitud -->
                    <div class="text-muted mb-4">
                        <small>
                            <i class="fas fa-clock me-1"></i>
                            Solicitud creada el {{ $justificacion->created_at->format('d/m/Y H:i') }}
                        </small>
                    </div>

                    <!-- Acciones -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ auth()->user()->hasAnyRole(['Administrador', 'Autoridad', 'Coordinador']) ? route('justificaciones.index') : route('justificaciones.mis-justificaciones') }}" 
                           class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Volver
                        </a>

                        @if($justificacion->estaPendiente() && auth()->user()->hasAnyRole(['Administrador', 'Autoridad', 'Coordinador']))
                            <div class="btn-group">
                                <!-- Botón Aprobar -->
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAprobar">
                                    <i class="fas fa-check me-1"></i>Aprobar
                                </button>
                                <!-- Botón Rechazar -->
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalRechazar">
                                    <i class="fas fa-times me-1"></i>Rechazar
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Aprobar -->
<div class="modal fade" id="modalAprobar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Aprobar Justificación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('justificaciones.aprobar', $justificacion->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <p>¿Está seguro de que desea <strong>aprobar</strong> esta justificación?</p>
                    <div class="mb-3">
                        <label for="observaciones_aprobar" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones_aprobar" name="observaciones" rows="3" 
                                  placeholder="Agregue algún comentario..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>Confirmar Aprobación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Rechazar -->
<div class="modal fade" id="modalRechazar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Rechazar Justificación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('justificaciones.rechazar', $justificacion->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <p>¿Está seguro de que desea <strong>rechazar</strong> esta justificación?</p>
                    <div class="mb-3">
                        <label for="observaciones_rechazar" class="form-label">Motivo del rechazo <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="observaciones_rechazar" name="observaciones" rows="3" 
                                  placeholder="Explique el motivo del rechazo..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i>Confirmar Rechazo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
