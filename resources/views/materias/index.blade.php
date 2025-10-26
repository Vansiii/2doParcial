@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-book me-2"></i>Gestión de Materias
                    </h5>
                    @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Coordinador'))
                    <a href="{{ route('materias.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i>Nueva Materia
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
                            <form method="GET" action="{{ route('materias.index') }}" class="row g-3">
                                <div class="col-md-3">
                                    <label for="sigla" class="form-label">Sigla</label>
                                    <input type="text" class="form-control" id="sigla" name="sigla" 
                                           value="{{ request('sigla') }}" placeholder="Ej: INF123">
                                </div>
                                <div class="col-md-4">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="{{ request('nombre') }}" placeholder="Ej: Programación">
                                </div>
                                <div class="col-md-3">
                                    <label for="semestre" class="form-label">Semestre</label>
                                    <select class="form-select" id="semestre" name="semestre">
                                        <option value="">Todos</option>
                                        @foreach($semestres as $semestre)
                                            <option value="{{ $semestre->id }}" 
                                                {{ request('semestre') == $semestre->id ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::parse($semestre->fechaini)->format('Y') }} - 
                                                {{ $semestre->periodo }}
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

                    <!-- Tabla de materias -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Sigla</th>
                                    <th>Nombre</th>
                                    <th>Semestre</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($materias as $materia)
                                    <tr>
                                        <td><strong>{{ $materia->sigla }}</strong></td>
                                        <td>{{ $materia->nombre }}</td>
                                        <td>
                                            @if($materia->semestre)
                                                <span class="badge bg-info">
                                                    {{ \Carbon\Carbon::parse($materia->semestre->fechaini)->format('Y') }} - 
                                                    {{ $materia->semestre->periodo }}
                                                </span>
                                            @else
                                                <span class="text-muted">Sin semestre</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('materias.show', $materia->sigla) }}" 
                                                   class="btn btn-sm btn-info" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Coordinador'))
                                                <a href="{{ route('materias.edit', $materia->sigla) }}" 
                                                   class="btn btn-sm btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('materias.destroy', $materia->sigla) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('¿Está seguro de eliminar esta materia?')">
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
                                            No se encontraron materias
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Mostrando {{ $materias->firstItem() ?? 0 }} - {{ $materias->lastItem() ?? 0 }} 
                            de {{ $materias->total() }} materias
                        </div>
                        <div>
                            {{ $materias->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
