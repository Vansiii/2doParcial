@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>Gestión de Usuarios
                    </h5>
                    @if(auth()->user()->hasRole('Administrador'))
                    <a href="{{ route('usuarios.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i>Nuevo Usuario
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
                            <form action="{{ route('usuarios.index') }}" method="GET">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="codigo" class="form-label">Código</label>
                                        <input type="number" class="form-control" id="codigo" name="codigo" 
                                               value="{{ request('codigo') }}" placeholder="Ej: 123456">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="nombre" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               value="{{ request('nombre') }}" placeholder="Ej: Juan">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="rol" class="form-label">Rol</label>
                                        <select class="form-select" id="rol" name="rol">
                                            <option value="">Todos</option>
                                            @foreach($roles as $rol)
                                                <option value="{{ $rol->id }}" 
                                                    {{ request('rol') == $rol->id ? 'selected' : '' }}>
                                                    {{ $rol->descripcion }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end gap-2">
                                        <button type="submit" class="btn btn-primary flex-fill">
                                            <i class="fas fa-search me-1"></i>Buscar
                                        </button>
                                        <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-redo"></i>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabla de usuarios -->
                    @if($usuarios->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th><i class="fas fa-id-card me-1"></i>Código</th>
                                        <th><i class="fas fa-user me-1"></i>Nombre</th>
                                        <th><i class="fas fa-envelope me-1"></i>Correo</th>
                                        <th><i class="fas fa-phone me-1"></i>Teléfono</th>
                                        <th><i class="fas fa-user-tag me-1"></i>Roles</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($usuarios as $usuario)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary fs-6">
                                                    {{ $usuario->codigo }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong>{{ $usuario->nombre }}</strong>
                                            </td>
                                            <td>
                                                <small>{{ $usuario->correo }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $usuario->telefono }}</small>
                                            </td>
                                            <td>
                                                @if($usuario->roles && $usuario->roles->count() > 0)
                                                    @foreach($usuario->roles as $rol)
                                                        <span class="badge bg-info me-1 mb-1">
                                                            {{ $rol->descripcion }}
                                                        </span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">
                                                        <i class="fas fa-info-circle me-1"></i>Sin rol
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('usuarios.show', $usuario->id) }}" 
                                                       class="btn btn-sm btn-info" title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if(auth()->user()->hasRole('Administrador'))
                                                    <a href="{{ route('usuarios.edit', $usuario->id) }}" 
                                                       class="btn btn-sm btn-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($usuario->id !== auth()->id())
                                                    <form action="{{ route('usuarios.destroy', $usuario->id) }}" 
                                                          method="POST" 
                                                          style="display: inline;"
                                                          onsubmit="return confirm('¿Está seguro de eliminar el usuario {{ $usuario->nombre }}?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                    @endif
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
                                Mostrando {{ $usuarios->firstItem() }} - {{ $usuarios->lastItem() }} de {{ $usuarios->total() }} registros
                            </div>
                            {{ $usuarios->links() }}
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                            <p class="mb-0">No se encontraron usuarios registrados.</p>
                            @if(auth()->user()->hasRole('Administrador'))
                                <a href="{{ route('usuarios.create') }}" class="btn btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i>Registrar Primer Usuario
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
