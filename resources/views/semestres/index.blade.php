@extends('layouts.app')

@section('title', 'Gestión de Semestres')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Gestión de Semestres
                    </h4>
                    <a href="{{ route('semestres.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i>Nuevo Semestre
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filtros de búsqueda -->
                    <div class="card mb-4">
                        <div class="card-body bg-light">
                            <h6 class="card-title mb-3">
                                <i class="fas fa-search me-2"></i>Filtros de búsqueda
                            </h6>
                            <form method="GET" action="{{ route('semestres.index') }}" class="row g-3">
                                <div class="col-md-4">
                                    <label for="abreviatura" class="form-label">Abreviatura</label>
                                    <input type="text" class="form-control" id="abreviatura" name="abreviatura" 
                                           value="{{ request('abreviatura') }}" placeholder="Ej: 1-2024">
                                </div>
                                <div class="col-md-8 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search me-1"></i>Buscar
                                    </button>
                                    <a href="{{ route('semestres.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-redo me-1"></i>Limpiar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabla de semestres -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Abreviatura</th>
                                    <th>Periodo</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($semestres as $semestre)
                                    <tr>
                                        <td>
                                            <span class="badge bg-info fs-6">
                                                <i class="fas fa-calendar me-1"></i>{{ $semestre->abreviatura }}
                                            </span>
                                        </td>
                                        <td>{{ $semestre->periodo }}</td>
                                        <td>{{ \Carbon\Carbon::parse($semestre->fechaini)->format('d/m/Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($semestre->fechafin)->format('d/m/Y') }}</td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('semestres.show', $semestre->id) }}" 
                                                   class="btn btn-sm btn-info" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('semestres.edit', $semestre->id) }}" 
                                                   class="btn btn-sm btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('semestres.destroy', $semestre->id) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('¿Está seguro de eliminar este semestre?')">
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
                                        <td colspan="5" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No se encontraron semestres</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $semestres->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
