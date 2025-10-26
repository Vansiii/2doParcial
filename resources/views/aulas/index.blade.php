@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-door-open me-2"></i>Gestión de Aulas
                    </h5>
                    @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Coordinador'))
                    <a href="{{ route('aulas.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i>Nueva Aula
                    </a>
                    @endif
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

                    <!-- Filtros de búsqueda -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <form method="GET" action="{{ route('aulas.index') }}" class="row g-3">
                                <div class="col-md-3">
                                    <label for="nroaula" class="form-label">Número de Aula</label>
                                    <input type="text" class="form-control" id="nroaula" name="nroaula" 
                                           value="{{ request('nroaula') }}" placeholder="Ej: A101">
                                </div>
                                <div class="col-md-2">
                                    <label for="piso" class="form-label">Piso</label>
                                    <select class="form-select" id="piso" name="piso">
                                        <option value="">Todos</option>
                                        @for($i = 1; $i <= 10; $i++)
                                            <option value="{{ $i }}" {{ request('piso') == $i ? 'selected' : '' }}>
                                                Piso {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="capacidad" class="form-label">Capacidad mín.</label>
                                    <input type="number" class="form-control" id="capacidad" name="capacidad" 
                                           value="{{ request('capacidad') }}" placeholder="30" min="1">
                                </div>
                                <div class="col-md-3">
                                    <label for="id_modulo" class="form-label">Módulo</label>
                                    <select class="form-select" id="id_modulo" name="id_modulo">
                                        <option value="">Todos</option>
                                        @foreach($modulos as $modulo)
                                            <option value="{{ $modulo->codigo }}" 
                                                {{ request('id_modulo') == $modulo->codigo ? 'selected' : '' }}>
                                                Código {{ $modulo->codigo }} - {{ $modulo->ubicacion }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-1"></i>Buscar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabla de aulas -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nro. Aula</th>
                                    <th>Piso</th>
                                    <th>Capacidad</th>
                                    <th>Módulo</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($aulas as $aula)
                                    <tr>
                                        <td>
                                            <strong class="text-primary">{{ $aula->nroaula }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-layer-group me-1"></i>Piso {{ $aula->piso }}
                                            </span>
                                        </td>
                                        <td>
                                            <i class="fas fa-users text-info me-1"></i>
                                            {{ $aula->capacidad }} personas
                                        </td>
                                        <td>
                                            @if($aula->modulo)
                                                <span class="badge bg-info">
                                                    Código {{ $aula->modulo->codigo }} - {{ $aula->modulo->ubicacion }}
                                                </span>
                                            @else
                                                <span class="text-muted">Sin módulo</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('aulas.show', $aula->nroaula) }}" 
                                                   class="btn btn-sm btn-info" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Coordinador'))
                                                <a href="{{ route('aulas.edit', $aula->nroaula) }}" 
                                                   class="btn btn-sm btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('aulas.destroy', $aula->nroaula) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('¿Está seguro de eliminar esta aula?')">
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
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            No se encontraron aulas
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Mostrando {{ $aulas->firstItem() ?? 0 }} - {{ $aulas->lastItem() ?? 0 }} 
                            de {{ $aulas->total() }} aulas
                        </div>
                        <div>
                            {{ $aulas->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
