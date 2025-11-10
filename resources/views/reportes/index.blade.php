@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-chart-bar"></i> Generar y Exportar Reportes</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Seleccione el tipo de reporte que desea generar y el formato de exportación.</p>

                    <!-- Tabs para tipos de reportes -->
                    <ul class="nav nav-tabs mb-4" id="reporteTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="horarios-tab" data-bs-toggle="tab" data-bs-target="#horarios" type="button" role="tab">
                                <i class="fas fa-calendar-week"></i> Horarios Semanales
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="carga-tab" data-bs-toggle="tab" data-bs-target="#carga" type="button" role="tab">
                                <i class="fas fa-briefcase"></i> Carga Horaria
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="asistencia-tab" data-bs-toggle="tab" data-bs-target="#asistencia" type="button" role="tab">
                                <i class="fas fa-check-circle"></i> Asistencia
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="aulas-tab" data-bs-toggle="tab" data-bs-target="#aulas" type="button" role="tab">
                                <i class="fas fa-door-open"></i> Aulas Disponibles
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="reporteTabsContent">
                        <!-- Reporte de Horarios Semanales -->
                        <div class="tab-pane fade show active" id="horarios" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Reporte de Horarios Semanales</h5>
                                    <p class="text-muted">Genera un reporte detallado de los horarios organizados por día de la semana.</p>
                                    
                                    <form action="{{ url('reportes/horarios-semanal') }}" method="POST" target="_blank">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="horarios_docente" class="form-label">Docente (Opcional)</label>
                                                <select name="id_docente" id="horarios_docente" class="form-select">
                                                    <option value="">Todos los docentes</option>
                                                    @foreach($docentes as $docente)
                                                        <option value="{{ $docente->id }}">
                                                            {{ $docente->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="horarios_grupo" class="form-label">Grupo (Opcional)</label>
                                                <select name="id_grupo" id="horarios_grupo" class="form-select">
                                                    <option value="">Todos los grupos</option>
                                                    @foreach($grupos as $grupo)
                                                        <option value="{{ $grupo->id }}">{{ $grupo->sigla }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="horarios_formato" class="form-label">Formato <span class="text-danger">*</span></label>
                                                <select name="formato" id="horarios_formato" class="form-select" required>
                                                    <option value="pdf">PDF</option>
                                                    <option value="excel">Excel (.xlsx)</option>
                                                    <option value="csv">CSV</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-download"></i> Generar Reporte
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Reporte de Carga Horaria -->
                        <div class="tab-pane fade" id="carga" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Reporte de Carga Horaria</h5>
                                    <p class="text-muted">Genera un reporte con la carga horaria de los docentes (total de períodos y materias asignadas).</p>
                                    
                                    <form action="{{ url('reportes/carga-horaria') }}" method="POST" target="_blank">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="carga_docente" class="form-label">Docente (Opcional)</label>
                                                <select name="id_docente" id="carga_docente" class="form-select">
                                                    <option value="">Todos los docentes</option>
                                                    @foreach($docentes as $docente)
                                                        <option value="{{ $docente->id }}">
                                                            {{ $docente->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="carga_formato" class="form-label">Formato <span class="text-danger">*</span></label>
                                                <select name="formato" id="carga_formato" class="form-select" required>
                                                    <option value="pdf">PDF</option>
                                                    <option value="excel">Excel (.xlsx)</option>
                                                    <option value="csv">CSV</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-download"></i> Generar Reporte
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Reporte de Asistencia -->
                        <div class="tab-pane fade" id="asistencia" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Reporte de Asistencia por Docente y Grupo</h5>
                                    <p class="text-muted">Genera un reporte detallado de asistencias con estadísticas de presentes, ausentes, licencias y retrasos.</p>
                                    
                                    <form action="{{ url('reportes/asistencia') }}" method="POST" target="_blank">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label for="asistencia_docente" class="form-label">Docente (Opcional)</label>
                                                <select name="id_docente" id="asistencia_docente" class="form-select">
                                                    <option value="">Todos los docentes</option>
                                                    @foreach($docentes as $docente)
                                                        <option value="{{ $docente->id }}">
                                                            {{ $docente->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="asistencia_grupo" class="form-label">Grupo (Opcional)</label>
                                                <select name="id_grupo" id="asistencia_grupo" class="form-select">
                                                    <option value="">Todos los grupos</option>
                                                    @foreach($grupos as $grupo)
                                                        <option value="{{ $grupo->id }}">{{ $grupo->sigla }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <label for="asistencia_inicio" class="form-label">Fecha Inicio</label>
                                                <input type="date" name="fecha_inicio" id="asistencia_inicio" class="form-control">
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <label for="asistencia_fin" class="form-label">Fecha Fin</label>
                                                <input type="date" name="fecha_fin" id="asistencia_fin" class="form-control">
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <label for="asistencia_formato" class="form-label">Formato <span class="text-danger">*</span></label>
                                                <select name="formato" id="asistencia_formato" class="form-select" required>
                                                    <option value="pdf">PDF</option>
                                                    <option value="excel">Excel (.xlsx)</option>
                                                    <option value="csv">CSV</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-download"></i> Generar Reporte
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Reporte de Aulas Disponibles -->
                        <div class="tab-pane fade" id="aulas" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Reporte de Aulas Disponibles</h5>
                                    <p class="text-muted">Genera un reporte con la disponibilidad y ocupación de las aulas.</p>
                                    
                                    <form action="{{ url('reportes/aulas-disponibles') }}" method="POST" target="_blank">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="aulas_dia" class="form-label">Día (Opcional)</label>
                                                <select name="id_dia" id="aulas_dia" class="form-select">
                                                    <option value="">Todos los días</option>
                                                    @foreach($dias as $dia)
                                                        <option value="{{ $dia->id }}">{{ $dia->nombre }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="aulas_formato" class="form-label">Formato <span class="text-danger">*</span></label>
                                                <select name="formato" id="aulas_formato" class="form-select" required>
                                                    <option value="pdf">PDF</option>
                                                    <option value="excel">Excel (.xlsx)</option>
                                                    <option value="csv">CSV</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-download"></i> Generar Reporte
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
