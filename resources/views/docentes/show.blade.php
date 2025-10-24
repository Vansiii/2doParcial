@extends('layouts.app')

@section('title', 'Ver Docente')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-gray-800">
                <i class="fas fa-user"></i> Información del Docente
            </h1>
            <div class="btn-group">
                <a href="{{ route('docentes.edit', $docente->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="{{ route('docentes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Datos del Docente</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="30%">ID:</th>
                        <td>{{ $docente->id }}</td>
                    </tr>
                    <tr>
                        <th>Nombre Completo:</th>
                        <td>{{ $docente->nombre }}</td>
                    </tr>
                    <tr>
                        <th>Correo Electrónico:</th>
                        <td>
                            <i class="fas fa-envelope"></i> 
                            <a href="mailto:{{ $docente->correo }}">{{ $docente->correo }}</a>
                        </td>
                    </tr>
                    <tr>
                        <th>Teléfono:</th>
                        <td>
                            <i class="fas fa-phone"></i> {{ $docente->telefono }}
                        </td>
                    </tr>
                    <tr>
                        <th>Roles Asignados:</th>
                        <td>
                            @foreach($docente->roles as $rol)
                                <span class="badge bg-info">{{ $rol->descripcion }}</span>
                                @if($rol->pivot->detalle)
                                    <br><small class="text-muted">{{ $rol->pivot->detalle }}</small>
                                @endif
                            @endforeach
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0">Acciones</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('docentes.edit', $docente->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar Información
                    </a>
                    <button type="button" 
                            class="btn btn-danger" 
                            onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Eliminar Docente
                    </button>
                </div>
                
                <form id="delete-form" 
                      action="{{ route('docentes.destroy', $docente->id) }}" 
                      method="POST" 
                      class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function confirmDelete() {
    if (confirm('¿Está seguro de que desea eliminar este docente?\n\nEsta acción no se puede deshacer.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endsection
