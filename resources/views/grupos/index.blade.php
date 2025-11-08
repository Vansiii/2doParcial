@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>Gestión de Grupos
                    </h5>
                    @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Coordinador'))
                    <a href="{{ route('grupos.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i>Nuevo Grupo
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
                            <form method="GET" action="{{ route('grupos.index') }}" class="row g-3">
                                <div class="col-md-4">
                                    <label for="sigla" class="form-label">Sigla del Grupo</label>
                                    <input type="text" class="form-control" id="sigla" name="sigla" 
                                           value="{{ request('sigla') }}" placeholder="Ej: A1">
                                </div>
                                <div class="col-md-6">
                                    <label for="sigla_materia" class="form-label">Materia</label>
                                    <input type="text" class="form-control" id="sigla_materia" name="sigla_materia" 
                                           value="{{ request('sigla_materia') }}" placeholder="Ej: INF123">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-1"></i>Buscar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabla de grupos -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nro. Grupo</th>
                                    <th>Materias Asignadas</th>
                                    <th>Docentes Asignados</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($grupos as $grupo)
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary fs-6">
                                                <i class="fas fa-users me-1"></i>Grupo {{ $grupo->sigla }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($grupo->grupoMaterias && $grupo->grupoMaterias->count() > 0)
                                                @php
                                                    $materiasUnicas = $grupo->grupoMaterias->pluck('materia')->unique('sigla');
                                                @endphp
                                                @foreach($materiasUnicas->take(3) as $materia)
                                                    <span class="badge bg-info me-1 mb-1">
                                                        {{ $materia->sigla }}
                                                    </span>
                                                @endforeach
                                                @if($materiasUnicas->count() > 3)
                                                    <span class="badge bg-secondary">
                                                        +{{ $materiasUnicas->count() - 3 }} más
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>Sin materias
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($grupo->grupoMaterias && $grupo->grupoMaterias->count() > 0)
                                                @php
                                                    $docentesUnicos = $grupo->grupoMaterias->pluck('docente')->filter()->unique('id');
                                                @endphp
                                                @foreach($docentesUnicos->take(2) as $docente)
                                                    <span class="badge bg-success me-1 mb-1" title="{{ $docente->nombre }}">
                                                        <i class="fas fa-chalkboard-teacher me-1"></i>{{ $docente->nombre }}
                                                    </span>
                                                @endforeach
                                                @if($docentesUnicos->count() > 2)
                                                    <span class="badge bg-secondary">
                                                        +{{ $docentesUnicos->count() - 2 }} más
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-user-slash me-1"></i>Sin docentes
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('grupos.show', $grupo->sigla) }}" 
                                                   class="btn btn-sm btn-info" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Coordinador'))
                                                <a href="{{ route('grupos.asignar-docentes', $grupo->sigla) }}" 
                                                   class="btn btn-sm btn-success" title="Asignar Docentes">
                                                    <i class="fas fa-user-plus"></i>
                                                </a>
                                                <a href="{{ route('grupos.edit', $grupo->sigla) }}" 
                                                   class="btn btn-sm btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('grupos.destroy', $grupo->sigla) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('¿Está seguro de eliminar este grupo?')">
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
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            No se encontraron grupos
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Mostrando {{ $grupos->firstItem() ?? 0 }} - {{ $grupos->lastItem() ?? 0 }} 
                            de {{ $grupos->total() }} grupos
                        </div>
                        <div>
                            {{ $grupos->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
