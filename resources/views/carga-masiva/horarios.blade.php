@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-clock"></i> Carga Masiva de Horarios
                    </h4>
                    <a href="{{ url('carga-masiva') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver al Menú
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Mensajes -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('errores'))
                    <div class="alert alert-danger">
                        <h6 class="alert-heading"><i class="fas fa-times-circle"></i> Errores encontrados:</h6>
                        <ul class="mb-0">
                            @foreach(session('errores') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('advertencias'))
                    <div class="alert alert-warning">
                        <h6 class="alert-heading"><i class="fas fa-exclamation-circle"></i> Advertencias:</h6>
                        <ul class="mb-0">
                            @foreach(session('advertencias') as $advertencia)
                                <li>{{ $advertencia }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Información Importante -->
                <div class="alert alert-info">
                    <h5 class="alert-heading"><i class="fas fa-info-circle"></i> Información Importante</h5>
                    <ul class="mb-0">
                        <li><strong>Requisitos previos:</strong>
                            <ol>
                                <li>Los grupos deben estar creados en el sistema</li>
                                <li>Las materias deben existir en el sistema</li>
                                <li>Los docentes deben estar registrados con rol "Docente"</li>
                                <li>Los módulos y aulas deben existir en el sistema</li>
                            </ol>
                        </li>
                        <li><strong>Asignación automática:</strong> Si la materia no está asignada al grupo con el docente, el sistema creará automáticamente la relación Grupo-Materia-Docente</li>
                        <li><strong>Formato del archivo:</strong> Excel (.xlsx, .xls) o CSV</li>
                        <li><strong>Estructura de columnas:</strong>
                            <ul>
                                <li><code>sigla_grupo</code>: Sigla del grupo (ej: Z1, Z2)</li>
                                <li><code>sigla_materia</code>: Sigla de la materia (ej: INF220)</li>
                                <li><code>cod_docente</code>: Código del docente</li>
                                <li><code>dia1, dia2, dia3, dia4</code>: Días de la semana (Lun, Mar, Mie, Jue, Vie, Sab, Dom)</li>
                                <li><code>horario1, horario2, horario3, horario4</code>: Horarios en formato HH:MM-HH:MM (ej: 7:00-8:30)</li>
                                <li><code>local1, local2, local3, local4</code>: Formato CódigoMódulo-NroAula (ej: 236-11)</li>
                            </ul>
                        </li>
                        <li><strong>Nota:</strong> Puede especificar hasta 4 días/horarios diferentes por materia en cada fila</li>
                    </ul>
                </div>

                <!-- Grupos Disponibles -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-users"></i> Grupos Disponibles en el Sistema</h6>
                    </div>
                    <div class="card-body">
                        @if($grupos->count() > 0)
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($grupos as $grupo)
                                    <span class="badge bg-success">
                                        {{ $grupo->sigla }}
                                        @if($grupo->periodo)
                                            ({{ $grupo->periodo->abreviatura }})
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">No hay grupos registrados. <a href="{{ route('grupos.create') }}">Crear grupo</a></p>
                        @endif
                    </div>
                </div>

                <!-- Formulario de Carga -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-upload"></i> Cargar Archivo de Horarios</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('carga-masiva.horarios.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="archivo" class="form-label">
                                    Seleccione el archivo <span class="text-danger">*</span>
                                </label>
                                <input type="file" 
                                       class="form-control @error('archivo') is-invalid @enderror" 
                                       id="archivo" 
                                       name="archivo" 
                                       accept=".xlsx,.xls,.csv"
                                       required>
                                @error('archivo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Formatos permitidos: .xlsx, .xls, .csv (máximo 10 MB)
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('carga-masiva.horarios.plantilla') }}" class="btn btn-outline-success">
                                    <i class="fas fa-download"></i> Descargar Plantilla de Ejemplo
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-file-import"></i> Procesar Archivo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Ejemplo de Datos -->
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-table"></i> Ejemplo de Estructura del Archivo</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-success">
                                    <tr>
                                        <th>sigla_grupo</th>
                                        <th>sigla_materia</th>
                                        <th>cod_docente</th>
                                        <th>dia1</th>
                                        <th>horario1</th>
                                        <th>local1</th>
                                        <th>dia2</th>
                                        <th>horario2</th>
                                        <th>local2</th>
                                        <th>dia3</th>
                                        <th>horario3</th>
                                        <th>local3</th>
                                        <th>dia4</th>
                                        <th>horario4</th>
                                        <th>local4</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Z1</td>
                                        <td>INF220</td>
                                        <td>107</td>
                                        <td>Lun</td>
                                        <td>7:00-8:30</td>
                                        <td>236-11</td>
                                        <td>Mie</td>
                                        <td>7:00-8:30</td>
                                        <td>236-11</td>
                                        <td>Vie</td>
                                        <td>7:00-8:30</td>
                                        <td>236-11</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>Z1</td>
                                        <td>MAT101</td>
                                        <td>112</td>
                                        <td>Lun</td>
                                        <td>8:30-10:00</td>
                                        <td>236-12</td>
                                        <td>Mie</td>
                                        <td>8:30-10:00</td>
                                        <td>236-12</td>
                                        <td>Vie</td>
                                        <td>8:30-10:00</td>
                                        <td>236-12</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>Z2</td>
                                        <td>INF220</td>
                                        <td>115</td>
                                        <td>Mar</td>
                                        <td>9:15-11:30</td>
                                        <td>236-13</td>
                                        <td>Jue</td>
                                        <td>9:15-11:30</td>
                                        <td>236-13</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>Z2</td>
                                        <td>FIS100</td>
                                        <td>108</td>
                                        <td>Lun</td>
                                        <td>10:00-11:30</td>
                                        <td>236-15</td>
                                        <td>Mie</td>
                                        <td>10:00-11:30</td>
                                        <td>236-15</td>
                                        <td>Vie</td>
                                        <td>10:00-11:30</td>
                                        <td>236-15</td>
                                        <td>Vie</td>
                                        <td>18:00-19:00</td>
                                        <td>260-11</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-success mt-3 mb-0">
                            <strong><i class="fas fa-magic"></i> Funcionalidad Automática:</strong>
                            <ul class="mb-0">
                                <li>Cada fila representa una <strong>materia asignada a un grupo con un docente</strong></li>
                                <li>Si la asignación Grupo-Materia-Docente <strong>no existe</strong>, el sistema la <strong>creará automáticamente</strong></li>
                                <li>Puede especificar hasta <strong>4 horarios diferentes</strong> (días) para la misma materia</li>
                                <li>Si una materia se imparte solo 2 días, deje los campos dia3, horario3, local3, dia4, horario4, local4 vacíos</li>
                                <li>El formato de <strong>horario</strong> debe ser: <code>HH:MM-HH:MM</code></li>
                                <li>El formato de <strong>local</strong> debe ser: <code>CódigoMódulo-NroAula</code> (ejemplo: 236-11)</li>
                                <li>El sistema calcula automáticamente la duración de cada clase en minutos</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
