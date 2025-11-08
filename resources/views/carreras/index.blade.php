@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>Gestión de Carreras
                    </h5>
                    @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Coordinador'))
                    <a href="{{ route('carreras.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i>Nueva Carrera
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
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros de Búsqueda</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('carreras.index') }}" method="GET">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="cod" class="form-label">Código</label>
                                        <input type="text" class="form-control" id="cod" name="cod" 
                                               value="{{ request('cod') }}" placeholder="Ej: IND">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="nombre" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               value="{{ request('nombre') }}" placeholder="Ej: Ingeniería">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end gap-2">
                                        <button type="submit" class="btn btn-primary flex-fill">
                                            <i class="fas fa-search me-1"></i>Buscar
                                        </button>
                                        <a href="{{ route('carreras.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-redo"></i>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabla de carreras -->
                    @if($carreras->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th><i class="fas fa-hashtag me-1"></i>Código</th>
                                        <th><i class="fas fa-graduation-cap me-1"></i>Nombre</th>
                                        <th><i class="fas fa-book me-1"></i>Materias</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($carreras as $carrera)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary fs-6">
                                                    {{ $carrera->cod }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong>{{ $carrera->nombre }}</strong>
                                            </td>
                                            <td>
                                                @if($carrera->materias && $carrera->materias->count() > 0)
                                                    @foreach($carrera->materias->take(3) as $materia)
                                                        <span class="badge bg-info me-1 mb-1">
                                                            {{ $materia->sigla }}
                                                        </span>
                                                    @endforeach
                                                    @if($carrera->materias->count() > 3)
                                                        <span class="badge bg-secondary">
                                                            +{{ $carrera->materias->count() - 3 }} más
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">
                                                        <i class="fas fa-info-circle me-1"></i>Sin materias
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('carreras.show', $carrera->cod) }}" 
                                                       class="btn btn-sm btn-info" title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Coordinador'))
                                                    <a href="{{ route('carreras.edit', $carrera->cod) }}" 
                                                       class="btn btn-sm btn-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('carreras.destroy', $carrera->cod) }}" 
                                                          method="POST" 
                                                          style="display: inline;"
                                                          onsubmit="return confirm('¿Está seguro de eliminar la carrera {{ $carrera->nombre }}?');">
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
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Mostrando {{ $carreras->firstItem() }} - {{ $carreras->lastItem() }} de {{ $carreras->total() }} registros
                            </div>
                            {{ $carreras->links() }}
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                            <p class="mb-0">No se encontraron carreras registradas.</p>
                            @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Coordinador'))
                                <a href="{{ route('carreras.create') }}" class="btn btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i>Registrar Primera Carrera
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
