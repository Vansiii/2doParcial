@extends('layouts.app')

@section('title', 'Gestión de Módulos')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-building me-2"></i>Gestión de Módulos
                    </h4>
                    <a href="{{ route('modulos.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i>Nuevo Módulo
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filtros de búsqueda -->
                    <div class="card mb-4">
                        <div class="card-body bg-light">
                            <h6 class="card-title mb-3">
                                <i class="fas fa-search me-2"></i>Filtros de búsqueda
                            </h6>
                            <form method="GET" action="{{ route('modulos.index') }}" class="row g-3">
                                <div class="col-md-4">
                                    <label for="ubicacion" class="form-label">Ubicación</label>
                                    <input type="text" class="form-control" id="ubicacion" name="ubicacion" 
                                           value="{{ request('ubicacion') }}" placeholder="Ej: Edificio A">
                                </div>
                                <div class="col-md-8 d-flex align-items-end">
                                    <button type="submit" class="btn btn-success me-2">
                                        <i class="fas fa-search me-1"></i>Buscar
                                    </button>
                                    <a href="{{ route('modulos.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-redo me-1"></i>Limpiar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabla de módulos -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Código</th>
                                    <th>Ubicación</th>
                                    <th class="text-center">Aulas</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($modulos as $modulo)
                                    <tr>
                                        <td>
                                            <span class="badge bg-success fs-6">
                                                <i class="fas fa-hashtag me-1"></i>{{ $modulo->codigo }}
                                            </span>
                                        </td>
                                        <td>
                                            <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                            {{ $modulo->ubicacion }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $modulo->aulas_count }} aulas</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('modulos.show', $modulo->codigo) }}" 
                                                   class="btn btn-sm btn-info" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('modulos.edit', $modulo->codigo) }}" 
                                                   class="btn btn-sm btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('modulos.destroy', $modulo->codigo) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('¿Está seguro de eliminar este módulo?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No se encontraron módulos</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $modulos->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
