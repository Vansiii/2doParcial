@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Editar Grupo
                    </h5>
                </div>
                <div class="card-body text-center py-5">
                    <i class="fas fa-users fa-4x text-info mb-4"></i>
                    <h4>Grupo {{ $grupo->sigla }}</h4>
                    <p class="text-muted mb-4">
                        Para modificar las materias y docentes de este grupo, <br>
                        use la opción "Gestionar Docentes".
                    </p>
                    
                    @if($grupo->grupoMaterias && $grupo->grupoMaterias->count() > 0)
                        <div class="alert alert-info">
                            <strong>Asignaciones actuales:</strong> {{ $grupo->grupoMaterias->count() }} materia(s) con docente(s)
                        </div>
                    @else
                        <div class="alert alert-warning">
                            Este grupo aún no tiene materias ni docentes asignados
                        </div>
                    @endif

                    <div class="d-flex gap-2 justify-content-center mt-4">
                        <a href="{{ route('grupos.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Volver
                        </a>
                        <a href="{{ route('grupos.asignar-docentes', $grupo->sigla) }}" class="btn btn-success">
                            <i class="fas fa-user-plus me-1"></i>Gestionar Docentes
                        </a>
                        <a href="{{ route('grupos.show', $grupo->sigla) }}" class="btn btn-info">
                            <i class="fas fa-eye me-1"></i>Ver Detalles
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
