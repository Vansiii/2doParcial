@extends('layouts.app')

@section('title', 'Detalle del Módulo')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-building me-2"></i>Detalle del Módulo
                    </h4>
                    <a href="{{ route('modulos.edit', $modulo->codigo) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-edit me-1"></i>Editar
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <strong>Código:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="badge bg-success fs-5">
                                <i class="fas fa-hashtag me-1"></i>{{ $modulo->codigo }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <strong>Ubicación:</strong>
                        </div>
                        <div class="col-md-8">
                            <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                            {{ $modulo->ubicacion }}
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">
                        <i class="fas fa-door-open me-2 text-primary"></i>Aulas en este Módulo
                    </h5>
                    @if($modulo->aulas->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nro. Aula</th>
                                        <th>Piso</th>
                                        <th>Capacidad</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($modulo->aulas as $aula)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary">
                                                    Aula {{ $aula->nroaula }}
                                                </span>
                                            </td>
                                            <td>Piso {{ $aula->piso }}</td>
                                            <td>
                                                <i class="fas fa-users me-1"></i>{{ $aula->capacidad ?? 'N/A' }}
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('aulas.show', $aula->nroaula) }}" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye me-1"></i>Ver
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No hay aulas registradas en este módulo
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('modulos.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
