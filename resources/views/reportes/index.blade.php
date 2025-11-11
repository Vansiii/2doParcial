@extends('layouts.app')

@section('content')
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
                            <button class="nav-link active" id="personalizado-tab" data-bs-toggle="tab" data-bs-target="#personalizado" type="button" role="tab">
                                <i class="fas fa-sliders-h"></i> Reporte Personalizado
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="horarios-tab" data-bs-toggle="tab" data-bs-target="#horarios" type="button" role="tab">
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
                        <!-- Reporte Personalizado -->
                        <div class="tab-pane fade show active" id="personalizado" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-sliders-h"></i> Reporte Personalizado</h5>
                                    <p class="text-muted">Seleccione el tipo de reporte y las columnas que desea incluir.</p>
                                    
                                    <form id="formReportePersonalizado" action="{{ url('reportes/personalizado') }}" method="POST" target="_blank">
                                        @csrf
                                        
                                        <!-- Tipo de Reporte -->
                                        <div class="row mb-4">
                                            <div class="col-md-4">
                                                <label for="tipo_reporte" class="form-label fw-bold">Tipo de Reporte <span class="text-danger">*</span></label>
                                                <select name="tipo_reporte" id="tipo_reporte" class="form-select" required>
                                                    <option value="">-- Seleccione --</option>
                                                    <option value="usuarios">Usuarios</option>
                                                    <option value="materias">Materias</option>
                                                    <option value="grupos">Grupos</option>
                                                    <option value="horarios">Horarios</option>
                                                    <option value="asistencias">Asistencias</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="formato" class="form-label fw-bold">Formato <span class="text-danger">*</span></label>
                                                <select name="formato" id="formato" class="form-select" required>
                                                    <option value="pdf">PDF</option>
                                                    <option value="excel">Excel (.xlsx)</option>
                                                    <option value="csv">CSV</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Columnas Usuarios -->
                                        <div id="columnas_usuarios" class="columnas-grupo" style="display: none;">
                                            <div class="card bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title fw-bold mb-3">Seleccione las columnas a incluir:</h6>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="codigo" id="col_usr_codigo">
                                                                <label class="form-check-label" for="col_usr_codigo">Código</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="ci" id="col_usr_ci">
                                                                <label class="form-check-label" for="col_usr_ci">CI</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="nombre" id="col_usr_nombre">
                                                                <label class="form-check-label" for="col_usr_nombre">Nombre</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="correo" id="col_usr_correo">
                                                                <label class="form-check-label" for="col_usr_correo">Correo</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="telefono" id="col_usr_telefono">
                                                                <label class="form-check-label" for="col_usr_telefono">Teléfono</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="roles" id="col_usr_roles">
                                                                <label class="form-check-label" for="col_usr_roles">Roles</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label for="filtro_rol_usuarios" class="form-label">Filtrar por Rol (Opcional)</label>
                                                            <select name="id_rol" id="filtro_rol_usuarios" class="form-select">
                                                                <option value="">Todos los roles</option>
                                                                @foreach($roles as $rol)
                                                                    <option value="{{ $rol->id }}">{{ $rol->descripcion }}</option>
                                                                @endforeach
                                                            </select>
                                                            <small class="text-muted">Ejemplo: Solo Docentes, Solo Estudiantes, etc.</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Columnas Materias -->
                                        <div id="columnas_materias" class="columnas-grupo" style="display: none;">
                                            <div class="card bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title fw-bold mb-3">Seleccione las columnas a incluir:</h6>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="sigla" id="col_mat_sigla">
                                                                <label class="form-check-label" for="col_mat_sigla">Sigla</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="nombre" id="col_mat_nombre">
                                                                <label class="form-check-label" for="col_mat_nombre">Nombre</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="nivel" id="col_mat_nivel">
                                                                <label class="form-check-label" for="col_mat_nivel">Nivel</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="carreras" id="col_mat_carreras">
                                                                <label class="form-check-label" for="col_mat_carreras">Carreras</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="periodos" id="col_mat_periodos">
                                                                <label class="form-check-label" for="col_mat_periodos">Periodos</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label for="filtro_periodo_materias" class="form-label">Filtrar por Periodo Académico (Opcional)</label>
                                                            <select name="id_periodo" id="filtro_periodo_materias" class="form-select">
                                                                <option value="">Todos los periodos</option>
                                                                @foreach($periodos as $periodo)
                                                                    <option value="{{ $periodo->id }}">
                                                                        {{ $periodo->abreviatura }} ({{ $periodo->gestion }}-{{ $periodo->periodo }})
                                                                        @if($periodo->activo) <strong>[ACTIVO]</strong> @endif
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Columnas Grupos -->
                                        <div id="columnas_grupos" class="columnas-grupo" style="display: none;">
                                            <div class="card bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title fw-bold mb-3">Seleccione las columnas a incluir:</h6>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="sigla" id="col_grp_sigla">
                                                                <label class="form-check-label" for="col_grp_sigla">Sigla</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="periodo" id="col_grp_periodo">
                                                                <label class="form-check-label" for="col_grp_periodo">Periodo</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="materias" id="col_grp_materias">
                                                                <label class="form-check-label" for="col_grp_materias">Materias</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="docentes" id="col_grp_docentes">
                                                                <label class="form-check-label" for="col_grp_docentes">Docentes</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="cantidad_materias" id="col_grp_cantidad">
                                                                <label class="form-check-label" for="col_grp_cantidad">Cantidad Materias</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label for="filtro_periodo_grupos" class="form-label">Filtrar por Periodo Académico (Opcional)</label>
                                                            <select name="id_periodo" id="filtro_periodo_grupos" class="form-select">
                                                                <option value="">Todos los periodos</option>
                                                                @foreach($periodos as $periodo)
                                                                    <option value="{{ $periodo->id }}">
                                                                        {{ $periodo->abreviatura }} ({{ $periodo->gestion }}-{{ $periodo->periodo }})
                                                                        @if($periodo->activo) <strong>[ACTIVO]</strong> @endif
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Columnas Horarios -->
                                        <div id="columnas_horarios" class="columnas-grupo" style="display: none;">
                                            <div class="card bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title fw-bold mb-3">Seleccione las columnas a incluir:</h6>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="grupo" id="col_hor_grupo">
                                                                <label class="form-check-label" for="col_hor_grupo">Grupo</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="periodo" id="col_hor_periodo">
                                                                <label class="form-check-label" for="col_hor_periodo">Periodo</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="materia" id="col_hor_materia">
                                                                <label class="form-check-label" for="col_hor_materia">Materia</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="docente" id="col_hor_docente">
                                                                <label class="form-check-label" for="col_hor_docente">Docente</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="aula" id="col_hor_aula">
                                                                <label class="form-check-label" for="col_hor_aula">Aula</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="dias" id="col_hor_dias">
                                                                <label class="form-check-label" for="col_hor_dias">Días</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="hora_inicio" id="col_hor_inicio">
                                                                <label class="form-check-label" for="col_hor_inicio">Hora Inicio</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="hora_fin" id="col_hor_fin">
                                                                <label class="form-check-label" for="col_hor_fin">Hora Fin</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label for="filtro_periodo_horarios" class="form-label">Filtrar por Periodo Académico (Opcional)</label>
                                                            <select name="id_periodo" id="filtro_periodo_horarios" class="form-select">
                                                                <option value="">Todos los periodos</option>
                                                                @foreach($periodos as $periodo)
                                                                    <option value="{{ $periodo->id }}">
                                                                        {{ $periodo->abreviatura }} ({{ $periodo->gestion }}-{{ $periodo->periodo }})
                                                                        @if($periodo->activo) <strong>[ACTIVO]</strong> @endif
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Columnas Asistencias -->
                                        <div id="columnas_asistencias" class="columnas-grupo" style="display: none;">
                                            <div class="card bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title fw-bold mb-3">Seleccione las columnas a incluir:</h6>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="fecha" id="col_asis_fecha">
                                                                <label class="form-check-label" for="col_asis_fecha">Fecha</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="hora" id="col_asis_hora">
                                                                <label class="form-check-label" for="col_asis_hora">Hora</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="docente_codigo" id="col_asis_docente_codigo">
                                                                <label class="form-check-label" for="col_asis_docente_codigo">Código Docente</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="docente_ci" id="col_asis_docente_ci">
                                                                <label class="form-check-label" for="col_asis_docente_ci">CI Docente</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="docente_nombre" id="col_asis_docente_nombre">
                                                                <label class="form-check-label" for="col_asis_docente_nombre">Nombre Docente</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="grupo" id="col_asis_grupo">
                                                                <label class="form-check-label" for="col_asis_grupo">Grupo</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="periodo" id="col_asis_periodo">
                                                                <label class="form-check-label" for="col_asis_periodo">Periodo</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="materia" id="col_asis_materia">
                                                                <label class="form-check-label" for="col_asis_materia">Materia</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="aula" id="col_asis_aula">
                                                                <label class="form-check-label" for="col_asis_aula">Aula</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="columnas[]" value="tipo" id="col_asis_tipo">
                                                                <label class="form-check-label" for="col_asis_tipo">Tipo</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label for="filtro_fecha_inicio" class="form-label">Fecha Inicio (Opcional)</label>
                                                            <input type="date" name="fecha_inicio" id="filtro_fecha_inicio" class="form-control">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="filtro_fecha_fin" class="form-label">Fecha Fin (Opcional)</label>
                                                            <input type="date" name="fecha_fin" id="filtro_fecha_fin" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Mensaje de validación -->
                                        <div id="alerta_columnas" class="alert alert-warning d-none" role="alert">
                                            <i class="fas fa-exclamation-triangle"></i> Debe seleccionar al menos una columna.
                                        </div>

                                        <!-- Botón Generar -->
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="fas fa-file-export"></i> Generar Reporte Personalizado
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Reporte de Horarios Semanales -->
                        <div class="tab-pane fade" id="horarios" role="tabpanel">
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoReporteSelect = document.getElementById('tipo_reporte');
    const formReporte = document.getElementById('formReportePersonalizado');
    const alertaColumnas = document.getElementById('alerta_columnas');
    
    // Ocultar todos los grupos de columnas al inicio
    function ocultarTodasLasColumnas() {
        document.querySelectorAll('.columnas-grupo').forEach(grupo => {
            grupo.style.display = 'none';
            // Desmarcar todos los checkboxes del grupo
            grupo.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
        });
        alertaColumnas.classList.add('d-none');
    }
    
    // Mostrar columnas según el tipo de reporte seleccionado
    tipoReporteSelect.addEventListener('change', function() {
        ocultarTodasLasColumnas();
        
        const tipoSeleccionado = this.value;
        if (tipoSeleccionado) {
            const grupoColumnas = document.getElementById('columnas_' + tipoSeleccionado);
            if (grupoColumnas) {
                grupoColumnas.style.display = 'block';
            }
        }
    });
    
    // Validar que al menos una columna esté seleccionada antes de enviar
    formReporte.addEventListener('submit', function(e) {
        const tipoSeleccionado = tipoReporteSelect.value;
        
        if (!tipoSeleccionado) {
            e.preventDefault();
            alert('Debe seleccionar un tipo de reporte.');
            return false;
        }
        
        const grupoColumnas = document.getElementById('columnas_' + tipoSeleccionado);
        const columnasSeleccionadas = grupoColumnas.querySelectorAll('input[type="checkbox"]:checked');
        
        if (columnasSeleccionadas.length === 0) {
            e.preventDefault();
            alertaColumnas.classList.remove('d-none');
            alertaColumnas.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
        
        // Si todo está bien, ocultar alerta
        alertaColumnas.classList.add('d-none');
        return true;
    });
    
    // Ocultar alerta cuando se selecciona alguna columna
    document.querySelectorAll('.columnas-grupo input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const grupoActual = this.closest('.columnas-grupo');
            const columnasSeleccionadas = grupoActual.querySelectorAll('input[type="checkbox"]:checked');
            
            if (columnasSeleccionadas.length > 0) {
                alertaColumnas.classList.add('d-none');
            }
        });
    });
});
</script>
@endsection
