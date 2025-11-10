@extends('layouts.app')

@section('title', 'Gestión de Semestres')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Gestión de Períodos Académicos
                    </h4>
                    <a href="{{ route('semestres.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i>Nuevo Período
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
                                <div class="col-md-3">
                                    <label for="gestion" class="form-label">Gestión</label>
                                    <select class="form-select" id="gestion" name="gestion">
                                        <option value="">Todas</option>
                                        @foreach($gestiones as $gestionOption)
                                            <option value="{{ $gestionOption }}" 
                                                {{ request('gestion') == $gestionOption ? 'selected' : '' }}>
                                                {{ $gestionOption }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="activo" class="form-label">Estado</label>
                                    <select class="form-select" id="activo" name="activo">
                                        <option value="">Todos</option>
                                        <option value="1" {{ request('activo') == '1' ? 'selected' : '' }}>Activo</option>
                                        <option value="0" {{ request('activo') == '0' ? 'selected' : '' }}>Inactivo</option>
                                    </select>
                                </div>
                                <div class="col-md-7 d-flex align-items-end">
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

                    <!-- Tabla de períodos académicos -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Abreviatura</th>
                                    <th>Gestión</th>
                                    <th>Período</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                    <th>Estado</th>
                                    <th>Grupos</th>
                                    <th>Materias</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($semestres as $semestre)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ $semestre->activo ? 'success' : 'info' }} fs-6">
                                                <i class="fas fa-calendar me-1"></i>{{ $semestre->abreviatura }}
                                            </span>
                                        </td>
                                        <td><strong>{{ $semestre->gestion }}</strong></td>
                                        <td>{{ $semestre->periodo }}</td>
                                        <td>{{ \Carbon\Carbon::parse($semestre->fechaini)->format('d/m/Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($semestre->fechafin)->format('d/m/Y') }}</td>
                                        <td>
                                            @if($semestre->activo)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>Activo
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $semestre->grupos_count }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $semestre->materias_count }}</span>
                                        </td>
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
                                                @if(!$semestre->activo)
                                                    <form action="{{ route('semestres.destroy', $semestre->id) }}" 
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirm('¿Está seguro de eliminar este período?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            No se encontraron períodos académicos
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Mostrando {{ $semestres->firstItem() ?? 0 }} - {{ $semestres->lastItem() ?? 0 }} 
                            de {{ $semestres->total() }} períodos académicos
                        </div>
                        <div>
                            {{ $semestres->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
