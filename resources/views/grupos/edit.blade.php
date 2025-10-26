@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Editar Grupo
                    </h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('grupos.update', $grupo->sigla) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label for="sigla" class="form-label">Sigla del Grupo</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="sigla" 
                                   value="{{ $grupo->sigla }}"
                                   disabled>
                            <small class="text-muted">La sigla del grupo no se puede modificar</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-book me-1"></i>Materias Asignadas
                            </label>
                            <div class="card">
                                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                    @if($materias->count() > 0)
                                        <div class="row">
                                            @foreach($materias as $materia)
                                                <div class="col-md-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" 
                                                               type="checkbox" 
                                                               name="materias[]" 
                                                               value="{{ $materia->sigla }}"
                                                               id="materia_{{ $materia->sigla }}"
                                                               {{ in_array($materia->sigla, old('materias', $grupo->materias->pluck('sigla')->toArray())) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="materia_{{ $materia->sigla }}">
                                                            <strong>{{ $materia->sigla }}</strong> - {{ $materia->nombre }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-info-circle me-1"></i>
                                            No hay materias disponibles. 
                                            <a href="{{ route('materias.create') }}">Crear una materia</a>
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <small class="text-muted">Seleccione las materias que se dictarán en este grupo</small>
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('grupos.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-1"></i>Actualizar Grupo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
