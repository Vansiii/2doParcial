@extends('layouts.app')

@section('styles')
<style>
    .time-slot {
        height: 60px;
        border: 1px solid #e0e0e0;
        padding: 5px;
        font-size: 0.85rem;
    }
    .schedule-header {
        background-color: #f8f9fa;
        font-weight: bold;
        text-align: center;
        padding: 10px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-plus me-2"></i>Asignar Horario Manualmente
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
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Formulario de asignación -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Nuevo Horario</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('horarios.guardar') }}" method="POST">
                                @csrf
                                
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="sigla_materia" class="form-label">
                                            Materia <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select @error('sigla_materia') is-invalid @enderror" 
                                                id="sigla_materia" 
                                                name="sigla_materia" required>
                                            <option value="">Seleccione</option>
                                            @foreach($materias as $materia)
                                                <option value="{{ $materia->sigla }}" 
                                                    {{ old('sigla_materia') == $materia->sigla ? 'selected' : '' }}>
                                                    {{ $materia->sigla }} - {{ $materia->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('sigla_materia')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-2">
                                        <label for="id_grupo" class="form-label">
                                            Grupo <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select @error('id_grupo') is-invalid @enderror" 
                                                id="id_grupo" 
                                                name="id_grupo" required>
                                            <option value="">Seleccione</option>
                                            @foreach($grupos as $grupo)
                                                <option value="{{ $grupo->id }}" 
                                                    {{ old('id_grupo') == $grupo->id ? 'selected' : '' }}>
                                                    Grupo {{ $grupo->sigla }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('id_grupo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-2">
                                        <label for="nroaula" class="form-label">
                                            Aula <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select @error('nroaula') is-invalid @enderror" 
                                                id="nroaula" 
                                                name="nroaula" required>
                                            <option value="">Seleccione</option>
                                            @foreach($aulas as $aula)
                                                <option value="{{ $aula->nroaula }}" 
                                                    {{ old('nroaula') == $aula->nroaula ? 'selected' : '' }}>
                                                    {{ $aula->nroaula }} (Cap: {{ $aula->capacidad }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('nroaula')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-2">
                                        <label for="horaini" class="form-label">
                                            Hora Inicio <span class="text-danger">*</span>
                                        </label>
                                        <input type="time" 
                                               class="form-control @error('horaini') is-invalid @enderror" 
                                               id="horaini" 
                                               name="horaini" 
                                               value="{{ old('horaini') }}" 
                                               required>
                                        @error('horaini')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-2">
                                        <label for="horafin" class="form-label">
                                            Hora Fin <span class="text-danger">*</span>
                                        </label>
                                        <input type="time" 
                                               class="form-control @error('horafin') is-invalid @enderror" 
                                               id="horafin" 
                                               name="horafin" 
                                               value="{{ old('horafin') }}" 
                                               required>
                                        @error('horafin')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row g-3 mt-2">
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Días <span class="text-danger">*</span>
                                        </label>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($dias as $dia)
                                                <div class="form-check">
                                                    <input class="form-check-input @error('dias') is-invalid @enderror" 
                                                           type="checkbox" 
                                                           name="dias[]" 
                                                           value="{{ $dia->id }}"
                                                           id="dia_{{ $dia->id }}"
                                                           {{ in_array($dia->id, old('dias', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="dia_{{ $dia->id }}">
                                                        {{ $dia->nombre }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        @error('dias')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Seleccione los días en que se dictará la clase</small>
                                    </div>
                                </div>

                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Nota:</strong> Los docentes asignados se obtienen automáticamente del grupo seleccionado.
                                </div>

                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Guardar Horario
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Filtros para ver horarios existentes -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filtrar Horarios Existentes</h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('horarios.asignar') }}" class="row g-3">
                                <div class="col-md-4">
                                    <select class="form-select" name="sigla_materia">
                                        <option value="">Todas las materias</option>
                                        @foreach($materias as $materia)
                                            <option value="{{ $materia->sigla }}" 
                                                {{ request('sigla_materia') == $materia->sigla ? 'selected' : '' }}>
                                                {{ $materia->sigla }} - {{ $materia->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select" name="id_grupo">
                                        <option value="">Todos los grupos</option>
                                        @foreach($grupos as $grupo)
                                            <option value="{{ $grupo->id }}" 
                                                {{ request('id_grupo') == $grupo->id ? 'selected' : '' }}>
                                                Grupo {{ $grupo->sigla }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-1"></i>Buscar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Lista de horarios -->
                    @if($horarios->count() > 0)
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-list me-2"></i>Horarios Encontrados ({{ $horarios->count() }})
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Materia</th>
                                                <th>Grupo</th>
                                                <th>Aula</th>
                                                <th>Horario</th>
                                                <th>Días</th>
                                                <th>Docentes</th>
                                                <th class="text-center">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($horarios as $horario)
                                                <tr>
                                                    <td>
                                                        @if($horario->materias->first())
                                                            <span class="badge bg-info">
                                                                {{ $horario->materias->first()->sigla }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($horario->grupo)
                                                            Grupo {{ $horario->grupo->sigla }}
                                                        @else
                                                            <span class="text-muted">Sin grupo</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($horario->aula)
                                                            {{ $horario->aula->nroaula }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-clock text-primary me-1"></i>
                                                        {{ \Carbon\Carbon::parse($horario->horaini)->format('H:i') }} - 
                                                        {{ \Carbon\Carbon::parse($horario->horafin)->format('H:i') }}
                                                    </td>
                                                    <td>
                                                        @foreach($horario->dias as $dia)
                                                            <span class="badge bg-secondary">{{ substr($dia->nombre, 0, 3) }}</span>
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        @if($horario->grupo && $horario->grupo->docentes->count() > 0)
                                                            @foreach($horario->grupo->docentes->take(2) as $docente)
                                                                <small class="d-block">{{ $docente->nombre }}</small>
                                                            @endforeach
                                                            @if($horario->grupo->docentes->count() > 2)
                                                                <small class="text-muted">+{{ $horario->grupo->docentes->count() - 2 }} más</small>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">Sin docente</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <form action="{{ route('horarios.destroy', $horario->id) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('¿Está seguro de eliminar este horario?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
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
    </div>
</div>
@endsection
