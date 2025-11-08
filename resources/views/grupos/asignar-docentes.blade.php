@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>Asignar Docentes al Grupo {{ $grupo->sigla }}
                    </h5>
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

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Información del Grupo -->
                    <div class="alert alert-info mb-4">
                        <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Información del Grupo</h6>
                        <div><strong>Sigla:</strong> {{ $grupo->sigla }}</div>
                    </div>

                    <!-- Formulario para Asignar Materia + Docente -->
                    <form action="{{ route('grupos.guardar-docentes', $grupo->sigla) }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <!-- Seleccionar Materia (de TODAS las materias disponibles) -->
                            <div class="col-md-5">
                                <label for="sigla_materia" class="form-label">
                                    <i class="fas fa-book me-2"></i>Materia *
                                </label>
                                <select name="sigla_materia" id="sigla_materia" class="form-select" required>
                                    <option value="">Seleccione una materia</option>
                                    @php
                                        $todasMaterias = \App\Models\Materia::orderBy('sigla')->get();
                                    @endphp
                                    @foreach($todasMaterias as $materia)
                                        <option value="{{ $materia->sigla }}" {{ old('sigla_materia') == $materia->sigla ? 'selected' : '' }}>
                                            {{ $materia->sigla }} - {{ $materia->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Al asignar el docente, la materia se agregará al grupo</small>
                            </div>

                            <!-- Seleccionar Docente -->
                            <div class="col-md-5">
                                <label for="id_docente" class="form-label">
                                    <i class="fas fa-chalkboard-teacher me-2"></i>Docente *
                                </label>
                                <select name="id_docente" id="id_docente" class="form-select" required>
                                    <option value="">Seleccione un docente</option>
                                    @foreach($docentes as $docente)
                                        <option value="{{ $docente->id }}" {{ old('id_docente') == $docente->id ? 'selected' : '' }}>
                                            {{ $docente->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Botón Asignar -->
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-plus me-1"></i>Asignar
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Botón Volver -->
                    <div class="mt-4">
                        <a href="{{ route('grupos.show', $grupo->sigla) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Volver al Grupo
                        </a>
                    </div>
                </div>
            </div>

            <!-- Asignaciones Actuales (Grupo-Materia-Docente) -->
            @if($grupo->grupoMaterias && $grupo->grupoMaterias->count() > 0)
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>Asignaciones Actuales (Materia → Docente)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th><i class="fas fa-book me-1"></i>Materia</th>
                                    <th><i class="fas fa-user-tie me-1"></i>Docente Asignado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($grupo->grupoMaterias as $gm)
                                    <tr>
                                        <td>
                                            <strong>{{ $gm->materia->sigla }}</strong> - {{ $gm->materia->nombre }}
                                        </td>
                                        <td>
                                            @if($gm->docente)
                                                <i class="fas fa-user-circle text-success me-2"></i>
                                                {{ $gm->docente->nombre }}
                                                @if($gm->docente->correo)
                                                    <small class="text-muted d-block">{{ $gm->docente->correo }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">Sin docente</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <form action="{{ route('grupos.eliminar-docente', [$grupo->sigla, $gm->sigla_materia]) }}" 
                                                  method="POST" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('¿Está seguro de eliminar esta asignación?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar asignación">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
