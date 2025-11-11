@extends('layouts.app')

@section('styles')
<style>
    .horario-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .horario-table th {
        background: linear-gradient(135deg, #c8e6c9 0%, #a5d6a7 100%);
        color: #2e7d32;
        padding: 12px 8px;
        text-align: center;
        font-weight: 700;
        border: 1px solid #a5d6a7;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .horario-table th:first-child {
        background: linear-gradient(135deg, #e0e0e0 0%, #bdbdbd 100%);
        color: #424242;
    }
    .horario-table td {
        border: 1px solid #e0e0e0;
        padding: 0;
        text-align: center;
        vertical-align: middle;
        min-width: 120px;
        height: 50px;
    }
    .horario-table td:first-child {
        background-color: #f5f5f5;
        font-weight: 600;
        color: #616161;
        white-space: nowrap;
        padding: 8px;
    }
    .horario-block {
        padding: 6px 8px;
        border-radius: 4px;
        font-weight: 600;
        color: #212529;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        line-height: 1.3;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .horario-block:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        cursor: pointer;
    }
    .horario-materia {
        font-size: 0.9rem;
        font-weight: 700;
    }
    .horario-aula {
        font-size: 0.75rem;
        margin-top: 2px;
        opacity: 0.9;
    }
    
    /* Colores distintivos por tipo de materia */
    .color-1 { background-color: #a8e6cf; } /* Verde claro */
    .color-2 { background-color: #ffd3b6; } /* Naranja claro */
    .color-3 { background-color: #ffaaa5; } /* Rojo claro */
    .color-4 { background-color: #ff8b94; } /* Rosa salmón */
    .color-5 { background-color: #dda0dd; } /* Ciruela */
    .color-6 { background-color: #98d8c8; } /* Turquesa */
    .color-7 { background-color: #f7dc6f; } /* Amarillo */
    .color-8 { background-color: #bb8fce; } /* Morado claro */
    .color-9 { background-color: #85c1e2; } /* Azul cielo */
    .color-10 { background-color: #f8b4d9; } /* Rosa */
    
    @media print {
        .no-print { display: none !important; }
        .sidebar { display: none !important; }
        #sidebar { display: none !important; }
        .main-content { margin-left: 0 !important; padding-left: 0 !important; }
        .container-fluid { padding: 0 !important; }
        .horario-table { page-break-inside: avoid; }
        .card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
        body { padding: 0 !important; margin: 0 !important; }
    }
    
    @media (max-width: 768px) {
        .horario-table {
            font-size: 0.7rem;
        }
        .horario-table td {
            min-width: 80px;
            height: 45px;
        }
        .horario-materia {
            font-size: 0.75rem;
        }
        .horario-aula {
            font-size: 0.65rem;
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
                                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="fas fa-calendar-week me-2"></i>Horario Semanal - Grupo {{ $grupoSeleccionado->sigla }}
                                    </h6>
                                    <button class="btn btn-light btn-sm no-print" onclick="window.print()">
                                        <i class="fas fa-print me-1"></i>Imprimir
                                    </button>
                                </div>
                                <div class="card-body p-3">
                                    @php
                                        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                                        $diasAbrev = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'];
                                        
                                        // Crear intervalos de 45 minutos desde las 7:00 hasta las 23:00
                                        $intervalos = [];
                                        $horaInicio = 7 * 60; // 7:00 en minutos
                                        $horaFin = 23 * 60; // 23:00 en minutos
                                        
                                        for ($minutos = $horaInicio; $minutos < $horaFin; $minutos += 45) {
                                            $horaIni = sprintf('%02d:%02d', floor($minutos / 60), $minutos % 60);
                                            $horaFinIntervalo = $minutos + 45;
                                            $horaFinStr = sprintf('%02d:%02d', floor($horaFinIntervalo / 60), $horaFinIntervalo % 60);
                                            
                                            // Solo agregar si hay algún horario en este intervalo
                                            foreach($horarios as $horario) {
                                                $horarioIni = \Carbon\Carbon::parse($horario->horaini);
                                                $horarioFin = \Carbon\Carbon::parse($horario->horafin);
                                                $horarioIniMinutos = $horarioIni->hour * 60 + $horarioIni->minute;
                                                $horarioFinMinutos = $horarioFin->hour * 60 + $horarioFin->minute;
                                                
                                                // Verificar si el horario se solapa con este intervalo
                                                if ($horarioIniMinutos < $horaFinIntervalo && $horarioFinMinutos > $minutos) {
                                                    $intervalo = $horaIni . ' - ' . $horaFinStr;
                                                    if (!isset($intervalos[$intervalo])) {
                                                        $intervalos[$intervalo] = [
                                                            'inicio' => $horaIni,
                                                            'fin' => $horaFinStr,
                                                            'minutos_inicio' => $minutos,
                                                            'minutos_fin' => $horaFinIntervalo
                                                        ];
                                                    }
                                                    break;
                                                }
                                            }
                                        }
                                        
                                        // Asignar colores únicos a cada materia
                                        $materiasColores = [];
                                        $colorIndex = 1;
                                        foreach($horarios as $horario) {
                                            $materia = $horario->materias->first();
                                            if ($materia && !isset($materiasColores[$materia->sigla])) {
                                                $materiasColores[$materia->sigla] = 'color-' . $colorIndex;
                                                $colorIndex = ($colorIndex % 10) + 1;
                                            }
                                        }
                                    @endphp
                                    
                                    <div class="table-responsive">
                                        <table class="horario-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 120px;">HORARIO</th>
                                                    @foreach($diasAbrev as $dia)
                                                        <th>{{ $dia }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($intervalos as $intervaloKey => $intervaloData)
                                                    <tr>
                                                        <td>{{ $intervaloKey }}</td>
                                                        @foreach($diasSemana as $nombreDia)
                                                            <td>
                                                @php
                                                    $horarioEncontrado = null;
                                                    foreach($horarios as $horario) {
                                                        $tieneDia = $horario->dias->contains('nombre', $nombreDia);
                                                        
                                                        if ($tieneDia) {
                                                            $horarioIni = \Carbon\Carbon::parse($horario->horaini);
                                                            $horarioFin = \Carbon\Carbon::parse($horario->horafin);
                                                            $horarioIniMinutos = $horarioIni->hour * 60 + $horarioIni->minute;
                                                            $horarioFinMinutos = $horarioFin->hour * 60 + $horarioFin->minute;
                                                            
                                                            // Verificar si el horario se solapa con este intervalo
                                                            if ($horarioIniMinutos <= $intervaloData['minutos_inicio'] && 
                                                                $horarioFinMinutos >= $intervaloData['minutos_fin']) {
                                                                $horarioEncontrado = $horario;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                @endphp                                                                @if($horarioEncontrado)
                                                                    @php
                                                                        $materia = $horarioEncontrado->materias->first();
                                                                        $aula = $horarioEncontrado->aula;
                                                                        $modulo = $aula && $aula->modulo ? $aula->modulo->codigo : '';
                                                                        $colorClass = $materia ? ($materiasColores[$materia->sigla] ?? 'color-1') : 'color-1';
                                                                    @endphp
                                                                    
                                                                    <div class="horario-block {{ $colorClass }}" 
                                                                         title="Docente: {{ $horarioEncontrado->materias->first() ? 
                                                                            (\App\Models\GrupoMateria::where('id_grupo', $grupoSeleccionado->id)
                                                                                ->where('sigla_materia', $horarioEncontrado->materias->first()->sigla)
                                                                                ->first()?->docente?->nombre ?? 'Sin asignar') : 'Sin asignar' }}">
                                                                        <div class="horario-materia">
                                                                            {{ $materia ? $materia->sigla : '' }}
                                                                        </div>
                                                                        <div class="horario-aula">
                                                                            {{ $modulo ? $modulo . '-' : '' }}{{ $aula ? $aula->nroaula : '' }}
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- Leyenda de materias -->
                                    @if(count($materiasColores) > 0)
                                        <div class="mt-3 p-3 bg-light rounded">
                                            <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Leyenda de Materias</h6>
                                            <div class="row">
                                                @foreach($materiasColores as $siglaMateria => $colorClass)
                                                    @php
                                                        $materiaInfo = \App\Models\Materia::where('sigla', $siglaMateria)->first();
                                                        $gm = \App\Models\GrupoMateria::where('id_grupo', $grupoSeleccionado->id)
                                                            ->where('sigla_materia', $siglaMateria)
                                                            ->first();
                                                        $docenteNombre = $gm?->docente?->nombre ?? 'Sin docente asignado';
                                                    @endphp
                                                    <div class="col-md-4 col-sm-6 mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <div class="horario-block {{ $colorClass }}" style="width: 60px; height: 50px; min-height: 50px; margin-right: 10px; flex-shrink: 0;">
                                                                <div class="horario-materia" style="font-size: 0.85rem;">{{ $siglaMateria }}</div>
                                                            </div>
                                                            <div style="flex: 1;">
                                                                <strong>{{ $materiaInfo?->nombre ?? $siglaMateria }}</strong><br>
                                                                <small class="text-muted">
                                                                    <i class="fas fa-user-tie"></i> {{ $docenteNombre }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
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

<script>
// Mejorar la función de impresión
window.addEventListener('beforeprint', function() {
    document.querySelector('.sidebar')?.classList.add('d-none');
    document.querySelector('.main-content')?.style.setProperty('margin-left', '0', 'important');
});

window.addEventListener('afterprint', function() {
    document.querySelector('.sidebar')?.classList.remove('d-none');
    document.querySelector('.main-content')?.style.removeProperty('margin-left');
});
</script>
@endsection
