@extends('layouts.app')

@section('title', 'Consultar Docentes')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-gray-800">
                <i class="fas fa-chalkboard-teacher"></i> Gestión de Docentes
            </h1>
            <a href="{{ route('docentes.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Registrar Docente
            </a>
        </div>
    </div>
</div>

<!-- Filtros de Búsqueda -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-search"></i> Filtros de Búsqueda</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('docentes.index') }}">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="ci" class="form-label">CI</label>
                            <input type="number" class="form-control" id="ci" name="ci" 
                                   value="{{ request('ci') }}" placeholder="Buscar por CI">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="{{ request('nombre') }}" placeholder="Buscar por nombre">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="correo" class="form-label">Correo</label>
                            <input type="text" class="form-control" id="correo" name="correo" 
                                   value="{{ request('correo') }}" placeholder="Buscar por correo">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" 
                                   value="{{ request('telefono') }}" placeholder="Buscar por teléfono">
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <a href="{{ route('docentes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Docentes -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-list"></i> Lista de Docentes 
                    <span class="badge bg-primary">{{ $docentes->total() }} registros</span>
                </h6>
            </div>
            <div class="card-body">
                @if($docentes->isEmpty())
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No se encontraron docentes registrados.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>CI</th>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Teléfono</th>
                                    <th>Roles</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($docentes as $docente)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-id-badge"></i> {{ $docente->ci }}
                                        </span>
                                    </td>
                                    <td>{{ $docente->id }}</td>
                                    <td>
                                        <i class="fas fa-user"></i> {{ $docente->nombre }}
                                    </td>
                                    <td>
                                        <i class="fas fa-envelope"></i> {{ $docente->correo }}
                                    </td>
                                    <td>
                                        <i class="fas fa-phone"></i> {{ $docente->telefono }}
                                    </td>
                                    <td>
                                        @foreach($docente->roles as $rol)
                                            <span class="badge bg-info">{{ $rol->descripcion }}</span>
                                        @endforeach
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('docentes.show', $docente->id) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('docentes.edit', $docente->id) }}" 
                                               class="btn btn-sm btn-warning" 
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    onclick="confirmDelete({{ $docente->id }})"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        
                                        <form id="delete-form-{{ $docente->id }}" 
                                              action="{{ route('docentes.destroy', $docente->id) }}" 
                                              method="POST" 
                                              class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $docentes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function confirmDelete(id) {
    if (confirm('¿Está seguro de que desea eliminar este docente?')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endsection
