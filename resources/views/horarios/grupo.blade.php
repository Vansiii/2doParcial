@extends('layouts.app')

@section('styles')
<style>
    .schedule-grid {
        display: grid;
        grid-template-columns: 100px repeat(7, 1fr);
        gap: 2px;
        background-color: #dee2e6;
        padding: 2px;
    }
    .schedule-header {
        background-color: #1cc88a;
        color: white;
        padding: 12px;
        text-align: center;
        font-weight: bold;
        font-size: 0.9rem;
    }
    .schedule-cell {
        background-color: white;
        padding: 8px;
        min-height: 80px;
        font-size: 0.85rem;
    }
    .schedule-time {
        background-color: #f8f9fa;
        padding: 12px;
        text-align: center;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .schedule-item {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        padding: 8px;
        border-radius: 6px;
        margin-bottom: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    @media (max-width: 768px) {
        .schedule-grid {
            grid-template-columns: 80px repeat(3, 1fr);
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar me-2"></i>Consultar Horario por Grupo
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Selector de grupo -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('horarios.grupo') }}" class="row g-3">
                                <div class="col-md-10">
                                    <label for="grupo" class="form-label">Seleccione un Grupo</label>
                                    <select class="form-select form-select-lg" 
                                            id="grupo" 
                                            name="id"
                                            onchange="this.form.submit()">
                                        <option value="">-- Seleccione un grupo --</option>
                                        @foreach($grupos as $grupo)
                                            <option value="{{ $grupo->id }}" 
                                                {{ $grupoSeleccionado && $grupoSeleccionado->id == $grupo->id ? 'selected' : '' }}>
                                                Grupo {{ $grupo->sigla }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-search me-1"></i>Consultar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if($grupoSeleccionado)
                        <!-- Información del grupo -->
                        <div class="alert alert-success">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-users fa-2x me-3"></i>
                                        <div>
                                            <h6 class="mb-1">Grupo {{ $grupoSeleccionado->sigla }}</h6>
                                            <small>
                                                @if($grupoSeleccionado->materias->count() > 0)
                                                    <strong>Materias:</strong>
                                                    @foreach($grupoSeleccionado->materias->take(5) as $materia)
                                                        <span class="badge bg-info">{{ $materia->sigla }}</span>
                                                    @endforeach
                                                    @if($grupoSeleccionado->materias->count() > 5)
                                                        <span class="badge bg-secondary">+{{ $grupoSeleccionado->materias->count() - 5 }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Sin materias asignadas</span>
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <span class="badge bg-white text-success fs-6">
                                        {{ $horarios->count() }} horarios asignados
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if($horarios->count() > 0)
                            <!-- Tabla de horarios -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-list me-2"></i>Horarios Detallados
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Materia</th>
                                                    <th>Aula</th>
                                                    <th>Días</th>
                                                    <th>Horario</th>
                                                    <th>Docentes</th>
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
                                                                <br>
                                                                <small class="text-muted">
                                                                    {{ $horario->materias->first()->nombre }}
                                                                </small>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($horario->aula)
                                                                <i class="fas fa-door-open text-success me-1"></i>
                                                                <strong>{{ $horario->aula->nroaula }}</strong>
                                                                <br>
                                                                <small class="text-muted">
                                                                    Piso {{ $horario->aula->piso }} - 
                                                                    Cap: {{ $horario->aula->capacidad }}
                                                                </small>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @foreach($horario->dias as $dia)
                                                                <span class="badge bg-secondary me-1 mb-1">
                                                                    {{ $dia->nombre }}
                                                                </span>
                                                            @endforeach
                                                        </td>
                                                        <td>
                                                            <i class="fas fa-clock text-primary me-1"></i>
                                                            <strong>
                                                                {{ \Carbon\Carbon::parse($horario->horaini)->format('H:i') }} - 
                                                                {{ \Carbon\Carbon::parse($horario->horafin)->format('H:i') }}
                                                            </strong>
                                                        </td>
                                                        <td>
                                                            @php
                                                                // Obtener docente de grupo_materia
                                                                $materia = $horario->materias->first();
                                                                $docenteHorario = null;
                                                                if ($materia && $grupoSeleccionado) {
                                                                    $gm = \App\Models\GrupoMateria::where('id_grupo', $grupoSeleccionado->id)
                                                                        ->where('sigla_materia', $materia->sigla)
                                                                        ->first();
                                                                    $docenteHorario = $gm ? $gm->docente : null;
                                                                }
                                                            @endphp
                                                            
                                                            @if($docenteHorario)
                                                                <div>
                                                                    <i class="fas fa-user-tie text-primary me-1"></i>
                                                                    <small>{{ $docenteHorario->nombre }}</small>
                                                                </div>
                                                            @else
                                                                <span class="text-muted">Sin docente</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Vista de calendario semanal -->
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-calendar-week me-2"></i>Vista Semanal
                                    </h6>
                                </div>
                                <div class="card-body p-2">
                                    <div class="schedule-grid">
                                        <div class="schedule-header">Hora</div>
                                        <div class="schedule-header">Lunes</div>
                                        <div class="schedule-header">Martes</div>
                                        <div class="schedule-header">Miércoles</div>
                                        <div class="schedule-header">Jueves</div>
                                        <div class="schedule-header">Viernes</div>
                                        <div class="schedule-header">Sábado</div>
                                        <div class="schedule-header">Domingo</div>

                                        @php
                                            $horaInicio = 7;
                                            $horaFin = 21;
                                            $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                                        @endphp

                                        @for($hora = $horaInicio; $hora < $horaFin; $hora++)
                                            <div class="schedule-time">
                                                {{ sprintf('%02d:00', $hora) }}
                                            </div>
                                            @foreach($diasSemana as $nombreDia)
                                                <div class="schedule-cell">
                                                    @foreach($horarios as $horario)
                                                        @php
                                                            $horaIni = (int)\Carbon\Carbon::parse($horario->horaini)->format('H');
                                                            $horaFi = (int)\Carbon\Carbon::parse($horario->horafin)->format('H');
                                                            $tieneDia = $horario->dias->contains('nombre', $nombreDia);
                                                        @endphp
                                                        @if($tieneDia && $hora >= $horaIni && $hora < $horaFi)
                                                            @if($hora == $horaIni)
                                                                @php
                                                                    // Obtener docente de grupo_materia
                                                                    $materia = $horario->materias->first();
                                                                    $docenteHorario = null;
                                                                    if ($materia && $grupoSeleccionado) {
                                                                        $gm = \App\Models\GrupoMateria::where('id_grupo', $grupoSeleccionado->id)
                                                                            ->where('sigla_materia', $materia->sigla)
                                                                            ->first();
                                                                        $docenteHorario = $gm ? $gm->docente : null;
                                                                    }
                                                                @endphp
                                                                <div class="schedule-item">
                                                                    <div><strong>{{ $materia->sigla ?? '' }}</strong></div>
                                                                    <div class="small">{{ $horario->aula->nroaula ?? '' }}</div>
                                                                    @if($docenteHorario)
                                                                        <div class="small">{{ $docenteHorario->nombre }}</div>
                                                                    @endif
                                                                    <div class="small">
                                                                        {{ \Carbon\Carbon::parse($horario->horaini)->format('H:i') }} - 
                                                                        {{ \Carbon\Carbon::parse($horario->horafin)->format('H:i') }}
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endforeach
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle me-2"></i>
                                El grupo seleccionado no tiene horarios asignados.
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">Seleccione un grupo para ver su horario</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
